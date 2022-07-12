<?php

namespace Quiz;

if (!defined('SMF'))
	die('Hacking attempt...');

class Integration
{
	public static function init()
	{
		Integration::setVersion();

		add_integration_function('integrate_autoload', __CLASS__ . '::autoload', false);
		add_integration_function('integrate_admin_areas', __CLASS__ . '::admin_areas', false);
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menu_buttons', false);
		add_integration_function('integrate_actions', __CLASS__ . '::actions', false);
		add_integration_function('integrate_load_permissions', __CLASS__ . '::permissions', false);
		add_integration_function('integrate_load_illegal_guest_permissions', __CLASS__ . '::illegal_guest_permissions', false);
		add_integration_function('integrate_load_permissions', __CLASS__ . '::permissions', false);
	}

	public static function setVersion()
	{
		global $modSettings;

		$defaults = [
			'SMFQuiz_version' => '2.0 Beta 1',
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
		$permissionList['membergroup']['quiz_admin'] = array(false, 'quiz');
	}

	public static function illegal_guest_permissions()
	{
		global $context;

		$context['non_guest_permissions'][] = 'quiz_play';
		$context['non_guest_permissions'][] = 'quiz_submit';
		$context['non_guest_permissions'][] = 'quiz_admin';
	}

	public static function admin_areas(&$admin_areas)
	{
		global $txt, $modSettings, $scripturl;

		loadLanguage('Quiz/Admin');
		loadLanguage('Quiz/Quiz');
		loadLanguage('Quiz/Common');

		$admin_areas['quiz'] = array(
			'title' => $txt['SMFQuiz'],
			'permission' => array('quiz_admin'),
			'areas' => array(
				'quiz' => array(
					'label' => $txt['SMFQuiz'],
					'file' => 'Quiz/Admin.php',
					'function' => 'SMFQuizAdmin',
					'icon' => 'icons/quiz.png',
					'permission' => array('quiz_admin'),
					'subsections' => array(
						'adminCenter' => array($txt['SMFQuizAdmin_Titles']['AdminCenter']),
						'settings' => array($txt['SMFQuizAdmin_Titles']['Settings']),
						'quizes' => array($txt['SMFQuizAdmin_Titles']['Quizes']),
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
	}

	public static function menu_buttons(&$buttons)
	{
		global $txt, $modSettings, $scripturl;

		loadLanguage('Quiz/Quiz');

		$before = 'mlist';
		$temp_buttons = array();
		foreach ($buttons as $k => $v)
		{
			if ($k == $before)
			{
				$temp_buttons['SMFQuiz'] = array(
					'title' => $txt['SMFQuiz'],
					'href' => $scripturl . '?action=SMFQuiz',
					'icon' => 'icons/quiz.png',
					'show' => allowedTo('quiz_view') && !empty($modSettings['SMFQuiz_enabled']),
					'sub_buttons' => array(),
				);
			}
			$temp_buttons[$k] = $v;
		}
		$buttons = $temp_buttons;
	}
}