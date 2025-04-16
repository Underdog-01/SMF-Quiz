/* SMFQuiz */
let id_dispute = 0;
if(typeof jQuery == "undefined") {
	let headTag = document.getElementsByTagName("head")[0], jqTag = document.createElement("script");
	jqTag.src = smf_default_theme_url + "/scripts/quiz/jquery-3.7.0.min.js";
	jqTag.onload = myJQueryCode;
	headTag.appendChild(jqTag);
}
function submitPreview (item)
{
	/* @TODO reimplement the preview */
	alert(quiz_mod_preview_disabled)
}

function checkAll(selectedForm, checked)
{
	for (var i = 0; i < selectedForm.elements.length; i++)
	{
		var e = selectedForm.elements[i];
		if (e.type == 'checkbox')
			e.checked = checked;
	}
}

function show_image(imgId, selectElement, imageFolder)
{
	var imgElement = document.getElementById(imgId);
	var selectedValue = selectElement[selectElement.selectedIndex].text;
	var imageUrl = smf_default_theme_url + "/images/quiz_images/blank.gif"
	if (selectedValue != "-")
		imageUrl = smf_default_theme_url + "/images/quiz_images/" + imageFolder + "/" + selectedValue;

	imgElement.src = imageUrl;
 }

function changeQuestionType(selectedForm)
{
	switch (selectedForm.options[selectedForm.options.selectedIndex].value)
	{
		case '1':
			$("#freeTextAnswer").css("display", "none");
			$("#multipleChoiceAnswer").css("display", "block");
			$("#trueFalseAnswer").css("display", "none");
			break;
		case '2':
			$("#freeTextAnswer").css("display", "block");
			$("#multipleChoiceAnswer").css("display", "none");
			$("#trueFalseAnswer").css("display", "none");
			break;
		case '3':
			$("#freeTextAnswer").css("display", "none");
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

function verifyQuizzesChecked(selectedForm)
{
	var foundChecked = false;
	var quizIds = "";
	for (var i = 0; i < selectedForm.elements.length; i++)
	{
		var e = selectedForm.elements[i];
		if (e.type == 'checkbox')
		{
			if (e.checked)
			{
				quizIds = quizIds + e.name.substr(4) + ",";
				foundChecked = true;
			}
		}
	}
	if (foundChecked == true)
	{
		var packageName = document.getElementById("packageName").value;
		var packageDescription = document.getElementById("packageDescription").value;
		var packageAuthor = document.getElementById("packageAuthor").value;
		var packageSiteAddress = document.getElementById("packageSiteAddress").value;
		$(".quizCheckbox").prop("checked", false);
		$("#quizCheckboxes").prop("checked", false);
		let exportData = {
			quizIds: encodeURIComponent(quizIds),
			packageName: encodeURIComponent(packageName),
			packageDescription: encodeURIComponent(packageDescription),
			packageAuthor: encodeURIComponent(packageAuthor),
			packageSiteAddress: encodeURIComponent(packageAuthor),
			packageSiteAddress: encodeURIComponent(packageSiteAddress)
		};

		$.post(smf_scripturl + "?action=SMFQuizExport", exportData)
		.done(function( resultData ) {
			if (resultData) {
				location.href = smf_scripturl.slice(0, -9) + 'Sources/Quiz/Temp/' + resultData;
				console.log("Quizzes were exported ~ " + resultData);
				$(".quizCheckbox").prop("checked", false);
				$("#quizCheckboxes").prop("checked", false);
			}
			else {
				alert("Error ~ Quizzes not exported ~ Missing package name");
			}

		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert("Error ~ Quizzes not exported ~ " + errorThrown);
		})
		.always(function() {
			console.log( "finished" );
		});
		//location.href = smf_scripturl + "?action=SMFQuizExport;quizIds=" + escape(quizIds) + ";packageName=" + escape(packageName) + ";packageDescription=" + escape(packageDescription) + ";packageAuthor=" + escape(packageAuthor) + ";packageSiteAddress=" + escape(packageSiteAddress);
	}
	else
	{
		alert(quizAlertOnePackage);
		$(".quizCheckbox").prop("checked", false);
		return false;
	}
}

function submitResponse(remove)
{
	$("#disputeDialog").wrap('<form id="QuizDisputeResponseForm" action=smf_scripturl+"?action=admin;area=quiz;sa=disputes">');
	$("#disputeText").after('<input type="hidden" name="reason" value="' + encodeURIComponent($("#disputeText").val()) + '"><input type="hidden" name="id_dispute" value="' + id_dispute + '"><input type="hidden" name="remove" value="' + remove + '">');
	$.post(smf_scripturl + "?action=SMFQuizDispute", $("#QuizDisputeResponseForm").serialize())
	.done(function( resultData ) {
		alert('Dispute response submitted successfully');
		console.log("Dispute response submitted successfully ~ " + resultData);
		setTimeout(function(){window.location.href = window.location.href;}, 500);
	}).fail(function() {
		alert("Error occurred sending response ~ " + errorThrown);
	})
	.always(function() {
		console.log( "finished" );
	});
}
function showDisputeDialog()
{
	$("#disputeText").val("");
	$("#disputeDialog").dialog({
		closeOnEscape: true,
		closeText: "",
		draggable: false,
		modal: false,
		resizable: false,
		show: { effect: "blind", duration: 400 },
		title: "Submit response",
		create: function(event, ui) {
			var widget = $(this).dialog("widget");
			$(".ui-dialog-titlebar-close span", widget).css({"filter":"brightness(85%) invert(1)","opacity":"1.0","margin":"0 auto","width":"100%","height":"100%"});
			$(".ui-dialog").css("z-index","1000");
			$(".ui-front").css("z-index","1000");
		},
		buttons: [
			{
				text: quizSendButton,
				showText: true,
				click: function() {
					submitResponse(0);
					$(this).dialog("close");
				}
			},
			{
				text: quizSendRemoveButton,
				showText: true,
				click: function() {
					submitResponse(1);
					$(this).dialog("close");
				}
			},
			{
				text: quizCancelButton,
				click: function() {
					$(this).dialog("close");
				}
			}
		]
	});
	$("#disputeDialog").dialog('open');

}

function QuizCreateNewTrigger() {
	$("#addNewQuiz").trigger("click");
}

$("#fileToUpload").on("click", function(){
	$(this).prop("accept", "image/png, image/gif, image/jpeg, image/bmp");
});

function ajaxFileUpload(subFolder) {
	/* start setting some animation when the ajax starts and completes */
	$(".preview_loading").each(function(event) {
		$(this).show();
	});
	let filenamex = $("#fileToUpload").val().replace(/\\/g, "/"), postFile = $("#fileToUpload").prop("files")[0], exportData = new FormData();
	filenamex = filenamex.substring(filenamex.lastIndexOf("/") + 1);
	exportData.append("fileToUpload", postFile);
	$.ajax({
		url: smf_scripturl + "?action=SMFQuizAjax;sa=imageUpload;xml;imageFolder="+subFolder,
		type: "POST",
		data: exportData,
		contentType: false,
		processData: false,
		error: function() {
			$(".preview_loading").each(function(event) {
				$(this).hide();
			});
			alert("Error uploading image file");
		},
		success: function(result) {
			$(".preview_loading").each(function(event) {
				$(this).hide();
			});
			refreshImageList(subFolder, filenamex);
			$("#icon").prop("src", smf_default_theme_url + "/images/quiz_images/" + subFolder + "/" + filenamex);
			$("#fileToUpload").val(null);
			$("#fileToUpload").prop("files")[0] = "";
		}
	});
	return false;
}

/* Refreshes all images in the image dropdown box */
function refreshImageList(subFolder, sel_file) {
	$("#imageList").removeOption(/./);
	$("#imageList").addOption("-", "-", sel_file == undefined);
	$.ajax({
		url: smf_scripturl + "?action=SMFQuizAjax;sa=imageList;xml;imageFolder="+ subFolder,
		type: "GET",
		dataType: "xml",
		timeout: 2000,
		error: function() {
			alert("Error loading XML file list");
		},
		success: function(xml) {
			$(xml).find("file").each(function() {
				var item_text = $(this).text();
				$("#imageList").addOption(item_text, item_text, sel_file != undefined && sel_file == item_text);
			});
		}
	});
}

function clearResults(thisform)
{
	thisform.formaction.value = "resetQuizzes";
	if(confirm(quizResetAllQuizData))
		thisform.submit();
	else
		return false;
}

$(document).ready(function(){
	$(".quizAdminFormButton").on("click", function() {
		let exportData = [{ name: smf_session_var, value: smf_session_id}], quizAction = $(this).attr("data-new_action");
		$.post(quizAction, exportData)
		.done(function( resultData ) {
			if (resultData) {
				location.href = location.href;
				console.log("Quiz admin option: " + quizAction + " selected ~ " + resultData);
			}
			else {
				alert("Error ~ Quiz admin option: " + quizAction);
			}

		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert("Error ~ Quiz admin option: " + quizAction + " ~ " + errorThrown);
		})
		.always(function() {
			console.log("Quiz admin option: " + quizAction + " finished");
		});
	});
	$(".disputeDialog").click(function() {
		id_dispute = this.id;
		showDisputeDialog();
	});
	$("#DeleteQuizDispute").click(function(){
		let checkDels = false;
		$("input.quiz_disputes:checked").each(function(){
			checkDels = true;
		});
		if (checkDels) {
			$("#DeleteQuizDispute").css("display","none");
			$("#quizReportDialog").dialog({
				closeOnEscape: true,
				closeText: "",
				draggable: false,
				modal: false,
				resizable: false,
				show: { effect: "blind", duration: 400 },
				title: "Submit response",
				create: function(event, ui) {
					var widget = $(this).dialog("widget");
					$(".ui-dialog-titlebar-close span", widget).css({"filter":"brightness(85%) invert(1)","opacity":"1.0","margin":"0 auto","width":"100%","height":"100%"});
					$(".ui-dialog").css("z-index","1000");
					$(".ui-front").css("z-index","1000");
				},
				buttons: [
					{
						text: quizConfirmButton,
						showText: true,
						click: function() {
							submitQuizDisputes();
							$(this).dialog("close");
						}
					},
					{
						text: quizCancelButton,
						click: function() {
							$("#DeleteQuizDispute").css("display","block");
							$(this).dialog("close");
						}
					}
				]
			});
			$("#quizReportDialog").dialog("open");
		}
	});
	/* Note: localization of console log not necessary */
	function submitQuizDisputes()
	{
		$.post(smf_scripturl + "?action=admin;area=quiz;sa=deldisputes", $("#QuizDisputeDelForm").serialize())
		.done(function( resultData ) {
			console.log("Disputes were deleted ~ " + resultData);
			setTimeout(function(){window.location.href = window.location.href;}, 500);
		}).fail(function() {
			alert("Error ~ Disputes not deleted ~ " + errorThrown);
		})
		.always(function() {
			console.log( "finished" );
		});
	}
	if ($("input#quizCheckboxes") && !$(".quizCheckbox").length) {
		$("input#quizCheckboxes").prop("type", "hidden");
		$('label[for="quizCheckboxes"]').hide();
	}
	$("#quizCheckboxes").on("click", function(){
		if ($("#quizCheckboxes").is(':checked')) {
			$(".quizCheckbox").prop("checked", true);
		}
		else {
			$(".quizCheckbox").prop("checked", false);
		}
	});
	$("#addQuizButton").on("click", function(){
		let $tr = $("#moreQuizzes > tbody > tr:last");
		let addQuizButton = "imported_quiz" + (parseInt($tr.find("input").attr("id").match(/\d+/))+1);
		let $clone = $tr.clone();
		$clone.find("input").attr("id", addQuizButton);
		$clone.find("a").attr("onclick", "cleanFileInput('" + addQuizButton + "')");
		$("#moreQuizzes > tbody").append($clone);
		return true;
	});
	/* Select element for categories that have children */
	$('#quizParentCat').on('change', function(index) {
		if (typeof($('option:selected', this).data('quizcatid')) !== "undefined") {
			let quizParentCat = $('option:selected', this).data('quizcatid');
			switch(quizParentCat) {
				case 0:
					window.location.href = smf_scripturl + "?index.php;action=admin;area=quiz;sa=categories";
					break;
				default:
					window.location.href = smf_scripturl + "?index.php;action=admin;area=quiz;sa=categories;children=" + String(quizParentCat);
			}
		}
		else {
			window.location.href = smf_scripturl + "?index.php;action=admin;area=quiz;sa=categories";
		}
	});
});