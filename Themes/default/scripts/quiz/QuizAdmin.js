/* SMFQuiz */
let id_dispute = 0;
if(typeof jQuery == "undefined") {
	var headTag = document.getElementsByTagName("head")[0];
	var jqTag = document.createElement("script");
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
	var selection = selectedForm.options[selectedForm.options.selectedIndex].value;

	document.getElementById("freeTextAnswer").style.display = selection == 2 ? 'block' : 'none';
	document.getElementById("multipleChoiceAnswer").style.display = selection == 1 ? 'block' : 'none';
	document.getElementById("trueFalseAnswer").style.display = selection == 3 ? 'block' : 'none';
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

// @TODO check tags case
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
	var rowCount = document.getElementById("answerTable").rows.length-1;
	if (rowCount > 1)
		document.getElementById("answerTable").deleteRow(rowCount);
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
				alert("Error ~ Quizzes not exported ~ Missing package name ~ " + errorThrown);
			}

		}).fail(function() {
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

$(document).ready(function() {
	$(".disputeDialog").click(function() {
		id_dispute = this.id;
		showDisputeDialog();
	});
});

function submitResponse(remove)
{
	$("#disputeDialog").wrap('<form id="QuizDisputeResponseForm" action=smf_scripturl+"?action=admin;area=quiz;sa=disputes">');
	$("#disputeText").after('<input type="hidden" name="reason" value="' + $("#disputeText").val() + '"><input type="hidden" name="id_dispute" value="' + id_dispute + '"><input type="hidden" name="remove" value="' + remove + '">');
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

$(document).ready(function(){
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
});