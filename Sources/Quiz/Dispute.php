<?php

if (!defined('SMF'))
	die('Hacking attempt...');

// @TODO move to another file
function quizDispute()
{
	global $smcFunc, $context, $user_settings, $sourcedir;

	// Get passed variables from client
	// @TODO sanitize (check reason)
	$id_quiz_question = isset($_GET["id_quiz_question"]) ? (int) $_GET["id_quiz_question"] : 0;
	$id_quiz = isset($_GET["id_quiz"]) ? (int) $_GET["id_quiz"] : 0;
	$reason = isset($_GET["reason"]) ? $smcFunc['htmlspecialchars']($_GET["reason"], ENT_QUOTES) : '';
	$id_user = $context['user']['id'];
	$id_dispute = isset($_GET["id_dispute"]) ? (int) $_GET["id_dispute"] : 0;
	// If the id_dispute is set then the admin is reponding

	if ($id_dispute != 0)
	{
		require_once($sourcedir . '/Subs-Post.php');

		$remove = isset($_GET["remove"]) ? (int) $_GET["remove"] : 0;

		$result = $smcFunc['db_query']('', '
			SELECT QD.id_user, Q.title, M.real_name,
				QQ.question_text, QD.reason, QD.updated
			FROM {db_prefix}quiz_dispute QD
			INNER JOIN {db_prefix}quiz Q
				ON QD.id_quiz = Q.id_quiz
			INNER JOIN {db_prefix}members M
				ON QD.id_user = M.id_member
			INNER JOIN {db_prefix}quiz_question QQ
				ON QD.id_quiz_question = QQ.id_question
			WHERE id_quiz_dispute = {int:id_quiz_dispute}',
			array(
				'id_quiz_dispute' => $id_dispute
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$pmto = array(
				'to' => array(),
				'bcc' => array($row['id_user'])
			);

	// @TODO localization
			$subject = "Quiz Dispute Response #" . $id_dispute;
			// @TODO check how the html_entity_decode work (+ UTF8?)
			$message = "Your dispute [b]" . html_entity_decode($row['reason'], ENT_QUOTES, 'UTF-8') . "[/b] against the question [b]" . $row['question_text'] . "[/b] in the quiz [b]" . $row['title'] . "[/b] has had the following response from the Quiz Administrator:
			
	[i]" . html_entity_decode($reason, ENT_QUOTES, 'UTF-8') . "[/i]";

			if ($remove == 1)
				$message .= "

	This dispute has now been removed";

			$pmfrom = array(
				'id' => $user_settings['id_member'],
				'name' => $user_settings['real_name'],
				'username' => $user_settings['member_name']
			);

			// Send message
			sendpm($pmto, $subject, $message, 0, $pmfrom);
		}
		$smcFunc['db_free_result']($result);

		// If the user wanted to remove this after do so now
		// @TODO the dispute should be set to "closed", but not removed
		if ($remove)
		{
			$smcFunc['db_query']('', '
				DELETE
				FROM {db_prefix}quiz_dispute
				WHERE id_quiz_dispute = {int:id_quiz_dispute}',
				array(
					'id_quiz_dispute' => $id_dispute
				)
			);
		}
	}
	elseif (!empty($reason) && !empty($id_quiz_question))
	{
		// Otherwise someone is submitting a dispute
		$smcFunc['db_insert']('insert', 
			'{db_prefix}quiz_dispute',
			array(
				'id_quiz_question' => 'int',
				'id_quiz' => 'int',
				'id_user' => 'int',
				'reason' => 'string',
				'updated' => 'int'
			),
			array(
				$id_quiz_question,
				$id_quiz,
				$id_user,
				$reason,
				time()
			),
			array('id_quiz_dispute')
		);
	}

// @TODO move to a template?
	// Just write out some arbitrary XML for the client
	header("Content-Type: text/xml");
	echo '<xml/>';
	die();
}
?>