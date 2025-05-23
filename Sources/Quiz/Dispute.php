<?php

if (!defined('SMF'))
	die('Hacking attempt...');

// @TODO move to another file
function quizDispute()
{
	global $smcFunc, $context, $user_settings, $sourcedir, $modSettings, $scripturl, $txt;

	// Get passed variables from client
	// @TODO sanitize (check reason)

	$usersPrefs = Quiz\Helper::quiz_usersAcknowledge('quiz_pm_alert');
	list($sentTo, $admins) = [[], []];
	$id_quiz_question = isset($_POST["id_quiz_question"]) ? (int)$_POST["id_quiz_question"] : 0;
	$id_quiz = isset($_POST["id_quiz"]) ? (int) $_POST["id_quiz"] : 0;
	$reason = isset($_POST["reason"]) ? Quiz\Helper::quiz_pmFilter(urldecode($_POST["reason"])) : '';
	$id_user = $context['user']['id'];
	$id_dispute = isset($_POST["id_dispute"]) ? (int) $_POST["id_dispute"] : 0;
	$auxID = !empty($modSettings['SMFQuiz_ImportQuizzesAsUserId']) ? (int)$modSettings['SMFQuiz_ImportQuizzesAsUserId'] : 0;

	// If the id_dispute is set then the admin is responding
	if ($id_dispute != 0 && allowedTo('quiz_admin'))
	{
		require_once($sourcedir . '/Subs-Post.php');

		$remove = isset($_POST["remove"]) ? (int) $_POST["remove"] : 0;

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
			[
				'id_quiz_dispute' => $id_dispute
			]
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			if (in_array($row['id_user'], $usersPrefs)) {
				$pmto = [
					'to' => array(),
					'bcc' => array($row['id_user'])
				];

	// @TODO localization
				$subject = sprintf($txt['quiz_dispute_userpm_subject'], (int)$id_dispute);
				// @TODO check how the html_entity_decode work (+ UTF8?)
				$message = sprintf($txt['quiz_dipute_userpm_message'], Quiz\Helper::quiz_pmFilter($row['title']), Quiz\Helper::quiz_pmFilter($row['reason']), Quiz\Helper::quiz_pmFilter($row['question_text']), Quiz\Helper::quiz_pmFilter($reason));

				if ($remove == 1)
					$message .= Quiz\Helper::quiz_pmFilter($txt['quiz_dipute_userpm_msg_del']);

				$user_name = !empty($user_settings['real_name']) ? $user_settings['real_name'] : (!empty($user_settings['member_name']) ? $user_settings['member_name'] : '');
				$pmfrom = [
					'id' => $user_settings['id_member'],
					'name' => $user_name,
					'username' => $user_name
				];

				if (!empty($modSettings['SMFQuiz_DisputeAux']) && !empty($auxID)) {
					$quiz_name = Quiz\Helper::quiz_userInfoName($auxID);
					if (!empty($quiz_name)) {
						$pmfrom = [
							'id' => $auxID,
							'name' => $quiz_name,
							'username' => $quiz_name
						];
					}
				}

				// Send message
				sendpm($pmto, $subject, Quiz\Helper::quiz_pmFilter($message), 0, $pmfrom);
				$sentTo[] = $row['id_user'];
			}
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
				[
					'id_quiz_dispute' => $id_dispute
				]
			);
		}
	}
	elseif (!empty($reason) && !empty($id_quiz_question))
	{
		// Gather the user ids of Quiz admins that want dispute PM's
		$usersPrefs = Quiz\Helper::quiz_usersAcknowledge('quiz_pm_report');
		$quizAdmins = array_values(array_filter(array_intersect(Quiz\Helper::quiz_usersAllowedTo('quiz_admin'), $usersPrefs)));

		if (!empty($quizAdmins)) {
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
				['id_quiz_dispute']
			);

			$user_name = !empty($user_settings['real_name']) ? $user_settings['real_name'] : (!empty($user_settings['member_name']) ? $user_settings['member_name'] : '');
			$pmfrom = [
				'id' => $user_settings['id_member'],
				'name' => $user_name,
				'username' => $user_name
			];

			if (!empty($modSettings['SMFQuiz_DisputeAux']) && !empty($auxID)) {
				$quiz_name = Quiz\Helper::quiz_userInfoName($auxID);
				if (!empty($quiz_name)) {
					$pmfrom = [
						'id' => $auxID,
						'name' => $quiz_name,
						'username' => $quiz_name
					];
				}
			}

			require_once($sourcedir . '/Subs-Post.php');
			sendpm(
				['to' => [], 'bcc' => $quizAdmins],
				Quiz\Helper::quiz_pmFilter($txt['quiz_dispute_pmtitle']),
				Quiz\Helper::quiz_pmFilter(sprintf($txt['quiz_dispute_report'], $scripturl . '?action=admin;area=quiz;sa=disputes') . sprintf($txt['quizDisputeReason'], $reason)),
				0,
				$pmfrom
			);
		}
	}

// @TODO move to a template?
	// Just write out some arbitrary XML for the client
	header("Content-Type: text/xml");
	echo '<xml/>';
	die();
}
?>