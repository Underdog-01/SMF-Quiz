<?php

if (!defined('SMF'))
	die('Hacking attempt...');

//Main function
function SMFQuizAdmin()
{
	global $context, $modSettings, $boardurl, $scripturl, $txt, $sourcedir, $settings;

	isAllowedTo('quiz_admin');

	// Load the language file
	loadLanguage('Quiz/Admin');
	loadLanguage('Quiz/Quiz');
	// @TODO are both needed?
	require_once($sourcedir . '/Quiz/Db.php');
	require_once($sourcedir . '/Quiz/Load.php');
	require_once($sourcedir . '/ManageServer.php');

	$context['html_headers'] .= '
		<style type="text/css">
			/* replacement for nobr tag */
			.nobr
			{
				white-space:nowrap
			}
			#SMFQuiz_PMBrokenTopScoreMsg, #SMFQuiz_PMLeagueRoundUpdateMsg
			{
				width: 85%;
			}
		</style>';

	// This uses admin tabs - as it should!
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['SMFQuiz'],
		'help' => $txt['SMFQuizMod'],
		'description' => $txt['SMFQuizModDescription'],
	);

	$modSettings['disableQueryCheck'] = 1;

	// Add javascript for multiple checkbox selection
	// TODO: Make this dependant on what we are showing
	// @TODO move as much as possible to a file
	$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery-1.3.2.min.js"></script>
		<script type="text/javascript"><!-- // --><![CDATA[
			function submitPreview (item)
			{
				// @TODO reimplement the preview
				alert(\'' . $txt['quiz_mod_preview_disabled'] . '\')
			}

			function checkAll(selectedForm, checked)
			{
				for (var i = 0; i < selectedForm.elements.length; i++)
				{
					var e = selectedForm.elements[i];
					if (e.type==\'checkbox\')
						e.checked = checked;
				}
			}
			
			function show_image(imgId, selectElement, imageFolder)
			{
				var imgElement = document.getElementById(imgId);
				var selectedValue = selectElement[selectElement.selectedIndex].text;
				var imageUrl = "' . $boardurl . '/Themes/default/images/quiz_images/blank.gif"
				if (selectedValue != "-")
					imageUrl = "' . $boardurl . '/Themes/default/images/quiz_images/" + imageFolder + "/" + selectedValue;

				imgElement.src = imageUrl;
			 }

			function changeQuestionType(selectedForm)
			{
				var selection = selectedForm.options[selectedForm.options.selectedIndex].value;

				document.getElementById("freeTextAnswer").style.display = selection == 2 ? \'block\' : \'none\';
				document.getElementById("multipleChoiceAnswer").style.display = selection == 1 ? \'block\' : \'none\';
				document.getElementById("trueFalseAnswer").style.display = selection == 3 ? \'block\' : \'none\';
			}

			function addRow()
			{
				var rowCount = document.getElementById("answerTable").rows.length;
				var radioElement = document.createElement("input");

				radioElement.setAttribute("name", "correctAnswer");
				radioElement.setAttribute("value", rowCount);
				radioElement.setAttribute("type", "radio");

				var answerElement = document.createElement("input");
				answerElement.setAttribute("name", "answer" + rowCount);
				answerElement.setAttribute("size", "50");
				answerElement.setAttribute("type", "text");

		// @TODO check tags case
				var tbody = document.getElementById("answerTable").getElementsByTagName("TBODY")[0];
				var row = document.createElement("TR");
				var td1 = document.createElement("TD");
				td1.appendChild(radioElement);
				var td2 = document.createElement("TD");
				td2.appendChild (answerElement);
				row.appendChild(td1);
				row.appendChild(td2);
				tbody.appendChild(row);
			}

			function deleteRow()
			{
				var rowCount = document.getElementById("answerTable").rows.length-1;
				if (rowCount > 1)
					document.getElementById("answerTable").deleteRow(rowCount);
			}

			function verifyQuizesChecked(selectedForm)
			{
				var foundChecked = false;
				var quizIds = "";
				for (var i = 0; i < selectedForm.elements.length; i++)
				{
					var e = selectedForm.elements[i];
					if (e.type==\'checkbox\')
					{
						if (e.checked)
						{
							quizIds = quizIds + e.name.substr(4) + ",";
							foundChecked = true;
						}
					}
				}
				if (foundChecked == true)
				{
					var packageName = document.getElementById("packageName").value;
					var packageDescription = document.getElementById("packageDescription").value;
					var packageAuthor = document.getElementById("packageAuthor").value;
					var packageSiteAddress = document.getElementById("packageSiteAddress").value;
// @TODO replace with POSTed data
					location.href = "' . $scripturl . '?action=SMFQuizExport;quizIds=" + escape(quizIds) + ";packageName=" + escape(packageName) + ";packageDescription=" + escape(packageDescription) + ";packageAuthor=" + escape(packageAuthor) + ";packageSiteAddress=" + escape(packageSiteAddress); 
				}
				else
				{
					alert("' . $txt['AlertOnePackage'] . '");
					return false;
				}
			}
			// ]]></script>
	';

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
			'function' => 'GetDisputesData',
			'text' => $txt['SMFQuizAdmin_Titles']['Disputes'],
		),
		'quizes' => array(
			'function' => 'GetQuizData',
			'text' => $txt['SMFQuizAdmin_Titles']['Quizes'],
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

function GetMaintenanceData()
{
	global $context, $txt;

	loadLanguage('ManageMaintenance');

	$context['html_headers'] .= '<script type="text/javascript"><!-- // --><![CDATA[
			function clearResults(thisform)
			{
				thisform.formaction.value = "resetQuizes";
				if(confirm(\'' . $txt['SMFQuizAdmin_Maintenance_Page']['ResetAllQuizData'] . '\'))
					thisform.submit();
				else
					return false;
			}
	// ]]></script>';
	
	// User has selected to reset the quiz scores
	if (isset($_POST['formaction']) && $_POST['formaction'] == 'resetQuizes')
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
		array('text', 'SMFQuiz_InfoBoardItemsToDisplay', 6, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['InfoBoardItemsToDisplay']),
		array('text', 'SMFQuiz_ListPageSizes', 6, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['ListPageSizes']),
		array('text', 'SMFQuiz_ImportQuizesAsUserId', 6, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['ImportQuizesAsUser']),
		array('text', 'SMFQuiz_SessionTimeLimit', 6, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['SessionTimeLimit']),
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
		array('large_text', 'SMFQuiz_PMBrokenTopScoreMsg', 10, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['PMBrokenTopScoreMsg'] . ' - ' . $txt['SMFQuiz_Common']['MessageBody'], 'postinput' => '<br/><img style="cursor:pointer" class="preview_loading" onclick="submitPreview(1);" src="' .$settings['default_images_url'] . '/quiz_images/preview.png" title="' . $txt['SMFQuiz_Common']['Preview'] . '" alt="' . $txt['SMFQuiz_Common']['Preview'] . '" />'),

		array('check', 'SMFQuiz_SendPMOnLeagueRoundUpdate', 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['SendPMOnLeagueRoundUpdate']),
		array('text', 'SMFQuiz_PMLeagueRoundUpdateSubject', 50, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['PMLeagueRoundUpdateMsg'] . ' - ' . $txt['SMFQuiz_Common']['Subject']),
		array('large_text', 'SMFQuiz_PMLeagueRoundUpdateMsg', 10, 'text_label' => $txt['SMFQuizAdmim_Settings_Page']['PMLeagueRoundUpdateMsg'] . ' - ' . $txt['SMFQuiz_Common']['MessageBody'], 'postinput' => '<br/><img style="cursor:pointer" class="preview_loading" onclick="submitPreview(2);" src="' .$settings['default_images_url'] . '/quiz_images/preview.png" title="' . $txt['SMFQuiz_Common']['Preview'] . '" alt="' . $txt['SMFQuiz_Common']['Preview'] . '" />'),
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
		elseif (isset($_GET["children"]))
			GetCategoryChildrenData($_GET["children"]);
		elseif (isset($_GET["parent"]))
			GetParentCategoryData($_GET["parent"]);
		// Otherwise just get the default category data
		else
			GetCategoryChildrenData(0);
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
		GetQuizesData();
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
	global $context;

	// If user is deleting disputes
	if (isset($_POST['DeleteQuizDispute']))
		GetDeleteQuizDisputeData();
	// Otherwise just get the default Disputes data
	else
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
			case 'New Quiz League' : // User wants to create a new Quiz league
				GetNewQuizLeagueData();
				break;

			case 'Delete Quiz League' : // User wants to delete the specified quiz league
				GetDeleteQuizLeagueData();
				break;

			case 'Update Quiz League' : // User is updating a quiz league
				GetUpdateQuizLeagueData();
				break;

			case 'Save Quiz League' : // User has selected to save a new quiz league
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

	// Append required javascript to context
	SetImageUploadJavascript();

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

	// Append required javascript to context
	SetImageUploadJavascript();

	if (isset($_POST['id_quiz']))
		$context['SMFQuiz']['id_quiz'] = $_POST['id_quiz'];

	// The new question page provides a list of quizes to select. Therefore we need to obtain a list of category data
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

	// Append required javascript to context
	SetImageUploadJavascript();

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

	// Append required javascript to context
	SetImageUploadJavascript();

		// @TODO ???
	isset($_GET['id']) ? $categoryId = $_GET['id'] : 0;

	GetCategory($categoryId);

	// The edit category page also shows a list of parent categories, so we must get this data
	GetAllCategoryDetails();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'EditCategory';
}

/*
Function used to set the jscript/javascript required for the image upload functionality
*/
function SetImageUploadJavascript()
{
	global $context, $boardurl, $settings;

		// @TODO update jQuery + CDN + local loading, etc.
	$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery.selectboxes.js"></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/quiz/ajaxfileupload.js"></script>
		<script type="text/javascript"><!-- // --><![CDATA[
		$(document).ready(function() {
		});

		function ajaxFileUpload(subFolder)
		{
			//starting setting some animation when the ajax starts and completes
			$(".preview_loading").each().ajaxStart(function() {
				$(this).show();
			}).ajaxComplete(function() {
				$(this).hide();
			});
			$.ajaxFileUpload
			(
				{
					url:\'' . $boardurl . '/index.php?action=SMFQuizAjax;sa=imageUpload;xml;imageFolder=\'+subFolder,
					secureuri:false,
					fileElementId:\'fileToUpload\',
					dataType: \'json\',
					success: function (data, status)
					{
						if(typeof(data.error) != \'undefined\')
						{
							if(data.error != \'\')
							{
								alert(data.error);
							}
							else
							{
								alert(data.msg);
								refreshImageList(subFolder, data.filename);
							}
						}
					},
					error: function (data, status, e)
					{
						alert(e);
					}
				}
			);
			return false;
		}

		// Refreshes all images in the image dropdown box
		function refreshImageList(subFolder, sel_file)
		{
			// Remove any existing entries
			$("#imageList").removeOption(/./);
			$("#imageList").addOption("-", "-", sel_file == undefined);
			$.ajax({
				url: \'' . $boardurl . '/index.php?action=SMFQuizAjax;sa=imageList;xml;imageFolder=\'+ subFolder,
				type: \'GET\',
				dataType: \'xml\',
				timeout: 2000,
				error: function() {
					alert(\'Error loading XML file list\');
				},
				success: function(xml) {
					$(xml).find(\'file\').each(function() {
						var item_text = $(this).text();
						$("#imageList").addOption(item_text, item_text, sel_file != undefined && sel_file == item_text);
					});
				}
			});
		}
		// ]]></script>
	';
}

function GetEditQuizData()
{
	global $context;

		// @TODO ???
	isset($_GET['id']) ? $quizId = $_GET['id'] : 0;

	// Append required javascript to context
	SetImageUploadJavascript();

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

	// Append required javascript to context
	SetImageUploadJavascript();

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
		DeleteQuizes($deleteKeys);

	// As we are going to return to the quiz detail list page after the delete, we need to load this data
	GetQuizesData();

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
	$showanswers = isset($_POST['show_answers']) ? $_POST['show_answers'] : '';
	$enabled = isset($_POST['enabled']) ? $_POST['enabled'] : '';
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? $_POST['image'] : '';	
	$categoryId = isset($_POST['id_category']) ? $_POST['id_category'] : '';
	$quizId = isset($_POST['id_quiz']) ? $_POST['id_quiz'] : '';
	$oldCategoryId = isset($_POST["oldCategoryId"]) ? $_POST["oldCategoryId"] : ''; // Need the old category, as if it is different we need to change quiz counts
	$for_review = 1;

	if ($showanswers == 'on')
		$showanswers = 1;
	else
		$showanswers = 0;

	if ($enabled == 'on')
		$enabled = 1;
	else
		$enabled = 0;

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
		GetQuizesData();
		$context['SMFQuiz']['Action'] = 'Quizes';
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
	$showanswers = isset($_POST['showanswers']) ? $_POST['showanswers'] : '';
	$enabled = isset($_POST['enabled']) ? $_POST['enabled'] : '';
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? $_POST['image'] : '';	
	$categoryId = isset($_POST['id_category']) ? $_POST['id_category'] : '';

	if ($showanswers == 'on')
		$showanswers = 1;
	else
		$showanswers = 0;

	if ($enabled == 'on')
		$enabled = 1;
	else
		$enabled = 0;

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
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? $_POST['image'] : '';	
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
	$quizId = isset($_POST['id_quiz']) ? $_POST['id_quiz'] : '';
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? $_POST['image'] : '';
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
	$interval = isset($_POST["interval"]) ? $_POST["interval"] : '';
	$questions = isset($_POST["questions"]) ? $_POST["questions"] : '';
	$seconds = isset($_POST['seconds']) ? $_POST['seconds'] : '';
	$points = isset($_POST["points"]) ? $_POST["points"] : '';
	$showanswers = isset($_POST['showanswers']) ? $_POST['showanswers'] : '';
	$totalRounds = isset($_POST["totalRounds"]) ? $_POST["totalRounds"] : '';
	$state = isset($_POST["state"]) ? $_POST["state"] : '';

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

	if ($showanswers == 'on')
		$showanswers = 1;
	else
		$showanswers = 0;

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
	$showanswers = isset($_POST['showanswers']) ? $_POST['showanswers'] : '';
	$totalRounds = isset($_POST["totalRounds"]) ? $_POST["totalRounds"] : '';
	$state = isset($_POST["state"]) ? $_POST["state"] : '';
	$id_quiz_league = isset($_POST["id_quiz_league"]) ? $_POST["id_quiz_league"] : 0;

	// Build the category selection string
	$categoryArray = $_POST["categories"];
	$categories = '';
	foreach ($categoryArray as $category)
		$categories .= $category . ',';

	if ($showanswers == 'on')
		$showanswers = 1;
	else
		$showanswers = 0;

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
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? $_POST['image'] : '';

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

function GetQuizesData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings, $settings;

	// If user has clicked button to disable quiz
	if (isset($_GET['disable_quiz_id']))
		UpdateQuizStatus($_GET['disable_quiz_id'], 0);

	// If user has clicked button to enable quiz
	if (isset($_GET['enable_quiz_id']))
		UpdateQuizStatus($_GET['enable_quiz_id'], 1);

	// If user has clicked button to upload quiz
	if (isset($_GET['upload_quiz_id']))
		UploadQuiz($_GET['upload_quiz_id'], 1);

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
		$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;starts_with=' . chr($i) . '">' . strtoupper(chr($i)) . '</a> ';

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=quiz;sa=quizes;starts_with=' . $starts_with . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';

	$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;">*</a> ';
	$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;enabled"><img src="' . $settings['default_images_url'] . '/quiz_images/unlock.png" alt="enabled" title="enabled" align="top" /></a> ';
	$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;disabled"><img src="' . $settings['default_images_url'] . '/quiz_images/lock.png" alt="disabled" title="disabled" align="top" /></a> ';
	$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;review"><img src="' . $settings['default_images_url'] . '/quiz_images/review.png" alt="for review" title="for review" align="top" /></a> ';

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
	list ($context['num_quizes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=quiz;sa=quizes;starts_with=' . $starts_with . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : '') . (isset($_REQUEST['disabled']) ? ';disabled' : '') . (isset($_REQUEST['enabled']) ? ';enabled' : '') . (isset($_REQUEST['review']) ? ';review' : ''), $_REQUEST['start'], $context['num_quizes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizes']);

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

	$context['SMFQuiz']['quizes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizes'][] = $row;

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'quizes';
}

function GetShowDisputesData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings, $settings, $boardurl;

	$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/quiz_css/jquery-ui-1.7.1.custom.css"/>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery-ui-1.7.1.custom.min.js"></script>
		<script type="text/javascript"><!-- // --><![CDATA[
		
		var id_dispute = 0;
		
		$(document).ready(function() {
			$(".disputeDialog").click(function() {
				id_dispute = this.id;
				showDisputeDialog();
			});
		});

		function submitResponse(remove)
		{
			// Get the reason entered
			var reason = $("#disputeText").val();

			$.ajax({
				type: "GET",
				// @TODO move to an action and allow js-less (that will fix the form validation too)
				url: "' . $boardurl . '/index.php?action=SMFQuizDispute;id_dispute=" + id_dispute + ";reason=" + reason + ";remove=" + remove,
				cache: false,
				dataType: "xml",
				timeout: 5000,
				success: function(xml) {
	// @TODO localization
					alert(\'Dispute response submitted successfully\');
					window.location.reload();
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
	// @TODO localization
					alert(\'Timeout occurred sending response\');
				}
			});
		}

		function showDisputeDialog()
		{
			$("#disputeText").val(\'\');
			$("#disputeDialog").dialog({
				bgiframe: true,
				modal: true,
				resizable: false,
				buttons: {
				// @TODO localization
					"Send": function() {
						submitResponse(0);
						$(this).dialog(\'close\');
					},
				// @TODO localization
					"Send and Remove": function() {
						submitResponse(1);
						$(this).dialog(\'close\');
					},
				// @TODO localization
					Cancel: function() {
						$(this).dialog(\'close\');
					}
				}
			});
			$("#disputeDialog").dialog(\'open\');
		}
		// ]]></script>
	';

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
	list ($context['num_quizes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=quiz;sa=disputes;sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizes'], $limit);

	// Send the data to the template.
	// @TODO check input?
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizes']);

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
	list ($context['num_quizes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=quiz;sa=results;sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizes']);

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
					QR.total_resumes
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

// Function that handles the saving of the specified new quiz league data
function GetSaveCategoryData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$name = isset($_POST["name"]) ? $_POST["name"] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$parent = isset($_POST["parentId"]) ? $_POST["parentId"] : '';
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? $_POST['image'] : '';	

	// Save the data
	SaveCategory($name, $description, $parent, $image);

	GetAllCategoryDetails();

	$context['SMFQuiz']['Action'] = 'SaveCategory';
}

	// @TODO to remove
function UploadQuiz($id_quiz)
{
	global $context, $settings, $txt;

	$content = BuildQuizXml($id_quiz);
	$server  = 'www.smfmodding.com';
	//$server  = 'localhost';
	$port    = '80';
	$uri     = '/Sources/SMFQuizUpload.php';
	//$uri     = '/smf2/Sources/SMFQuizUpload.php';

	$post_results = httpFunc('POST',$server,$port,$uri,$content);

	if (!is_string($post_results))
		$context['SMFQuiz']['uploadResponse'] = '<img src="' . $settings['default_images_url'] . '/quiz_images/warning.png" alt="yes" title="Warning" align="top" />&nbsp;' . $txt['SMFQuizAdmin_Quizes_Page']['QuizUploadError'];
	else
	{
		if (strpos($post_results,'exists'))
			$context['SMFQuiz']['uploadResponse'] = '<img src="' . $settings['default_images_url'] . '/quiz_images/warning.png" alt="yes" title="Warning" align="top" />&nbsp;' . $txt['SMFQuizAdmin_Quizes_Page']['QuizUploadExists'];
		else
		{
			$context['SMFQuiz']['uploadResponse'] = '<img src="' . $settings['default_images_url'] . '/quiz_images/information.png" alt="yes" title="Information" align="top" />&nbsp;' . $txt['SMFQuizAdmin_Quizes_Page']['QuizUploadedSuccessfully'];
			upload_images($id_quiz);
		}
	}
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
		$status .= '<br/>' . load_image($row['image']);

	$smcFunc['db_free_result']($result);	
	$context['SMFQuiz']['uploadResponse'] .= $status;
}

function load_image($imageFileName)
{
	global $boarddir, $boarddir;

	$filename = $boarddir . '/Themes/default/images/quiz_images/Questions/' . $imageFileName;
	// @TODO check if file exists
	$handle = fopen($filename, "rb");
	$contents = fread($handle, filesize($filename));
	fclose($handle);

	return upload_image($contents, $imageFileName);
}

	// @TODO to remove
function upload_image($imageString, $imageFileName)
{
	global $boarddir, $settings;

	$server  = 'www.smfmodding.com';
	//$server  = 'localhost';
	$port    = '80';
	$uri     = '/Sources/SMFQuizImageUpload.php?filename=' . urlencode($imageFileName);
	//$uri     = '/smf2/Sources/SMFQuizImageUpload.php?filename=' . $imageFileName;
	$content = $imageString;

	$get_results = httpFunc('POST',$server,$port,$uri,$content);
	if (strstr($get_results,'done'))
		return '<img src="' . $settings['default_images_url'] . '/quiz_images/information.png" alt="yes" title="Information" align="top" /> ' . $imageFileName . ' uploaded successfully';
	elseif (strstr($get_results,'exists'))
		return '<img src="' . $settings['default_images_url'] . '/quiz_images/warning.png" alt="yes" title="Warning" align="top" /> ' . $imageFileName . ' already exists on server';
	else
		return '<img src="' . $settings['default_images_url'] . '/quiz_images/warning.png" alt="yes" title="Warning" align="top" /> ' . $imageFileName . ' did not upload due to unexpected error (' . $get_results . ')';
}

function escape($url)
{
	return str_replace(" ", "%20", $url);
}

function ImportQuizFile($urlPath, $categoryId, $isEnabled, $image, $fileCount)
{
	global $context, $modSettings;
 
	$isEnabled = $isEnabled == 'on' ? 1 : 0;
	$image = !empty($image) && $image != '-' ? $image : null;
	$newUrlPath = escape($urlPath);

	$quizString = load($newUrlPath);

	$creator_id = isset($modSettings['SMFQuiz_ImportQuizesAsUserId']) ? $modSettings['SMFQuiz_ImportQuizesAsUserId'] : 1;

	if (!$myxml=simplexml_load_string($quizString))
		fatal_lang_error('quiz_mod_quiz_already_exists', false);
	else
	{
		// TODO: Should really check XML is valid here
		foreach($myxml->quiz as $quiz)
		{
			$newQuizId = ImportQuiz($quiz->title, $quiz->description, $quiz->playLimit, $quiz->secondsPerQuestion, $quiz->showAnswers, $categoryId, $isEnabled, $image, $creator_id);

			foreach($quiz->questions->children() as $questions)
			{
				$newQuestionId = ImportQuizQuestion($newQuizId, $questions->questionText, $questions->questionTypeId, $questions->answerText);
				foreach ($questions->children()->answers->children() as $answers)
					ImportQuizAnswer($newQuestionId, $answers->answerText, $answers->isCorrect);
			}
		}
		$context['SMFQuiz']['SMFQuizImported'] = $fileCount+1;
	}
}

	// @TODO to replace
function ImportQuizes($quizDetails)
{
	global $boarddir, $context, $boardurl;

	// Retrieve indexes of files and post values into array
	$fileIndexesToImport = Array();
	$fileCategoriesToImport = Array();
	$fileEnabledToImport = Array();
	$fileImagesToImport = Array();

	// Loop through posted values and assing to arrays appropriately
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

	$path = 'http://www.smfmodding.com/quizes/';

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

	$path = $boarddir . '/tempQuizes/';
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
	$context['SMFQuiz_totalQuizes'] = 0;
	GetTotalQuizStats();
	if (isset($context['SMFQuiz']['totalQuizStats']))
	{
		foreach($context['SMFQuiz']['totalQuizStats'] as $row)
		{
			$context['SMFQuiz_totalQuizes'] = $row['total_quiz_count'];
			$context['SMFQuiz_totalResults'] = $row['total_quiz_plays'];
		}
	}
	GetTotalDisputesCount();
	GetDisabledQuizCount();
	GetTotalReviewCount();
}

	// @TODO to recode with an access to multiple remote servers
function import_quiz($quizXmlString, $image = 'Default-64.png')
{
	global $modSettings, $user_settings, $settings, $context, $txt, $sourcedir;

	if (empty($quizXmlString))
		return;
	// @TODO this function still needs a bit of work

	$installedCategories = get_category_names();
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

	// Only continue if XML is valid
	require_once($sourcedir . '/Class-Package.php');
	$quizzes = New xmlArray($quizXmlString);

	if (!$quizzes->exists('quizes'))
		$unsuccessful[] = array($txt['quiz_mod_unknown_quiz'], 'quiz_mod_error_reading_file');
	else
	{
		foreach ($quizzes->set('quizes/quiz') as $quiz)
		{
			$title = $quiz->fetch('title');
			if (empty($title))
				continue;

			$id_category = 0;
			$creator_id = isset($modSettings['SMFQuiz_ImportQuizesAsUserId']) ? $modSettings['SMFQuiz_ImportQuizesAsUserId'] : 1;
			$categoryLocator = !empty($installedCategories['name']) && $quiz->exists('categoryName') ? array_search($quiz->fetch('categoryName'), $installedCategories['name']) : '';

			// We have found a matching category, so use this
			if (!empty($categoryLocator))
				$id_category = $installedCategories['id_category'][$categoryLocator];

			if ($quiz->exists('image'))
			{
				$image = $quiz->fetch('image');
				$disabledFiles = array('con', 'com1', 'com2', 'com3', 'com4', 'prn', 'aux', 'lpt1', '.htaccess', 'index.php');
				if (in_array(strtolower($image), $disabledFiles))
					$image = 'quiz_' . $image;
			}

			if ($quiz->exists('image') && $quiz->exists('imageData'))
			{
				$dest = $settings['default_theme_dir'] . '/images/quiz_images/Quizes/' . $image;
				if (!file_exists($dest) && is_writable($settings['default_theme_dir'] . '/images/quiz_images/Quizes/'))
				{
					$imageData = base64_decode($quiz->fetch('imageData'));
					file_put_contents($dest, $imageData);
					$size = @getimagesize($dest);
					// Default to png (3)
					$fileType = isset($validImageTypes[$size[2]]) ? $size[2] : 3;
					require_once($sourcedir . '/Subs-Graphics.php');
					if (!reencodeImage($dest, $fileType))
					{
						@unlink($dest);
						@unlink($dest . '.tmp');
					}
				}
			}

			$newQuizId = ImportQuiz($title, $quiz->fetch('description'), $quiz->fetch('playLimit'), $quiz->fetch('secondsPerQuestion'), $quiz->fetch('showAnswers'), $id_category, 1, $image, $creator_id);

			if (!is_numeric($newQuizId))
				$unsuccessful[md5($title)] = array($title, 'quiz_mod_quiz_already_exists');
			foreach ($quiz->set('questions/question') as $questions)
			{
				$newQuestionId = ImportQuizQuestion($newQuizId, $questions->fetch('questionText'), $questions->fetch('questionTypeId'), $questions->fetch('answerText'), $questions->fetch('image'),  $questions->fetch('imageData'));

				foreach ($questions->set('answers/answer') as $answers)
					ImportQuizAnswer($newQuestionId, $answers->fetch('answerText'), $answers->fetch('isCorrect'));
			}
			$context['SMFQuiz']['importResponse'] = '<img src="' . $settings['default_images_url'] . '/quiz_images/information.png" alt="yes" title="Information" align="top" />&nbsp;' . $txt['SMFQuizAdmin_Quizes_Page']['QuizImportedSuccessfully'];
// 			if (!empty($newQuizId))
// 				import_quiz_images($newQuizId);
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

function get_category_names()
{
	global $smcFunc;

	// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QC.id_category,
					QC.name
		FROM 		{db_prefix}quiz_category QC'
	);

	$categoryNames = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$categoryNames['id_category'][] = $row['id_category'];
		$categoryNames['name'][] = $row['name'];
	}

	$smcFunc['db_free_result']($result);	

	return $categoryNames;
}

	// @TODO to replace
function save_image($image)
{
	global $boarddir;

	// Where to save the file
	$outFilePath = $boarddir . '/Themes/default/images/quiz_images/Quizes/' . $image;

	$server  = 'www.smfmodding.com';
	$port    = '80';
	$uri     = '/Themes/default/images/quiz_images/Quizes/' . urlencode($image);
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

	// Nasty hack for when we are retrieving Quizes to import. For some reason we
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
	
function GetQuizImportData()
{
	global $context, $scripturl, $modSettings, $smcFunc, $txt, $settings;

	$importResults = array();
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
		$numFiles = count($_FILES['imported_quiz']['tmp_name']);
		for ($i = 0; $i < $numFiles; $i++)
		{
			$file = $_FILES['imported_quiz']['tmp_name'][$i];
			$imgFile = '';
			if (!empty($_FILES['imported_quiz_img']['tmp_name'][$i]))
			{
				// Borrowed from Subs-Post.php {
				$size = @getimagesize($_FILES['imported_quiz_img']['tmp_name'][$i]);
				// Sometime in the future we my want to check that the size is not higher than...something
				list ($width, $height) = $size;

				// Is a valid image: green light for upload
				if (isset($validImageTypes[$size[2]]))
				{
				//}
					$imgFile = un_htmlspecialchars($_FILES['imported_quiz_img']['name'][$i]);
					$img_destination = $settings['default_theme_dir'] . '/images/quiz_images/Quizes/' . $imgFile;
					if (!file_exists($img_destination) && is_writable($settings['default_theme_dir'] . '/images/quiz_images/Quizes'))
						move_uploaded_file($_FILES['imported_quiz_img']['tmp_name'][$i], $img_destination);
				}
			}
			if (!empty($file))
			{
				if (isset($_GET['image']))
					save_image($_GET['image']);

				$fileContent = file_get_contents($file);
				$importResults[$i] = array(
					'name' => $_FILES['imported_quiz']['name'][$i],
					'returns' => import_quiz($fileContent, $imgFile),
				);
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
	}
/*
	$starts_with = isset($_GET['starts_with']) ? $_GET['starts_with'] : '';
	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'updated';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];
	$start = isset($_REQUEST['start']) ? $_REQUEST['start'] : 1;
	$hide_imported = isset($_REQUEST['hide_imported']) ? $_REQUEST['hide_imported'] : false;

	// Set up the columns...
	$context['columns'] = array(
		'updated' => array(
			'label' => $txt['SMFQuiz_Common']['Date']
		),
	// @TODO '' => ???
		'' => array(
			'label' => '',
			'width' => '2'
		),
		'title' => array(
			'label' => $txt['SMFQuiz_Common']['Title']
		),
		'description' => array(
			'label' => $txt['SMFQuiz_Common']['Description']
		),
		'category' => array(
			'label' => $txt['SMFQuiz_Common']['Category']
		),
	// @TODO '' => ???
		'' => array(
			'label' => '',
			'width' => '2'
		)		
	);

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=quiz;sa=quizimporter;hide_imported=' . $hide_imported . ';starts_with=' . $starts_with . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	// Load quiz importer stats XML
	$getQuizImporterStatsUrl = "http://www.smfmodding.com/Sources/SMFQuizImporter.php?action=getImporterStats";
	$getQuizImporterStatsXml = simplexml_load_string(load($getQuizImporterStatsUrl));
	$context['SMFQuiz_top10QuizImports'] = $getQuizImporterStatsXml->top10Quizes->top10Quiz;
	$context['SMFQuiz_latestQuizImports'] = $getQuizImporterStatsXml->latestImports->latestImport;

	// Load quiz XML
	$getQuizesUrl = "http://www.smfmodding.com/Sources/SMFQuizImporter.php?action=getQuizes;sort=" . $sort . ";limit=" . $limit . ";start=" . $start . ";hide_imported=" . $hide_imported . ";starts_with=" . $starts_with;
	if (isset($_REQUEST['desc']))
		$getQuizesUrl .= ";desc";

	$quizesXmlString = load($getQuizesUrl);
	$getQuizesXml = simplexml_load_string($quizesXmlString);

	// Get quiz data and store in context
	$context['SMFQuiz_quizesToImport'] = $getQuizesXml->quiz;

	$context['num_quizes'] =  $getQuizesXml->count;

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=quiz;sa=quizimporter;starts_with=' . $starts_with . ';hide_imported=' . $hide_imported . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $start, $context['num_quizes'], $limit);
	$hide_imported == 0 ? $hideText = 'hide imported' :  $hideText = 'show imported';
	$context['hide_imported'] = '[<b><a href="' . $scripturl . '?action=admin;area=quiz;sa=quizimporter;hide_imported=' . !$hide_imported . ';starts_with=' . $starts_with . ';sort=' . $sort . ';start=' . $start . '">' . $hideText . ' </a></b>]';

	// Set the filter links
	$context['letter_links'] = '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizimporter;hide_imported=' . $hide_imported . ';">*</a> ';
	for ($i = 97; $i < 123; $i++)
		$context['letter_links'] .= '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizimporter;hide_imported=' . $hide_imported . ';starts_with=' . chr($i) . '">' . strtoupper(chr($i)) . '</a> ';

	// Get all quizes for compare
	// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.title
		FROM 		{db_prefix}quiz Q'
	);

	$context['SMFQuiz']['quizTitles'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizTitles'][] = format_string2($row['title']);

	$smcFunc['db_free_result']($result);*/
}

// TODO
function format_string2($stringToFormat)
{
	global $smcFunc;

	// Remove any slashes. These should not be here, but it has been known to happen
	$returnString = str_replace("\\", "", $smcFunc['db_unescape_string']($stringToFormat));

	return html_entity_decode($returnString, ENT_QUOTES, 'UTF-8');
}

function BuildQuizXml($id_quiz)
{
	global $context, $modSettings, $user_settings, $settings;

	$quizXml = '<?xml version="1.0" encoding="ISO-8859-1"?>
			<quizes>
	';
	$quizRows = ExportQuizes($id_quiz);
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
					<category_name><![CDATA[{$row['category_name']}]]></category_name>
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
	// @TODO double quotes
			$quizXml .= "
						<question>
							<questionText><![CDATA[{$questionRow['question_text']}]]></questionText>
							<questionTypeId>{$questionRow['id_question_type']}</questionTypeId>
							<answerText><![CDATA[{$questionRow['answer_text']}]]></answerText>
							<image><![CDATA[{$questionRow['image']}]]></image>
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
			</quizes>
	";
	return ($quizXml);
}

?>