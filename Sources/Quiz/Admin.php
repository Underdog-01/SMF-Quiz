<?php

if (!defined('SMF'))
	die('Hacking attempt...');

//Main function
function SMFQuizAdmin()
{
	global $context, $modSettings, $boardurl, $scripturl, $txt, $sourcedir, $settings;

	isAllowedTo('quiz_admin');

	// @TODO are both needed?
	require_once($sourcedir . '/Quiz/Db.php');
	require_once($sourcedir . '/Quiz/Load.php');
	require_once($sourcedir . '/ManageServer.php');
	$qv = !empty($modSettings['smf_quiz_version']) && (stripos($modSettings['smf_quiz_version'], '-beta') !== FALSE || stripos($modSettings['smf_quiz_version'], '-rc') !== FALSE) ? bin2hex(random_bytes(12/2)) : 'stable';
	$quizVarsJS = 'let smfQuizVersion = "' . $modSettings['smf_quiz_version'] . '";';
	foreach ((array_merge($txt['quizLocalizationTextJS'], $txt['quizLocalizationAdminAlertsJS'])) as $key => $val) {
		$quizVarsJS .= '
			let ' . $key . ' = "' . $val . '";';
	}

	$context['html_headers'] .= '
		<script>
			' . ($quizVarsJS) . '
		</script>
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/quiz/QuizAdmin.css?v=' . $qv . '"/>
		<script src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery.selectboxes.js?v=' . $qv . '"></script>
		<script src="' . $settings['default_theme_url'] . '/scripts/quiz/QuizAdmin.js?v=' . $qv . '"></script>';

	// This uses admin tabs - as it should!
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['SMFQuiz'],
		'help' => 'SMFQuizMod',
		'description' => $txt['SMFQuizModDescription'],
	);

	$modSettings['disableQueryCheck'] = 1;
	$subActions = array(
		'settings' => array(
			'function' => 'GetSettingsData',
			'text' => $txt['SMFQuizAdmin_Titles']['Settings'],
		),
		'results' => array(
			'function' => 'GetResultsData',
			'text' => $txt['SMFQuizAdmin_Titles']['Results'],
		),
		'disputes' => array(
			'function' => 'GetShowDisputesData',
			'text' => $txt['SMFQuizAdmin_Titles']['Disputes'],
		),
		'deldisputes' => array(
			'function' => 'GetDeleteQuizDisputeData',
			'text' => $txt['SMFQuizAdmin_Titles']['Disputes'],
		),
		'quizzes' => array(
			'function' => 'GetQuizData',
			'text' => $txt['SMFQuizAdmin_Titles']['Quizzes'],
		),
		'quizleagues' => array(
			'function' => 'GetQuizLeagueData',
			'text' => $txt['SMFQuizAdmin_Titles']['QuizLeagues'],
		),
		'categories' => array(
			'function' => 'GetCategoryData',
			'text' => $txt['SMFQuizAdmin_Titles']['Categories'],
		),
		'questions' => array(
			'function' => 'GetQuestionData',
			'text' => $txt['SMFQuizAdmin_Titles']['Questions'],
		),
		'maintenance' => array(
			'function' => 'GetMaintenanceData',
			'text' => $txt['SMFQuizAdmin_Titles']['Maintenance'],
		),
		'quizimporter' => array(
			'function' => 'GetQuizImportData',
			'text' => $txt['SMFQuizAdmin_Titles']['QuizImporter'],
		),
		'admincenter' => array(
			'function' => 'GetAdminCenterData',
			'text' => $txt['SMFQuizAdmin_Titles']['AdminCenter'],
		),
	);

	$context['current_subaction'] = isset($_REQUEST['sa']) && in_array($_REQUEST['sa'], array_keys($subActions)) ? $_REQUEST['sa'] : 'admincenter';
	$context['page_title'] = $txt['SMFQuiz'] . ' - ' . $subActions[$context['current_subaction']]['text'];
	$subActions[$context['current_subaction']]['function']();

	// Load the admin template to present the data
	loadTemplate('Quiz/Admin');
}

function SMFQuiz_AdminPatch()
{
	global $scripturl, $context, $txt;

	$areaArray = [
		'quizSettings' => 'settings',
		'quizResults' => 'results',
		'quizDisputes' => 'disputes',
		'quizQuizzes' => 'quizzes',
		'quizQuizLeagues' => 'quizleagues',
		'quizCategories' => 'categories',
		'quizQuestions' => 'questions',
		'quizMaintenance' => 'maintenance',
		'quizQuizImporter' => 'quizimporter',
	];
	$area = isset($_REQUEST['area']) && is_string($_REQUEST['area']) ? $_REQUEST['area'] : '';
	if (!empty($area) && array_key_exists($area, $areaArray)) {
		$context['html_headers'] .= '
		<script>
			$(document).ready(function(){
				setTimeout(function(){
					window.location.href = "' . $scripturl . '?index.php;action=admin;area=quiz;sa=' . $areaArray[$area] . '";
				}, 10);
			});
		</script>';
		//redirectexit($scripturl . '?index.php;action=admin;area=quiz;sa=' . $areaArray[$area]);
	}
	$context['page_title'] = $txt['SMFQuiz'];
	$context['current_subaction'] = 'patch';
	loadTemplate('Quiz/Admin');
}

function GetMaintenanceData()
{
	global $context, $txt;

	loadLanguage('ManageMaintenance');

	$context['quiz_mtasks'] = ['FindOrphanQuestions', 'FindOrphanAnswers', 'FindOrphanQuizResults', 'FindOrphanCategories'];
	$context['html_headers'] .= '<script type="text/javascript"><!-- // --><![CDATA[
			function clearResults(thisform)
			{
				thisform.formaction.value = "resetQuizzes";
				if(confirm(\'' . $txt['SMFQuizAdmin_Maintenance_Page']['ResetAllQuizData'] . '\'))
					thisform.submit();
				else
					return false;
			}
	// ]]></script>';

	// User has selected to reset the quiz scores
	if (isset($_POST['formaction']) && $_POST['formaction'] == 'resetQuizzes')
	{
		ResetQuizResults();
		ResetQuizTopScores();
		$context['MaintenanceResult'] = $txt['quiz_maint_results_removed'];
	}

	// User has selected to complete the quiz sessions
	if (isset($_POST['btnCompleteSessions']))
	{
		if ($_POST['txtSessionDays'] != 0)
			$date  = mktime(0, 0, 0, date("m")  , date("d") - $_POST['txtSessionDays'], date("Y"));
		else
			$date = mktime(0, 0, 0, date("m")  , date("d") + 1, date("Y"));

		$rows = CompleteQuizSessions($date);

		$context['MaintenanceResult'] = sprintf($txt['quiz_maint_sessions_removed'], $rows);
	}

	// User has selected to clean the infoboard
	if (isset($_POST['btnCleanInfoBoard']))
	{
		if ($_POST['txtInfoBoardDays'] != 0)
			$date  = mktime(0, 0, 0, date("m")  , date("d") - $_POST['txtInfoBoardDays'], date("Y"));
		else
			$date = mktime(0, 0, 0, date("m")  , date("d") + 1, date("Y"));

		$rows = DeleteInfoBoardEntries($date);
		$context['MaintenanceResult'] = $txt['quiz_maint_infoboard_entries_removed'];
	}

		// @TODO can be replaced by a for?
	// User has selected to find orphaned question data
	if (isset($_POST['btnFindOrphanQuestions']))
		FindOrphanedQuestionsData();

	// User has selected to find orphaned answer data
	if (isset($_POST['btnFindOrphanAnswers']))
		FindOrphanedAnswersData();

	// User has selected to find orphaned quiz result data
	if (isset($_POST['btnFindOrphanQuizResults']))
		FindOrphanedQuizResultsData();

	// User has selected to find orphaned categories data
	if (isset($_POST['btnFindOrphanCategories']))
		FindOrphanedCategoriesData();

	// User has selected to delete orphaned questions
	if (isset($_POST['btnDeleteOrphanedQuestions']))
		DeleteOrphanedQuestionsData();

	// User has selected to delete orphaned answers
	if (isset($_POST['btnDeleteOrphanedAnswers']))
		DeleteOrphanedAnswersData();

	// User has selected to delete orphaned quiz results
	if (isset($_POST['btnDeleteOrphanedQuizResults']))
		DeleteOrphanedQuizResultsData();

	// User has selected to delete orphaned categories
	if (isset($_POST['btnDeleteOrphanedCategories']))
		DeleteOrphanedCategoriesData();
}

function ParseMessage($message, $quiztitle, $total_seconds, $total_points, $top_time, $top_points, $quizImage, $scripturl, $id_quiz, $old_member_name)
{
		// @TODO single replace
	global $user_settings;

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

function ParseLeagueMessage($message, $quizLeagueName, $oldPosition, $newPosition, $positionMovement, $id_quiz_league)
{
	global $user_settings, $scripturl;

		// @TODO single replace
	$message = str_replace("{quiz_league_name}", $quizLeagueName, $message);
	$message = str_replace("{old_position}", $oldPosition, $message);
	$message = str_replace("{new_position}", $newPosition, $message);
	$message = str_replace("{position_movement}", $positionMovement, $message);
	$message = str_replace("{quiz_league_link}", $scripturl . '?action=SMFQuiz;sa=quizleagues;id=' . $id_quiz_league, $message);
	return $message;
}

// @TODO review the saving with some hard-coded numbers
function GetSettingsData($return_config = false)
{
	global $settings, $modSettings, $user_settings, $scripturl, $sourcedir, $context, $txt;

	$context['settings_title'] = $txt['SMFQuizAdmim_Settings_Page']['GeneralSettings'];
	$context['post_url'] = $scripturl . '?action=admin;area=quiz;sa=settings;save';

	$config_vars = array(
		array('check', 'SMFQuiz_enabled', 'text_label' => $txt['SMFQuiz_Common']['QuizEnabled']),
		array('large_text', 'SMFQuiz_Welcome', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['WelcomeMessage']),
		array('check', 'SMFQuiz_showUserRating', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['ShowUserRating']),
		array('float', 'SMFQuiz_InfoBoardItemsToDisplay', 6, 'min' => 1, 'step' => 1, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['InfoBoardItemsToDisplay']),
		array('float', 'SMFQuiz_ListPageSizes', 6, 'min' => 1, 'step' => 1, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['ListPageSizes']),
		array('float', 'SMFQuiz_SessionTimeLimit', 6, 'min' => 0, 'step' => 1, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['SessionTimeLimit']),
		array('float', 'SMFQuiz_ImportQuizzesAsUserId', 6, 'min' => 0, 'step' => 1, 'help' => 'SMFQuiz_ImportQuizzesAsUserHelp', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['ImportQuizzesAsUser']),
		array('select', 'SMFQuiz_ZipExport', explode('|', $txt['SMFQuiz_ExportType']), 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['QuizZipExport']),
		array('check', 'SMFQuiz_DisputeAux', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['DisputeAuxUser']),
		array('check', 'SMFQuiz_AutoClean', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['QuizAutoClean']),
		array('title', 'QuizCompletionSettings', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['QuizCompletionSettings']),
		array('text', 'SMFQuiz_0to19', 50, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['Score0to19']),
		array('text', 'SMFQuiz_20to39', 50, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['Score20to39']),
		array('text', 'SMFQuiz_40to59', 50, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['Score40to59']),
		array('text', 'SMFQuiz_60to79', 50, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['Score60to79']),
		array('text', 'SMFQuiz_80to99', 50, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['Score80to99']),
		array('text', 'SMFQuiz_99to100', 50, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['Score100']),
		array('title', 'QuizMessagingSettings', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['QuizMessagingSettings']),
		array('check', 'SMFQuiz_SendPMOnBrokenTopScore', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['SendPMOnBrokenTopScore']),
		array('text', 'SMFQuiz_PMBrokenTopScoreSubject', 50, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['PMBrokenTopScoreMsg'] . ' - ' . $txt['SMFQuiz_Common']['Subject']),
		array('large_text', 'SMFQuiz_PMBrokenTopScoreMsg', 10, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['PMBrokenTopScoreMsg'] . ' - ' . $txt['SMFQuiz_Common']['MessageBody'],
			'postinput' => '<br/><img style="cursor:pointer" class="preview_loading" onclick="submitPreview(1);" src="' .$settings['default_images_url'] . '/quiz_images/preview.png" title="' . $txt['SMFQuiz_Common']['Preview'] . '" alt="' . $txt['SMFQuiz_Common']['Preview'] . '" />',
			'help' => 'quiz_mod_pm_placeolders'),
		array('check', 'SMFQuiz_SendPMOnLeagueRoundUpdate', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['SendPMOnLeagueRoundUpdate']),
		array('text', 'SMFQuiz_PMLeagueRoundUpdateSubject', 50, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['PMLeagueRoundUpdateMsg'] . ' - ' . $txt['SMFQuiz_Common']['Subject']),
		array('large_text', 'SMFQuiz_PMLeagueRoundUpdateMsg', 10, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['PMLeagueRoundUpdateMsg'] . ' - ' . $txt['SMFQuiz_Common']['MessageBody'],
			'postinput' => '<br/><img style="cursor:pointer" class="preview_loading" onclick="submitPreview(2);" src="' .$settings['default_images_url'] . '/quiz_images/preview.png" title="' . $txt['SMFQuiz_Common']['Preview'] . '" alt="' . $txt['SMFQuiz_Common']['Preview'] . '" />',
			'help' => 'quiz_mod_pm_placeolders'),
	);

	if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);

		redirectexit('action=admin;area=quiz;sa=settings');
	}

	prepareDBSettingContext($config_vars);
	$context['sub_template'] = 'show_settings';
	// @TODO reimplement the preview
}

// Function to get appropriate data in the category administration area
function GetCategoryData()
{
	// If CategoryAction has been set it means the user has clicked on one of the buttons
	if (isset($_POST['NewCategory'])) // User wants to create a new Category
		GetNewCategoryData();
	elseif (isset($_POST['DeleteCategory'])) // User wants to delete the specified category
		GetDeleteCategoryData();
	elseif (isset($_POST['UpdateCategory']))
		GetUpdateCategoryData();
	elseif (isset($_POST['SaveCategory'])) // User has selected to save a new category
		GetSaveCategoryData();
	// @TODO ParentCategory?
	elseif (!isset($_POST['CategoryAction']))
	{
		if (isset($_GET["id"]))
			GetEditCategoryData();
		elseif (isset($_GET["children"])) {
			GetCategoryChildrenData($_GET["children"]);
			QuizGetCategoryParentsWithChild();
		}
		elseif (isset($_GET["parent"]))
			GetParentCategoryData($_GET["parent"]);
		// Otherwise just get the default category data
		else {
			GetCategoryChildrenData(0);
			QuizGetCategoryParentsWithChild();
		}
	}
}

// Function to get appropriate data in the question administration area
function GetQuestionData()
{
	global $txt, $context;

	if (isset($_POST['id_quiz']))
		$context['SMFQuiz']['id_quiz'] = $_POST['id_quiz'];
	elseif (isset($_GET['id_quiz']))
		$context['SMFQuiz']['id_quiz'] = $_GET['id_quiz'];
	else
		$context['SMFQuiz']['id_quiz'] = 0;

	// If QuestionAction has been set it means the user has clicked on one of the buttons
	if (isset($_POST['NewQuestion'])) // User wants to create a new Question
		GetNewQuestionData();
	elseif (isset($_POST['DeleteQuestion'])) // User wants to delete the specified question
		GetDeleteQuestionData();
	elseif (isset($_POST['SaveAndAddMore'])) // User has selected to save a new question and add more after
		GetSaveQuestionData(1);
	elseif (isset($_POST['SaveQuestion'])) // User has selected to save a new question
		GetSaveQuestionData(0);
	elseif (isset($_POST['UpdateQuestion'])) // User has selected to save a new question
		GetUpdateQuestionData(0);
	elseif (isset($_POST['UpdateQuestionAndAddMore'])) // User has selected to save a new question
		GetUpdateQuestionData(1);
	elseif (!isset($_POST["QuestionAction"]))
	{
		if (isset($_GET["id"]))
			GetEditQuestionData();
		else
			// Note that this function is in Quiz/Db.php
			GetQuestionsData($context['SMFQuiz']['id_quiz']);
	}
}

// Function to get appropriate data in the Quiz administration section
function GetQuizData()
{
	global $context;

	// If QuizAction has been set it means the user has clicked on one of the buttons
	if (isset($_POST['NewQuiz'])) // User wants to create a new Quiz
		GetNewQuizData();
	elseif (isset($_POST['DeleteQuiz'])) // User wants to delete the specified quiz
		GetDeleteQuizData();
	elseif (isset($_POST['UpdateQuizAndAddQuestions']) || isset($_POST['UpdateQuiz'])) // User has selected to save a new quiz and then enter questions into that quiz // User is updating a quiz
		GetUpdateQuizData();
	elseif (isset($_POST['QuizQuestions']))
	{
		$context['current_subaction'] = 'questions';
		$context['SMFQuiz']['id_quiz'] = $_POST['id_quiz'];
		GetQuestionsData($context['SMFQuiz']['id_quiz']);
	}
	elseif (isset($_POST['SaveQuizAndAddQuestions']) || isset($_POST['SaveQuiz']))  // User has selected to save a new quiz and then enter questions into that quiz // User has selected to save a new quiz
		GetSaveQuizData();
	elseif (!isset($_POST["QuizAction"]) && isset($_GET["id"]))
		GetEditQuizData();
	// Otherwise just get the default Quiz League data
	elseif (!isset($_POST["QuizAction"]))
		GetQuizzesData();
}


// Function to get appropriate data in the Results administration section
function GetResultsData()
{
	global $context;

	// If user is deleting results
	if (isset($_POST["QuizAction"]))
		GetDeleteQuizResultData();
	// Otherwise just get the default Results data
	else
		GetShowResultsData();
}

function GetDisputesData()
{
	global $context, $txt;

	GetShowDisputesData();
}

// Function to get appropriate data in the Quiz Leage administration section
function GetQuizLeagueData()
{
	global $context;

	// If user has clicked button to enable quiz league
	if (isset($_GET['enable_quizleague_id']))
		UpdateQuizLeagueStatus($_GET['enable_quizleague_id'], 1);

	// If user has clicked button to enable quiz league
	if (isset($_GET['disable_quizleague_id']))
		UpdateQuizLeagueStatus($_GET['disable_quizleague_id'], 0);

	// If QuizLeagueAction has been set it means the user has clicked on one of the buttons
	if (isset($_POST["QuizLeagueAction"]))
	{
		// Determine which action is being taken and do appropriate data work
		// @TODO localization
		switch ($_POST["QuizLeagueAction"])
		{
			case 'newleague' : // User wants to create a new Quiz league
				GetNewQuizLeagueData();
				break;

			case 'delete' : // User wants to delete the specified quiz league
				GetDeleteQuizLeagueData();
				break;

			case 'update' : // User is updating a quiz league
				GetUpdateQuizLeagueData();
				break;

			case 'save' : // User has selected to save a new quiz league
				GetSaveQuizLeagueData();
				break;
		}
	}
	elseif (isset($_GET["id"]))
		GetEditQuizLeagueData();
	// Otherwise just get the default Quiz League data
	else
	{
		// Note that this function is in Quiz/Db.php
		$context['SMFQuiz']['Action'] = 'ShowQuizLeagues';
		GetAllQuizLeagueDetails();
	}
}

// Function to get appropriate data in the new category section
function GetNewCategoryData()
{
	global $context;

	// The new category page provides a list of categories to select as a parent for the new category. Therefore we need to obtain
	// a list of category data
	GetAllCategoryDetails();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'NewCategory';
}

function GetNewQuestionData()
{
	global $context;

	if (isset($_POST['id_quiz']))
		$context['SMFQuiz']['id_quiz'] = $_POST['id_quiz'];

	// The new question page provides a list of quizzes to select. Therefore we need to obtain a list of category data
	GetAllQuizDetails();

	// The new question page provides a list of question types to select. Therefore we need to obtain a list of question type data
	GetAllQuestionTypes();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'NewQuestion';
}

function GetEditQuestionData()
{
	global $context;

		// @TODO ???
	isset($_GET['id']) ? $questionId = $_GET['id'] : 0;

	GetQuestionAndAnswers($questionId);

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'EditQuestion';
}

function GetEditCategoryData()
{
	global $context;

		// @TODO ???
	isset($_GET['id']) ? $categoryId = $_GET['id'] : 0;

	GetCategory($categoryId);

	// The edit category page also shows a list of parent categories, so we must get this data
	GetAllCategoryDetails();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'EditCategory';
}

function GetEditQuizData()
{
	global $context;

		// @TODO ???
	isset($_GET['id']) ? $quizId = $_GET['id'] : 0;

	GetQuiz($quizId);

	// The edit quiz page also shows a list of categories, so we must get this data
	GetAllCategoryDetails();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'EditQuiz';
}

function GetEditQuizLeagueData()
{
	global $context;

		// @TODO ???
	isset($_GET['id']) ? $id_quiz_league = $_GET['id'] : 0;

	GetQuizLeagueDetails($id_quiz_league);

	// The edit quiz league page also shows a list of categories, so we must get this data
	GetAllCategoryDetails();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'EditQuizLeague';
}

function GetNewQuizData()
{
	global $context;

	// The new quiz page also shows a list of categories, so we must get this data
	GetAllCategoryDetails();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'NewQuiz';
}

function GetNewQuizLeagueData()
{
	global $context;

	GetAllCategoryDetails();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'NewQuizLeague';
}

// From the specified id key, loop through the form variables and extract the associated identifiers. Return a string containing these
// identifiers in a comma separated list
function GetKeysFromPost($id)
{
	$deleteKeys = '';

	foreach($_POST as $key => $value)
		if (substr($key, 0, strlen($id)) == $id)
			$deleteKeys .= substr($key, strlen($id)) . ',';

	if (substr($deleteKeys, strlen($deleteKeys)-1) == ',')
		$deleteKeys = substr($deleteKeys, 0, strlen($deleteKeys)-1);

	return $deleteKeys;
}


function GetDeleteQuestionData()
{
	global $context;

	// Get the key ids for the questions to delete. This function returns a string containing a comma separated list of id's
	$deleteKeys = GetKeysFromPost('question');

	if (!empty($deleteKeys))
		DeleteQuestions($deleteKeys);

	// As we are going to return to the question list page after the delete, we need to load this data
	GetAllQuestionDetails(1, '', '', 0);

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'DeleteQuestion';
}

function GetDeleteCategoryData()
{
	global $context;

	// Get the key ids for the categories to delete. This function returns a string containing a comma separated list of id's
	$deleteKeys = GetKeysFromPost('cat');

	if (!empty($deleteKeys))
		DeleteCategories($deleteKeys);

	// As we are going to return to the category list page after the delete, we need to load this data
	GetAllCategoryDetails();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'DeleteCategory';
}

function GetDeleteQuizData()
{
	global $context;

	// Get the key ids for the quiz leagues to delete. This function returns a string containing a comma separated list of id's
	$deleteKeys = GetKeysFromPost('quiz');

	if (!empty($deleteKeys))
		DeleteQuizzes($deleteKeys);

	// As we are going to return to the quiz detail list page after the delete, we need to load this data
	GetQuizzesData();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'DeleteQuiz';
}

function GetDeleteQuizResultData()
{
	global $context;

	// Get the key ids for the quiz results to delete. This function returns a string containing a comma separated list of id's
	$deleteKeys = GetKeysFromPost('quiz_result');

	if (!empty($deleteKeys))
		DeleteQuizResults($deleteKeys);

	// As we are going to return to the quiz detail list page after the delete, we need to load this data
	GetShowResultsData();
}

function GetDeleteQuizDisputeData()
{
	global $context;

	// Get the key ids for the quiz disputes to delete. This function returns a string containing a comma separated list of id's
	$deleteKeys = GetKeysFromPost('quiz_dispute');

	if (!empty($deleteKeys))
		DeleteQuizDisputes($deleteKeys);

	// As we are going to return to the quiz dispute list page after the delete, we need to load this data
	GetShowDisputesData();
}

function GetDeleteQuizLeagueData()
{
	global $context;

	// Get the key ids for the quiz leagues to delete. This function returns a string containing a comma separated list of id's
	$deleteKeys = GetKeysFromPost('quiz');

	if (!empty($deleteKeys))
		DeleteQuizLeagues($deleteKeys);

	// As we are going to return to the quiz detail list page after the delete, we need to load this data
	GetAllQuizLeagueDetails();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'DeleteQuizLeague';
}


function GetUpdateQuizData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$limit = isset($_POST['limit']) ? $_POST['limit'] : '';
	$seconds = isset($_POST['seconds']) ? $_POST['seconds'] : '';
	$showanswers = isset($_POST['show_answers']) && strval($_POST['show_answers']) == 'on' ? 1 : 0;
	$enabled = isset($_POST['enabled']) && strval($_POST['enabled']) == 'on' ? 1 : 0;
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? Quiz\Helper::quiz_commonImageFileFilter($_POST['image']) : '';
	$categoryId = isset($_POST['id_category']) ? $_POST['id_category'] : '';
	$quizId = isset($_POST['id_quiz']) ? $_POST['id_quiz'] : '';
	$oldCategoryId = isset($_POST["oldCategoryId"]) ? $_POST["oldCategoryId"] : ''; // Need the old category, as if it is different we need to change quiz counts
	$for_review = 1;

	// TODO: At a later date we probably want to make this a little more sophisticated by adding
	// PMs back to the creator and some workflow
	if ($enabled == 1)
		$for_review = 0;

	// Save the data and return the identifier for this newly created quiz
	UpdateQuiz($quizId, $title, $description, $limit, $seconds, $showanswers, $image, $categoryId, $oldCategoryId, $enabled, $for_review);

	// If the user wants to add questions after saving the quiz we need to output the appropriate page which is dictated by these context values
	if (!empty($_POST['UpdateQuizAndAddQuestions']))
	{
		$context['current_subaction'] = 'questions';
		$context['SMFQuiz']['Action'] = 'NewQuestion';
		$context['SMFQuiz']['id_quiz'] = $quizId;

		// We need to get the data required for new questions
		GetNewQuestionData();
	}
	else
	{
		// We need to get new quiz data, as that will be the next page shown
		GetQuizzesData();
		$context['SMFQuiz']['Action'] = 'Quizzes';
	}
}

function GetSaveQuizData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$limit = isset($_POST['limit']) ? $_POST['limit'] : '';
	$seconds = isset($_POST['seconds']) ? $_POST['seconds'] : '';
	$showanswers = isset($_POST['showanswers']) && strval($_POST['showanswers']) == 'on' ? 1 : 0;
	$enabled = isset($_POST['enabled']) && strval($_POST['enabled']) == 'on' ? 1 : 0;
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? Quiz\Helper::quiz_commonImageFileFilter($_POST['image']) : '';
	$categoryId = isset($_POST['id_category']) ? $_POST['id_category'] : '';

	// Save the data and return the identifier for this newly created quiz
	$newQuizId = SaveQuiz($title, $description, $limit, $seconds, $showanswers, $image, $categoryId, $enabled, $context['user']['id'], 0);

	// If the user wants to add questions after saving the quiz we need to output the appropriate page which is dictated by these context values
	if (isset($_POST['SaveQuizAndAddQuestions']))
	{
		$context['current_subaction'] = 'questions';
		$context['SMFQuiz']['Action'] = 'NewQuestion';
		$context['SMFQuiz']['id_quiz'] = $newQuizId;

		// We need to get the data required for new questions
		GetNewQuestionData();
	}
	else
	{
		// We need to get new quiz data, as that will be the next page shown
		GetNewQuizData();
		$context['SMFQuiz']['Action'] = 'SaveQuiz';
	}
}

// Function to replace curly quotes with normal ones - might be a better way of doing this, but this
// will do for the moment
function ReplaceCurlyQuotes($stringToReplace)
{
	// @TODO single replace
	$replaceString = str_replace('�', '"', $stringToReplace);
	$replaceString = str_replace('�', '"', $replaceString);
	$replaceString = str_replace('�', '\'', $replaceString);
	return $replaceString;
}

function GetUpdateQuestionData($addMore)
{
	global $context, $smcFunc, $db_prefix;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$questionId = isset($_POST["questionId"]) ? $_POST["questionId"] : '';
	$questionText = isset($_POST['question_text']) ? ReplaceCurlyQuotes($_POST['question_text']) : '';
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? Quiz\Helper::quiz_commonImageFileFilter($_POST['image']) : '';
	$answerText = isset($_POST['quiz_answer_text']) ? ReplaceCurlyQuotes($_POST['quiz_answer_text']) : '';
	$questionTypeId = isset($_POST['id_question_type']) ? $_POST['id_question_type'] : '';
	// @TODO check input
	$quizId = $_POST['id_quiz'];

	// Update the Question
	UpdateQuestion($questionId, $questionText, $image, $answerText);

	// Update the answer
	switch ($questionTypeId)
	{
		case '1' : // Multiple Choice
	// @TODO query
			$smcFunc['db_query']('', "
				DELETE FROM {$db_prefix}quiz_answer
				WHERE		id_question = {$questionId}
			");
			AddMultipleChoiceAnswer($questionId);
			break;

		case '2' : // Free Text
			UpdateFreeTextAnswer($questionId);
			break;

		case '3' : // True/False
			UpdateTrueFalseAnswer($questionId);
			break;
	}

	$context['SMFQuiz']['id_quiz'] = $quizId;

	if ($addMore == 0)
	{
		$context['SMFQuiz']['Action'] = 'Questions';
		// The next page will show all the questions, so get this data
		GetAllQuestionDetails(1, '', '', $quizId);
	}
	else
	{
		GetNewQuestionData();
		$context['SMFQuiz']['Action'] = 'NewQuestion';
	}
}

// Function that handles the saving of the specified new quiz league data
function GetSaveQuestionData($addMore)
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$questionText = isset($_POST['question_text']) ? ReplaceCurlyQuotes($_POST['question_text']) : '';
	$questionTypeId = isset($_POST['id_question_type']) ? $_POST['id_question_type'] : '';
	$quizId = isset($_POST['id_quiz']) ? $_POST['id_quiz'] : 0;
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? Quiz\Helper::quiz_commonImageFileFilter($_POST['image']) : '';
	$answerText = isset($_POST['question_answer_text']) ? ReplaceCurlyQuotes($_POST['question_answer_text']) : '';

	// Save the Question
	$questionId = SaveQuestion($questionText, $questionTypeId, $quizId, $image, $answerText);

	// Save the answer
	switch ($questionTypeId)
	{
		case '1' : // Multiple Choice
			AddMultipleChoiceAnswer($questionId);
			break;

		case '2' : // Free Text
			AddFreeTextAnswer($questionId);
			break;

		case '3' : // True/False
			AddTrueFalseAnswer($questionId);
			break;
	}

	if ($addMore == 0)
	{
		$context['SMFQuiz']['Action'] = 'Questions';

		// The next page will show all the questions, so get this data
		GetAllQuestionDetails(1, '', '', $quizId);
	}
	else
	{
		GetNewQuestionData();
		$context['SMFQuiz']['id_quiz'] = $_POST['id_quiz'];
		$context['SMFQuiz']['Action'] = 'NewQuestion';
	}
}

function UpdateFreeTextAnswer()
{
	// Free text answer simply has the text entered as the answer, so we only need to insert this into the database marking it as correct
	$answerText = isset($_POST["freeTextAnswer"]) ? ReplaceCurlyQuotes($_POST["freeTextAnswer"]) : '';
	$answerId = isset($_POST["answerId"]) ? $_POST["answerId"] : '';

	// Update the data
	UpdateAnswer($answerId, $answerText, 1);
}

function AddFreeTextAnswer($questionId)
{
	// Free text answer simply has the text entered as the answer, so we only need to insert this into the database marking it as correct
	$answerText = isset($_POST["freeTextAnswer"]) ? ReplaceCurlyQuotes($_POST["freeTextAnswer"]) : '';

	// Save the data
	SaveAnswer($questionId, $answerText, 1);
}

function UpdateTrueFalseAnswer()
{
	$correctAnswerId = isset($_POST["trueFalseAnswer"]) ? $_POST["trueFalseAnswer"] : 0;

	foreach($_POST as $key => $value)
	{
		// If the form value is one of the answers
		if (substr($key, 0, 8) == 'answerId')
		{
			// Need to have some text in answer
			if (strlen($value) > 0)
			{
				// Determine whether correct answer or not
				if (substr($key, 8) == $correctAnswerId)
					UpdateAnswer(substr($key, 8), $value, 1);
				else
					UpdateAnswer(substr($key, 8), $value, 0);
			}
		}
	}
}

function AddTrueFalseAnswer($questionId)
{
	// True false answer is simply saved as one asnwer that is correct
	$answerText = isset($_POST["trueFalseAnswer"]) ? ReplaceCurlyQuotes($_POST["trueFalseAnswer"]) : 'false';

	SaveAnswer($questionId, $answerText, 1);

	// Add the alternative answer
	if ($answerText == 'false')
		SaveAnswer($questionId, 'true', 0);
	else
		SaveAnswer($questionId, 'false', 0);
}

function UpdateMultipleChoiceAnswer()
{
	// For mutiple choice answers we need to loop through each choice adding the answer and setting the correct one
	$correctAnswerId = isset($_POST["correctAnswer"]) ? $_POST["correctAnswer"] : 0;

	foreach($_POST as $key => $value)
	{
		// If the form value is one of the answers
		if (substr($key, 0, 6) == 'answer' && $key != 'quiz_answer_text')
		{
			// Need to have some text in answer
			if (strlen($value) > 0)
			{
				// Determine whether correct answer or not
				if (substr($key, 6) == $correctAnswerId)
					UpdateAnswer(substr($key, 6), $value, 1);
				else
					UpdateAnswer(substr($key, 6), $value, 0);
			}
		}
	}
}

function AddMultipleChoiceAnswer($questionId)
{
	// For mutiple choice answers we need to loop through each choice adding the answer and setting the correct one
	$correctAnswerId = isset($_POST["correctAnswer"]) ? $_POST["correctAnswer"] : 0;

	// @TODO check input?
	foreach ($_POST as $key => $value)
	{
		// If the form value is one of the answers
		if (substr($key, 0, 6) == 'answer' && $key != 'answerText')
		{
			// Need to have some text in answer
			if (strlen($value) > 0)
			{
				// Determine whether correct answer or not
				if (substr($key, 6) == $correctAnswerId)
					SaveAnswer($questionId, $value, 1);
				else
					SaveAnswer($questionId, $value, 0);
			}
		}
	}
}

// Function that handles the saving of the specified new quiz league data
function GetSaveQuizLeagueData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$interval = isset($_POST["interval"]) ? (int)$_POST["interval"] : '';
	$questions = isset($_POST["questions"]) ? (int)$_POST["questions"] : '';
	$seconds = isset($_POST['seconds']) ? (int)$_POST['seconds'] : '';
	$points = isset($_POST["points"]) ? (int)$_POST["points"] : '';
	$showanswers = isset($_POST['showanswers']) && strval($_POST['showanswers']) == 'on' ? 1 : 0;
	$totalRounds = isset($_POST["totalRounds"]) ? (int)$_POST["totalRounds"] : '';
	$state = isset($_POST["state"]) ? (int)$_POST["state"] : '';

	// Build the category selection string
	$categoryArray = $_POST["categories"];
	$categories = '';
	foreach ($categoryArray as $category)
	{
		// If the ALL category has been selected at all there is no point in storing the
		// category selected data
		if ($category == 0)
		{
			$categories = '';
			break;
		}
		$categories .= $category . ',';
	}

	// Save the data
	if (!empty($title))
		SaveQuizLeague($title, $description, $interval, $questions, $seconds, $points, $showanswers, $totalRounds, $state, substr_replace($categories,"",-1));

	GetAllCategoryDetails();
	$context['SMFQuiz']['Action'] = 'SaveQuizLeague';
}


// Function that handles the updating of the specified quiz league data
function GetUpdateQuizLeagueData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$interval = isset($_POST["interval"]) ? $_POST["interval"] : '';
	$questions = isset($_POST["questions"]) ? $_POST["questions"] : '';
	$seconds = isset($_POST['seconds']) ? $_POST['seconds'] : '';
	$points = isset($_POST["points"]) ? $_POST["points"] : '';
	$showanswers = isset($_POST['showanswers']) && strval($_POST['showanswers']) == 'on' ? 1 : 0;
	$totalRounds = isset($_POST["totalRounds"]) ? $_POST["totalRounds"] : '';
	$state = isset($_POST["state"]) ? $_POST["state"] : '';
	$id_quiz_league = isset($_POST["id_quiz_league"]) ? $_POST["id_quiz_league"] : 0;

	// Build the category selection string
	$categoryArray = isset($_POST["categories"]) ? (array)$_POST["categories"] : [];
	$categories = '';
	foreach ($categoryArray as $category)
		$categories .= $category . ',';

	// Save the data
	if (!empty($title))
		UpdateQuizLeague($id_quiz_league, $title, $description, $interval, $questions, $seconds, $points, $showanswers, $totalRounds, $state, substr_replace($categories,"",-1));

	GetAllQuizLeagueDetails();
	$context['SMFQuiz']['Action'] = 'ShowQuizLeagues';
}

function GetUpdateCategoryData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$categoryId = isset($_POST['id_category']) ? $_POST['id_category'] : '';
	$name = isset($_POST["name"]) ? $_POST["name"] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$parent = isset($_POST["parentId"]) ? $_POST["parentId"] : '';
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? Quiz\Helper::quiz_commonImageFileFilter($_POST['image']) : '';

	// Save the data
	UpdateCategory($categoryId, $name, $description, $parent, $image);

	GetAllCategoryDetails();
	$context['SMFQuiz']['Action'] = 'Categories';
}

function GetQuestionsData($quizId)
{
	global $context;

	// Create an array that will map the sort selection to the query value
	$sort_methods = array(
		'Question' => 'Q.question_text',
		'Type' => 'QT.description',
		'Quiz' => 'Q.id_quiz',
	);

	// If sort not set, do so now
	if (!isset($_GET['orderBy']))
	{
		$context['SMFQuiz']['orderBy'] = 'Question';
		$context['SMFQuiz']['orderDir'] = 'up';
	}
	else
	{
		// Otherwise set the sort query string and reset context
		$context['SMFQuiz']['orderBy'] = $_GET['orderBy'];
		if ($_GET['orderDir'] == 'up')
			$context['SMFQuiz']['orderDir'] = 'down';
		else
			$context['SMFQuiz']['orderDir'] = 'up';
	}

	$context['SMFQuiz']['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
	GetQuizQuestionCount($quizId);
	$context['SMFQuiz']['Action'] = 'ShowQuestions';
	GetAllQuestionDetails($context['SMFQuiz']['page'], $sort_methods[$context['SMFQuiz']['orderBy']], $context['SMFQuiz']['orderDir'], $quizId);
}

function GetCategoryChildrenData($categoryId)
{
	global $context, $modSettings;

	$pageSize = $modSettings['SMFQuiz_ListPageSizes'];

	// Create an array that will map the sort selection to the query value
	$sort_methods = array(
		'Name' => 'C.name',
		'Description' => 'C.description',
		'Parent' => 'C2.name',
	);

	// If sort not set, do so now
	if (!isset($_GET['orderBy']))
	{
		$context['SMFQuiz']['orderBy'] = 'Name';
		$context['SMFQuiz']['orderDir'] = 'up';
	}
	else
	{
		// Otherwise set the sort query string and reset context
		$context['SMFQuiz']['orderBy'] = $_GET['orderBy'];
		if ($_GET['orderDir'] == 'up')
			$context['SMFQuiz']['orderDir'] = 'down';
		else
			$context['SMFQuiz']['orderDir'] = 'up';
	}

	$context['SMFQuiz']['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
	GetCategoryCount($categoryId);
	$context['SMFQuiz']['Action'] = 'ShowCategories';
	GetCategoryChildren($context['SMFQuiz']['page'], $sort_methods[$context['SMFQuiz']['orderBy']], $context['SMFQuiz']['orderDir'], $pageSize, $categoryId);
}

function GetParentCategoryData($categoryId)
{
	global $context, $modSettings;

	$pageSize = $modSettings['SMFQuiz_ListPageSizes'];

	// Create an array that will map the sort selection to the query value
	$sort_methods = array(
		'Name' => 'C.name',
		'Description' => 'C.description',
		'Parent' => 'C2.name',
	);

	// If sort not set, do so now
	if (!isset($_GET['orderBy']))
	{
		$context['SMFQuiz']['orderBy'] = 'Name';
		$context['SMFQuiz']['orderDir'] = 'up';

	}
	else
	{
		// Otherwise set the sort query string and reset context
		$context['SMFQuiz']['orderBy'] = $_GET['orderBy'];
		if ($_GET['orderDir'] == 'up')
			$context['SMFQuiz']['orderDir'] = 'down';
		else
			$context['SMFQuiz']['orderDir'] = 'up';
	}

	$context['SMFQuiz']['page'] = isset($_GET['page']) ? $_GET['page'] : 1;

	GetCategoryCount($categoryId);
	GetCategoryParent($context['SMFQuiz']['page'], $sort_methods[$context['SMFQuiz']['orderBy']], $context['SMFQuiz']['orderDir'], $pageSize, $categoryId);
}

function UpdateQuizStatus($id_quiz, $enabled)
{
	global $smcFunc;

	// Execute the query
	// @TODO query
	$smcFunc['db_query']('', '
		UPDATE		{db_prefix}quiz
		SET			enabled = {int:enabled},
					for_review = 0,
					updated = {int:updated}
		WHERE		id_quiz = {int:id_quiz}',
		array(
			'id_quiz' => $id_quiz,
			'enabled' => $enabled,
			'updated' => time()
	));
}

function UpdateQuizLeagueStatus($id_quiz_league, $enabled)
{
	global $smcFunc;

	// Execute the query
	// @TODO query
	$smcFunc['db_query']('', '
		UPDATE		{db_prefix}quiz_league
		SET			state = {int:enabled},
					updated = {int:updated}
		WHERE		id_quiz_league = {int:id_quiz_league}',
		array(
			'id_quiz_league' => $id_quiz_league,
			'enabled' => $enabled,
			'updated' => time()
	));
}

function GetQuizzesData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings, $settings;

	// If user has clicked button to disable quiz
	if (isset($_GET['disable_quiz_id']))
		UpdateQuizStatus($_GET['disable_quiz_id'], 0);

	// If user has clicked button to enable quiz
	if (isset($_GET['enable_quiz_id']))
		UpdateQuizStatus($_GET['enable_quiz_id'], 1);

	$starts_with = isset($_GET['starts_with']) ? $_GET['starts_with'] : '';
	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'updated';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];
	$disabled = isset($_REQUEST['disabled']) ? true : false;
	$enabled = isset($_REQUEST['enabled']) ? true : false;
	$forReview = isset($_REQUEST['review']) ? true : false;

	$queryExtra = $disabled == true ? " AND enabled = 0" : "";
	$queryExtra .= $enabled == true ? " AND enabled = 1" : "";
	$queryExtra .= $forReview == true ? " AND for_review = 1" : "";

	// Set up the columns...
	$context['columns'] = array(
	// @TODO '' => ???
		'' => array(
			'label' => '',
			'width' => '2'
		),
		'image' => array(
			'label' => 'Image',
			'width' => '2'
		),
		'title' => array(
			'label' => $txt['SMFQuiz_Common']['Title']
		),
		'updated' => array(
			'label' => $txt['SMFQuiz_Common']['Updated']
		),
		'owner' => array(
			'label' => $txt['SMFQuiz_Common']['Owner'],
			'width' => '25'
		),
		'description' => array(
			'label' => $txt['SMFQuiz_Common']['Description']
		),
		'category' => array(
			'label' => $txt['SMFQuiz_Common']['Category'],
			'width' => '20',
			'link_with' => 'website',
		),
		'play_limit' => array(
			'label' => $txt['SMFQuiz_Common']['PlayLimit'],
			'width' => '20'
		),
		'questions' => array(
			'label' => $txt['SMFQuiz_Common']['Qs'],
			'width' => '20'
		),
		'seconds' => array(
			'label' => $txt['SMFQuiz_Common']['Secs'],
			'width' => '20'
		),
		'answers' => array(
			'label' => $txt['SMFQuiz_Common']['Answers'],
			'width' => '20'
		),
		'enabled' => array(
			'label' => $txt['SMFQuiz_Common']['Functions'],
			'width' => '20'
		)
	);

	// Set the filter links
	$context['letter_links'] = '';
	for ($i = 97; $i < 123; $i++)
		$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizzes;starts_with=' . chr($i) . '">' . strtoupper(chr($i)) . '</a> ';

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=quiz;sa=quizzes;starts_with=' . $starts_with . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';

	$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizzes;">*</a> ';
	$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizzes;enabled"><img src="' . $settings['default_images_url'] . '/quiz_images/unlock.png" alt="enabled" title="enabled" align="top" /></a> ';
	$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizzes;disabled"><img src="' . $settings['default_images_url'] . '/quiz_images/lock.png" alt="disabled" title="disabled" align="top" /></a> ';
	$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizzes;review"><img src="' . $settings['default_images_url'] . '/quiz_images/review.png" alt="for review" title="for review" align="top" /></a> ';

	// List out the different sorting methods...
	$sort_methods = array(
		'title' => array(
			'down' => 'title DESC',
			'up' => 'title ASC'
		),
		'updated' => array(
			'down' => 'updated ASC',
			'up' => 'updated DESC'
		),
		'owner' => array(
			'down' => 'real_name DESC',
			'up' => 'real_name ASC'
		),
		'description' => array(
			'down' => 'description DESC',
			'up' => 'description ASC'
		),
		'category' => array(
			'down' => 'category_name DESC',
			'up' => 'category_name ASC'
		),
		'play_limit' => array(
			'down' => 'play_limit DESC',
			'up' => 'play_limit ASC'
		),
		'questions' => array(
			'down' => 'questions_per_session DESC',
			'up' => 'questions_per_session ASC'
		),
		'seconds' => array(
			'down' => 'seconds_per_question DESC',
			'up' => 'seconds_per_question ASC'
		),
		'answers' => array(
			'down' => 'show_answers DESC',
			'up' => 'show_answers ASC'
		),
		'enabled' => array(
			'down' => 'enabled DESC',
			'up' => 'enabled ASC'
		),
		'for_review' => array(
			'down' => 'for_review DESC',
			'up' => 'for_review ASC'
		)
	);

	$query_parameters = array(
		'sort' => $sort_methods[$sort][$context['sort_direction']],
		'starts_with' => strtoupper($starts_with) . '%',
		'limit' => $limit,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
		'queryExtra' => $queryExtra
	);

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}quiz
		WHERE title LIKE {string:starts_with} {raw:queryExtra}' ,
		$query_parameters
	);
	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=quiz;sa=quizzes;starts_with=' . $starts_with . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : '') . (isset($_REQUEST['disabled']) ? ';disabled' : '') . (isset($_REQUEST['enabled']) ? ';enabled' : '') . (isset($_REQUEST['review']) ? ';review' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT		Q.id_quiz,
					Q.title,
					Q.updated,
					Q.creator_id,
					M.real_name,
					Q.description,
					Q.play_limit,
					Q.seconds_per_question,
					Q.show_answers,
					Q.for_review,
					Q.enabled,
					Q.image,
					QC.id_category,
					(CASE WHEN Q.id_category = 0 THEN \'Top Level\' ELSE QC.name END) AS category_name,
					COUNT(U.id_quiz) AS questions_per_session
		FROM 		{db_prefix}quiz Q
		LEFT JOIN	{db_prefix}quiz_category QC
		ON 			Q.id_category = QC.id_category
		LEFT JOIN	{db_prefix}quiz_question U
		ON			Q.id_quiz = U.id_quiz
		LEFT JOIN	{db_prefix}members M
		ON			Q.creator_id = M.id_member
		WHERE		Q.title LIKE {string:starts_with} {raw:queryExtra}
		GROUP BY	Q.id_quiz,
			        Q.title,
					Q.updated,
					Q.creator_id,
					M.real_name,
					Q.description,
					Q.play_limit,
					Q.seconds_per_question,
					Q.show_answers,
					Q.for_review,
					Q.enabled,
					Q.image,
					QC.id_category,
					Q.id_category,
					QC.name,
					U.id_quiz
		ORDER BY	{raw:sort}
		LIMIT 		{int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['quizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizzes'][] = $row;

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'quizzes';
}

function GetShowDisputesData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings, $settings, $boardurl;

	$qv = !empty($modSettings['smf_quiz_version']) && (stripos($modSettings['smf_quiz_version'], '-beta') !== FALSE || stripos($modSettings['smf_quiz_version'], '-rc') !== FALSE) ? bin2hex(random_bytes(12/2)) : 'stable';
	$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/quiz/jquery-ui-1.14.1.css?v=' . $qv . '"/>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery-ui-1.14.1.min.js?v=' . $qv . '"></script>';

	$starts_with = isset($_GET['starts_with']) ? $_GET['starts_with'] : '';
	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'updated';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];

	// Set up the columns...
	$context['columns'] = array(
	// @TODO '' => ???
		'' => array(
			'label' => '',
			'width' => '2'
		),
		'updated' => array(
			'label' => $txt['SMFQuiz_Common']['Date']
		),
		'member' => array(
			'label' => $txt['SMFQuiz_Common']['Member'],
			'width' => '25'
		),
		'title' => array(
			'label' => $txt['SMFQuiz_Common']['Title']
		),
		'question_text' => array(
			'label' => $txt['SMFQuiz_Common']['Question']
		),
		'reason' => array(
			'label' => $txt['SMFQuiz_Common']['Reason']
		),
		'function' => array(
			'label' => '',
			'width' => '2'
		)
	);

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=quiz;sa=disputes;sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	// List out the different sorting methods...
	$sort_methods = array(
		'updated' => array(
			'down' => 'updated DESC',
			'up' => 'updated ASC'
		),
		'member' => array(
			'down' => 'real_name DESC',
			'up' => 'real_name ASC'
		),
		'title' => array(
			'down' => 'title DESC',
			'up' => 'title ASC'
		),
		'question_text' => array(
			'down' => 'question_text DESC',
			'up' => 'question_text ASC'
		),
		'reason' => array(
			'down' => 'reason DESC',
			'up' => 'reason ASC'
		)
	);

	$query_parameters = array(
		'sort' => $sort_methods[$sort][$context['sort_direction']],
		'limit' => $limit,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
	);

	// @TODO query
	$request = $smcFunc['db_query']('', '
		SELECT 		COUNT(*)
		FROM 		{db_prefix}quiz_dispute QD
		INNER JOIN 	{db_prefix}quiz Q
		ON 			QD.id_quiz = Q.id_quiz
		INNER JOIN 	{db_prefix}members M
		ON 			QD.id_user = M.id_member
		INNER JOIN	{db_prefix}quiz_question QQ
		ON			QD.id_quiz_question = QQ.id_question',
		$query_parameters
	);
	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=quiz;sa=disputes;sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	// @TODO check input?
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QD.id_quiz_dispute,
					M.real_name,
					M.id_member,
					QD.updated,
					Q.title,
					Q.id_quiz,
					QQ.question_text,
					QQ.id_question,
					QD.reason
		FROM 		{db_prefix}quiz_dispute QD
		INNER JOIN 	{db_prefix}quiz Q
		ON 			QD.id_quiz = Q.id_quiz
		INNER JOIN 	{db_prefix}members M
		ON 			QD.id_user = M.id_member
		INNER JOIN	{db_prefix}quiz_question QQ
		ON			QD.id_quiz_question = QQ.id_question
		ORDER BY	{raw:sort}
		LIMIT 		{int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['disputes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['disputes'][] = $row;

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'disputes';
}

function GetShowResultsData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	$starts_with = isset($_GET['starts_with']) ? $_GET['starts_with'] : '';
	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'result_date';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];

	// Set up the columns...
	$context['columns'] = array(
	// @TODO '' => ???
		'' => array(
			'label' => '',
			'width' => '2'
		),
		'result_date' => array(
			'label' => $txt['SMFQuiz_Common']['ResultDate']
		),
		'member' => array(
			'label' => $txt['SMFQuiz_Common']['Member'],
			'width' => '25'
		),
		'title' => array(
			'label' => $txt['SMFQuiz_Common']['Title']
		),
		'questions' => array(
			'label' => $txt['SMFQuiz_Common']['Questions']
		),
		'correct' => array(
			'label' => $txt['SMFQuiz_Common']['Correct']
		),
		'incorrect' => array(
			'label' => $txt['SMFQuiz_Common']['Incorrect']
		),
		'timeouts' => array(
			'label' => $txt['SMFQuiz_Common']['Timeouts']
		),
		'total_seconds' => array(
			'label' => $txt['SMFQuiz_Common']['Seconds']
		),
		'total_resumes' => array(
			'label' => $txt['SMFQuiz_Common']['Resumes']
		)
	);

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=quiz;sa=results;sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	// List out the different sorting methods...
	$sort_methods = array(
		'title' => array(
			'down' => 'title DESC',
			'up' => 'title ASC'
		),
		'member' => array(
			'down' => 'real_name DESC',
			'up' => 'real_name ASC'
		),
		'result_date' => array(
			'down' => 'result_date DESC',
			'up' => 'result_date ASC'
		),
		'questions' => array(
			'down' => 'questions DESC',
			'up' => 'questions ASC'
		),
		'correct' => array(
			'down' => 'correct DESC',
			'up' => 'correct ASC'
		),
		'incorrect' => array(
			'down' => 'incorrect DESC',
			'up' => 'incorrect ASC'
		),
		'timeouts' => array(
			'down' => 'timeouts DESC',
			'up' => 'timeouts ASC'
		),
		'total_seconds' => array(
			'down' => 'total_seconds DESC',
			'up' => 'total_seconds ASC'
		),
		'total_resumes' => array(
			'down' => 'total_resumes DESC',
			'up' => 'total_resumes ASC'
		)
	);

	$query_parameters = array(
		'sort' => $sort_methods[$sort][$context['sort_direction']],
		'limit' => $limit,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
	);

	$request = $smcFunc['db_query']('', '
		SELECT 	COUNT(*)
		FROM 	{db_prefix}quiz_result',
		$query_parameters
	);
	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=quiz;sa=results;sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QR.id_quiz_result,
					M.real_name,
					M.id_member,
					QR.result_date,
					Q.title,
					QR.questions,
					QR.correct,
					QR.incorrect,
					QR.timeouts,
					QR.total_seconds,
					QR.total_resumes,
					QR.player_limit
		FROM 		{db_prefix}quiz_result QR
		INNER JOIN 	{db_prefix}quiz Q
		ON 			QR.id_quiz = Q.id_quiz
		INNER JOIN 	{db_prefix}members M
		ON 			QR.id_user = M.id_member
		ORDER BY	{raw:sort}
		LIMIT 		{int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['results'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['results'][] = $row;

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'results';
}

// Function that handles the saving of the specified new quiz category data
function GetSaveCategoryData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$name = isset($_POST["name"]) ? $_POST["name"] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$parent = isset($_POST["parentId"]) ? $_POST["parentId"] : '';
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? Quiz\Helper::quiz_commonImageFileFilter($_POST['image']) : '';
	$image = !empty($_POST['imageDefault-64_png']) ? Quiz\Helper::quiz_commonImageFileFilter($_POST['imageDefault-64_png']) : $image;
	$image = !empty($_POST['fileToUpload']) ? Quiz\Helper::quiz_commonImageFileFilter($_POST['fileToUpload']) : $image;

	// Save the data
	SaveCategory($name, $description, $parent, $image);

	GetAllCategoryDetails();

	$context['SMFQuiz']['Action'] = 'SaveCategory';
}

function upload_images($id_quiz)
{
	global $context, $smcFunc;

	// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		image FROM
					{db_prefix}quiz_question
		WHERE 		id_quiz = {int:id_quiz}
		AND 		!ISNULL(image)
		AND			image != ""',
		array (
			'id_quiz' => $id_quiz
		)
	);

	$id_questions = '';
	$status = '';
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$status .= '<br>' . load_image($row['image']);

	$smcFunc['db_free_result']($result);
	$context['SMFQuiz']['uploadResponse'] .= $status;
}

function load_image($imageFileName)
{
	global $settings;

	$filename = $settings['default_theme_dir'] . '/images/quiz_images/Questions/' . $imageFileName;
	// @TODO check if file exists


	$handle = fopen($filename, "rb");
	$contents = fread($handle, filesize($filename));
	fclose($handle);

	clearstatcache();

	if (!is_dir($filename) && file_exists($filename)) {
		return '<img src="' . $settings['default_images_url'] . '/quiz_images/Questions/' . $imageFileName . '" alt="yes" title="Information" style="vertical-align: top;"><span style="padding-left: 1rem;">' . $imageFileName . ' uploaded successfully</span>';
	}
	else {
		return '<img src="' . $settings['default_images_url'] . '/quiz_images/warning.png" alt="Warning" title="Information" style="vertical-align: top;"><span style="padding-left: 1rem;">' . $imageFileName . ' did not upload due to unexpected error</span>';
	}
}

function ImportQuizFile($urlPath, $categoryId, $isEnabled, $image, $fileCount)
{
	global $context, $modSettings, $sourcedir;

	$isEnabled = $isEnabled == 'on' ? 1 : 0;
	$image = !empty($image) && $image != '-' ? $image : null;
	$newUrlPath = str_replace(" ", "%20", $urlPath);

	$catData = quizGetCategoryInfo();
	$quizString = quizLoad($newUrlPath);

	$creator_id = isset($modSettings['SMFQuiz_ImportQuizzesAsUserId']) ? (int)$modSettings['SMFQuiz_ImportQuizzesAsUserId'] : 1;

	libxml_use_internal_errors(true);
	$tempFile = $sourcedir . '/Quiz/Temp/temp_' . substr(md5(rand(1000, 9999999)), 0, 5) . '.xml';
	file_put_contents($quizString, $tempFile);
	@chmod($tempFile, 0644);
	if (!$myxml = simplexml_load_file($tempFile)) {
		@unlink($tempFile);
		fatal_lang_error('quiz_mod_quiz_already_exists', false);
	}
	else
	{
		// TODO: Should really check XML is valid here
		@unlink($tempFile);
		foreach($myxml->quiz as $quiz)
		{
			$currentCat = $quiz->exists('categoryName') ? strtolower(trim(format_string2((string)$quiz->fetch('categoryName')))) : '';
			$findCat = !empty($currentCat) ? array_search($currentCat, array_column($catData, 'cat_name')) : 0;
			$id_category = !empty($findCat) ? $catData[$findCat]['id_cat'] : 0;
			$newQuizId = ImportQuiz(format_string2($quiz->title), format_string2($quiz->description), $quiz->playLimit, $quiz->secondsPerQuestion, $quiz->showAnswers, $id_category, $isEnabled, $image, $creator_id);

			foreach($quiz->questions->children() as $questions)
			{
				$qImage = $questions->exists('image') ? format_string2($questions->image) : '';
				$qImageData = $questions->exists('imageData') ? $questions->imageData : '';
				$newQuestionId = ImportQuizQuestion($newQuizId, format_string2($questions->questionText), $questions->questionTypeId, format_string2($questions->answerText), $qImage, $qImageData);
				foreach ($questions->children()->answers->children() as $answers)
					ImportQuizAnswer($newQuestionId, format_string2($answers->answerText), $answers->isCorrect);
			}
		}
		$context['SMFQuiz']['SMFQuizImported'] = $fileCount+1;
	}
}

	// @TODO to replace
function ImportQuizzes($quizDetails)
{
	global $boarddir, $context, $boardurl;

	if (intval(@ini_get('memory_limit')) < 512) {
		@ini_set('memory_limit', '512M');
	}

	// Retrieve indexes of files and post values into array
	$fileIndexesToImport = Array();
	$fileCategoriesToImport = Array();
	$fileEnabledToImport = Array();
	$fileImagesToImport = Array();

	// Loop through posted values and assign to the appropriate array
	foreach($_POST as $key => $value)
	{
		if (substr($key, 0, 4) == 'quiz')
		{
			// Only set this if it was selected on form
			if ($value == 'on')
			{
				$index = substr($key, 4)-1;
				$fileIndexesToImport[$index] = $index;
			}
		}
		if (substr($key, 0, 16) == 'importCategoryId')
		{
			$index = substr($key, 16)-1;
			$fileCategoriesToImport[$index] = $_POST[$key];
		}
		if (substr($key, 0, 8) == 'enableId')
		{
			$index = substr($key, 8)-1;
			$fileEnabledToImport[$index] = $_POST[$key];
		}
		if (substr($key, 0, 5) == 'image')
		{
			$index = substr($key, 5)-1;
			$fileImagesToImport[$index] = $_POST[$key];
		}
	}

	$path = 'http://www.smfmodding.com/quizzes/';

	// Loop through quiz detail array
	for ($count = 0; $count < sizeof($quizDetails); $count++)
	{
		// If this quiz is to be imported
		if (isset($fileIndexesToImport[$count]))
		{
			// Get quiz file name from array
			$quizDetailSplit = explode("<", $quizDetails[$count]);

			// Import the quiz
			ImportQuizFile($path.$quizDetailSplit[1], $fileCategoriesToImport[$count], $fileEnabledToImport[$count], $fileImagesToImport[$count], $count);
		}
	}
}

function DeleteQuizImport()
{
	global $boarddir;

	$path = $boarddir . '/tempQuizzes/';
	$count = 1;
	if ($handle = opendir($path))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != "..")
			{
				if ($_POST['fileId' . $count] == 'on')
					unlink($path . $file);

				$count++;
			}
		}
		closedir($handle);
	}
}

	// @TODO to replace
function GetAdminCenterData()
{
	global $context;
/*
	// Load admin center XML
	$adminCenterXml = simplexml_load_string(load("http://www.smfmodding.com/support/adminCenter.php"));

	// Get and set the latest version information
	$context['SMFQuiz_currentVersion'] = $adminCenterXml->smfQuiz->latestVersion;

	// Get and set the latest news information
	$context['SMFQuiz_latestNews'] = $adminCenterXml->smfQuiz->newsEntries->newsEntry;*/

	// Get some of the data to show
	$context['SMFQuiz_totalQuizzes'] = 0;
	GetTotalQuizStats();
	if (isset($context['SMFQuiz']['totalQuizStats']))
	{
		foreach($context['SMFQuiz']['totalQuizStats'] as $row)
		{
			$context['SMFQuiz_totalQuizzes'] = $row['total_quiz_count'];
			$context['SMFQuiz_totalResults'] = $row['total_quiz_plays'];
		}
	}
	GetTotalDisputesCount();
	GetDisabledQuizCount();
	GetTotalReviewCount();
}

	// @TODO to recode with an access to multiple remote servers
function import_quiz($quizXmlString, $image = 'Default-64.png', $catOverride = 0)
{
	global $modSettings, $user_settings, $settings, $context, $txt, $sourcedir;

	if (empty($quizXmlString))
		return;
	// @TODO this function still needs a bit of work

	if (intval(@ini_get('memory_limit')) < 512) {
		@ini_set('memory_limit', '512M');
	}

	$unsuccessful = array();
	$successful = array();
	// These are the only valid image types for SMF.
	$validImageTypes = array(
		1 => 'gif',
		2 => 'jpeg',
		3 => 'png',
		5 => 'psd',
		6 => 'bmp',
		7 => 'tiff',
		8 => 'tiff',
		9 => 'jpeg',
		14 => 'iff'
	);

	require_once($sourcedir . '/Class-Package.php'); // ENT_DISALLOWED
	$quizXmlString = format_string2(($quizXmlString));
	$quizzes = New xmlArray($quizXmlString);
	$catData = quizGetCategoryInfo();

	// Only continue if XML is valid
	if (!$quizzes->exists('quizzes'))
		$unsuccessful[] = array($txt['quiz_mod_unknown_quiz'], 'quiz_mod_error_reading_file');
	else
	{
		foreach ($quizzes->set('quizzes/quiz') as $quiz)
		{
			$title = $quiz->fetch('title');
			if (empty($title))
				continue;

			$creator_id = isset($modSettings['SMFQuiz_ImportQuizzesAsUserId']) ? $modSettings['SMFQuiz_ImportQuizzesAsUserId'] : 1;

			// Check for a matching category
			$currentCat = $quiz->exists('categoryName') ? strtolower(trim(format_string2((string)$quiz->fetch('categoryName')))) : '';
			$findCat = !empty($currentCat) ? array_search($currentCat, array_column($catData, 'cat_name')) : 0;
			$id_category = !empty($catOverride) ? $catOverride : (!empty($findCat) ? $catData[$findCat]['id_cat'] : 0);

			if ($quiz->exists('image'))
			{
				$image = $quiz->fetch('image');
				$disabledFiles = array('con', 'com1', 'com2', 'com3', 'com4', 'prn', 'aux', 'lpt1', '.htaccess', 'index.php');
				if (in_array(strtolower($image), $disabledFiles))
					$image = 'quiz_' . $image;
			}

			if ($quiz->exists('image') && $quiz->exists('imageData'))
			{
				$dest = $settings['default_theme_dir'] . '/images/quiz_images/Quizzes/' . $image;
				if (!file_exists($dest) && is_writable($settings['default_theme_dir'] . '/images/quiz_images/Quizzes/') && $quiz->exists('imageData'))
				{
					$imageData = base64_decode($quiz->fetch('imageData'));
					file_put_contents($dest, $imageData);
					clearstatcache(dirname($dest));
					if (!is_dir($dest) && file_exists($dest)) {
						$mimeType = @mime_content_type($dest);
						$mimeTypes = ['auto', 'image/gif', 'image/jpeg', 'image/png', 'image/avif', 'image/tiff', 'image/bmp'];
						// Default to png
						$fileType = in_array($mimeType, $mimeTypes) ? array_search($mimeType, $mimeTypes) : 3;
						$fileType = !in_array($fileType, [1, 2, 3, 6]) ? 3 : $fileType;
						require_once($sourcedir . '/Subs-Graphics.php');
						if (!reencodeImage($dest, $fileType))
						{
							@unlink($dest);
							@unlink($dest . '.tmp');
						}
					}
				}
			}

			$newQuizId = ImportQuiz($title, $quiz->fetch('description'), $quiz->fetch('playLimit'), $quiz->fetch('secondsPerQuestion'), $quiz->fetch('showAnswers'), (int)$id_category, 1, $image, $creator_id);

			if (!is_numeric($newQuizId))
				$unsuccessful[md5($title)] = array($title, 'quiz_mod_quiz_already_exists');
			foreach ($quiz->set('questions/question') as $questions)
			{
				$newQuestionId = ImportQuizQuestion($newQuizId, $questions->fetch('questionText'), $questions->fetch('questionTypeId'), $questions->fetch('answerText'), $questions->fetch('image'),  $questions->fetch('imageData'));

				foreach ($questions->set('answers/answer') as $answers)
					ImportQuizAnswer($newQuestionId, $answers->fetch('answerText'), $answers->fetch('isCorrect'));
			}
			$context['SMFQuiz']['importResponse'] = '<img src="' . $settings['default_images_url'] . '/quiz_images/information.png" alt="yes" title="Information" align="top" />&nbsp;' . $txt['SMFQuizAdmin_Quizzes_Page']['QuizImportedSuccessfully'];

			if (!isset($unsuccessful[md5($title)]))
				$successful[] = array($title, null);
		}
	}

	return array('successful' => $successful, 'unsuccessful' => $unsuccessful);
}

function import_quiz_images($id_quiz)
{
	global $smcFunc, $context;

	$result = $smcFunc['db_query']('', '
		SELECT image
		FROM {db_prefix}quiz_question
		WHERE id_quiz = {int:id_quiz}
			AND !ISNULL(image)',
		array (
			'id_quiz' => $id_quiz
		)
	);

	$id_questions = '';
	$status = '';
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$status .= '<br/>' . import_image($row['image']);

	$smcFunc['db_free_result']($result);
	$context['SMFQuiz']['importResponse'] .= $status;
}

	// @TODO to replace
function import_image($imageFileName)
{
	global $boarddir, $settings;

	// Where to save the file
	$outFilePath = $boarddir . '/Themes/default/images/quiz_images/Questions/' . $imageFileName;

	$server  = 'www.smfmodding.com';
	$port    = '80';
	$uri     = '/Themes/default/images/quiz_images/Questions/' . urlencode($imageFileName);
	$content = 'test';

	$post_results = httpFunc('GET',$server,$port,$uri,$content);
	if (!is_string($post_results))
		return '<img src="' . $settings['default_images_url'] . '/quiz_images/warning.png" alt="yes" title="Warning" align="top" /> An unexpected error occurred while importing ' . $imageFileName;
	elseif (file_exists($outFilePath))
		return '<img src="' . $settings['default_images_url'] . '/quiz_images/warning.png" alt="yes" title="Warning" align="top" /> The file ' . $imageFileName . ' already exists locally, skipping';
	else
	{
		$fileWrite = fopen($outFilePath, 'x');
	// @TODO check if is_writable
		if (fwrite( $fileWrite, $post_results ) == false)
			return '<img src="' . $settings['default_images_url'] . '/quiz_images/warning.png" alt="yes" title="Warning" align="top" /> An unexpected error occurred while importing ' . $imageFileName;

		fclose($fileWrite);
	}
	return '<img src="' . $settings['default_images_url'] . '/quiz_images/information.png" alt="yes" title="Information" align="top" /> ' . $imageFileName . ' imported successfully';
}

	// @TODO to replace
function save_image($image)
{
	global $boarddir;

	// Where to save the file
	$outFilePath = $boarddir . '/Themes/default/images/quiz_images/Quizzes/' . $image;

	$server  = 'www.smfmodding.com';
	$port    = '443';
	$uri     = '/Themes/default/images/quiz_images/Quizzes/' . urlencode($image);
	$content = 'test';

	$post_results = httpFunc('GET',$server,$port,$uri,$content);
	if (!is_string($post_results))
		die('uh oh, something went wrong');
	else
	{
	// @TODO check if is_writable
		$fileWrite = fopen($outFilePath, 'w');
		fwrite( $fileWrite, $post_results );
		fclose($fileWrite);
	}
}

//
// Post provided content to an http server and optionally
// convert chunk encoded results.  Returns false on errors,
// result of post on success.  This example only handles http,
// not https.
//
	// @TODO still needed?
function httpFunc($action='GET',$ip=null,$port=80,$uri=null,$content=null,$doHack=false)
{
	if (empty($ip))
		return false;
	if (!is_numeric($port))
		return false;
	if (empty($uri))
		return false;
	if (empty($content))
		return false;
	// generate headers in array.
	$t   = array();
	$t[] = $action . ' ' . $uri . ' HTTP/1.1';
	$t[] = 'Content-Type: text/html';
	$t[] = 'Host: ' . $ip . ':' . $port;
	$t[] = 'Content-Length: ' . strlen($content);
	$t[] = 'Connection: close';
	$t   = implode("\r\n",$t) . "\r\n\r\n" . $content;
	//
	// Open socket, provide error report vars and timeout of 10
	// seconds.
	//
	$fp  = @fsockopen($ip,$port,$errno,$errstr,10);
	// If we don't have a stream resource, abort.
	if (!(get_resource_type($fp) == 'stream')) { return false; }
	//
	// Send headers and content.
	//
	if (!fwrite($fp,$t))
	{
		fclose($fp);
		return false;
	}
	//
	// Read all of response into $rsp and close the socket.
	//
	$rsp = '';
	while(!feof($fp)) { $rsp .= fgets($fp,8192); }
	fclose($fp);
	//
	// Call parseHttpResponse() to return the results.
	//
	return parseHttpResponse($rsp, $doHack);
}

//
// Accepts provided http content, checks for a valid http response,
// unchunks if needed, returns http content without headers on
// success, false on any errors.
//
	// @TODO still needed?
function parseHttpResponse($content=null,$doHack=false)
{
	if (empty($content))
		return false;

	// Nasty hack for when we are retrieving Quizzes to import. For some reason we
	// get some garbage at the end of the XML
	if ($doHack==true)
	{
		$startPos = strpos($content, "<quiz>");
		$endPos = strpos($content, "</quiz>");
		$length = $endPos - $startPos + 8;
		$newBody = substr($content, $startPos, $length);
		return trim($newBody);
	}

	// split into array, headers and content.
	$hunks = explode("\r\n\r\n",trim($content));
	if (!is_array($hunks) or count($hunks) < 2)
		return false;

	$header  = $hunks[count($hunks) - 2];
	$body    = $hunks[count($hunks) - 1];

	$headers = explode("\n",$header);
	unset($hunks);
	unset($header);
	if (!validateHttpResponse($headers))
		return false;
	if (in_array('Transfer-Coding: chunked',$headers))
		return trim(unchunkHttpResponse($body));
	else
		return trim($body);
}

//
// Validate http responses by checking header.  Expects array of
// headers as argument.  Returns boolean.
//
	// @TODO still needed?
function validateHttpResponse($headers=null)
{
	if (!is_array($headers) or count($headers) < 1)
		return false;

	switch (trim(strtolower($headers[0])))
	{
		case 'http/1.0 100 ok':
		case 'http/1.0 200 ok':
		case 'http/1.1 100 ok':
		case 'http/1.1 200 ok':
				return true;
		break;
	}
	return false;
}

//
// Unchunk http content.  Returns unchunked content on success,
// false on any errors...  Borrows from code posted above by
// jbr at ya-right dot com.
//
	// @TODO still needed?
function unchunkHttpResponse($str=null)
{
	if (!is_string($str) or strlen($str) < 1)
		return false;

	$eol = "\r\n";
	$add = strlen($eol);
	$tmp = $str;
	$str = '';
	do
	{
		$tmp = ltrim($tmp);
		$pos = strpos($tmp, $eol);
		if ($pos === false)
			return false;
		$len = hexdec(substr($tmp,0,$pos));
		if (!is_numeric($len) || $len < 0)
			return false;
		$str .= substr($tmp, ($pos + $add), $len);
		$tmp  = substr($tmp, ($len + $pos + $add));
		$check = trim($tmp);
	} while(!empty($check));
	unset($tmp);
	return $str;
}

function quizCreateDirs($path)
{
	global $boarddir;
	if (!is_dir($path))
	{
		$directory_path = $boarddir . '/';
		$directories = explode("/", $path);
		array_pop($directories);

		foreach($directories as $directory)
		{
			$directory_path .= $directory . '/';
			if (!is_dir($directory_path)) {
				@mkdir($directory_path, 0755);
			}
		}
	}
}

function unzipQuizArchive($src_file, $dest_dir)
{
	global $sourcedir;
	require_once($sourcedir . '/Subs-Package.php');

	$dest_dir = !empty($dest_dir) ? rtrim(str_replace('\\', '/', $dest_dir), '/') : false;
	if (class_exists('ZipArchive')) {
		$splitter = '/';
		if ($dest_dir === false)
		{
			$dest_dir = mb_substr($src_file, 0, strrpos($src_file, $splitter)) . '/';
			$dest_dir = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2",  $dest_dir);
		}

		$dest_dir = rtrim(str_replace('\\', '/', $dest_dir), '/');
		quizCreateDirs($dest_dir);
		$zip = new ZipArchive;
		$res = $zip->open($src_file);
		if ($res === TRUE) {
			for($i = 0; $i < $zip->numFiles; $i++) {
				@$zip->extractTo($dest_dir, array($zip->getNameIndex($i)));
			}
			$zip->close();
		}
		else
			$zip = false;
	}
	elseif (function_exists('zip_open'))
	{
		if ($zip = zip_open($src_file))
		{
			if ($zip)
			{
				$splitter = ($create_zip_name_dir === true) ? '.' : '/';
				if ($dest_dir === false) {
					$dest_dir = mb_substr($src_file, 0, strrpos($src_file, $splitter)) . '/';
				}
				$dest_dir = rtrim(str_replace('\\', '/', $dest_dir), '/');
				quizCreateDirs($dest_dir);

				while ($zip_entry = zip_read($zip))
				{
					$pos_last_slash = strrpos(zip_entry_name($zip_entry), '/');
					if ($pos_last_slash !== false)
						quizCreateDirs($dest_dir . '/' . mb_substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1));

					if (zip_entry_open($zip,$zip_entry, 'rb'))
					{
						$file_name = $dest_dir . '/' . zip_entry_name($zip_entry);
						$dir_name = dirname($file_name);

							if ($overwrite === true || ($overwrite === false && !is_file($file_name)))
							{
								$fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
								@file_put_contents($file_name, $fstream);
								@chmod($file_name, 0755);
								if (substr($file_name, -4) == '.htm')
									@rename($file_name, $file_name. 'l');
							}

						zip_entry_close($zip_entry);
					}
				}

				zip_close($zip);
			}
		}
		else
			return false;
	}
	else
	{
		$splitter = ($create_zip_name_dir === true) ? '.' : '/';
		if ($dest_dir === false)
		{
			$dest_dir = mb_substr($src_file, 0, strrpos($src_file, $splitter)) . '/';
			$dest_dir = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2",  $dest_dir);
		}
		$dest_dir = rtrim(str_replace('\\', '/', $dest_dir), '/');
		quizCreateDirs($dest_dir);
		if (function_exists('read_zip_file'))
			$zip = read_zip_file($src_file, $dest_dir, false, false, null);
		elseif (function_exists('read_tgz_file'))
			$zip = read_tgz_file($src_file, $dest_dir, false, $overwrite, null);
		elseif (class_exists('ZipArchive')) {
			// PHP 8 fallback only if pecl-zip package is installed
			$zip = new ZipArchive;
			$res = $zip->open($src_file);
			if ($res === TRUE) {
				for($i = 0; $i < $zip->numFiles; $i++) {
					$zip->extractTo($dest_dir, array($zip->getNameIndex($i)));
				}
				$zip->close();
			}
			else
				$zip = false;
		}
		else
			$zip = false;

		if (empty($zip))
			return false;
	}

	return true;
}

function GetQuizImportData()
{
	global $context, $scripturl, $modSettings, $smcFunc, $txt, $settings, $sourcedir;

	list($importResults, $context['quiz_category_data']) = [[], quizGetCategoryInfo()];
	// Borrowed from Subs-Post.php
	// These are the only valid image types for SMF.
	$validImageTypes = array(
		1 => 'gif',
		2 => 'jpeg',
		3 => 'png',
		5 => 'psd',
		6 => 'bmp',
		7 => 'tiff',
		8 => 'tiff',
		9 => 'jpeg',
		14 => 'iff'
	);

	if (!empty($_FILES))
	{
		$catOverride = isset($_POST['quizCategoryOverride']) ? (int)$_POST['quizCategoryOverride'] : 0;
		$numFiles = count($_FILES['imported_quiz']['tmp_name']);
		for ($i = 0; $i < $numFiles; $i++)
		{
			$fileData = pathinfo(basename($_FILES['imported_quiz']['name'][$i]));
			move_uploaded_file($_FILES['imported_quiz']['tmp_name'][$i], $sourcedir . '/Quiz/Temp/' . $fileData['basename']);
			$tempPath = 'temp_' . substr(md5(rand(1000, 9999999)), 0, 6);
			$file = $sourcedir . '/Quiz/Temp/' . $fileData['basename'];
			mkdir($sourcedir . '/Quiz/Temp/' . $tempPath, 0755);
			if (!empty($fileData['extension']) && $fileData['extension'] == 'zip' && unzipQuizArchive($sourcedir . '/Quiz/Temp/' . $fileData['basename'], $sourcedir . '/Quiz/Temp/' . $tempPath)) {
				clearstatcache();
				if (file_exists($sourcedir . '/Quiz/Temp/' . $tempPath . '/' . $fileData['filename'] . '.xml')) {
					$file = $sourcedir . '/Quiz/Temp/' . $tempPath . '/' . $fileData['filename'] . '.xml';
					@chmod($file, 0644);
				}
				elseif (file_exists($sourcedir . '/Quiz/Temp/' . $tempPath . '/' . $fileData['filename'] . '.php')) {
					$tempFiles = glob($sourcedir . '/Quiz/Temp/' . $tempPath . '/*');
					foreach ($tempFiles as $tempFile) {
						if (is_file($tempFile)) {
							$ext = pathinfo($tempFile, PATHINFO_EXTENSION);
							@chmod($tempFile, 0644);
							if (in_array($ext, array('gif', 'jpeg', 'png', 'jpg', 'bmp'))) {
								$newFileName = $ext == 'jpeg' ? pathinfo($tempFile, PATHINFO_FILENAME) . 'jpg' : pathinfo($tempFile, PATHINFO_BASENAME);
								@rename($tempFile, $settings['default_theme_dir'] . '/images/quiz_images/' . $newFileName);
								continue;
							}
							elseif ($ext == 'php') {
								$processPhpFile = quizGetPhpData($tempFile, $catOverride);
								$importResults[$i] = array(
									'name' => pathinfo($tempFile, PATHINFO_FILENAME),
									'returns' => $processPhpFile,
								);
							}
						}
						elseif (is_dir($tempFile) && in_array(basename($tempFile), array('Quizzes', 'Questions'))) {
							$tempFilez = glob($tempFile . '/*');
							foreach ($tempFilez as $tempImg) {
								$imgExt = pathinfo($tempImg, PATHINFO_EXTENSION);
								@chmod($tempImg, 0644);
								if (in_array($imgExt, array('gif', 'jpeg', 'png', 'jpg', 'bmp'))) {
									$newImgName = $imgExt == 'jpeg' ? pathinfo($tempImg, PATHINFO_FILENAME) . 'jpg' : basename($tempImg);
									@rename($tempImg, $settings['default_theme_dir'] . '/images/quiz_images/' . basename($tempFile) . '/' . $newImgName);
									continue;
								}
							}
						}
					}
					quizRmdir($sourcedir . '/Quiz/Temp/' . $tempPath);
					continue;
				}
			}

			$imgFile = '';
			if (!empty($_FILES['imported_quiz_img']['tmp_name'][$i]))
			{
				// Borrowed from Subs-Post.php {
				$size = @getimagesize($file);
				// Sometime in the future we my want to check that the size is not higher than...something
				list ($width, $height) = $size;

				// Is a valid image: green light for upload
				if (isset($validImageTypes[$size[2]]))
				{
					$imgFile = un_htmlspecialchars($file);
					$img_destination = $settings['default_theme_dir'] . '/images/quiz_images/Quizzes/' . Quiz\Helper::quiz_commonImageFileFilter($imgFile);
					if (!file_exists($img_destination) && is_writable($settings['default_theme_dir'] . '/images/quiz_images/Quizzes'))
						move_uploaded_file($_FILES['imported_quiz_img']['tmp_name'][$i], $img_destination);
				}
			}
			if (!empty($file))
			{
				if (isset($_GET['image']))
					save_image($_GET['image']);

				clearstatcache();
				if (file_exists($file) && substr($file, -4) == '.xml') {
					$fileContent = file_get_contents($file);
					$importResults[$i] = array(
						'name' => $file,
						'returns' => import_quiz($fileContent, $imgFile, $catOverride),
					);
				}
			}
		}

		foreach ($importResults as $return)
		{
			foreach ($return['returns'] as $key => $quizzes)
			{
				if (!empty($quizzes))
					foreach ($quizzes as $quiz)
						$context[$key . '_import'][] = str_replace('{FILENAME}', $return['name'], $quiz[0]) . ($key == 'successful' ? '' : ' (' . $txt['quiz_mod_failure_reason'] . ': ' . $txt[$quiz[1]] . ')');
			}
		}

		$tempPaths = glob($sourcedir . '/Quiz/Temp/*');
		foreach ($tempPaths as $tempPath) {
			if (is_dir($tempPath)) {
				quizRmdir($tempPath);
			}
			elseif (!in_array(basename($tempPath), array('index.php', '.htaccess'))) {
				@unlink($tempPath);
			}
		}
	}

	clearstatcache();
}

function quizGetPhpData($tempFile, $catOverride = 0)
{
	global $modSettings, $user_info, $context;

	list(
		$newQuizLoadData,
		$successful,
		$unsuccessful,
		$x,
		$y,
		$z,
		$isEnabled,
		$creator_id,
		$catData
	) = [
		[],
		[],
		[],
		0,
		0,
		0,
		1,
		!empty($modSettings['SMFQuiz_ImportQuizzesAsUserId']) ? (int)$modSettings['SMFQuiz_ImportQuizzesAsUserId'] : $user_info['id'],
		quizGetCategoryInfo(),
	];

	if (file_exists($tempFile) && is_file($tempFile)) {
		require_once($tempFile);

		if (!class_exists('Quiz\QuizImport')) {
			$unsuccessful[] = array($txt['quiz_mod_unknown_quiz'], 'quiz_mod_error_reading_file');
		}
		else {
			$newQuizLoadData = Quiz\QuizImport::quizImportData();
			// Quiz objects
			while (array_key_exists('quiz' . $x, $newQuizLoadData['quizzes'])) {
				$quiz = $newQuizLoadData['quizzes']['quiz' . $x];
				// Check for a matching category
				$currentCat = !empty($quiz['categoryName']) ? strtolower(trim(format_string2(strval($quiz['categoryName'])))) : '';
				$findCat = !empty($currentCat) ? array_search($currentCat, array_column($catData, 'cat_name')) : 0;
				$id_category = !empty($catOverride) ? $catOverride : (!empty($findCat) ? $catData[$findCat]['id_cat'] : 0);

				$image = !empty($quiz['image']) && $quiz['image'] != '-' ? format_string2($quiz['image']) : 'Default-64.png';
				$newQuizId = ImportQuiz(format_string2($quiz['title']), format_string2($quiz['description']), (int)$quiz['playLimit'], (int)$quiz['secondsPerQuestion'], (int)$quiz['showAnswers'], (int)$id_category, $isEnabled, $image, $creator_id);
				if (!is_numeric($newQuizId)) {
					$unsuccessful[md5(format_string2($quiz['title']))] = array(format_string2($quiz['title']), 'quiz_mod_quiz_already_exists');
				}
				else {
					$successful[] = array(format_string2($quiz['title']), null);
					// Question objects
					while (array_key_exists('question' . $y, $quiz['questions'])) {
						$questions = $quiz['questions']['question' . $y];
						$qImage = !empty($questions['image']) ? format_string2($questions['image']) : '';
						$newQuestionId = ImportQuizQuestion($newQuizId, format_string2($questions['questionText']), (int)$questions['questionTypeId'], format_string2($questions['answerText']), $qImage, '');
						// Answer objects
						while (array_key_exists('answer' . $z, $questions['answers'])) {
							$answers = $questions['answers']['answer' . $z];
							ImportQuizAnswer($newQuestionId, format_string2($answers['answerText']), (int)$answers['isCorrect']);
							$z++;
						}
						$z = 0;
						$y++;
					}
					$y = 0;
				}
				$x++;
			}

		}
	}
	else {
		$unsuccessful[] = array($txt['quiz_mod_unknown_quiz'], 'quiz_mod_error_reading_file');
	}

	//$context['SMFQuiz']['SMFQuizImported'] = $fileCount+1;
	return array('successful' => $successful, 'unsuccessful' => $unsuccessful);
}

function quizGetCategoryInfo()
{
	global $smcFunc;

	$catData = [];
	$catData[] = ['id_cat' => 0, 'cat_name' => ''];
	$result = $smcFunc['db_query']('', '
		SELECT 		id_category, name
		FROM 		{db_prefix}quiz_category
		WHERE		id_category > 0',
		[]
	);

	while ($row = $smcFunc['db_fetch_assoc']($result)) {
		$catData[] = [
			'id_cat' => (int)$row['id_category'],
			'cat_name' => strtolower(trim(format_string2($row['name']))),
		];
	}

	// Free the database
	$smcFunc['db_free_result']($result);

	return !empty($catData) ? array_filter($catData) : ['id_cat' => 0, 'cat_name' => ''];
}

// TODO
function format_string2($stringToFormat)
{
	global $smcFunc;

	// Remove any backslashes
	$stringToFormat = str_replace(array("\\", "quizes", "Quizes"), array("", "quizzes", "Quizzes"), stripcslashes($stringToFormat));

	// Ensure double|single quotes are explicitly HTML5 entities
	$returnString = htmlspecialchars_decode($stringToFormat, ENT_NOQUOTES);
	$returnString = str_replace(array("'", '"'), array('&apos;', '&quot;'), html_entity_decode($stringToFormat, ENT_QUOTES|ENT_HTML5, 'UTF-8'));
	$returnString = Quiz\Helper::format_entities($returnString, true);

	return $returnString;
}

function BuildQuizXml($id_quiz)
{
	global $context, $modSettings, $user_settings, $settings;

	$quizXml = '<?xml version="1.0" encoding="utf-8"?>
			<quizzes>
	';
	$quizRows = ExportQuizzes($id_quiz);
	foreach ($quizRows as $row)
	{
	// @TODO double quotes
		$quizXml .= "
				<quiz>
					<title><![CDATA[{$row['title']}]]></title>
					<description><![CDATA[{$row['description']}]]></description>
					<playLimit>{$row['play_limit']}</playLimit>
					<secondsPerQuestion>{$row['seconds_per_question']}</secondsPerQuestion>
					<showAnswers>{$row['show_answers']}</showAnswers>
					<categoryName><![CDATA[{$row['category_name']}]]></categoryName>
					<image><![CDATA[{$row['image']}]]></image>
					<image_data><![CDATA[{$row['image_data']}]]></image_data>
					<user_name><![CDATA[{$user_settings['member_name']}]]></user_name>
					<email_address><![CDATA[{$user_settings['email_address']}]]></email_address>
					<theme_url><![CDATA[{$settings['theme_url']}]]></theme_url>
					<questions>
		";

		$quizQuestionRows = ExportQuizQuestions($row['id_quiz']);

		foreach ($quizQuestionRows as $questionRow)
		{
			$questionRow['image_data'] = !empty($questionRow['image_data']) ? $questionRow['image_data'] : '';
	// @TODO double quotes
			$quizXml .= "
						<question>
							<questionText><![CDATA[{$questionRow['question_text']}]]></questionText>
							<questionTypeId>{$questionRow['id_question_type']}</questionTypeId>
							<answerText><![CDATA[{$questionRow['answer_text']}]]></answerText>
							<image><![CDATA[{$questionRow['image']}]]></image>
							<image_data><![CDATA[{$questionRow['image_data']}]]></image_data>
							<answers>
			";

			$quizAnswerRows = ExportQuizAnswers($questionRow['id_question']);

			foreach ($quizAnswerRows as $answerRow)
	// @TODO double quotes
				$quizXml .= "
								<answer>
									<answerText><![CDATA[{$answerRow['answer_text']}]]></answerText>
									<isCorrect>{$answerRow['is_correct']}</isCorrect>
								</answer>
				";

			$quizXml .= "
							</answers>
						</question>
			";
		}

		$quizXml .= "
					</questions>
				</quiz>";
	}

	$quizXml .= "
			</quizzes>
	";
	return $quizXml;
}

function quizRmdir($dir, $ignore = '')
{
	global $modSettings, $boarddir;

	// linux/windows compatibility
	$ignore = empty($ignore) ? '/' . uniqid('ignore_', true) . '/' : $ignore;
	$boarddirx = str_replace('\\', '/', $boarddir);
	$thisPath = str_replace('\\', '/', $dir);
	$ignore = str_replace('\\', '/', $ignore);

	$boarddirx = trim($boarddirx, '/');
	$mainPathArray = array('Sources', 'Themes', 'Packages', 'Smileys', 'cache', 'avatars', 'attachments');
	$thisPath = trim($thisPath, '/');

	// make absolutely sure the deleted path is not an essential parent path
	if ($thisPath == '.' || $thisPath == '..' || $thisPath == $boarddirx || !is_dir($dir))
		return false;

	foreach ($mainPathArray as $path)
	{
		if ($thisPath == $boarddirx . '/' . $path)
			return false;
	}

	clearstatcache(false, $dir);
	if (is_dir($dir) && stripos(str_replace('\\', '/', $dir), $ignore) === FALSE && stripos(str_replace('\\', '/', $dir), rtrim($ignore, '/')) === FALSE)
	{
		$objects = scandir($dir);
		foreach ($objects as $object)
		{
			if ($object != '.' && $object != '..')
			{
				clearstatcache(false, $dir . '/' . $object);
				if (is_dir($dir . '/' . $object)) {
					quizRmdir($dir . '/' . $object, $ignore);
				}
				else {
					@chmod($dir . '/' . $object, 0777);
					@unlink($dir . '/' . $object);
				}
			}
		}

		reset($objects);
		clearstatcache(false, $dir);
		if (is_readable($dir) && is_dir($dir)) {
			if (count(scandir($dir)) == 2) {
				@chmod($dir, 0777);
				if (@rmdir($dir)) {
					clearstatcache(false, $dir);
					return true;
				}
			}
		}
	}

	return false;
}

?>