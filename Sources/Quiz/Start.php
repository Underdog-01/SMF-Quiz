<?php

if (!defined('SMF'))
	die('Hacking attempt...');

function loadQuiz ()
{
	global $context, $txt;
	loadTemplate('Quiz/Admin');

	if (!allowedTo('quiz_play'))
	{
		// @TODO implement an error handling
		$context['quiz_error'] = 'cannot_play';
		die();
	}

	// Get passed variables from client
	// @TODO sanitize
	// @TODO permission check needed
	$id_quiz_league = isset($_GET["id_quiz_league"]) ? $_GET["id_quiz_league"] : 0;
	$id_quiz = isset($_GET["id_quiz"]) ? (int) $_GET["id_quiz"] : 0;
	$id_user = $context['user']['id'];

	$id_session = isset($_GET["id_session"]) ? $_GET["id_session"] : 0;
	$questionId = isset($_GET["questionId"]) ? $_GET["questionId"] : 0;
	$answerId = isset($_GET["answerId"]) ? $_GET["answerId"] : 0;
	$time = isset($_GET["time"]) ? $_GET["time"] : 0;
	$debugOn = isset($_GET["debugOn"]) ? 1 : 0;

	$id_session = md5(uniqid(mt_rand(), true));

	// @TODO move a lot to template
	$xmlReturn = '<smfQuiz>';

	if ($id_quiz != 0)
	{
		// Check if any sessions exist for this user, as we would need to try and continue any existing sessions
		$sessions = QuizSessionExists($id_user, $id_quiz);
		if (sizeof($sessions) > 0)
		{
			// If a session does exist we should return the session data along with the quiz data
			$xmlReturn .= GetQuizSessionXml($sessions);
			$xmlReturn .= GetQuizDetails($id_quiz, $id_user, $id_session, $debugOn);
		}
		else
		{
			// Otherwise this is a new session, so just return the quiz data and create a new session
			$xmlReturn .= GetQuizDetails($id_quiz, $id_user, $id_session, $debugOn);

			// If user hasn't played then start new session
			$pos = strpos($xmlReturn, 'title');

			if ($pos !== false)
				InsertQuizSession($id_session, $id_user, $id_quiz, null);
		}
	}
	elseif ($id_quiz_league != 0)
	{
		// Check if any sessions exist for this user, as we would need to try and continue any existing sessions
		$sessions = QuizLeagueSessionExists($id_user, $id_quiz_league);

		if (sizeOf($sessions) > 0)
		{
			// If a session does exist we should return the session data along with the quiz data
			$xmlReturn .= GetQuizSessionXml($sessions);
			$xmlReturn .= GetQuizLeagueDetails($id_quiz_league, $id_user, $id_session, $debugOn);
		}
		else
		{
			// Otherwise this is a new session, so just return the quiz data and create a new session
			$xmlReturn .= GetQuizLeagueDetails($id_quiz_league, $id_user, $id_session, $debugOn);
			InsertQuizSession($id_session, $id_user, null, $id_quiz_league);
		}
	}
	else
		$xmlReturn .= '<Error>' . $txt['quiz_xml_error_no_id'] . '</Error>';

	$xmlReturn .= '</smfQuiz>';

	header("Content-Type: text/xml");

	echo $xmlReturn;
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

function GetQuizSessionXml($sessions)
{
	// @TODO move to a template
	$xmlFragment = '';
	foreach ($sessions as $session)
	{
		$xmlFragment .= '<session>';
		$xmlFragment .= '<id_quiz_session>' . $session['id_quiz_session'] . '</id_quiz_session>';
		$xmlFragment .= '<session_start>' . $session['session_start'] . '</session_start>';
		$xmlFragment .= '<last_question_start>' . $session['last_question_start'] . '</last_question_start>';
		$xmlFragment .= '<question_count>' . $session['question_count'] . '</question_count>';
		$xmlFragment .= '<session_correct>' . $session['session_correct'] . '</session_correct>';
		$xmlFragment .= '<session_incorrect>' . $session['session_incorrect'] . '</session_incorrect>';
		$xmlFragment .= '<session_timeouts>' . $session['session_timeouts'] . '</session_timeouts>';
		$xmlFragment .= '<session_time>' . $session['session_time'] . '</session_time>';
		$xmlFragment .= '<total_resumes>' . $session['total_resumes'] . '</total_resumes>';
		$xmlFragment .= '</session>';
	}
	return $xmlFragment;
}

function QuizSessionExists($id_user, $id_quiz)
{
	global $smcFunc;

	// Attempt to return any previous session data for this user for the selected quiz
	$sessionResult = $smcFunc['db_query']('', '
		SELECT id_quiz_session, session_start, last_question_start, question_count AS question_count,
			id_quiz, id_quiz_league, correct AS session_correct, incorrect AS session_incorrect,
			timeouts AS session_timeouts, total_seconds AS session_time, total_resumes
		FROM {db_prefix}quiz_session
		WHERE id_user = {int:id_user}
			AND id_quiz = {int:id_quiz}',
		array(
			'id_user' => $id_user,
			'id_quiz' => $id_quiz,
		)
	);

	$returnRow = array();

	// If there is session data, populate an array containing it, as we need to see if we can continue the previous session
	if ($smcFunc['db_num_rows']($sessionResult) > 0)
	{
		while ($sessionRow = $smcFunc['db_fetch_assoc']($sessionResult))
		{
			$returnRow[] = $sessionRow;

			// We also need to update the session to add a timeout, otherwise a user could shut down the window after each question to investigate the answer.
			// There has to be a penalty for closing the window
			//$updateSessionQuery = "
			//	UPDATE		{$db_prefix}quiz_session
			//	SET			timeouts = timeouts + 1,
			//				question_count = question_count + 1
			//	WHERE		id_quiz_session = '{$sessionRow['id_quiz_session']}'
			//";
			//$smcFunc['db_query']('', $updateSessionQuery);
		}
	}
	$smcFunc['db_free_result']($sessionResult);
	return $returnRow;
}

function QuizLeagueSessionExists($id_user, $id_quiz_league)
{
	global $smcFunc;

	// Attempt to return any previous session data for this user for the selected quiz league
	$sessionResult = $smcFunc['db_query']('', '
		SELECT id_quiz_session, session_start, last_question_start, (question_count + 1) AS question_count,
			id_quiz, id_quiz_league, correct AS session_correct, incorrect AS session_incorrect,
			timeouts AS session_timeouts, total_seconds AS session_time, total_resumes
		FROM {db_prefix}quiz_session
		WHERE id_user = {int:id_user}
			AND id_quiz_league = {int:id_quiz_league}',
		array(
			'id_user' => (int)$id_user,
			'id_quiz_league' => (int)$id_quiz_league,
		)
	);

	$returnRow = array();

	// If there is session data, populate an array containing it, as we need to see if we can continue the previous session
	if ($smcFunc['db_num_rows']($sessionResult) > 0)
	{
		while ($sessionRow = $smcFunc['db_fetch_assoc']($sessionResult))
		{
			$returnRow[] = $sessionRow;

			// We also need to update the session to add a timeout, otherwise a user could shut down the window after each question to investigate the answer.
			// There has to be a penalty for closing the window
			// @TODO move the query out of the cycle?
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}quiz_session
				SET
					timeouts = timeouts + 1,
					question_count = question_count + 1
				WHERE id_quiz_session = {string:id_quiz_session}',
				array(
					'id_quiz_session' => $sessionRow['id_quiz_session'],
				)
			);
		}
	}
	$smcFunc['db_free_result']($sessionResult);
	return $returnRow;
}

function GetQuizLeagueDetails($id_quiz_league, $id_user, $id_session, $debugOn)
{
	global $smcFunc;

	if (!isset($id_quiz_league)) {
		return '';
	}

	// Get the quiz league details, but only if the user has played less than once for this round
	$leagueResult = $smcFunc['db_query']('', '
		SELECT title, description, day_interval, question_plays, questions_per_session,
			seconds_per_question, points_for_correct, show_answers,
			current_round
		FROM {db_prefix}quiz_league QL
		WHERE id_quiz_league = {int:id_quiz_league}
			AND state = 1',
		array(
			'id_user' => (int)$id_user,
			'id_quiz_league' => (int)$id_quiz_league,
		)
	);
	$leagueRow = $smcFunc['db_fetch_assoc']($leagueResult);

	if (empty($leagueRow)) {
		return '';
	}

	$leaguePlays = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS user_plays
		FROM {db_prefix}quiz_league_result
		WHERE id_quiz_league = {int:id_quiz_league}
			AND id_user = {int:id_user}
			AND round = {int:current_round}',
		array(
			'id_user' => (int)$id_user,
			'id_quiz_league' => (int)$id_quiz_league,
			'current_round' => (int)$leagueRow['current_round'],
		)
	);
	list($timesPlayed) = $smcFunc['db_fetch_row']($leaguePlays);
	$smcFunc['db_free_result']($leaguePlays);

	// @TODO move to a template!
	// Firstly, build the league details
	$xmlFragment = '<leagueDetail>';
	$questionId = 0;
	if (empty($timesPlayed))
	{
		$xmlFragment .= '<title>' . xmlencode(ajax_format_string($leagueRow["title"])) . '</title>';
		$xmlFragment .= '<id_session>' . $id_session . '</id_session>';
		$xmlFragment .= '<description>' . xmlencode(ajax_format_string($leagueRow["description"])) . '</description>';
		$xmlFragment .= '<day_interval>' . $leagueRow["day_interval"] . '</day_interval>';
		$xmlFragment .= '<question_plays>' . $leagueRow["question_plays"] . '</question_plays>';
		$xmlFragment .= '<questions_per_session>' . $leagueRow["questions_per_session"] . '</questions_per_session>';
		$xmlFragment .= '<seconds_per_question>' . $leagueRow["seconds_per_question"] . '</seconds_per_question>';
		$xmlFragment .= '<points_for_correct>' . $leagueRow["points_for_correct"] . '</points_for_correct>';
		$xmlFragment .= '<show_answers>' . $leagueRow["show_answers"] . '</show_answers>';
		$xmlFragment .= '<current_round>' . $leagueRow["current_round"] . '</current_round>';
		$xmlFragment .= '<image></image>';

	}
	$smcFunc['db_free_result']($leagueResult);
	$xmlFragment .= '
			</leagueDetail>
			<leagueResults>
	';
	// @TODO Why leagueResults???
	$xmlFragment .= '</leagueResults>';
	return $xmlFragment;
}

function GetQuizDetails($id_quiz, $id_user, $id_session, $debugOn)
{
	global $smcFunc;

	//$timesPlayed['player_limit'] = 0;
	// Get the quiz details, but only if they have not gone beyond the count of plays
	$leagueResult = $smcFunc['db_query']('', '
		SELECT Q.title, Q.description, Q.play_limit, Q.seconds_per_question, Q.show_answers, Q.image,
			Q.creator_id, Q.enabled, Q.play_limit, Q.top_user_id
		FROM {db_prefix}quiz Q
		WHERE Q.id_quiz = {int:id_quiz}',
		array(
			'id_user' => (int)$id_user,
			'id_quiz' => (int)$id_quiz,
		)
	);
	$rows = $smcFunc['db_num_rows']($leagueResult);
	if ($rows > 0)
	{
		$leagueRow = $smcFunc['db_fetch_assoc']($leagueResult);
		$questionsData = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS questions_per_session
			FROM {db_prefix}quiz_question
			WHERE id_quiz = {int:id_quiz}',
			array(
				'id_quiz' => (int)$id_quiz,
			)
		);
		list($questions_per_session) = $smcFunc['db_fetch_row']($questionsData);
		$smcFunc['db_free_result']($questionsData);

		$quizPlays = $smcFunc['db_query']('', '
			SELECT player_limit
			FROM {db_prefix}quiz_result
			WHERE id_quiz = {int:id_quiz}
				AND id_user = {int:id_user}
			LIMIT 1',
			array(
				'id_user' => (int)$id_user,
				'id_quiz' => (int)$id_quiz,
			)
		);
		$timesPlayed = $smcFunc['db_fetch_row']($quizPlays);
		$smcFunc['db_free_result']($quizPlays);
	}

	$smcFunc['db_free_result']($leagueResult);

	// Firstly, build the league details
	// @TODO move to a template!
	$xmlFragment = '<quizDetail>';
	if ($rows > 0)
	{
		$xmlFragment .= '<title>' . xmlencode(ajax_format_string($leagueRow["title"])) . '</title>';
		$xmlFragment .= '<id_session>' . $id_session . '</id_session>';
		$xmlFragment .= '<creator_id>' . $leagueRow["creator_id"] . '</creator_id>';
		$xmlFragment .= '<description>' . xmlencode(ajax_format_string($leagueRow["description"])) . '</description>';
		$xmlFragment .= '<questions_per_session>' . $questions_per_session . '</questions_per_session>';
		$xmlFragment .= '<seconds_per_question>' . $leagueRow["seconds_per_question"] . '</seconds_per_question>';
		$xmlFragment .= '<show_answers>' . $leagueRow["show_answers"] . '</show_answers>';
		$xmlFragment .= '<image>' . $leagueRow["image"] . '</image>';
		$xmlFragment .= '<enabled>' . (!empty($leagueRow["enabled"]) ? '1' : '0') . '</enabled>';
		$xmlFragment .= '<play_limit>' . $leagueRow["play_limit"] . 	'</play_limit>';
	}

	$xmlFragment .= '</quizDetail>';
	if ($leagueRow["top_user_id"] == (int)$id_user) {
		$xmlFragment .= '<quizResults><topReached>1</topReached></quizResults>';
	}
	elseif ($rows > 0 && (empty($timesPlayed) || ($leagueRow['play_limit'] > $timesPlayed[0])))
	{
		$xmlFragment .= '<quizResults>';

		// Now get the answers for the selected question
		// @TODO query...all these ifnull should be slow...I think
		$resultsResult = $smcFunc['db_query']('', '
			SELECT IFNULL(SUM(QR.questions),0) AS total_questions,
				IFNULL(SUM(QR.correct),0) AS total_correct,
				IFNULL(SUM(QR.incorrect),0) AS total_incorrect,
				IFNULL(SUM(QR.timeouts),0) AS total_timeouts,
				IFNULL(SUM(QR.total_seconds),0) AS total_seconds
			FROM {db_prefix}quiz_result QR
			WHERE QR.id_user = {int:id_user}
				AND QR.id_quiz = {int:id_quiz}',
			array(
				'id_user' => (int)$id_user,
				'id_quiz' => (int)$id_quiz,
			)
		);

	// @TODO move to a template!
		while ($resultsRow = $smcFunc['db_fetch_assoc']($resultsResult))
		{
			$xmlFragment .= '<total_questions>' . $resultsRow["total_questions"] . '</total_questions>';
			$xmlFragment .= '<total_correct>' . $resultsRow["total_correct"] . '</total_correct>';
			$xmlFragment .= '<total_incorrect>' . $resultsRow["total_incorrect"] . '</total_incorrect>';
			$xmlFragment .= '<total_timeouts>' . $resultsRow["total_timeouts"] . '</total_timeouts>';
			$xmlFragment .= '<total_seconds>' . $resultsRow["total_seconds"] . '</total_seconds>';
		}
		$smcFunc['db_free_result']($resultsResult);

		$xmlFragment .= '</quizResults>';
	}
	else {
		$xmlFragment .= '<quizResults><limitReached>1</limitReached></quizResults>';
	}

	return $xmlFragment;
}

function InsertQuizSession($id_session, $id_user, $id_quiz, $id_quiz_league)
{
	global $smcFunc;

	$id_quiz_league = (int) $id_quiz_league;
	$id_quiz = (int) $id_quiz;

	if (empty($id_quiz_league) && empty($id_quiz))
		return;

	// Create a session for this quiz play in the database
	$smcFunc['db_insert']('',
		'{db_prefix}quiz_session',
		array(
			'id_quiz_session' => 'string-38',
			'id_user' => 'int',
			'session_start' => 'int',
			'last_question_start' => 'int',
			'id_quiz_league' => 'int',
			'question_count' => 'int',
			'id_quiz' => 'int',
			'correct' => 'int',
			'incorrect' => 'int',
			'timeouts' => 'int',
		),
		array(
			$id_session,
			$id_user,
			time(),
			time(),
			$id_quiz_league,
			0,
			$id_quiz,
			0,
			0,
			0,
		),
		array(
			'id_quiz_session',
		)
	);
}

function ajax_format_string($stringToFormat)
{
	global $smcFunc;

	// Remove any slashes. These should not be here, but it has been known to happen
	$returnString = str_replace("\\", "", $smcFunc['db_unescape_string']($stringToFormat));

// @TODO utf8?
	return html_entity_decode($returnString, ENT_QUOTES, 'UTF-8');
}

?>