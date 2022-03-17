<?php

if (!defined('SMF'))
	die('Hacking attempt...');

function UpdateSession()
{
	global $smcFunc, $db_prefix;

	if (!allowedTo('quiz_play'))
	{
		// @TODO implement an error handling
		$context['quiz_error'] = 'cannot_play';
		die();
	}

	if (isset($_GET['id_session']) && isset($_GET['is_correct']) && isset($_GET['time']))
	{
		$answer = array(-1 => 'timeouts', 0 => 'incorrect', 1 => 'correct');
		// Get passed variables from client
		$queryParam = array(
			'id_session' => isset($_GET['id_session']) ? $_GET['id_session'] : 0,
			'is_correct' => isset($_GET['is_correct']) && in_array($_GET['is_correct'], $answer) ? $answer[$_GET['is_correct']] : 'timeouts',
			'time' => isset($_GET['time']) ? $_GET['time'] : 0,
			'last_question_start' => time(),
		);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}quiz_session
			SET
				question_count = question_count + 1,
				last_question_start = {int:last_question_start},
				{raw:is_correct} = {raw:is_correct} + 1,
				total_seconds = {int:time}
			WHERE id_quiz_session = {string:id_session}',
			$queryParam
		);
	}
	// Just write out some arbitrary XML for the client and die
	// @NOTE before was a file called directly
	// @TODO replace with a proper template?
	header('Content-Type: text/xml');
	echo '<xml/>';
	die();
}

?>