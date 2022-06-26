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
	// @TODO move a lot to template
	// @TODO permission check needed
	$id_quiz_league = isset($_GET["id_quiz_league"]) ? (int) $_GET["id_quiz_league"] : 0;
	$id_quiz = isset($_GET["id_quiz"]) ? (int) $_GET["id_quiz"] : 0;
	$id_user = $context['user']['id'];
	$name = $context['user']['name'];
	$id_session = isset($_GET["id_session"]) ? $_GET["id_session"] : '';
	$questions = isset($_GET["questions"]) ? (int) $_GET["questions"] : 0;
	$correct = isset($_GET["correct"]) ? (int) $_GET["correct"] : 0;
	$incorrect = isset($_GET["incorrect"]) ? (int) $_GET["incorrect"] : 0;
	$timeouts = isset($_GET["timeouts"]) ? (int) $_GET["timeouts"] : 0;
	$total_seconds = isset($_GET["total_seconds"]) ? (int) $_GET["total_seconds"] : 0;
	$creatorId = isset($_GET["creator_id"]) ? (int) $_GET["creator_id"] : 0;
	$points = isset($_GET["points"]) ?(int)  $_GET["points"] : 0;
	$round = isset($_GET["round"]) ? (int) $_GET["round"] : 0;
	$totalResumes = isset($_GET["totalResumes"]) ? (int) $_GET["totalResumes"] : 0;

	// Load the language file
	loadLanguage('Quiz/Quiz');

	if (!empty($id_quiz))
	{
		// Don't make these changes if the user playing is the creator of the quiz, only kill the session
		if ($creatorId != $id_user)
		{
			// Only add result if one doesn't already exist for this user and quiz
			if (CheckResultExists($id_quiz, $id_user) == false)
			{
				InsertQuizEnd($id_quiz, $id_user, $questions, $correct, $incorrect, $timeouts, $total_seconds, $totalResumes);
				UpdateQuiz($id_quiz, $questions, $correct, $total_seconds, $id_user, $name);
			}
		}
	}
	elseif (!empty($id_quiz_league))
		InsertQuizLeagueEnd($id_quiz_league, $id_user, $questions, $correct, $incorrect, $timeouts, $total_seconds, $points, $round, $total_seconds, $name);

	EndSession($id_session);

	// Just write out some arbitrary XML for the client
	header("Content-Type: text/xml");
	echo '<xml/>';
	die();
}

/*
Check whether the specified quiz result already has an entry. We also check
whether the quiz is enabled here, as we don't want results being submitted
if the quiz is not enabled
*/
function CheckResultExists($id_quiz, $id_user)
{
	global $smcFunc;

	$result = $smcFunc['db_query']('','
		SELECT id_quiz_result
		FROM {db_prefix}quiz_result QR
		RIGHT JOIN {db_prefix}quiz Q
			ON QR.id_quiz = Q.id_quiz
		WHERE (QR.id_quiz = {int:id_quiz} AND QR.id_user = {int:id_user})
			OR (Q.id_quiz = {int:id_quiz} AND Q.enabled = {int:quiz_disabled})',
		array(
			'id_quiz' => $id_quiz,
			'id_user' => $id_user,
			'quiz_disabled' => 0
		)
	);

	$count = $smcFunc['db_num_rows']($result);

	$smcFunc['db_free_result']($result);

	if ($count > 0)
		return true;
	else
		return false;
}

function InsertQuizEnd($id_quiz, $id_user, $questions, $correct, $incorrect, $timeouts, $total_seconds, $totalResumes)
{
	global $smcFunc, $db_prefix;

	$result_date = time();

	// Create a session for this quiz play in the database
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
		if ($modSettings['SMFQuiz_SendPMOnBrokenTopScore'])
		{
			// PM the user who had the top score
			require_once($sourcedir . '/Subs-Post.php');

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
			sendpm($pmto, $subject, $message, 0, $pmfrom);
				
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