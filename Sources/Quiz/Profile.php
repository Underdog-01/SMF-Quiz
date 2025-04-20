<?php

namespace Quiz;

use Quiz\Profile;

if (!defined('SMF'))
	die('Hacking attempt...');

class Profile
{
	public static function user_info()
	{
		global $modSettings, $smcFunc, $user_info;

		$profileSets = [
			'int' => array('quiz_pm_report', 'quiz_pm_alert', 'quiz_count'),
		];

		// set defaults before the quiz profile query
		list($user_info['quiz_pm_report'], $user_info['quiz_pm_alert'], $user_info['quiz_count']) = [0, 1, 0];

		// Load Quiz profile settings and add them to $user_info
		if (!empty($user_info['id']) && $user_info['id'] > 0)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member, quiz_pm_report, quiz_pm_alert, quiz_count
				FROM {db_prefix}quiz_members
				WHERE id_member = {int:member}',
				array(
					'member' => $user_info['id'],
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				foreach ($profileSets as $key => $update) {
					foreach ($update as $set) {
						switch($key) {
							case 'int':
								$user_info[$set] = !empty($row[$set]) ? (int)$row[$set] : 0;
								break;
							default:
								$user_info[$set] = !empty($row[$set]) ? (string)$row[$set] : '';
						}
					}
				}
			}

			$smcFunc['db_free_result']($request);
		}

	}

	public static function profile_areas(&$profile_areas)
	{
		global $modSettings, $txt;

		$profile_areas['edit_profile']['areas'] += array(
			'quizProfileSettings' => array(
				'label' => $txt['quiz_profileSettings'],
				'file' => '/Quiz/Profile.php',
				'function' => 'Quiz\Profile::custom_profile_settings',
				'icon' => '../quiz_images/quiz_profile.png',
				'enabled' => !empty($modSettings['SMFQuiz_enabled']) ? true : false,
				'permission' => array(
					'own' => array('quiz_profile'),
					'any' => array('quiz_admin'),
				),
			),
		);
	}

	public static function custom_profile_settings($memID)
	{
		global $context, $txt, $modSettings, $user_info, $scripturl, $smcFunc;

		$context['profile_fields'] = !empty($context['profile_fields']) ? $context['profile_fields'] : [];
		$member_info = [];

		// Current user settings which are not adjusted in profile ~ (int)'' = 0
		if ($user_info['id'] == $memID || empty($memID)) {
			foreach (['quiz_pm_report', 'quiz_pm_alert', 'quiz_count'] as $key => $userSet) {
				$member_info[$userSet] = !empty($user_info[$userSet]) ? $user_info[$userSet] : '';
			}
		}
		else {
			$member_info = Helper::quiz_userPrefs($memID);
		}

		if (!empty($modSettings['SMFQuiz_enabled']) && allowedTo('quiz_admin')) {
			$context['profile_fields'] += [
				'quiz_pm_report' => [
					'type' => 'check',
					'label' => $txt['quiz_pm_report'],
					'permission' => 'quiz_admin',
					'enabled' => true,
					'input_attr' => '',
					'name' => 'quiz_pm_report',
					'value' => !empty($member_info) ? (int)$member_info['quiz_pm_report'] : 0,
				],
			];
		}
		if (!empty($modSettings['SMFQuiz_enabled']) && (allowedTo('quiz_admin') || allowedTo('quiz_profile'))) {
			$context['profile_fields'] += [
				'quiz_pm_alert' => [
					'type' => 'check',
					'label' => $txt['quiz_pm_alert'],
					'permission' => 'quiz_view',
					'enabled' => true,
					'input_attr' => '',
					'name' => 'quiz_pm_alert',
					'value' => !empty($member_info) ? (int)$member_info['quiz_pm_alert'] : 0,
				],
			];
		}

		if (isset($_REQUEST['save']))
		{
			checkSession('post');
			list($errors, $updates) = array(false, []);

			foreach ($context['profile_fields'] as $id => $field)
			{
				if ($id == 'notifications' || !isset($_POST[$id]))
					continue;

				switch($field['type']) {
					case 'check':
						$_POST[$id] = (int)$_POST[$id];
						break;
					default:
						if (isset($field['options'][$_POST[$id]])) {
							$updates[] = [$memID, $id, $_POST[$id]];
						}
				}
			}

			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}quiz_members
				WHERE id_member = {int:memID}',
				array(
					'memID' => $memID,
				)
			);

			foreach (array('quiz_pm_report', 'quiz_pm_alert', 'quiz_count') as $key => $userSet) {
				$_POST[$userSet] = !empty($_POST[$userSet]) ? $_POST[$userSet] : '';
			}

			$smcFunc['db_insert']('insert',
				'{db_prefix}quiz_members',
				[
					'id_member' => 'int', 'quiz_pm_report' => 'int', 'quiz_pm_alert' => 'int', 'quiz_count' => 'int',
				],
				[
					$memID, (int)$_POST['quiz_pm_report'], (int)$_POST['quiz_pm_alert'],  (int)$user_info['quiz_count'],
				],
				['id_member']
			);

			unset($_REQUEST['save']);
			redirectexit($scripturl . '?action=profile;area=quizProfileSettings;u=' . $memID);
		}

		$context['profile_custom_submit_url'] = $scripturl . '?action=profile;area=quizProfileSettings;u=' . $memID . ';save';
		$context['page_desc'] = $txt['quiz_profileSettings_title'];
		$context['sub_template'] = 'edit_options';
	}
}