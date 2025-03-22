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

			$profileSets = [
				'int' => array('quiz_pm_report', 'quiz_pm_alert', 'quiz_count'),
			];

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
				'icon' => 'portal_profile',
				'enabled' => !empty($modSettings['SMFQuiz_enabled']),
				'permission' => array(
					'own' => array('quiz_profile_settings'),
					'any' => array('quiz_admin'),
				),
			),
		);
	}

	public static function custom_profile_settings($memID)
	{
		global $context, $txt, $modSettings, $user_info, $scripturl, $smcFunc;

		$context['profile_fields'] = !empty($context['profile_fields']) ? $context['profile_fields'] : [];

		// Current user settings which are not adjusted in profile ~ (int)'' = 0
		foreach (array('quiz_count') as $key => $userSet) {
			$user_info[$userSet] = !empty($user_info[$userSet]) ? $user_info[$userSet] : '';
		}
		$context['profile_fields'] += array(
			'quiz_pm_report' => array(
				'type' => 'check',
				'label' => $txt['quiz_pm_report'],
				'permission' => 'quiz_admin',
				'enabled' => !empty($modSettings['SMFQuiz_enabled']),
				'input_attr' => '',
				'name' => 'quiz_pm_report',
				'value' => !empty($user_info['quiz_pm_report']) ? (int)$user_info['quiz_pm_report'] : 0,
			),
			'quiz_pm_alert' => array(
				'type' => 'check',
				'label' => $txt['quiz_pm_alert'],
				'permission' => 'quiz_view',
				'enabled' => !empty($modSettings['SMFQuiz_enabled']),
				'input_attr' => '',
				'name' => 'quiz_pm_alert',
				'value' => !empty($user_info['quiz_pm_alert']) ? (int)$user_info['quiz_pm_alert'] : 0,
			),
		);

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

			$smcFunc['db_insert']('insert',
				'{db_prefix}quiz_members',
				[
					'id_member' => 'int', 'quiz_pm_report' => 'int', 'quiz_pm_alert' => 'int', 'quiz_count' => 'int',
				],
				[
					$memID, $_POST['quiz_pm_report'], $_POST['quiz_pm_alert'],  (int)$user_info['quiz_count'],
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