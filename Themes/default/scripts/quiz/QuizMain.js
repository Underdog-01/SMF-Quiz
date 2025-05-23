/* SMFQuiz */
let quiz_search_wait = false, quiz_search_url = smf_scripturl + "?action=SMFQuiz;sa=search;xml", quiz_search_divQ = "quick_div";
/* force detail window cells to have a uniform row height (2 cells per row) */
$(document).ready(function(){
	quizSearchLoader();
	let heightQuizLeft = 0, heightQuizRight = 0;
	$(".quizdetailwindow").each(function(cellindex) {
		$(this).attr("id", "quizDetailCell" + cellindex);
		if ((cellindex % 2) != 0) {
			heightQuizRight = Math.ceil($(this).height());
			if (heightQuizLeft > heightQuizRight) {
				$(this).height(heightQuizLeft);
				$(this).css("min-height", String(heightQuizLeft) + "px");
				$("#quizDetailCell" + (cellindex-1)).css("min-height", String(heightQuizLeft) + "px");
				$("#quizDetailCell" + (cellindex-1)).height(heightQuizLeft);
			}
			else {
				$("#quizDetailCell" + (cellindex-1)).height(heightQuizRight);
				$("#quizDetailCell" + (cellindex-1)).css("min-height", String(heightQuizRight) + "px");
				$(this).css("min-height", String(heightQuizRight) + "px");
				$(this).height(heightQuizRight);
			}
		}
		else {
			heightQuizLeft = Math.ceil($(this).height());
		}
	});
	$("#quick_name").keydown(function(e){
		if (e.key === "Enter") {
			e.preventDefault();
			quizSearchSubmit();
		}
	});
	$(".quizNewUserAction").on("click", function() {
		let exportData = [{ name: smf_session_var, value: smf_session_id}], quizAction = $(this).attr("data-new_action"), quizMsg = '';
		let searchParams = new URLSearchParams(quizAction);
		let searchAction = searchParams.has('action') ? searchParams.get('action') : quizAction;

		$.post(quizAction, exportData)
		.done(function( resultData ) {
			if (resultData) {
				quizMsg = quizFormAction.replace('[--action--]', searchAction).replace('[--msg--]', resultData);
				console.log(quizMsg);
				location.href = location.href;
			}
			else {
				quizMsg = quizFormActionError.replace('[--action--]', searchAction).replace('[--msg--]', exportData);
				alert(quizMsg);
			}

		}).fail(function(jqXHR, textStatus, errorThrown) {
			quizMsg = quizFormActionError.replace('[--action--]', searchAction).replace('[--msg--]', errorThrown);
			alert(quizMsg);
		})
		.always(function() {
			quizMsg = quizFormActionDone.replace('[--action--]', searchAction);
			console.log(quizMsg);
		});
	});
	$("input#quizUserImageFile:file").change(function (){
		let fileName = $(this).val(), fileData = $("input#quizUserImageFile")[0].files[0], quizMsg = '';
		if (fileData && fileData.size <= parseInt(quizImageSize)) {
			$("input[name='formaction']").val("userQuizImage");
			let exportData = new FormData(), quizAction = $(this).attr("data-new_action"), quizRegex = /\.(jpg|jpeg|gif|png|bmp)$/i;
			let searchParams = new URLSearchParams(quizAction.replace(';', '&'));
			let searchAction = searchParams.has('sa') ? searchParams.get('sa') : quizAction;
			exportData.append("quizUserImageFile", $(this).prop("files")[0]);
			exportData.append(smf_session_var, smf_session_id);
			exportData.append("formaction", "userQuizImage");
			if (confirm(quizImageConfirm.replace('[--image--]', $(this).val().replace(/.*(\/|\\)/, '')))) {
				$.ajax({
					type: "POST",
					url: quizAction,
					data: exportData,
					processData: false,
					contentType: false,
					success: function(resultData) {
						if (quizRegex.test(resultData.toLowerCase())) {
							$("#imageList").append('<option value="' + resultData + '" selected="selected">' + resultData + '</option>');
							$("#imageList").val(resultData).change();
							quizMsg = quizFormAction.replace('[--action--]', searchAction).replace('[--msg--]', resultData);
							console.log(quizMsg);
							$("input#quizUserImageFile").val("");
						}
						else {
							console.log(quizImageFileError + " ~ " + resultData);
							alert(quizImageFileError + " ~ " + resultData);
							location.href = location.href;
						}
					},
					error: function(errorThrown) {
						quizMsg = quizFormActionError.replace('[--action--]', searchAction).replace('[--msg--]', errorThrown);
						console.log(quizMsg);
						alert(quizMsg);
						location.href = location.href;
					}
				});
			}
			else {
				location.href = location.href;
			}
		}
		else {
			alert(quizImageFileError);
			location.href = location.href;
		}
	});
});
function quizSearchLoader() {
	if ($("#quick_name")) {
		var quizSearchTrigger = $("#quick_name");
		sessionStorage.setItem("quizQuickNameVal", $.trim(quizSearchTrigger.val()));
		$("#quick_name").on("keypress", function(){
			$("#"+ quiz_search_divQ).html("");
			QuizQuickSearch();
			setTimeout(function(){
				let quick_name = $.trim($("#quick_name").val());
				if (sessionStorage.getItem("quizQuickNameVal") != quick_name)
					QuizQuickSearch();
			}, 2000);
		});
		setInterval(function(){
			let quick_name = $.trim($("#quick_name").val());
			if (quick_name == "")
				$("#"+ quiz_search_divQ).html("");
		}, 5000);
	}
}
function QuizQuickSearch()
{
	if (quiz_search_wait) {
		setTimeout(function(){QuizQuickSearch();}, 800);
		return 1;
	}

	quiz_search_wait = true;
	setInterval(function(){resetWait();}, 800);

	var i, x = new Array();
	var n =  $.trim($("#quick_name").val());
	x[0] = "name=" + encodeURIComponent(textToEntities(n.replace(/&#/g, "&#38;#"))).replace(/\+/g, "%2B")
	sendXMLDocument(quiz_search_url, x.join("&"), onQuizSearch);
	ajax_indicator(true);
}

function textToEntities(text)
{
	var entities = "";
	for (var i = 0; i < text.length; i++)
	{
		if (text.charCodeAt(i) > 127)
			entities += "&#" + text.charCodeAt(i) + ";";
		else
			entities += text.charAt(i);
	}

	return entities;
}

function decodeQuizHTML(html) {
	var txt = document.createElement("textarea");
	txt.innerHTML = html;
	return txt.value;
}

function resetWait() {
	sessionStorage.setItem("quizQuickNameVal", "");
}

function onQuizSearch(XMLDoc)
{
	if (!XMLDoc)
		$("#" + quiz_search_divQ).text(quizGeneralErrorText);
	else {
		quiz_search_wait = false;
		let quizzes = XMLDoc.getElementsByTagName("quiz"), $searchMainDiv = $("#" + quiz_search_divQ), quizVal = "", i=0;
		$searchMainDiv.html("");
		for (i = 0; i < quizzes.length; i++) {
			quizVal = $.trim(quizzes[i].getElementsByTagName("url")[0].firstChild.nodeValue);
			$searchMainDiv.not("a[href=\'" + quizVal + "\']");
			$searchMainDiv.append('<div><a href="' +  quizVal + '" title="' +  $.trim(decodeQuizHTML(quizzes[i].getElementsByTagName("name")[0].firstChild.nodeValue)) + '">' +  $.trim(decodeQuizHTML(quizzes[i].getElementsByTagName("name")[0].firstChild.nodeValue)) + '</div>');
		}
	}
	ajax_indicator(false);
}
function quizSearchSubmit() {
	if ($("#quick_name") && $.trim($("#quick_name").val()) != "") {
		window.location.href = smf_scripturl + "?action=SMFQuiz;search=" + encodeURIComponent($.trim($("#quick_name").val()));
	}
	else if ($("#quick_name")) {
		alert(quizSearchNoText);
	}
}

function validateQuiz(form, action)
{
	if ($("#title") && $("#title").val() != "") {
		$("#formaction").val(action);
		form.submit();
	}
	else {
		alert(quizNoTitle);
		$("#title").focus();
	}
}

function checkAll(selectedForm, checked)
{
	for (var i = 0; i < selectedForm.elements.length; i++)
	{
		var e = selectedForm.elements[i];
		if (e.type == 'checkbox') {
			e.checked = checked;
		}
	}
}

function changeQuestionType(selectedForm)
{
	switch (selectedForm.options[selectedForm.options.selectedIndex].value)
	{
		case '1':
			$("#freeTextAnswerdiv").css("display", "none");
			$("#multipleChoiceAnswer").css("display", "block");
			$("#trueFalseAnswer").css("display", "none");
			break;
		case '2':
			$("#freeTextAnswerdiv").css("display", "block");
			$("#multipleChoiceAnswer").css("display", "none");
			$("#trueFalseAnswer").css("display", "none");
			break;
		case '3':
			$("#freeTextAnswerdiv").css("display", "none");
			$("#multipleChoiceAnswer").css("display", "none");
			$("#trueFalseAnswer").css("display", "block");
			break;
	}
}

function addRow()
{
	let rowCount = $('#answerTable tr').length-1;
	$('#answerTable').append('<tr><td><input type="radio" name="correctAnswer" value="' + rowCount + '"></td><td><input type="text" name="answer' + rowCount + '" size="50"></td></tr>');
}

function deleteRow()
{
	/* Restrict them to leave at least 1 row */
	if ($('#answerTable tr') && $('#answerTable tr').length > 1) {
		$('#answerTable tr:last').remove();
	}
}

function validateQuestion(form, action)
{
	let validated = false;

	if ($("#question_text") && $("#question_text").val() == "") {
		alert(quizQuestionNoTitle);
		$("#question_text").focus();
	}
	else if ($("#id_question_type")) {
		switch ($("#id_question_type").val())
		{
			case "1":
			case "3":
				validated = true;
				break;
			case "2":
				if ($("#freeTextAnswer") && $("#freeTextAnswer").val() == "") {
					alert(quizNoTextAnswer);
					$("#freeTextAnswer").focus();
				}
				else {
					validated = true;
				}
				break;
		}
	}

	if (validated) {
		$("#formaction").val(action);
		form.submit();
	}
}

function show_image(imgId, selectElement, imageFolder)
{
	var imgElement = document.getElementById(imgId);
	var selectedValue = selectElement[selectElement.selectedIndex].text;
	var imageUrl = smf_default_theme_url + "/images/quiz_images/blank.gif";
	if (selectedValue != "-")
		imageUrl = smf_default_theme_url + "/images/quiz_images/" + imageFolder + "/" + selectedValue;

	imgElement.src = imageUrl;
}