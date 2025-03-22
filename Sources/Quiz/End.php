<?php

if (!defined('SMF'))
	die('Hacking attempt...');

function endQuiz()
{
	global $boardurl, $context;

	if (!allowedTo('quiz_play'))
	{
		// @TODO implement an error handling
		$context['quiz_error'] = 'cannot_play';
		die();
	}

	// Get passed variables from client
	// @TODO sanitize
	// @TODO figure out enabled & play_limit not passed from XML
	// @TODO move a lot to template
	// @TODO permission check needed
	$quizData = ["id_quiz_league", "id_quiz", "id_session", "questions", "correct", "incorrect", "timeouts", "total_seconds", "creator_id", "points", "round", "totalResumes", "enabled", "play_limit"];
	$id_user = $context['user']['id'];
	$name = $context['user']['name'];
	foreach ($quizData as $key => $quizDatum) {
		$$quizDatum = isset($_POST[$quizDatum]) && !in_array($quizDatum, array('id_session')) ? (int)$_POST[$quizDatum] : (isset($_POST[$quizDatum]) ? (string)$_POST[$quizDatum] : 0);
	}

	//log_error(json_encode($_POST));

	// Load the language file
	loadLanguage('Quiz/Quiz');

	if (!empty($id_quiz))
	{
		// Don't make these changes if the user playing is the creator of the quiz, only kill the session
		if ($creator_id != $id_user)
		{
			// Only add result if the quiz is enabled and the user is below the play limit
			if (CheckQuizLimit($id_quiz, $id_user))
			{
				InsertQuizEnd($id_quiz, $id_user, $questions, $correct, $incorrect, $timeouts, $total_seconds, $totalResumes);
				UpdateQuiz($id_quiz, $questions, $correct, $total_seconds, $id_user, $name);
				call_integration_hook('integrate_quiz_result', array($id_quiz, $id_user, $questions, $correct, $incorrect, $timeouts, $total_seconds, $totalResumes));
			}
		}
	}
	elseif (!empty($id_quiz_league))
		InsertQuizLeagueEnd($id_quiz_league, $id_user, $questions, $correct, $incorrect, $timeouts, $total_seconds, $points, $round, $total_seconds, $name);
		call_integration_hook('integrate_quiz_league_result', array($id_quiz_league, $id_user, $questions, $correct, $incorrect, $timeouts, $total_seconds, $points, $round, $total_seconds, $name));

	EndSession($id_session);

	// Just write out some arbitrary XML for the client
	header("Content-Type: text/xml");
	echo '<xml/>';
	die();
}

/*
Check whether the player has exceeded the play limit for this specific quiz
*/
function CheckQuizLimit($id_quiz, $id_user)
{
	global $smcFunc;
	list($enabled, $play_limit, $userLimit) = array(0, 0, 0);

	$result = $smcFunc['db_query']('','
		SELECT Q.id_quiz, Q.play_limit, Q.enabled
		FROM {db_prefix}quiz Q
		WHERE Q.id_quiz = {int:id_quiz}
		LIMIT 1',
		[
			'id_quiz' => $id_quiz
		]
	);

	$rows = $smcFunc['db_num_rows']($result);
	if ($rows > 0) {
		while ($quizLimit = $smcFunc['db_fetch_assoc']($result)) {
			$play_limit = $quizLimit['play_limit'];
			$enabled = $quizLimit['enabled'];
		}
	}

	$smcFunc['db_free_result']($result);

	if (empty($enabled)) {
		return false;
	}

	$result = $smcFunc['db_query']('','
		SELECT QR.id_quiz_result, QR.player_limit
		FROM {db_prefix}quiz_result QR
		WHERE QR.id_quiz = {int:id_quiz} AND QR.id_user = {int:id_user}
		LIMIT 1',
		[
			'id_quiz' => $id_quiz,
			'id_user' => $id_user
		]
	);

	$rows = $smcFunc['db_num_rows']($result);
	if ($rows > 0) {
		while ($quizLimit = $smcFunc['db_fetch_assoc']($result)) {
			$userLimit = $quizLimit['player_limit'];
		}
	}

	$smcFunc['db_free_result']($result);

	if (!empty($play_limit) && $userLimit >= $play_limit)
		return false;

	return true;
}

function InsertQuizEnd($id_quiz, $id_user, $questions, $correct, $incorrect, $timeouts, $total_seconds, $totalResumes)
{
	global $smcFunc, $db_prefix;

	list($result_date, $quizResultData) = array(time(), array('id_quiz_result' => 0, 'player_limit' => 0));

	// Create a session for this quiz play in the database
	$result = $smcFunc['db_query']('', '
		SELECT 		id_quiz_result,	id_quiz, id_user, player_limit
		FROM 		{db_prefix}quiz_result
		WHERE 		id_quiz = {int:id_quiz} AND id_user = {int:id_user}',
		[
			'id_quiz' => $id_quiz, 'id_user' => $id_user
		]
	);

	while ($row = $smcFunc['db_fetch_assoc']($result)) {
		$quizResultData = [
			'id_quiz_result' => $row['id_quiz_result'],
			'player_limit' => $row['player_limit'],
		];
	}

	// Free the database
	$smcFunc['db_free_result']($result);

	if (!empty($quizResultData['id_quiz_result'])) {
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}quiz_result
			WHERE id_quiz_result = {int:id_result}',
			array(
				'id_result' => $quizResultData['id_quiz_result'],
			)
		);
	}

	$smcFunc['db_insert']('',
		'{db_prefix}quiz_result',
		array(
			'id_quiz' => 'int',
			'id_user' => 'int',
			'result_date' => 'int',
			'questions' => 'int',
			'correct' => 'int',
			'incorrect' => 'int',
			'timeouts' => 'int',
			'total_seconds' => 'int',
			'total_resumes' => 'int',
			'player_limit' => 'int',
		),
		array(
			$id_quiz,
			$id_user,
			$result_date,
			$questions,
			$correct,
			$incorrect,
			$timeouts,
			$total_seconds,
			$totalResumes,
			($quizResultData['player_limit']+1)
		),
		array(
			'id_quiz_result'
		)
	);
}

// @TODO is a function really necessary?
function EndSession($id_session)
{
	global $smcFunc;

	// Create a session for this quiz play in the database
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}quiz_session
		WHERE id_quiz_session = {string:id_session}',
		array(
			'id_session' => $id_session,
		)
	);
}

function UpdateQuiz($id_quiz, $questions, $correct, $total_seconds, $id_user, $name)
{
	global $smcFunc, $db_prefix, $scripturl, $sourcedir, $modSettings, $settings, $user_settings;

	// Retrieve quiz info and top score
	$quizTopResult = $smcFunc['db_query']('', '
		SELECT Q.top_correct, Q.top_time, Q.top_user_id,
			Q.title, Q.image, M.real_name
		FROM {db_prefix}quiz Q
		LEFT JOIN {db_prefix}members M
			ON M.id_member = Q.top_user_id
		WHERE id_quiz = {int:id_quiz}',
		array(
			'id_quiz' => $id_quiz,
		)
	);

	// Coming in next release
	$total_points = 0;
	$top_points = 0;

	// Set defaults
	$quizTitle = '';
	$quizImage = $settings['default_images_url'] . '/quiz_images/Quizes/Default-64.png';
	$topScore = false;

	// Retrieve quiz info and top score
	$rows = $smcFunc['db_num_rows']($quizTopResult);
	if ($rows > 0)
	{
		while ($quiztitleRow = $smcFunc['db_fetch_assoc']($quizTopResult))
		{
			$top_correct = $quiztitleRow['top_correct'];
			$top_id_user = $quiztitleRow['top_user_id'];
			$top_user_name = $quiztitleRow['real_name'];
			$top_time = $quiztitleRow['top_time'];
			$quizTitle = $quiztitleRow['title'];
			$quizImage = !empty($quiztitleRow['image']) ? $settings["default_images_url"] . '/quiz_images/Quizes/' . $quiztitleRow['image'] : $quizImage ;
		}
		if (($correct > $top_correct) || ($correct == $top_correct && $total_seconds < $top_time))
			$topScore = true;
	}
	else
		$topScore = true;

	$smcFunc['db_free_result']($quizTopResult);

	// If this is not a top score
	if ($topScore == false)
	{
		// No top score, just update the number of quiz plays for this quiz
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}quiz
			SET
				quiz_plays = quiz_plays + 1,
				question_plays = question_plays + {int:questions},
				total_correct = total_correct + {int:correct}
			WHERE id_quiz = {int:id_quiz}',
			array(
				'questions' => $questions,
				'correct' => $correct,
				'id_quiz' => $id_quiz,
			)
		);

		// Add entry for infoboard
		AddInfoBoardentry($id_user, $name, $id_quiz, $correct, $total_seconds, false, $quizTitle, $quizImage);

	// Otherwise a top score
	}
	else
	{
		// Only send PM if set to do so
		if (!empty($modSettings['SMFQuiz_SendPMOnBrokenTopScore']))
		{
			// PM the user who had the top score
			require_once($sourcedir . '/Subs-Post.php');
			$usersPrefs = Quiz\Helper::quiz_usersAcknowledge('quiz_pm_alert');

			if (in_array($top_id_user, $usersPrefs)) {
				$pmto = array(
					'to' => array(),
					'bcc' => array($top_id_user)
				);

				$subject = ParseMessage($modSettings['SMFQuiz_PMBrokenTopScoreSubject'], $quizTitle, $total_seconds, $correct, $top_time, $top_correct, $quizImage, $scripturl, $id_quiz, $top_user_name);
				$message = ParseMessage($modSettings['SMFQuiz_PMBrokenTopScoreMsg'], $quizTitle, $total_seconds, $correct, $top_time, $top_correct, $quizImage, $scripturl, $id_quiz, $top_user_name);

				$pmfrom = array(
					'id' => $user_settings['id_member'],
					'name' => $user_settings['real_name'],
					'username' => $user_settings['member_name']
				);

				// Send message
				sendpm($pmto, $subject, Quiz\Helper::quiz_pmFilter($message), 0, $pmfrom);
			}

		}

		// Update top score too
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}quiz
			SET
				quiz_plays = quiz_plays + 1,
				question_plays = question_plays + {int:questions},
				total_correct = total_correct + {int:correct},
				top_user_id = {int:id_user},
				top_correct = {int:correct},
				top_time = {int:total_seconds}
			WHERE id_quiz = {int:id_quiz}",
			array(
				'questions' => $questions,
				'correct' => $correct,
				'id_user' => $id_user,
				'total_seconds' => $total_seconds,
				'id_quiz' => $id_quiz,
			)
		);

		AddInfoBoardentry($id_user, $name, $id_quiz, $correct, $total_seconds, true, $quizTitle, $quizImage);
	}
}

// @TODO complete re-work, probably a log that would allow for localization
function AddInfoBoardentry($id_user, $name, $id_quiz, $correct, $total_seconds, $topScore, $quizTitle, $quizImage)
{
	global $smcFunc, $db_prefix, $boardurl, $settings, $txt;

	// Format the infoboard entry
	if ($topScore == true)
		$entry = '<img src="' . $settings['default_images_url'] . '/quiz_images/cup_g.gif"/> <a href="' . $boardurl . '/index.php?action=SMFQuiz;sa=userdetails;id_user=' . $id_user . '"><b>' . addslashes($name) . '</b></a> ' . $txt['SMFQuiz_QuizEnd_Page']['JustAnswered'] . ' <b>' . $correct . '</b> ' . $txt['SMFQuiz_QuizEnd_Page']['QuestionsCorrectlyInThe'] . ' <img width="17" height="17" src="' . $quizImage . '"/><b> <a href="' . $boardurl . '/index.php?action=SMFQuiz;sa=categories;id_quiz=' . $id_quiz . '">' . addslashes($quizTitle) . '</a></b> ' . $txt['SMFQuiz_QuizEnd_Page']['QuizInATimeOf'] . ' <b>' . $total_seconds . '</b> ' . $txt['SMFQuiz_QuizEnd_Page']['SecondsThisIsANewTopScore'];
	else
		$entry = '<a href="' . $boardurl . '/index.php?action=SMFQuiz;sa=userdetails;id_user=' . $id_user . '"><b>' . addslashes($name) . '</b></a> ' . $txt['SMFQuiz_QuizEnd_Page']['JustAnswered'] . ' <b>' . $correct . '</b> ' . $txt['SMFQuiz_QuizEnd_Page']['QuestionsCorrectlyInThe'] . ' <img width="17" height="17" src="' . $quizImage . '"/><b> <a href="' . $boardurl . '/index.php?action=SMFQuiz;sa=categories;id_quiz=' . $id_quiz . '">' . addslashes($quizTitle) . '</a></b> ' . $txt['SMFQuiz_QuizEnd_Page']['QuizInATimeOf'] . ' <b>' . $total_seconds . '</b> ' . $txt['SMFQuiz_Common']['seconds'] ;

	$time = time();

// @TODO utf8
	$entry = $smcFunc['db_escape_string'](html_entity_decode($entry, ENT_QUOTES, 'UTF-8'));

	// Write the infoboard entry to the database
	$smcFunc['db_insert']('',
		'{db_prefix}quiz_infoboard',
		array(
			'entry_date' => 'int',
			'entry' => 'string',
		),
		array(
			$time,
			$entry,
		),
		array(
			'id_infoboard'
		)
	);
}

// @TODO complete re-work, probably a log that would allow for localization
function AddQuizLeagueInfoBoardentry($id_user, $name, $id_quiz_league, $correct, $total_seconds)
{
	global $smcFunc, $db_prefix, $boardurl, $settings, $txt;

	// Get title of quiz league just played
	// TODO - More efficient to pass in querystring
	$quiztitleResult = $smcFunc['db_query']('', '
		SELECT QL.title
		FROM {db_prefix}quiz_league QL
		WHERE QL.id_quiz_league = {int:id_quiz_league}',
		array(
			'id_quiz_league' => $id_quiz_league,
		)
	);

	$quiztitle = '';
	if ($smcFunc['db_num_rows']($quiztitleResult) > 0)
		list($quiztitle) = $smcFunc['db_fetch_row']($quiztitleResult);

	$smcFunc['db_free_result']($quiztitleResult);

	// Format the infoboard entry
// @TODO localization
// @TODO check escaping
	$entry = '<a href="' . $boardurl . '/index.php?action=SMFQuiz;sa=userdetails;id_user=' . $id_user . '"><b>' . addslashes($name) . '</b></a> just answered <b>' . $correct . '</b> questions correctly in the <b>' . addslashes($quiztitle) . '</b> quiz league in a time of <b>' . $total_seconds . '</b> seconds.';
// @TODO utf8
	$entry = $smcFunc['db_escape_string'](html_entity_decode($entry, ENT_QUOTES, 'UTF-8'));
	$time = time();

	// Write the infoboard entry to the database
	$smcFunc['db_insert']('',
		'{db_prefix}quiz_infoboard',
		array(
			'entry_date' => 'int',
			'entry' => 'string',
		),
		array(
			$time,
			$entry,
		),
		array(
			'id_infoboard'
		)
	);
}

function InsertQuizLeagueEnd($id_quiz_league, $id_user, $questions, $correct, $incorrect, $timeouts, $total_seconds, $points, $round, $seconds, $name)
{
	global $smcFunc, $db_prefix;

	$result_date = time();

	// Create a result for this quiz league play in the database
	$smcFunc['db_insert']('',
		'{db_prefix}quiz_league_result',
		array(
			'id_quiz_league' => 'int',
			'id_user' => 'int',
			'round' => 'int',
			'correct' => 'int',
			'points' => 'int',
			'result_date' => 'int',
			'incorrect' => 'int',
			'timeouts' => 'int',
			'seconds' => 'int',
		),
		array(
			$id_quiz_league,
			$id_user,
			$round,
			$correct,
			$points,
			$result_date,
			$incorrect,
			$timeouts,
			$seconds,
		),
		array(
			'id_quiz_league_result'
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}quiz_league
		SET
			total_plays = total_plays + 1,
			total_correct = total_correct + {int:correct},
			total_incorrect = total_incorrect + {int:incorrect},
			total_timeouts = total_timeouts + {int:timeouts}',
		array(
			'correct' => $correct,
			'incorrect' => $incorrect,
			'timeouts' => $timeouts,
		)
	);

	// Write the infoboard entry to the database
	AddQuizLeagueInfoBoardentry($id_user, $name, $id_quiz_league, $correct, $total_seconds);
}

function ParseMessage($message, $quiztitle, $total_seconds, $total_points, $top_time, $top_points, $quizImage, $scripturl, $id_quiz, $old_member_name)
{
	global $user_settings;

// @TODO single replace
	$message = str_replace("{quiz_name}", $quiztitle, $message);
	$message = str_replace("{new_score_seconds}", $total_seconds, $message);
	$message = str_replace("{new_score}", $total_points, $message);
	$message = str_replace("{old_score_seconds}", $top_time, $message);
	$message = str_replace("{old_score}", $top_points, $message);
	$message = str_replace("{member_name}", $user_settings['real_name'], $message);
	$message = str_replace("{old_member_name}", $old_member_name, $message);
	$message = str_replace("{quiz_image}", "[img]" . $quizImage . "[/img]", $message);
	$message = str_replace("{quiz_link}", $scripturl . '?action=SMFQuiz;sa=categories;id_quiz=' . $id_quiz, $message);
	return $message;
}

?>