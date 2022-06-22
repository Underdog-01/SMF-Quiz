<?php

if (!defined('SMF'))
	die('Hacking attempt...');

function quizQuestions()
{
	global $context;

	// Get passed variables from client
	if (!allowedTo('quiz_play'))
	{
		// @TODO implement an error handling
		$context['quiz_error'] = 'cannot_play';
		die();
	}

	// @TODO sanitize
	$id_quiz_league = isset($_GET["id_quiz_league"]) ? (int) $_GET["id_quiz_league"] : 0;
	$id_quiz = isset($_GET["id_quiz"]) ? (int) $_GET["id_quiz"] : 0;
	$userId = $context['user']['id'];
	$id_session = isset($_GET["id_session"]) ? $_GET["id_session"] : 0;
	$id_question = isset($_GET["id_question"]) ? (int) $_GET["id_question"] : 0;
	$id_answer = isset($_GET["id_answer"]) ? (int) $_GET["id_answer"] : 0;
	$time = isset($_GET["time"]) ? (int) $_GET["time"] : 0;
	$debugOn = isset($_GET["debugOn"]) ? 1 : 0;
	$questionNum = isset($_GET["questionNum"]) ? (int) $_GET["questionNum"] : 0;
	$updateResumes = isset($_GET["updateResumes"]) ? (bool) $_GET["updateResumes"] : false;

	UpdateSession($id_session, $updateResumes);

	if ($id_quiz != 0)
		GetQuizQuestion($id_quiz, $questionNum, $debugOn);
	else
		GetQuizLeagueQuestion($id_quiz_league, $debugOn);

	die();
}

function xmlencode($txt)
{
// @TODO single replace
	$txt = str_replace('&','&amp;',	$txt);
	$txt = str_replace('<', '&lt;',	$txt);
	$txt = str_replace(	'>', '&gt;', $txt);
	$txt = str_replace("'", '&apos;', $txt);
	$txt = str_replace('"', '&quot;', $txt);
	return $txt;
}

function GetQuizQuestion($id_quiz, $questionNum, $debugOn)
{
	global $smcFunc, $settings;

	// TODO: It might be worth to return all the questions and answers back to the client in one go
	// instead of one-by-one like we do here?
	$questionResult = $smcFunc['db_query']('', '
		SELECT Q.id_question, Q.question_text, Q.id_question_type,
			Q.answer_text, Q.image
		FROM {db_prefix}quiz_question Q
		WHERE Q.id_quiz = {int:id_quiz}
		LIMIT {int:questionNum}, 1',
		array(
			'id_quiz' => $id_quiz,
			'questionNum' => $questionNum,
		)
	);

	// @TODO move to template
	// Firstly, get the question
	$xmlFragment = '
		<smfQuiz>
			<question>
	';

	$id_question = 0;
	while ($questionRow = $smcFunc['db_fetch_assoc']($questionResult))
	{
		$id_question = $questionRow["id_question"];

		$xmlFragment .= '<id_question>' . $id_question . '</id_question>';
		$xmlFragment .= '<question_text>' . xmlencode(format_string($questionRow["question_text"])) . '</question_text>';
		$xmlFragment .= '<id_question_type>' . $questionRow["id_question_type"] . '</id_question_type>';
		$xmlFragment .= '<questionanswer_text>' . xmlencode(format_string($questionRow["answer_text"])) . '</questionanswer_text>';
		$xmlFragment .= '<image>';
		if (!empty($questionRow["image"]))
			$xmlFragment .= $settings["default_images_url"] . '/quiz/Questions/' . $questionRow["image"];

		$xmlFragment .= '</image>';
	}
	$smcFunc['db_free_result']($questionResult);

	$xmlFragment .= '
			</question>
			<answers>
	';

	// Now get the answers for the selected question
	$answerResult = $smcFunc['db_query']('', "
		SELECT A.id_answer, A.answer_text, A.is_correct
		FROM {db_prefix}quiz_answer A
		WHERE id_question = {int:id_question}
		ORDER BY RAND()",
		array(
			'id_question' => $id_question,
		)
	);

	while ($answerRow = $smcFunc['db_fetch_assoc']($answerResult))
	{
		$xmlFragment .= '<answer>';
		$xmlFragment .= '<id_answer>' . $answerRow["id_answer"] . '</id_answer>';
		$xmlFragment .= '<answer_text>' . xmlencode(format_string($answerRow["answer_text"])) . '</answer_text>';
		$xmlFragment .= '<is_correct>' . $answerRow["is_correct"] . '</is_correct>';
		$xmlFragment .= '</answer>';
	}
	$smcFunc['db_free_result']($answerResult);

	$xmlFragment .= '
			</answers>
		</smfQuiz>
	';

	// Finally, just update a few counts
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}quiz_question 
		SET plays = plays + 1
		WHERE id_question = {int:id_question}',
		array(
			'id_question' => $id_question,
		)
	);

	if ($debugOn == 1)
		echo $questionQuery;
	else
	{
		header("Content-Type: text/xml");
		echo $xmlFragment;
	}
}

function GetQuizLeagueQuestion($id_quiz_league, $debugOn)
{
	global $smcFunc, $settings;

	// Get the categories the question should be from
	$result = $smcFunc['db_query']('', '
		SELECT categories
		FROM {db_prefix}quiz_league
		WHERE id_quiz_league = {int:id_quiz_league}',
		array (
			'id_quiz_league' => $id_quiz_league,
		)
	);

	$categories = null;
	while ($row = $smcFunc['db_fetch_assoc']($result))
		if ($row['categories'] != null)
			$categories = $row['categories'];

	$smcFunc['db_free_result']($result);

	// We now either get a random question from all quizzes or the categories returned
	// TODO: It might be worth to return all the questions and answers back to the client in one go
	// instead of one-by-one like we do here?
	
	$categories = $categories == null ? '' : $smcFunc['db_quote']('WHERE Q.id_category IN ({string:categories})', array('categories' => $categories));
	$questionResult = $smcFunc['db_query']('', '
		SELECT QQ.id_question, QQ.question_text, QQ.id_question_type,
			QQ.answer_text, QQ.image, QQ.id_quiz, Q.Title
		FROM {db_prefix}quiz_question QQ
		INNER JOIN {db_prefix}quiz Q
			ON QQ.id_quiz = Q.id_quiz
		{raw:where_categories}
		ORDER BY RAND()
		LIMIT 1',
		array(
			'where_categories' => $categories,
		)
	);

	// @TODO: move to a template
	// Firstly, get the question
	$xmlFragment = '
		<smfQuiz>
			<question>
	';

	$id_question = 0;
	while ($questionRow = $smcFunc['db_fetch_assoc']($questionResult))
	{
		$id_question = $questionRow["id_question"];

		$xmlFragment .= '<id_question>' . $id_question . '</id_question>';
		$xmlFragment .= '<question_text>' . xmlencode(format_string($questionRow["question_text"])) . '</question_text>';
		$xmlFragment .= '<id_question_type>' . $questionRow["id_question_type"] . '</id_question_type>';
		$xmlFragment .= '<questionanswer_text>' . xmlencode(format_string($questionRow["answer_text"])) . '</questionanswer_text>';
		$xmlFragment .= '<image>';
		if (!empty($questionRow["image"]))
			$xmlFragment .= $settings["default_images_url"] . '/quiz/Questions/' . $questionRow["image"];

		$xmlFragment .= '</image>';
		$xmlFragment .= '<quizTitle>' . xmlencode(format_string($questionRow["Title"])) . '</quizTitle>';
	}
	$smcFunc['db_free_result']($questionResult);
	$xmlFragment .= '
			</question>
			<answers>
	';

	// Now get the answers for the selected question
	$answerResult = $smcFunc['db_query']('', '
		SELECT A.id_answer, A.answer_text, A.is_correct
		FROM {db_prefix}quiz_answer A
		WHERE id_question = {int:id_question}',
		array(
			'id_question' => $id_question,
		)
	);

	while ($answerRow = $smcFunc['db_fetch_assoc']($answerResult))
	{
		$xmlFragment .= '<answer>';
		$xmlFragment .= '<id_answer>' . $answerRow["id_answer"] . '</id_answer>';
		$xmlFragment .= '<answer_text>' . xmlencode(format_string($answerRow["answer_text"])) . '</answer_text>';
		$xmlFragment .= '<is_correct>' . $answerRow["is_correct"] . '</is_correct>';
		$xmlFragment .= '</answer>';
	}
	$smcFunc['db_free_result']($answerResult);

	$xmlFragment .= '
			</answers>
		</smfQuiz>
	';

	// Finally, just update a few counts
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}quiz_question 
		SET plays = plays + 1
		WHERE id_question = {int:id_question}',
		array(
			'id_question' => $id_question,
		)
	);

// @TODO query
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}quiz_league 
		SET question_plays = question_plays + 1
		WHERE id_quiz_league = {int:id_quiz_league}',
		array(
			'id_quiz_league' => $id_quiz_league,
		)
	);

	if ($debugOn == 1)
		echo $questionQuery;
	else
	{
		header("Content-Type: text/xml");
		echo $xmlFragment;
	}
}

function format_string($stringToFormat)
{
	global $smcFunc;

	// Remove any slashes. These should not be here, but it has been known to happen
	$returnString = str_replace("\\", "", $smcFunc['db_unescape_string']($stringToFormat));

	// Add some breaks in for carriage returns
	$returnString = str_replace(chr(10), "&lt;br/&gt;", $returnString);

// @TODO utf8
	return html_entity_decode($returnString, ENT_QUOTES, 'UTF-8');
}

/*
Updates the quiz session, as we need to change the last question start time
*/
// @TODO is a function really needed?
function UpdateSession($id_session, $updateResumes = false)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}quiz_session
		SET last_question_start = {int:last_question_start}
			{raw:resumeCount}
		WHERE id_quiz_session = {string:id_session}',
		array(
			'last_question_start' => time(),
			'resumeCount' => $updateResumes ? ', total_resumes = total_resumes + 1 ' : '',
			'id_session' => $id_session,
		)
	);
}
?>