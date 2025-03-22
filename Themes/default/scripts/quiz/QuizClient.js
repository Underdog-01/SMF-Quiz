// Percentage value for the countdown
var countdownPercentage = 100;

// The number of seconds remaining to answer the question
var secondsRemaining = 1000;

// The maximum number of Ajax retries that can occur - best to set this so members don't just keep
// retrying
var ajaxMaxRetries = 5;

// The Ajax timeout value
var ajaxTimeout = 10000;

// The total number of Ajax retries
var ajaxRetries = 0;

// The identifier for the timer
var timerId = 0;

// The current question identifier
var currentid_question = 0;

// The correct answer identifier
var correctid_answer = 0;

// What the correct answer is
var correctAnswer = '';

// The identifier for the quiz league that is being played
var id_quiz_league = 0;

// The identifier for the quiz that is being played
var id_quiz = 0;

// The number of questions in the quiz - this value will be populated from the quiz detail call
var number_of_questions = 5;

// The current question number the user is on
var currentQuestionNumber = 0;

// Any text to be presented about the answer once the user has answered the question - this value will be populated from the quiz detail call
var questionanswer_text = '';

// The number of answers the user has answered correctly
var correct_answer_count = 0;

// The number of answers the user has answered incorrectly
var incorrect_answer_count = 0;

// The number of timeouts the user has had
var timeout_count = 0;

// Whether the answers should be shown after answering a question - this value will be populated from the quiz detail call
var show_answers = 1;

// The unique session identifier for this quiz session - this value will be populated from the quiz detail call
var id_session = '';

// The total number of seconds taken in the quiz
var total_quiz_seconds = 0;

// The creator of the quiz
var creator_id = 0;

// Points for a correct answer when playing in the league
var points_for_correct = 0;

// The current round of the league
var round = 0;

// Whether the resume count should be updated
var updateResumes = 0;
var totalResumes = 0;

var seconds_per_question = 0;

var question_text = '';

// The last calculated total correct
var lastTotalCorrect = 0;

// The last calculated total incorrect
var lastTotalIncorrect = 0;

// The last calculated total timeouts
var lastTotalTimeouts = 0;

// Whether the answer has already been submitted
var answerSubmitted = 0;

var enabled = 0, play_limit = 0;
$(document).ready(function() {

	// Make sure user is logged in
	if (id_user == 0) {
		alert(textLoggedIn);
		window.close();
	}

	// Hide elements not needed yet
	$('#zoomImageWrapper').hide();
	$('#nextQuestionButton').hide();
	$('#disputeButton').hide();
	$('#exitQuizButton').hide();

	// Set the progress bar
	$("#progressBarDiv").progressbar({ value: 100 });

	// Bind the AJAX send and complete functions so that when we are retrieving data the
	// AJAX loading image is shown
	$("#ajaxLoading").bind("ajaxSend", function() {
		$(this).show();
	}).bind("ajaxComplete", function() {
		$(this).hide();
	});

	// Wire up the click event of the get next question button
	$("#nextQuestionButton").click(function() {
		nextQuestion();
		$('#nextQuestionButton').hide();
	});

	// Wire up the click event of the quiz start button
	$("#startQuizButton").click(function() {
		$("#startQuizButton").hide();
		nextQuestion();
	});

	// Wire up the click event of the exit quiz button
	$("#exitQuizButton").click(function() {
		window.close();
	});

	// Wire up the click event of the dispute button
	$("#disputeButton").click(function() {
		showDispute();
	});

	// Disable some keys
	$(document).keydown(function (e) {

		// Enter key
		//if (e.keyCode == 13) return false;

		// F5 key
		if (e.keyCode == 116) return false;

		// Ctrl key
		if (e.keyCode == 17) return false;
	});

	// When the window closes we want to refresh the parent
	$(window).bind("beforeunload", function(e) {
		window.opener.location.reload();
	});

	// Setup the lightbox
	$(".lightbox").lightbox({
		fileLoadingImage: quizImageRootFolder + "loading.gif",
		fileBottomNavCloseImage: quizImageRootFolder + "closelabel.gif",
		fitToScreen: true,
		overlayOpacity: 0.5
	});

	// Initialise the quiz
	initialiseQuiz();
});

/*
Function that initialises the quiz
*/
function initialiseQuiz()
{
	// Get the querystring parameters
	var qsParm = parseQuerystring();
	id_quiz = qsParm['id_quiz'];

	id_quiz_league = qsParm['id_quiz_league'];

	// Get quiz or quiz league depending on what was passed in querystring
	if (id_quiz != 0 && id_quiz != undefined)
		getQuiz();
	else
		getQuizLeague();
}

/*
Function to parse the items in the querystring
*/
function parseQuerystring()
{
	var qsParm = new Array();
	var query = window.location.search.substring(1);
	var parms = query.split(';');
	for (var i=0; i<parms.length; i++)
	{
		var pos = parms[i].indexOf('=');
		if (pos > 0)
		{
			var key = parms[i].substring(0,pos);
			var val = parms[i].substring(pos+1);
			qsParm[key] = val;
		}
	}
	return qsParm;
}

/*
Function that gets the quiz from the server
*/
function getQuiz()
{
	$.ajax({
		type: "GET",
		url: smf_scripturl + "?action=SMFQuizStart;xml;id_quiz=" + id_quiz,
		cache: false,
	dataType: "xml",
		timeout: ajaxTimeout,
		success: function(xml) {
			parseQuizDetailXml(xml);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			handleAjaxError(XMLHttpRequest, textStatus, errorThrown, "getQuiz()");
		}
	});
}

/*
Function that gets the quiz question from the server
*/
function getQuizQuestion()
{
	$.ajax({
			type: "GET",
			url: smf_scripturl + "?action=SMFQuizQuestions;id_quiz=" + id_quiz + ";updateResumes=" + updateResumes + ";id_session=" + id_session + ";questionNum=" + (currentQuestionNumber-1),
			cache: false,
	dataType: "xml",
	timeout: ajaxTimeout,
	success: function(xml) {
		parseQuizQuestionXml(xml);

		// We need to increment as the resume value won't be incremented until we get the first
		// question
		if (updateResumes == 1)
			totalResumes++;

		updateResumes = 0;
		startCounter();
	},
	error: function(XMLHttpRequest, textStatus, errorThrown) {
		handleAjaxError(XMLHttpRequest, textStatus, errorThrown, "getQuizQuestion()");
	}
	});
}

/*
Function that gets the quiz league question from the server
*/
function getQuizLeagueQuestion()
{
	$.ajax({
		type: "GET",
		url: smf_scripturl + "?action=SMFQuizQuestions;id_quiz_league=" + id_quiz_league + ";updateResumes=" + updateResumes + ";id_session=" + id_session,
		cache: false,
		dataType: "xml",
		timeout: ajaxTimeout,
		success: function(xml) {
			parseQuizQuestionXml(xml);

			// We need to increment as the resume value won't be incremented until we get the first
			// question
			if (updateResumes == 1)
				totalResumes++;

			updateResumes = 0;
			startCounter();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			handleAjaxError(XMLHttpRequest, textStatus, errorThrown, "getQuizLeagueQuestion()");
		}
	});
}

/*
Function that gets the quiz league from the server
*/
function getQuizLeague()
{
	$.ajax({
		type: "GET",
		url: smf_scripturl + "?action=SMFQuizStart;id_quiz_league=" + id_quiz_league,
		cache: false,
		dataType: "xml",
		timeout: ajaxTimeout,
		success: function(xml) {
			parseQuizLeagueDetailXml(xml);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			handleAjaxError(XMLHttpRequest, textStatus, errorThrown, "getQuizLeague()");
		}
	});
}

/*
Function that saves the quiz answer
*/
function saveQuizAnswer(is_correct)
{
	// Reset the counter
	clearInterval(timerId);

	// Update the display counters
	updateCounts();

	// Don't want to view the image
	$('#zoomImageWrapper').hide();

	$.ajax({
		type: "GET",
		url: smf_scripturl + '?action=SMFQuizAnswers;id_session=' + id_session + ';time=' + total_quiz_seconds + ';is_correct=' + is_correct,
		cache: false,
		dataType: "xml",
		timeout: ajaxTimeout,
		success: function(xml) {

			// We need to clear down the detail now so the user can't select the answer again
			$("#firstAnswerSpan").html(questionanswer_text);

			// If we have reached the total number of questions, end the Quiz
			if (currentQuestionNumber > number_of_questions)
				endQuiz();
			else
				// Otherwise setup for next question
				$("#nextQuestionButton").show();

			answerSubmitted = 0;
			$('#disputeButton').show();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			handleAjaxError(XMLHttpRequest, textStatus, errorThrown, "saveQuizAnswer(" + is_correct + ")");
		}
	});
}

/*
Function that ends the quiz
*/
function quizEnd()
{
	$.ajax({
		type: 'POST',
		url: smf_scripturl + "?action=SMFQuizEnd",
		data: {"id_quiz":id_quiz,"questions":number_of_questions,"correct":correct_answer_count, "incorrect":incorrect_answer_count,"timeouts":timeout_count, "id_session":id_session, "total_seconds":total_quiz_seconds, "creator_id":creator_id, "totalResumes":totalResumes, "enabled":enabled, "play_limit":play_limit},
		cache: false,
		dataType: "xml",
		timeout: ajaxTimeout,
		success: function(xml) {
			showResults();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
				handleAjaxError(XMLHttpRequest, textStatus, errorThrown, "quizEnd()");
		}
	});
}

/*
Function that ends the quiz league
*/
function quizLeagueEnd()
{
	// Calculate the points
	points = points_for_correct * correct_answer_count;
	$.ajax({
		type: 'POST',
		url: smf_scripturl + "?action=SMFQuizEnd",
		data: {"id_quiz_league":id_quiz_league,"questions":number_of_questions,"correct":correct_answer_count, "incorrect":incorrect_answer_count,"timeouts":timeout_count, "id_session":id_session, "total_seconds":total_quiz_seconds, "points":points, "round":round, "totalResumes":totalResumes, "enabled":enabled, "play_limit":play_limit},
		cache: false,
		dataType: "xml",
		timeout: ajaxTimeout,
		success: function(xml) {
			showResults();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			handleAjaxError(XMLHttpRequest, textStatus, errorThrown, "quizLeagueEnd()");
		}
	});
}

function parseQuizDetailXml(xmlDoc)
{
	if (xmlDoc.getElementsByTagName('title').length > 0)
	{
		var questions_per_session = xmlDoc.getElementsByTagName('questions_per_session')[0].firstChild.nodeValue;
		seconds_per_question = xmlDoc.getElementsByTagName('seconds_per_question')[0].firstChild.nodeValue;
		creator_id = xmlDoc.getElementsByTagName('creator_id')[0].firstChild.nodeValue;
		show_answers = xmlDoc.getElementsByTagName('show_answers')[0].firstChild.nodeValue;
		number_of_questions = questions_per_session;

		// If a session is returned in the XML it means that this user has an outstanding session for this quiz, so they need complete this
		var sessionOutput = '';
		if (xmlDoc.getElementsByTagName('topReached')[0] != null) {
			alert(textPlayedQuizTopScore);
			window.close();
		}
		else if (xmlDoc.getElementsByTagName('limitReached')[0] != null) {
			alert(textPlayedQuizOverLimit);
			window.close();
		}
		else if (xmlDoc.getElementsByTagName('session')[0] != null)
		{

			// Get the time the last question was answered
			var lastQuestionStart = xmlDoc.getElementsByTagName('last_question_start')[0].firstChild.nodeValue;

			// Get current time in seconds
			var timestamp = Math.round(new Date().getTime()/1000);

			// Get duration of the two in seconds
			var duration = timestamp - lastQuestionStart;

			// If the duration is less that set in admin then they cannot continue quiz
			// We should make this period quite high to discourage cheating
			if (duration < (SessionTimeLimit*60))
			{
				alert(textSessionTime);
				window.close();
			}

			updateResumes = 1;

// @TODO localization
			sessionOutput += "<br/><b><font color=\"#FF3333\">You currently have a session for this quiz open and therefore this previous session will be resumed</font></b>";

			total_quiz_seconds = parseInt(xmlDoc.getElementsByTagName('session_time')[0].firstChild.nodeValue);
			correct_answer_count = xmlDoc.getElementsByTagName('session_correct')[0].firstChild.nodeValue;
			incorrect_answer_count = xmlDoc.getElementsByTagName('session_incorrect')[0].firstChild.nodeValue;
			timeout_count = xmlDoc.getElementsByTagName('session_timeouts')[0].firstChild.nodeValue;
			play_limit = xmlDoc.getElementsByTagName('play_limit')[0].firstChild.nodeValue;
			enabled = xmlDoc.getElementsByTagName('enabled')[0].firstChild.nodeValue;
			currentQuestionNumber = xmlDoc.getElementsByTagName('question_count')[0].firstChild.nodeValue;
			updateCounts();

			id_session = xmlDoc.getElementsByTagName('id_quiz_session')[0].firstChild.nodeValue;
			totalResumes = xmlDoc.getElementsByTagName('total_resumes')[0].firstChild.nodeValue;
		}
		else
		{
			// Show the number of questions
// @TODO localization
			$('#currentQuestion').html("Question " + "0/" + number_of_questions);

			id_session = xmlDoc.getElementsByTagName('id_session')[0].firstChild.nodeValue;
		}

		// Quiz Title
		var title = xmlDoc.getElementsByTagName('title')[0].firstChild.nodeValue;
		$("#quizTitleSpan").html(title);

		// Quiz Image
		var image = '';
		if (xmlDoc.getElementsByTagName('image')[0].firstChild != null)
		{
			image = xmlDoc.getElementsByTagName('image')[0].firstChild.nodeValue;
			$("#quizImage").attr('src', quizImageFolder + image);
			$("#quizImage").attr('alt', title);
		}

		// Quiz Description
		if (xmlDoc.getElementsByTagName('description')[0].firstChild != null)
			$("#quizDescriptionSpan").html(xmlDoc.getElementsByTagName('description')[0].firstChild.nodeValue);

		// Quiz Overview
// @TODO localization
		var firstDivOutput = "<b><font size=\"6\" color=\"#99FF66\">Quiz Details</font></b>";
		firstDivOutput += "<br/><br/>You will be given <font color=\"#F5F54A\">" + questions_per_session + "</font> questions";
		firstDivOutput += "<br/>You will have <font color=\"#F5F54A\">" + seconds_per_question + "</font> seconds to answer each question";
		firstDivOutput += "<br/>This quiz can be played <font color=\"#F5F54A\">" + play_limit + "</font> times";
		if (show_answers == 1)
			firstDivOutput += "<br/>This quiz is configured to <font color=\"#F5F54A\">show</font> correct answers after submitting answer";
		else
			firstDivOutput += "<br/>This quiz is configured to <font color=\"#F5F54A\">not show</font> correct answers after submitting answer";

		firstDivOutput += sessionOutput;
		$("#firstQuestionSpan").html(firstDivOutput);
	}
	else
	{
		alert(textPlayedQuizOverMaximum);
		window.close();
	}
}

function parseQuizLeagueDetailXml(xmlDoc)
{
	if (xmlDoc.getElementsByTagName('title').length > 0)
	{
		// Quiz League Title
		var title = xmlDoc.getElementsByTagName('title')[0].firstChild.nodeValue;
		$("#quizTitleSpan").html(title);

		// Quiz League Image
		var image = '';
		if (xmlDoc.getElementsByTagName('image')[0].firstChild != null)
		{
			image = xmlDoc.getElementsByTagName('image')[0].firstChild.nodeValue;
			$("#quizImage").attr('src', quizImageFolder + image);
			$("#quizImage").attr('alt', title);
		}

		// Quiz League Description
		if (xmlDoc.getElementsByTagName('description')[0].firstChild != null)
			$("#quizDescriptionSpan").html(xmlDoc.getElementsByTagName('description')[0].firstChild.nodeValue);

		var questions_per_session = xmlDoc.getElementsByTagName('questions_per_session')[0].firstChild.nodeValue;
		var day_interval = xmlDoc.getElementsByTagName('day_interval')[0].firstChild.nodeValue;
		points_for_correct = xmlDoc.getElementsByTagName('points_for_correct')[0].firstChild.nodeValue;
		round = xmlDoc.getElementsByTagName('current_round')[0].firstChild.nodeValue;

		seconds_per_question = xmlDoc.getElementsByTagName('seconds_per_question')[0].firstChild.nodeValue;
		show_answers = xmlDoc.getElementsByTagName('show_answers')[0].firstChild.nodeValue;

		number_of_questions = questions_per_session;

// @TODO localization
		var firstDivOutput = "<b><font size=\"6\" color=\"#99FF66\">Quiz League Details</font></b>";
		firstDivOutput += "<br/><br/>You will be given <font color=\"#F5F54A\">" + questions_per_session + "</font> questions";
		firstDivOutput += "<br/>You will have <font color=\"#F5F54A\">" + seconds_per_question + "</font> seconds to answer each question";
		firstDivOutput += "<br/>You can play this quiz league every <font color=\"#F5F54A\">" + day_interval + "</font> days";
		firstDivOutput += "<br/>You will receive <font color=\"#F5F54A\">" + points_for_correct + "</font> points for each correct answer";

		if (show_answers == 1)
			firstDivOutput += "<br/>This quiz is configured to <font color=\"#F5F54A\">show</font> correct answers after submitting answer";
		else
			firstDivOutput += "<br/>This quiz is configured to <font color=\"#F5F54A\">not show</font> correct answers after submitting answer";

		// If a session is returned in the XML it means that this user has an outstanding session for this quiz, so they need complete this
		if (xmlDoc.getElementsByTagName('session')[0] != null)
		{
// @TODO localization
			firstDivOutput += "<br/><b><font color=\"#FF3333\">You currently have a session for this quiz open and therefore this previous session will be resumed</font></b>";

			total_quiz_seconds = parseInt(xmlDoc.getElementsByTagName('session_time')[0].firstChild.nodeValue);
			correct_answer_count = xmlDoc.getElementsByTagName('session_correct')[0].firstChild.nodeValue;
			incorrect_answer_count = xmlDoc.getElementsByTagName('session_incorrect')[0].firstChild.nodeValue;
			timeout_count = xmlDoc.getElementsByTagName('session_timeouts')[0].firstChild.nodeValue;
			currentQuestionNumber = xmlDoc.getElementsByTagName('question_count')[0].firstChild.nodeValue;
			updateCounts();

			id_session = xmlDoc.getElementsByTagName('id_quiz_session')[0].firstChild.nodeValue;
		}
		else
			id_session = xmlDoc.getElementsByTagName('id_session')[0].firstChild.nodeValue;

		$("#firstQuestionSpan").html(firstDivOutput);
	}
	else
	{
		alert(textPlayedQuizLeagueOverMaximum);
		window.close();
	}
}

/*
function ajaxFunction(){
	var ajaxRequest;

	try {
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e) {
		// Internet Explorer Browsers
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				//browsers all not support, rare case
				alert(textBrowserBroke);
				return false;
			}
		}

	}
	return ajaxRequest;
}
*/
function doCounter()
{
	// Count down the percentage time remaining
	countdownPercentage--;

	// Calculate the seconds remaining
	var newSecondsRemaining = Math.ceil(countdownPercentage / 100 * seconds_per_question);

	// If the new seconds remaining is different from the old seconds remaining
	if (newSecondsRemaining != secondsRemaining)
	{
		// Save this value
		secondsRemaining = newSecondsRemaining;

		// Set the countdown remaining text
// @TODO localization
		$('#countDownDiv').html(secondsRemaining + ' seconds');
	}

	// Set the progress bar percentage
	$('#progressBarDiv').progressbar('option', 'value', countdownPercentage);

	// If the countdown has completed
	if (countdownPercentage < 1)
		outOfTime(true);
}

function startCounter()
{
	// Calculate what the timer interval should be based on the seconds it should
	// be counting down
	var interval = (seconds_per_question * 1000) / 100;

	// Reset the countdown percentage
	countdownPercentage = 100;

	// Start the timer
	timerId = setInterval("doCounter();", interval);
}

function nextQuestion()
{
	$('#disputeButton').hide();

	// Reset the Ajax retries in case there were timeouts in a previous call
	ajaxRetries = 1;

	// Increment the counter that determines how many questions have been had
	currentQuestionNumber++;

	// Only get the next question if the quiz hasn't finished
	if (currentQuestionNumber <= number_of_questions)
	{
		$("#progressBarDiv").progressbar({ value: 100 });

		// Reset question answer text
		questionanswer_text = '';

		// Get the question to be shown
		question_text = '';
		getQuestion();
	}
	else
		endQuiz();
}

function endQuiz()
{
	// Open call to server
	if (id_quiz != 0 && id_quiz != undefined) {
		quizEnd();
	} else if (id_quiz_league != 0) {
		quizLeagueEnd();
	} else {
		alert(textNoLeagueOrQuizSpecified);
	}
}

function showResults()
{
	$('#exitQuizButton').show();

// @TODO localization
	// Now display results to the user
	var quizCompleteDivOutput = "<b><font color=\"#99FF66\" size=\"6\">Quiz Completed</font></b>";

	$("#firstQuestionSpan").html(quizCompleteDivOutput);

	var quizStatsOutput = "<b><font size=\"6\">Your Statistics</font></b>";
	quizStatsOutput += "<br/><br/>You answered a total of <font color=\"#F5F54A\">" + number_of_questions + "</font> questions";
	quizStatsOutput += "<br/>You answered <font color=\"#F5F54A\">" + correct_answer_count + "</font> correctly";
	quizStatsOutput += "<br/>You answered <font color=\"#F5F54A\">" + incorrect_answer_count + "</font> incorrectly";
	quizStatsOutput += "<br/>You ran out of time with <font color=\"#F5F54A\">" + timeout_count + "</font> questions";
	quizStatsOutput += "<br/>You took a total of <font color=\"#F5F54A\">" + total_quiz_seconds + "</font> seconds to answer the questions";
	var percentage = (correct_answer_count / number_of_questions) * 100;

	if (percentage < 20) {
		quizStatsOutput += "<br/><b><i><font color=\"#F5F54A\">" + SMFQuiz_0to19 + "</font></i></b>";
	} else if (percentage < 40) {
		quizStatsOutput += "<br/><b><i><font color=\"#F5F54A\">" + SMFQuiz_20to39 + "</font></i></b>";
	} else if (percentage < 60) {
		quizStatsOutput += "<br/><b><i><font color=\"#F5F54A\">" + SMFQuiz_40to59 + "</font></i></b>";
	} else if (percentage < 80) {
		quizStatsOutput += "<br/><b><i><font color=\"#F5F54A\">" + SMFQuiz_60to79 + "</font></i></b>";
	} else if (percentage < 99) {
		quizStatsOutput += "<br/><b><i><font color=\"#F5F54A\">" + SMFQuiz_80to99 + "</font></i></b>";
	} else {
		quizStatsOutput += "<br/><b><i><font color=\"#F5F54A\">" + SMFQuiz_99to100 + "</font></i></b>";
	}

	if (creator_id == id_user)
		quizStatsOutput += "<br/><br/><b><font color=\"#FF3333\">THIS SCORE WILL NOT BE SAVED AS YOU CREATED THE QUIZ</font></b>";
	else
		quizStatsOutput += "<br/><br/><b>These results have been submitted to the database, click the button below to finish</b>";

	$("#firstAnswerSpan").html(quizStatsOutput);
}

function getQuestion()
{
	// Depending on whether this is a quiz or league question, take appropriate
	// action
	if (id_quiz != 0 && id_quiz != undefined) {
		getQuizQuestion();
	} else if (id_quiz_league != 0) {
		getQuizLeagueQuestion();
	} else {
		alert(textNoLeagueOrQuizSpecified);
	}
}

function parseQuizQuestionXml(xmlDoc)
{
	// First get the type of question it is
	var id_question_type = xmlDoc.getElementsByTagName('id_question_type')[0].firstChild.nodeValue;

	// Get the actual question. Note that for quiz leagues we want to display the quiz from which the question came from as well

	if (xmlDoc.getElementsByTagName('quizTitle')[0] != null)
		question_text = '<font size="3"><b>From the quiz <font color="#99FF66">' + xmlDoc.getElementsByTagName('quizTitle')[0].firstChild.nodeValue + '</font></b></font><br/>' + xmlDoc.getElementsByTagName('question_text')[0].firstChild.nodeValue;
	else
		question_text = xmlDoc.getElementsByTagName('question_text')[0].firstChild.nodeValue;

	// Get the question id, this is used for submitting back
	currentid_question = xmlDoc.getElementsByTagName('id_question')[0].firstChild.nodeValue;

	if (xmlDoc.getElementsByTagName('questionanswer_text')[0].firstChild != null)
	{
		// The answer text is a comment about the answer which can be shown to provide context after the question was answered
		questionanswer_text = xmlDoc.getElementsByTagName('questionanswer_text')[0].firstChild.nodeValue;
	}

	var image = '';
	if (xmlDoc.getElementsByTagName('image')[0].firstChild != null)
	{
		image = xmlDoc.getElementsByTagName('image')[0].firstChild.nodeValue;
		$('#zoomImageWrapper').show();
		$('#zoomImage').attr("href", image);
		$('#zoomImageThumb').show().attr("src", image);
	}

	// Reset correct answer
	correctid_answer = 0;

	// Now we need to deal with it differently based on this quiz type, the HTML will be different
	switch (id_question_type)
	{
		case '1': // Multiple Choice
			htmlFragment = questionMultipleChoice(xmlDoc);
			break;
		case '2': // Free Text
			htmlFragment = questionFreeText(xmlDoc);
			break;
		case '3': // True/False
			htmlFragment = questionTrueFalse(xmlDoc);
			break;
		default:
// @TODO localization
			htmlFragment = "There has been an error, please contact the administrator";
			break;
	}

	// Output the HTML that was returned
	$("#firstQuestionSpan").html(question_text);
	$("#firstAnswerSpan").html(htmlFragment);

	// Wire up the click event of the autoSubmitAnswer class items
	$(".autoSubmitAnswer").click(function() {
		answerSelected(this);
	});

	$("#freeTextAnswer").focus().keypress(function(e) {
		submitenter(this,e);
	});

	// Wire up the click event of the free text answer button
	$("#freeTextAnswerButton").click(function() {
		freeTextAnswerSelected();
	});
}


function questionMultipleChoice(xmlDoc)
{
	// This variable is used to store the HTML fragment to return
	var htmlFragment = "";

	// We need to get the answer options
	var answers = xmlDoc.getElementsByTagName('answer');

	// Loop through each of the answer options
	for (var i = 0; i < answers.length; i++)
	{
		// Retrieve the identifier and text of this answer
		var id_answer = answers[i].getElementsByTagName("id_answer")[0].firstChild.nodeValue;
		var answer_text = answers[i].getElementsByTagName("answer_text")[0].firstChild.nodeValue;

		// Try and find the correct answer Id
		if (correctid_answer == 0)
		{
			is_correct = answers[i].getElementsByTagName("is_correct")[0].firstChild.nodeValue;

			if (is_correct == 1)
			{
				correctid_answer = id_answer;
				correctAnswer = answer_text;
			}
		}

		// Build up the HTML fragment for this answer
		htmlFragment += '<br/><input name="answers" class="autoSubmitAnswer" value="' + id_answer + '" type="radio">' + answer_text + '</option>';
	}

	// Return the HTML fragment we have built
	return htmlFragment;
}

function submitenter(myfield,e)
{
	var keycode;
	if (window.event)
		keycode = window.event.keyCode;
	else if
		(e) keycode = e.which;
	else
		return true;

	if (keycode == 13)
	{
		freeTextAnswerSelected();
		return false;
	}
	else
		return true;
}

function questionFreeText(xmlDoc)
{
	correctAnswer = xmlDoc.getElementsByTagName("answer_text")[0].firstChild.nodeValue;

// @TODO localization? (submit)
	htmlFragment = '<br/><input type="text" class="ui-state-default ui-corner-all" id="freeTextAnswer" onKeyPress="return submitenter(this,event)" size="50" name="freeTextAnswer">&nbsp;<input type="hidden" id="freetext_answer" name="answer" value="' + correctAnswer + '"/><input type="button" value="Submit" id="freeTextAnswerButton" class="ui-state-default ui-corner-all">';
	return htmlFragment;
}

function questionTrueFalse(xmlDoc)
{
	// This variable is used to store the HTML fragment to return
	var htmlFragment = "";

	// We need to get the answer options
	var answers = xmlDoc.getElementsByTagName('answer');

	// Loop through each of the answer options
	for (var i = 0; i < answers.length; i++)
	{
		// Retrieve the identifier and text of this answer
		var id_answer = answers[i].getElementsByTagName("id_answer")[0].firstChild.nodeValue;
		var answer_text = answers[i].getElementsByTagName("answer_text")[0].firstChild.nodeValue;

		// Try and find the correct answer Id
		if (correctid_answer == 0)
		{
			is_correct = answers[i].getElementsByTagName("is_correct")[0].firstChild.nodeValue;
			if (is_correct == 1)
			{
				correctid_answer = id_answer;
				correctAnswer = answer_text;
			}
		}

		// Build up the HTML fragment for this answer
		htmlFragment += '<br/><input name="answers" class="autoSubmitAnswer" value="' + id_answer + '" type="radio">' + answer_text + '</option>';
	}

	// Return the HTML fragment we have built
	return htmlFragment;
}

function freeTextAnswerSelected()
{
	if (answerSubmitted == 0)
	{
		answerSubmitted = 1;
		is_correct = 0;
		var submittedAnswer = $("#freeTextAnswer").val();

		if (submittedAnswer.toLowerCase() == correctAnswer.toLowerCase())
		{
			correct_answer_count++;
// @TODO localization
			$("#firstQuestionSpan").html("<font color=\"#99FF66\" size=\"6\"><b>Correct!</b></font><br>You answered that in " + (seconds_per_question - Math.ceil(countdownPercentage / 100 * seconds_per_question)) + " seconds");
			is_correct = 1;
		}
		else
		{
			incorrect_answer_count++;
// @TODO localization
			if (show_answers == 1)
				$("#firstQuestionSpan").html("<font color=\"#FF3333\" size=\"6\"><b>Incorrect!</b></font><br/>The correct answer was <font color=\"#FF3333\">" + correctAnswer + "</font><br>You answered that in " + (seconds_per_question - Math.ceil(countdownPercentage / 100 * seconds_per_question)) + " seconds");
			else
				$("#firstQuestionSpan").html("<font color=\"#FF3333\" size=\"6\"><b>Incorrect!</b></font><br>You answered that in " + (seconds_per_question - Math.ceil(countdownPercentage / 100 * seconds_per_question)) + " seconds");
		}

		// Save the answer to the server
		saveQuizAnswer(is_correct)
	}
}

function answerSelected(obj)
{
	if (answerSubmitted == 0)
	{
		answerSubmitted = 1;
		is_correct = 0;
		// Let the user know if this was the correctly chosen answer or not
		if (obj.value == correctid_answer)
		{
			correct_answer_count++;
// @TODO localization
			$("#firstQuestionSpan").html("<font color=\"#99FF66\" size=\"6\"><b>Correct!</b></font><br>You answered that in " + (seconds_per_question - Math.ceil(countdownPercentage / 100 * seconds_per_question)) + " seconds");
			is_correct = 1;
		}
		else
		{
			incorrect_answer_count++;
// @TODO localization
			if (show_answers == 1)
				$("#firstQuestionSpan").html("<font color=\"#FF3333\" size=\"6\"><b>Incorrect!</b></font><br/>The correct answer was <font color=\"#FF3333\">" + correctAnswer + "</font><br/>You answered that in " + (seconds_per_question - Math.ceil(countdownPercentage / 100 * seconds_per_question)) + " seconds");
			else
				$("#firstQuestionSpan").html("<font color=\"#FF3333\" size=\"6\"><b>Incorrect!</b></font><br/>You answered that in " + (seconds_per_question - Math.ceil(countdownPercentage / 100 * seconds_per_question)) + " seconds");
		}

		// Save the answer to the server
		saveQuizAnswer(is_correct)
	}
}

function outOfTime()
{
	answerSubmitted = 1;

// @TODO localization
	if (show_answers == 1)
		$("#firstQuestionSpan").html("<font color=\"#FFCC66\" size=\"6\"><b>Timeout!</b></font><br/>The correct answer was <font color=\"#FF3333\">" + correctAnswer + "</font>");
	else
		$("#firstQuestionSpan").html("<font color=\"#FFCC66\" size=\"6\"><b>Timeout!</b></font>");

	timeout_count++;

	// Save the answer to this question, we use -1 for the answer identifier to indicate an out of time
	saveQuizAnswer(-1)
}

/*
Function that updates the display counts
*/
function updateCounts()
{
	total_quiz_seconds = parseInt(total_quiz_seconds) + parseInt(seconds_per_question) - parseInt(Math.ceil(countdownPercentage / 100 * seconds_per_question));

	$('#totalSeconds').slideUp(function() {
// @TODO localization
		$(this).html(total_quiz_seconds + " seconds").slideDown();
	});

	$('#currentQuestion').slideUp(function() {
// @TODO localization
		$(this).html("Question " + currentQuestionNumber + "/" + number_of_questions).slideDown();
	});

	if (correct_answer_count != lastTotalCorrect) {
		$('#answerCorrectIcon').fadeIn();
		$('#totalCorrect').slideUp(function() {
// @TODO localization
			$(this).html(correct_answer_count + " correct").slideDown();
		});
		lastTotalCorrect = correct_answer_count;
	}

	if (incorrect_answer_count != lastTotalIncorrect)
	{
		$('#answerIncorrectIcon').fadeIn();
		$('#totalIncorrect').slideUp(function() {
// @TODO localization
			$(this).html(incorrect_answer_count + " incorrect").slideDown();
		});
		lastTotalIncorrect = incorrect_answer_count;
	}

	if (timeout_count != lastTotalTimeouts)
	{
		$('#answerTimeoutIcon').fadeIn();
		$('#totalTimeouts').slideUp(function() {
// @TODO localization
			$(this).html(timeout_count + " timeouts").slideDown();
		});
		lastTotalTimeouts = timeout_count;
	}
}

/*
Function to handle an error when one is raised during the AJAX call
*/
function handleAjaxError(XMLHttpRequest, textStatus, errorThrown, functionToCall)
{
	switch (textStatus)
	{
		case "timeout": // Timeout
			handleTimeout(functionToCall);
			break;
		case "parsererror":
			showErrorDialogAndCloseWindow(quizAjaxErrorTimeout, quizAjaxErrorDataLoad);
			break;
		default:
			showErrorDialogAndCloseWindow(quizAjaxErrorGeneral, quizAjaxErrorDataLoad);
			break;
	}
}

/*
Simple function to show the error and close the window. At the moment this is simply
an alert, but going forward will be JQuery UI Dialog
*/
function showErrorDialogAndCloseWindow(text, title)
{
	alert(text);
	window.close();
}

/*
Function to handle a timeout error when one is raised during the AJAX call
*/
function handleTimeout(functionToCall)
{
	alert(quizTimeoutErrorData.replace('%ATTEMPTS%', String(ajaxRetries)));
	if (ajaxRetries >= ajaxMaxRetries)
	{
		alert(quizTimeoutErrorDataMax.replace('%ATTEMPTS%', String(ajaxMaxRetries)));
		window.close();
	}
	else
	{
		ajaxRetries++;
		//getQuiz();
		eval(functionToCall);
	}
}

/*
Function displays the dispute quiz question dialog box
*/
function showDispute()
{
	$("#disputeText").val('');
	$("#disputeDialog").dialog({
		closeOnEscape: true,
		closeText: "",
		draggable: false,
		modal: false,
		resizable: false,
		show: { effect: "blind", duration: 400 },
		title: quizSubmittedDisputeTitle,
		create: function(event, ui) {
			var widget = $(this).dialog("widget");
			$(".ui-dialog-titlebar-close span", widget)
			.css({"filter":"brightness(85%) invert(1)","opacity":"1.0","margin":"0 auto","width":"100%","height":"100%"});
		},
		buttons: [
			{
				text: quizConfirmButton,
				showText: true,
				click: function() {
					submitDispute();
					$(this).dialog('close');
				}
			},
			{
				text: quizCancelButton,
				click: function() {
					$(this).dialog('close');
				}
			}
		]
	});
	$("#disputeDialog").dialog('open');
}

/*
Function that submits the Ajax dispute data
*/
function submitDispute()
{
	/* Get the reason entered */
	let quizInputs = $('<input type="hidden" name="reason" value="' + $("#disputeText").val() + '"><input type="hidden" name="id_quiz" value="' + id_quiz + '"><input type="hidden" name="id_user" value="' + id_user + '"><input type="hidden" name="id_quiz_question" value="' + currentid_question + '">');
	$("#disputeTextReason").append(quizInputs);
	$.post(smf_scripturl + "?action=SMFQuizDispute", $("#disputeTextReason").serialize())
	.done(function( resultData ) {
		alert(quizSubmittedDisputeSuccess);
		console.log(quizSubmittedDisputeSuccess + " ~ " + resultData);
	}).fail(function() {
		alert(quizSubmittedDisputeError + " ~ " + errorThrown);
	})
	.always(function() {
		console.log(quizSubmittedDisputeFinish);
		exit(quizSubmittedDisputeFinish);
	});
}