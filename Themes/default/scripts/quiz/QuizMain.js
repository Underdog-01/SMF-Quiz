/* SMFQuiz */
/* force detail window cells to have a uniform row height (2 cells per row) */
$(document).ready(function(){
	let heightQuizLeft = 0, heightQuizRight = 0;
	$(".quizdetailwindow").each(function(cellindex) {
		$(this).attr("id", "quizDetailCell" + cellindex);
		if ((cellindex % 2) != 0) {
			heightQuizRight = Math.ceil($(this).height());
			if (heightQuizLeft > heightQuizRight) {
				$(this).height(heightQuizLeft);
				$("#quizDetailCell" + (cellindex-1)).height(heightQuizLeft);
			}
			else {
				$("#quizDetailCell" + (cellindex-1)).height(heightQuizRight);
				$(this).height(heightQuizRight);
			}
		}
		else {
			heightQuizLeft = Math.ceil($(this).height());
		}
	});
});
