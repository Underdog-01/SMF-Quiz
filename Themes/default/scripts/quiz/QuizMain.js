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
	$(".quizDelUserQuiz").on("click", function() {
		let exportData = [{ name: smf_session_var, value: smf_session_id}], quizAction = $(this).attr("data-new_action");
		$.post(quizAction, exportData)
		.done(function( resultData ) {
			if (resultData) {
				location.href = location.href;
				console.log("Quiz was deleted ~ " + resultData);
			}
			else {
				alert("Error ~ Quiz not deleted");
			}

		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert("Error ~ Quiz not deleted ~ " + errorThrown);
		})
		.always(function() {
			console.log( "Quiz deletion finished" );
		});
	});
});
function quizSearchLoader() {
	if ($("#quick_name").length) {
		var quizSearchTrigger = $("#quick_name");
		sessionStorage.setItem("quizQuickNameVal", quizSearchTrigger.val().trim());
		if (quizSearchTrigger) {
			quizSearchTrigger.onkeypress = function(){
				QuizQuickSearch();
				setTimeout(function(){
					let quick_name = $("#quick_name").val().trim();
					if (sessionStorage.getItem("quizQuickNameVal") != quick_name)
						QuizQuickSearch();
				}, 2000);
			};
		}
		setInterval(function(){
			let quick_name = $("#quick_name").val().trim();
			if (quick_name == "")
				$("#quiz_search_divQ").html("");
		}, 5000);
	}
}
function QuizQuickSearch()
{
	if (quiz_search_wait) // Wait before new search.
	{
		setTimeout(function(){QuizQuickSearch();}, 800);
		return 1;
	}

	quiz_search_wait = true;
	setInterval(function(){resetWait();}, 800);

	var i, x = new Array();
	var n = document.getElementById("quick_name").value.trim();
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

}
function onQuizSearch(XMLDoc)
{
	if (!XMLDoc)
		document.getElementById(quiz_search_divQ).textContent = "Error";
	else {
		quiz_search_wait = false;
		var quizzes = XMLDoc.getElementsByTagName("quiz");
		var addNewNode = [], addNewLink = [], addNewText = [],searchDiv = document.createElement("DIV"), searchMainDiv = document.getElementById(quiz_search_divQ), i=0;
		for (i = 0; i < quizzes.length; i++) {
			addNewNode[i] = document.createElement("div");
			addNewLink[i] = document.createElement("a");
			addNewLink[i].href = quizzes[i].getElementsByTagName("url")[0].firstChild.nodeValue;
			addNewText[i] = document.createTextNode(decodeQuizHTML(quizzes[i].getElementsByTagName("name")[0].firstChild.nodeValue));
			addNewLink[i].appendChild(addNewText[i]);
			addNewNode[i].appendChild(addNewLink[i]);
			searchDiv.appendChild(addNewNode[i]);
		}
		searchMainDiv.innerHTML = searchDiv.innerHTML;
	}
	ajax_indicator(false);
}
function quizSearchSubmit() {
	let searchVal = $("#quick_name").val();
	window.location.href = smf_scripturl + "?action=SMFQuiz;search=" + searchVal;
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
			document.getElementById("freeTextAnswerdiv").style.display = 'none';
			document.getElementById("multipleChoiceAnswer").style.display = 'block';
			document.getElementById("trueFalseAnswer").style.display = 'none';
			break;
		case '2':
			document.getElementById("freeTextAnswerdiv").style.display = 'block';
			document.getElementById("multipleChoiceAnswer").style.display = 'none';
			document.getElementById("trueFalseAnswer").style.display = 'none';
			break;
		case '3':
			document.getElementById("freeTextAnswerdiv").style.display = 'none';
			document.getElementById("multipleChoiceAnswer").style.display = 'none';
			document.getElementById("trueFalseAnswer").style.display = 'block';
			break;
	}
}

function addRow()
{
	var rowCount = document.getElementById("answerTable").rows.length;
	var radioElement = document.createElement("input");
	radioElement.setAttribute("name", "correctAnswer");
	radioElement.setAttribute("value", rowCount);
	radioElement.setAttribute("type", "radio");

	var answerElement = document.createElement("input");
	answerElement.setAttribute("name", "answer" + rowCount);
	answerElement.setAttribute("size", "50");
	answerElement.setAttribute("type", "text");

	var tbody = document.getElementById("answerTable").getElementsByTagName("TBODY")[0];
	var row = document.createElement("TR");
	var td1 = document.createElement("TD");
	td1.appendChild(radioElement);
	var td2 = document.createElement("TD");
	td2.appendChild (answerElement);
	row.appendChild(td1);
	row.appendChild(td2);
	tbody.appendChild(row);
}

function deleteRow()
{
	var rowCount = document.getElementById("answerTable").rows.length - 1;

	if (rowCount > 1)
		document.getElementById("answerTable").deleteRow(rowCount);
}

function validateQuestion(form, action)
{
	let isValid = true;

	if ($("#question_text") && $("#question_text").val() == "")
	{
		alert(quizQuestionNoTitle);
		$("#question_text").focus();
		isValid = false;
	}

	if (isValid == true)
	{
		switch ($("#id_question_type").val())
		{
			case "1":
				break;

			case "2":
				if ($("#freeTextAnswer") && $("#freeTextAnswer").val() == "")
				{
					alert(quizNoTextAnswer);
					$("#freeTextAnswer").focus();
					isValid = false;
				}
				break;

			case "3":
				break;
		}
	}

	if (isValid == true)
	{
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