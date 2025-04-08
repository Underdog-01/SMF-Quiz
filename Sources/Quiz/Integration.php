<?php

namespace Quiz;

use Quiz\Helper;

if (!defined('SMF'))
	die('Hacking attempt...');

class Integration
{
	public static function init()
	{
		global $txt, $sourcedir, $context;

		loadLanguage('Quiz/Common+Quiz/Quiz+Quiz/Admin');
		self::setVersion();
		self::loadClasses();
		self::quiz_uninstall_options();


		add_integration_function('integrate_autoload', __CLASS__ . '::autoload', false);
		add_integration_function('integrate_admin_areas', __CLASS__ . '::admin_areas', false);
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menu_buttons', false);
		add_integration_function('integrate_actions', __CLASS__ . '::actions', false);
		add_integration_function('integrate_load_permissions', __CLASS__ . '::permissions', false);
		add_integration_function('integrate_load_illegal_guest_permissions', __CLASS__ . '::illegal_guest_permissions', false);
		add_integration_function('integrate_pre_css_output', __CLASS__ . '::preCSS', false);
		add_integration_function('integrate_pre_profile_areas', __NAMESPACE__ . '\Profile::profile_areas', false);
		add_integration_function('integrate_user_info', __NAMESPACE__ . '\Profile::user_info', false);
	}

	public static function loadClasses()
	{
		global $sourcedir;

		// Ensure the Quiz classes are available
		$neededClasses = ['Tasks\Scheduled', 'Helper', 'ForceUTF8'];
		foreach ($neededClasses as $class) {
			if (!class_exists(__NAMESPACE__  . '\\' . $class)) {
				require_once($sourcedir . '/' . __NAMESPACE__  . '/' . (str_replace('\\', '/', $class)) . '.php');
			}
		}
	}

	public static function setVersion()
	{
		global $modSettings;

		$defaults = [
			'SMFQuiz_version' => $modSettings['smf_quiz_version'],
			'SMFQuiz_ListPageSizes' => 20,
			'SMFQuiz_InfoBoardItemsToDisplay' => 20,
			'SMFQuiz_showUserRating' => 1,
			'SMFQuiz_AutoClean' => 'on',
			'SMFQuiz_SessionTimeLimit' => 30,

			// Results
			'SMFQuiz_0to19' => 'Oh dear, you really were poor in that quiz.',
			'SMFQuiz_20to39' => 'That was not your best effort now was it?',
			'SMFQuiz_40to59' => 'Well - You could have done better. Mediocrity is not the end of the world!',
			'SMFQuiz_60to79' => 'That is a pretty good score, well done.',
			'SMFQuiz_80to99' => 'Good score, we like that!',
			'SMFQuiz_99to100' => 'WOW - You are simply amazing. That is a Perfect Score! Did you Google those answers?',
		];
		$modSettings = array_merge($defaults, $modSettings);
	}

	public static function autoload(&$classMap)
	{
		$classMap['Quiz\\'] = 'Quiz/';
	}

	// Hook Add Action
	public static function actions(&$actionArray)
	{
		$actionArray['SMFQuiz'] = array('Quiz/Quiz.php', 'SMFQuiz');
		$actionArray['SMFQuizAnswers'] = array('Quiz/Answers.php', 'UpdateSession');
		$actionArray['SMFQuizStart'] = array('Quiz/Start.php', 'loadQuiz');
		$actionArray['SMFQuizEnd'] = array('Quiz/End.php', 'endQuiz');
		$actionArray['SMFQuizQuestions'] = array('Quiz/Questions.php', 'quizQuestions');
		$actionArray['SMFQuizDispute'] = array('Quiz/Dispute.php', 'quizDispute');
		$actionArray['SMFQuizAjax'] = array('Quiz/Ajax.php', 'quizImageUpload');
		$actionArray['SMFQuizExport'] = array('Quiz/Export.php', 'quizExport');
	}

	// Permissions
	public static function permissions(&$permissionGroups, &$permissionList)
	{
		$permissionGroups['membergroup'][] = 'quiz';
		$permissionList['membergroup']['quiz_view'] = array(false, 'quiz');
		$permissionList['membergroup']['quiz_play'] = array(false, 'quiz');
		$permissionList['membergroup']['quiz_submit'] = array(false, 'quiz');
		$permissionList['membergroup']['quiz_profile'] = array(false, 'quiz');
		$permissionList['membergroup']['quiz_admin'] = array(false, 'quiz');
	}

	public static function illegal_guest_permissions()
	{
		global $context;

		$context['non_guest_permissions'][] = 'quiz_play';
		$context['non_guest_permissions'][] = 'quiz_submit';
		$context['non_guest_permissions'][] = 'quiz_admin';
		$context['non_guest_permissions'][] = 'quiz_profile';
	}

	public static function admin_areas(&$admin_areas)
	{
		global $txt, $modSettings, $scripturl, $sourcedir, $context;
		$admin_areas['quiz'] = array(
			'title' => $txt['SMFQuiz'],
			'permission' => array('quiz_admin'),
			'areas' => array(
				'quiz' => array(
					'label' => $txt['SMFQuizAdmin_Titles']['Main'],
					'file' => 'Quiz/Admin.php',
					'function' => 'SMFQuizAdmin',
					'icon' => '../../quiz_images/Admin/quiz.png',
					'permission' => array('quiz_admin'),
					'subsections' => array(
						'adminCenter' => array($txt['SMFQuizAdmin_Titles']['AdminCenter']),
						'settings' => array($txt['SMFQuizAdmin_Titles']['Settings']),
						'quizzes' => array($txt['SMFQuizAdmin_Titles']['Quizzes']),
						'quizleagues' => array($txt['SMFQuizAdmin_Titles']['QuizLeagues']),
						'categories' => array($txt['SMFQuizAdmin_Titles']['Categories']),
						'questions' => array($txt['SMFQuizAdmin_Titles']['Questions']),
						'results' => array($txt['SMFQuizAdmin_Titles']['Results']),
						'disputes' => array($txt['SMFQuizAdmin_Titles']['Disputes']),
						'quizimporter' => array($txt['SMFQuizAdmin_Titles']['QuizImporter']),
						'maintenance' => array($txt['SMFQuizAdmin_Titles']['Maintenance']),
					),
				),
			),
		);
		// SMFQuiz_AdminPatch
		$quizAdminAreas = ['quizSettings', 'quizQuizzes', 'quizQuizLeagues', 'quizCategories', 'quizQuestions', 'quizResults', 'quizDisputes', 'quizQuizImporter', 'quizMaintenance'];
		foreach ($quizAdminAreas as $area) {
			$key = str_replace('quiz', '', $area);
			$admin_areas['quiz']['areas'][$area] = [
				'label' => $txt['SMFQuizAdmin_Titles'][$key],
				'file' => 'Quiz/Admin.php',
				'function' => 'SMFQuiz_AdminPatch',
				'icon' => '../../quiz_images/Admin/' . strtolower($key) . '.png',
				'permission' => ['quiz_admin'],
				'subsections' => [],
			];

		}
	}

	public static function menu_buttons(&$buttons)
	{
		global $txt, $modSettings, $scripturl;

		$before = 'mlist';
		$temp_buttons = array();
		foreach ($buttons as $k => $v)
		{
			if ($k == $before)
			{
				$temp_buttons['SMFQuiz'] = array(
					'title' => $txt['SMFQuiz'],
					'href' => $scripturl . '?action=SMFQuiz',
					'icon' => 'quiz',
					'show' => allowedTo('quiz_view') && !empty($modSettings['SMFQuiz_enabled']),
					'sub_buttons' => array(),
				);
			}
			$temp_buttons[$k] = $v;
		}
		$buttons = $temp_buttons;
	}

	public static function preCSS()
	{
		global $settings;

		// Add the icon using inline css
		addInlineCss('
			.main_icons.quiz::before {
				background-position: 0;
				background-image: url("' . $settings['default_images_url'] . '/icons/quiz.png");
				background-size: contain;
			}
		');
	}

	public static function quiz_uninstall_options()
	{
		global $context, $txt;

		// custom remove all data info
		$context['html_headers'] = !empty($context['html_headers']) ? $context['html_headers'] : '';
		foreach(array('action', 'area', 'sa', 'package') as $request) {
			$$request = !empty($_REQUEST[$request]) && is_string($_REQUEST[$request]) ? $_REQUEST[$request] : '';
		}
		$actionCheck = stripos($action, 'admin;area=packages;sa=uninstall;package=') !== FALSE && stripos($action, 'smf-quiz') !== FALSE;
		if ((array($action, $area, $sa) == array('admin', 'packages', 'uninstall') && stripos($package, 'smf-quiz') !== FALSE) || $actionCheck) {
			$context['html_headers'] .= '
			<script>
				$(document).ready(function(){
					$("input[name=\'do_db_changes\']").css({"display":"inline-flex","flex-direction":"column","align-self":"center","margin":"0.5rem"});
					$("#db_changes_div > ul.normallist li").remove();
					$("#db_changes_div > ul.normallist").append("<li>' . $txt['quiz_uninstall_db'] . '</li>");
					$("#db_changes_div > ul.normallist").append("<li>' . $txt['quiz_uninstall_files'] . '</li>");
					$("#db_changes_div").append(\'<span style="font-weight: bold;">' . $txt['quiz_uninstall_warning'] . '</span>\');
				});
			</script>';
		}
	}
}