/* SMFQuiz */
var id_dispute = 0;

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
});