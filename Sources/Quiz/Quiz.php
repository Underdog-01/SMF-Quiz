<?php

if (!defined('SMF'))
	die('Hacking attempt...');

// Include the SMF2 specific database file
// @TODO move into the function/s
require_once($sourcedir . '/Quiz/Db.php');

function SMFQuiz()
{
	global $context, $txt, $sourcedir, $settings, $modSettings;

	isAllowedTo('quiz_view');
	$context['page_title'] = $txt['SMFQuiz'];
	addJavaScriptVar('id_user', $context['user']['id'], false);
	$qv = !empty($modSettings['smf_quiz_version']) && (stripos($modSettings['smf_quiz_version'], '-beta') !== FALSE || stripos($modSettings['smf_quiz_version'], '-rc') !== FALSE) ? bin2hex(random_bytes(12/2)) : 'stable';
	$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/quiz/QuizMain.css?v=' . $qv . '"/>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/quiz/QuizMain.js?v=' . $qv . '"></script>';

	if ($context['current_subaction'] == 'play')
	{
		$context['template_layers'] = array();
		$context['sub_template'] = 'quiz_play';
	}

	if (isset($_POST['id_quiz']))
		$context['id_quiz'] = $_POST['id_quiz'];
	elseif (isset($_GET['id_quiz']))
		$context['id_quiz'] = $_GET['id_quiz'];
	else
		$context['id_quiz'] = !empty($context['id_quiz']) ? $context['id_quiz'] : 0;

	// Create an array of possible actions with the functions that will be called
	$actions = array(
		'home' => 'GetHomePageData',
		'categories' => 'GetCategoriesData',
		'quizleagues' => 'GetQuizLeaguesData',
		'statistics' => 'GetStatisticsData',
		'userdetails' => 'GetUserDetailsData',
		'userquizzes' => 'GetUserQuizzesData',
		'usersmostactive' => 'GetUsersActiveData',
		'addquiz' => 'GetAddQuizData',
		'saveQuizAndAddQuestions' => 'SaveQuizData',
		'saveQuiz' => 'SaveQuizData',
		'saveQuestion' => 'SaveQuestionData',
		'saveQuestionAndAddMore' => 'SaveQuestionData',
		'updateQuestion' => 'GetUpdateQuestionData',
		'updateQuestionAndAddMore' => 'GetUpdateQuestionData',
		'quizQuestions' => 'GetQuestionsData',
		'updateQuiz' => 'GetUpdateQuizData',
		'updateQuizAndAddQuestions' => 'GetUpdateQuizData',
		'deleteQuestion' => 'GetDeleteQuestionData',
		'newQuestion' => 'GetNewQuestionData',
		'deleteQuiz' => 'GetDeleteQuizData',
		'editQuiz' => 'GetEditQuizData',
		'search' => 'QuizSearchXML',
		'quizscores' => 'GetQuizScoresData',
		'quizzes' => 'GetQuizzesData',
		'quizmasters' => 'GetQuizMastersData',
		'quizleaguetable' => 'GetQuizLeagueData',
		'quizleagueresults' => 'GetQuizLeagueResultsData',
		'unplayedQuizzes' => 'GetUnplayedQuizzesData',
		'playedQuizzes' => 'GetPlayedQuizzesData',
		'preview' => 'GetPreviewQuizData',
		);

// @TODO localization
	$context['tab_links'] = [];
	$context['tab_links'][] = array(
		'action' => 'home',
		'label' => isset($txt['SMFQuiz_tabs']['home']) ? $txt['SMFQuiz_tabs']['home'] : 'Home'
	);
	$context['tab_links'][] = array(
		'action' => 'categories',
		'label' => isset($txt['SMFQuiz_tabs']['categories']) ? $txt['SMFQuiz_tabs']['categories'] : 'Categories'
	);
	$context['tab_links'][] = array(
		'action' => 'quizleagues',
		'label' => isset($txt['SMFQuiz_tabs']['quizleagues']) ? $txt['SMFQuiz_tabs']['quizleagues'] : 'Quiz Leagues'
	);
	$context['tab_links'][] = array(
		'action' => 'statistics',
		'label' => isset($txt['SMFQuiz_tabs']['statistics']) ? $txt['SMFQuiz_tabs']['statistics'] : 'Statistics'
	);
	$context['tab_links'][] = array(
		'action' => 'userdetails',
		'label' => isset($txt['SMFQuiz_tabs']['userDetails']) ? $txt['SMFQuiz_tabs']['userDetails'] : 'User Details',
		'show' => $context['user']['is_logged'],
	);
	$context['tab_links'][] = array(
		'action' => 'userquizzes',
		'label' => isset($txt['SMFQuiz_tabs']['userQuizzes']) ? $txt['SMFQuiz_tabs']['userQuizzes'] : 'User Quizzes'
	);
	$context['tab_links'][] = array(
		'action' => 'usersmostactive',
		'label' => isset($txt['SMFQuiz_tabs']['usersMostActive']) ? $txt['SMFQuiz_tabs']['usersMostActive'] : 'Active Players'
	);

	if (isset($_POST['formaction']))
		$action = $_POST['formaction'];
	elseif (!isset($_GET['sa']))
	{
		$action = 'home';
		$context['current_subaction'] = 'home';
	}
	else
		$action = $_GET['sa'];

	// Load the template
	if ($action != 'search')
		loadTemplate('Quiz/Quiz');

	if (isset($actions[$action]))
		$actions[$action]();
}

// @TODO move to a proper template file?
function template_xml_list()
{
	global $context, $txt;

	echo '<smf>';

	if (isset($context['quiz']['search']['quizzes']))
		foreach ($context['quiz']['search']['quizzes'] as $quiz)
			echo '
			<quiz>
				<id>', $quiz['id'], '</id>
				<name><![CDATA[', $quiz['title'], ']]></name>
				<url><![CDATA[', $quiz['url'], ']]></url>
			</quiz>';

	echo '</smf>';
}

function QuizSearchXML()
{
	global $smcFunc, $scripturl, $db_prefix, $context;

	$context['template_layers'] = array();
	$limit = 5;

	// @TODO check input before queries
	$search = '%'.addslashes($_REQUEST['name']).'%';
	$result = $smcFunc['db_query']('', '
		SELECT count(*) AS quizzes
		FROM {db_prefix}quiz as Q
		WHERE Q.Title LIKE {string:quiz}',
		array(
		'quiz' => $search,
		)
	);
	$row = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);
	// @TODO $row['quizzes'] ?
	$how_many = $row[0];

	$context['SMFQuiz']['search'] = array();
	$context['SMFQuiz']['search']['quizzes'] = array();

	$result = $smcFunc['db_query']('', '
		SELECT Q.id_quiz, Q.title
		FROM {db_prefix}quiz as Q
		WHERE Q.title LIKE {string:quiz}
		LIMIT 0, {int:limit}',
		array(
		'quiz' => $search,
		'limit' => $limit,
		)
	);

	while ($quiz = $smcFunc['db_fetch_assoc']($result))
	{
		$context['quiz']['search']['quizzes'][] = array(
		'title' => $quiz['title'],
		'id' => $quiz['id_quiz'],
		'url' => $scripturl . '?action=SMFQuiz;sa=categories;id_quiz=' . $quiz['id_quiz']
		);
	}
	$smcFunc['db_free_result']($result);

	$context['sub_template'] = 'xml_list';
}

function GetQuestionsData()
{
	global $context;

	// They need a quiz to access here...
	if (!isset($_GET['id_quiz']) || empty($_GET['id_quiz']))
		fatal_lang_error('no_access', false);

	if (isset($_GET['questionId']))
	{
		QuestionScript();
		GetQuestionAndAnswers($_GET['questionId']);
		$context['current_subaction'] = 'editQuestion';
	}
	else
	{
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
			// @TODO check input
			$context['SMFQuiz']['orderBy'] = $_GET['orderBy'];
			if ($_GET['orderDir'] == 'up')
				$context['SMFQuiz']['orderDir'] = 'down';
			else
				$context['SMFQuiz']['orderDir'] = 'up';
		}
		$context['current_subaction'] = 'quizQuestions';
		$context['SMFQuiz']['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
		GetUserQuestionCount($context['id_quiz'], $context['user']['id']);
		GetUserQuestionDetails($context['SMFQuiz']['page'], $sort_methods[$context['SMFQuiz']['orderBy']], $context['SMFQuiz']['orderDir'], $context['id_quiz'], $context['user']['id']);
	}
}

function QuizScript()
{
	global $context;

	// Add javascript for multiple checkbox selection
	// TODO: Make this dependant on what we are showing
	$context['html_headers'] .= '<script type="text/javascript"><!-- // --><![CDATA[
			function validateQuiz(form, action)
			{
				if (document.getElementById("title").value.length > 0)
				{
					document.getElementById("formaction").value = action;
					form.submit();
				}
				else
				{
					// @TODO localization
					alert("The quiz must have a title");
					document.getElementById("title").focus();
				}
			}
			// ]]></script>
	';
}

function QuestionScript()
{
	global $context;

	// Add javascript for multiple checkbox selection
	// TODO: Make this dependant on what we are showing
	$context['html_headers'] .= '<script>
			function checkAll(selectedForm, checked)
			{
				for (var i = 0; i < selectedForm.elements.length; i++)
				{
					var e = selectedForm.elements[i];
					if (e.type==\'checkbox\') {
						e.checked = checked;
					}
				}
			}
			function changeQuestionType(selectedForm)
			{
				switch (selectedForm.options[selectedForm.options.selectedIndex].value)
				{
					case \'1\' : // Multiple Choice
						document.getElementById("freeTextAnswerdiv").style.display = \'none\';
						document.getElementById("multipleChoiceAnswer").style.display = \'block\';
						document.getElementById("trueFalseAnswer").style.display = \'none\';
						break;
					case \'2\' : // Free Text
						document.getElementById("freeTextAnswerdiv").style.display = \'block\';
						document.getElementById("multipleChoiceAnswer").style.display = \'none\';
						document.getElementById("trueFalseAnswer").style.display = \'none\';
						break;
					case \'3\' : // True/False
						document.getElementById("freeTextAnswerdiv").style.display = \'none\';
						document.getElementById("multipleChoiceAnswer").style.display = \'none\';
						document.getElementById("trueFalseAnswer").style.display = \'block\';
						break;
				}
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
				var rowCount = document.getElementById("answerTable").rows.length - 1;

				if (rowCount > 1)
					document.getElementById("answerTable").deleteRow(rowCount);
			}

			function validateQuestion(form, action)
			{
				var isValid = true;

				if (document.getElementById("question_text").value.length == 0)
				{
					// @TODO localization
					alert("The question must have a title");
					document.getElementById("question_text").focus();
					isValid = false;
				}

				if (isValid == true)
				{
					switch (document.getElementById("id_question_type").value)
					{
						case "1" : // Multiple choice
							// No validation for the moment
							break;

						case "2" : // Free text
							if (document.getElementById("freeTextAnswer").value.length == 0)
							{
								// @TODO localization
								alert("The free text answer cannot be empty");
								document.getElementById("freeTextAnswer").focus();
								isValid = false;
							}
							break;

						case "3" : // True fase
							// No need to do anything here
							break;
					}
				}

				if (isValid == true)
				{
					document.getElementById("formaction").value = action;
					form.submit();
				}
			}
			</script>';
}

function SaveQuestionData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$questionText = isset($_POST['question_text']) ? ReplaceCurlyQuotes($_POST['question_text']) : '';
	$questionTypeId = isset($_POST['id_question_type']) ? $_POST['id_question_type'] : '';
	$imageUrl = isset($_POST['image']) ? $_POST['image'] : '';
	$answerText = isset($_POST['answer_text']) ? ReplaceCurlyQuotes($_POST['answer_text']) : '';

	// Save the Question
	$questionId = SaveQuestion($questionText, $questionTypeId, $context['id_quiz'], $imageUrl, $answerText);

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

	// @TODO check input
	if ($_POST['formaction'] == 'saveQuestion')
		GetQuestionsData();
	else
		GetNewQuestionData();
}

function SaveQuizData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$description = isset($_POST['description']) ? $_POST['description'] : '';
	$limit = isset($_POST['limit']) ? $_POST['limit'] : '';
	$seconds = isset($_POST['seconds']) ? $_POST['seconds'] : '';
	$showanswers = isset($_POST['showanswers']) && $_POST['showanswers'] == 'on' ? 1 : 0;
	$categoryId = isset($_POST['id_category']) ? $_POST['id_category'] : '';
	$image = isset($_POST['image']) && $_POST['image'] != '-' ? $_POST['image'] : '';
	$userId = $context['user']['id'];

	// Save the data and return the identifier for this newly created quiz
	$newQuizId = SaveQuiz($title, $description, $limit, $seconds, $showanswers, $image, $categoryId, 0, $userId, 0);

	// If the user wants to add questions after saving the quiz we need to output the appropriate page which is dictated by these context values
	if ($_POST['formaction'] == 'saveQuizAndAddQuestions')
	{
		$context['id_quiz'] = $newQuizId;

		// We need to get the data required for new questions
		GetNewQuestionData();
	}
	else
	{
		// We need to get new quiz data, as that will be the next page shown
		GetUserQuizzesData();

		$context['current_subaction'] = 'userquizzes';
	}
}

function GetAddQuizData()
{
	QuizScript();

	AddShowImageScript();

	// The new quiz page also shows a list of categories, so we must get this data
	GetAllCategoryDetails();
}

function AddShowImageScript()
{
	global $context, $boardurl;

	$context['html_headers'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[
		function show_image(imgId, selectElement, imageFolder)
		{
			var imgElement = document.getElementById(imgId);
			var selectedValue = selectElement[selectElement.selectedIndex].text;
			var imageUrl = "' . $boardurl . '/Themes/default/images/quiz_images/blank.gif";
			if (selectedValue != "-")
				imageUrl = "' . $boardurl . '/Themes/default/images/quiz_images/" + imageFolder + "/" + selectedValue;

			imgElement.src = imageUrl;
		}
	// ]]></script>';
}

function GetUserQuizzesData()
{
	global $context, $sourcedir, $txt;

	isAllowedTo('quiz_submit');

	QuizScript();

	if (isset($_GET['id_user']))
		$userId = $_GET['id_user'];
	else
		$userId = $context['user']['id'];

	// @TODO check input
	if (isset($_GET['review']))
	{
		$usersPrefs = Quiz\Helper::quiz_usersAcknowledge('quiz_pm_alert');
		$quizAdmins = array_filter(array_merge(Quiz\Helper::quiz_usersAllowedTo('quiz_admin'), $usersPrefs));
		if (!empty($quizAdmins)) {
			SetQuizForReview($_GET['review']);

			include_once($sourcedir . '/Subs-Post.php');

			$pmto = array(
				'to' => $quizAdmins,
				'bcc' => array()
			);

			$subject = $txt['SMFQuiz_UserQuizzes_Page']['UserQuizSubmittedForReview'];
			$message = $txt['SMFQuiz_UserQuizzes_Page']['QuizSubmittedForReview'];

			$pmfrom = array(
				'id' => $userId,
				'name' => 'Quiz',
				'username' => 'Quiz'
			);

			// Send message
			sendpm($pmto, $subject, Quiz\Helper::quiz_pmFilter($message), 0, $pmfrom);
		}
	}

	GetUserQuizzes($userId);
	$context['current_subaction'] = 'userquizzes';
}

function GetQuizLeaguesData()
{
	global $context;

	// If the ID has been set then the user has selected a specific league
	if (isset($_GET['id']))
	{
		// Check whether user can play this league - they might have already played it. This will populate a context param
		CanUserPlayQuizLeagueData($_GET['id'], $context['user']['id']);

		GetQuizLeagueDetails($_GET['id']);

		foreach($context['SMFQuiz']['quizLeague'] as $quizLeagueRow)
			GetQuizLeagueTable($_GET['id'], $quizLeagueRow['current_round'] - 1);

		GetQuizLeagueResults($_GET['id']);

	// Otherwise just show the quiz league listing
	}
	else
		GetUserQuizLeagueDetails($context['user']['id']);
}

function GetUserDetailsData()
{
	global $context, $memberContext, $user_info;

	// Guests can't see details...
	if ($user_info['is_guest'])
		redirectexit('action=SMFQuiz');

	// @TODO isAllowed?
	if (isset($_GET['id_user']))
		$userId = $_GET['id_user'];
	else
		$userId = $context['user']['id'];

	// Get member statistics
	GetMemberStatistics($userId);

	// Get member wins
	GetTotalUserWins($userId);

	// Get latest scores by this user
	GetUserQuizScores($userId);

	// Get correct scores by this user
	GetUserCorrectScores($userId);

	// Get category plays
	GetUserCategoryPlays($userId);

	// Let's have some information about this member ready, too.
	$memberResult = loadMemberData((int) $userId, false, 'profile');
	loadMemberContext($userId);
	$context['member'] = $memberContext[$userId];

	$context['id_user'] = $userId;
}

function GetHomePageData()
{
	global $context, $modSettings;

	$context['html_headers'] .= '
		<script>
		var search_wait = false;
		var search_url = smf_scripturl + "?action=SMFQuiz;sa=search;xml";
		var search_divQ = "quick_div";
		function quizSearchLoader() {
			var quizSearchTrigger = document.getElementById("quick_name");
			sessionStorage.setItem("quizQuickNameVal", quizSearchTrigger.value.trim());
			if (quizSearchTrigger) {
				quizSearchTrigger.onkeypress = function(){
					QuizQuickSearch();
					setTimeout(function(){
						var quick_name = document.getElementById("quick_name").value.trim();
						if (sessionStorage.getItem("quizQuickNameVal") != quick_name)
							QuizQuickSearch();
					}, 2000);
				};
			}
			setInterval(function(){
				var quick_name = document.getElementById("quick_name").value.trim();
				if (quick_name == "")
					document.getElementById(search_divQ).innerHTML = "";
			}, 5000);
		}
		function QuizQuickSearch()
		{
			if (search_wait) // Wait before new search.
			{
				setTimeout(function(){QuizQuickSearch();}, 800);
				return 1;
			}

			search_wait = true;
			setInterval(function(){resetWait();}, 800);

			var i, x = new Array();
			var n = document.getElementById("quick_name").value.trim();
			x[0] = "name=" + escape(textToEntities(n.replace(/&#/g, "&#38;#"))).replace(/\+/g, "%2B");
			sendXMLDocument(search_url, x.join("&"), onQuizSearch);
			ajax_indicator(true);
		}

		function textToEntities(text)
		{
			var entities = "";
			for (var i = 0; i < text.length; i++)
			{
				if (text.charCodeAt(i) > 127)
					entities += "&#" + text.charCodeAt(i) + ";";
				else
					entities += text.charAt(i);
			}

			return entities;
		}
		function decodeQuizHTML(html) {
			var txt = document.createElement("textarea");
			txt.innerHTML = html;
			return txt.value;
		}
		function resetWait() {

		}
		function onQuizSearch(XMLDoc)
		{
			if (!XMLDoc)
				document.getElementById(search_divQ).textContent = "Error";
			else {
				search_wait = false;
				var quizzes = XMLDoc.getElementsByTagName("quiz");
				var addNewNode = [], addNewLink = [], addNewText = [],searchDiv = document.createElement("DIV"), searchMainDiv = document.getElementById(search_divQ), i=0;
				for (i = 0; i < quizzes.length; i++) {
					addNewNode[i] = document.createElement("div");
					addNewLink[i] = document.createElement("a");
					addNewLink[i].href = quizzes[i].getElementsByTagName("url")[0].firstChild.nodeValue;
					addNewText[i] = document.createTextNode(decodeQuizHTML(quizzes[i].getElementsByTagName("name")[0].firstChild.nodeValue));
					addNewLink[i].appendChild(addNewText[i]);
					addNewNode[i].appendChild(addNewLink[i]);
					searchDiv.appendChild(addNewNode[i]);
				}
				searchMainDiv.innerHTML = searchDiv.innerHTML;
			}
			ajax_indicator(false);
		}
		if (window.addEventListener) {
			window.addEventListener("load", quizSearchLoader, false);
		}
		else {
			window.attachEvent("onload", quizSearchLoader);
		}
	</script>';

	// Get any outstanding sessions
	GetQuizSessions($context['user']['id']);

	// Need to get the latest quizzes
	GetLatestQuizzes();

	// Need to get the most popular quizzes
	GetPopularQuizzes(8);

	// Need this for calculations
	GetTotalQuizzes();

	// Need to get the most popular quizzes
	GetQuizMasters(8);

	// Need to get the quiz league leaders
	GetQuizLeagueLeaders(8);

	// Get some random quizzes
	GetRandomQuizzes(5, $context['user']['id']);

	// Finally we need to get the Infoboard data
	GetLatestInfoBoard($modSettings['SMFQuiz_InfoBoardItemsToDisplay']);
}

function GetCategoriesData()
{
	global $context, $txt;

	if ($context['id_quiz'] == 0)
	{
		$categoryId = isset($_GET['categoryId']) ? $_GET['categoryId'] : 0;

		// Get all categories in this category
		GetParentCategoryDetails($categoryId);

		// Get the details for the selected category
		if ($categoryId != 0)
		{
			GetCategory($categoryId);
		}
		else
		{
			// Otherwise this is the top level category, so populate with default data
			// TODO - Get this out of modsettings
			$row = array();
			$row['name'] = $txt['SMFQuiz_Categories_Page']['TopLevel'];
			$row['description'] = $txt['SMFQuiz_Categories_Page']['ThisIsTheTopLevelCategory'];
			$context['SMFQuiz']['category'][] = $row;
		}

		// Get any quizzes that exist in this category
		GetQuizzesInCategoryData($categoryId, $context['user']['id']);
	// Otherwise we are showing the quiz detail page
	}
	else
	{
		GetQuiz($context['id_quiz']);
		GetQuizResults($context['id_quiz']);
		GetQuizCorrect($context['id_quiz']);
	}
}

function GetStatisticsData()
{
	// @TODO Performance?
	// Could probably do this a little more efficiently, but for the meantime this will do

	// Get total quizzes
	GetTotalQuizStats();

	// Need this for calculations
	GetTotalQuizzes();

	// Get total questions
	GetTotalQuestions();

	// Get total answers
	GetTotalAnswers();

	// Get total categories
	GetTotalCategories();

	// Get quiz masters
	GetQuizMasters(10);

	// Need to get the most popular quizzes
	GetPopularQuizzes(10);

	// Get the best quiz result
	GetBestQuizResult();

	// Get the worst quiz result
	GetWorstQuizResult();

	// Get the newest quiz
	GetNewestQuiz();

	// Get the oldest quiz
	GetOldestQuiz();

	// Get the most quiz wins
	MostQuizWins();

	// Get the hardest quizzes
	GetHardestQuizzes();

	// Get the easiest quizzes
	GetEasiestQuizzes();

	// Get the most active players
	GetMostActivePlayers();

	// Get the most quiz creators
	GetMostQuizCreators();
}

function GetNewQuestionData()
{
	global $context;

	QuestionScript();

	// The new question page provides a list of quizzes to select. Therefore we need to obtain a list of category data
	GetUserQuizzes($context['user']['id']);

	// The new question page provides a list of question types to select. Therefore we need to obtain a list of question type data
	GetAllQuestionTypes();

	// We need to set the SMFQuiz specific action here so the template knows what to do. This could be achieved through the FORM
	// variable, but tidier this way
	$context['SMFQuiz']['Action'] = 'NewQuestion';
	$context['current_subaction'] = 'questions';
}

function GetEditQuizData()
{
	global $context, $user_info;

	QuizScript();

	AddShowImageScript();

	GetQuiz($context['id_quiz']);

	// Only the quiz creator can edit the quiz
	if ($user_info['id'] != $context['SMFQuiz']['quiz'][0]['creator_id'] && !allowedTo('quiz_admin'))
		fatal_lang_error('no_access', false);

	// The edit quiz page also shows a list of categories, so we must get this data
	GetAllCategoryDetails();

	$context['current_subaction'] = 'editquiz';
}

function UpdateFreeTextAnswer()
{
	// Free text answer simply has the text entered as the answer, so we only need to insert this into the database marking it as correct
	$answerText = isset($_POST["freeTextAnswer"]) ? ReplaceCurlyQuotes($_POST["freeTextAnswer"]) : '';

	$answerId = isset($_POST["id_answer"]) ? $_POST["id_answer"] : '';

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
		// @TODO remove some nesting
		// If the form value is one of the answers
		if (substr($key, 0, 8) == 'id_answer')
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
		// @TODO remove some nesting
		// If the form value is one of the answers
		if (substr($key, 0, 6) == 'answer' && $key != 'answer_text')
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

	foreach($_POST as $key => $value)
	{
		// @TODO remove some nesting
		// If the form value is one of the answers
		if (substr($key, 0, 6) == 'answer' && $key != 'answer_text')
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

// @TODO
// Function to replace curly quotes with normal ones - might be a better way of doing this, but this
// will do for the moment
function ReplaceCurlyQuotes($stringToReplace)
{
	$replaceString = str_replace('�', '"', $stringToReplace);
	$replaceString = str_replace('�', '"', $replaceString);
	$replaceString = str_replace('�', '\'', $replaceString);
	return $replaceString;
}

function GetUpdateQuizData()
{
	global $context;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$title = isset($_POST["title"]) ? $_POST["title"] : '';
	$description = isset($_POST["description"]) ? $_POST["description"] : '';
	$limit = isset($_POST["limit"]) ? $_POST["limit"] : '';
	$seconds = isset($_POST["seconds"]) ? $_POST["seconds"] : '';
	$showanswers = isset($_POST["showanswers"]) ? $_POST["showanswers"] : '';
	$image = isset($_POST["image"]) ? $_POST["image"] : '';
	$categoryId = isset($_POST["id_category"]) ? $_POST["id_category"] : '';
	$oldCategoryId = isset($_POST["oldCategoryId"]) ? $_POST["oldCategoryId"] : ''; // Need the old category, as if it is different we need to change quiz counts

	if ($showanswers == 'on')
		$showanswers = 1;
	else
		$showanswers = 2;

	// Save the data and return the identifier for this newly created quiz
	UpdateQuiz($context['id_quiz'], $title, $description, $limit, $seconds, $showanswers, $image, $categoryId, $oldCategoryId, 0, 0);

	// If the user wants to add questions after saving the quiz we need to output the appropriate page which is dictated by these context values
	// We need to get the data required for new questions
	// @TODO check input
	if ($_POST["formaction"] == "updateQuizAndAddQuestions")
		GetNewQuestionData();
	else
	{
		GetUserQuizzesData();
		$context['current_subaction'] = 'userquizzes';
	}
}

function GetDeleteQuestionData()
{
	global $context;

	// Get the key ids for the questions to delete. This function returns a string containing a comma separated list of id's
	$deleteKeys = GetKeysFromPost('question');

	if (!empty($deleteKeys))
		DeleteQuestions($deleteKeys);

	GetQuestionsData();
}

// From the specified id key, loop through the form variables and extract the associated identifiers. Return a string containing these
// identifiers in a comma separated list
function GetKeysFromPost($id)
{
	$deleteKeys = '';

	// @TODO check input
	foreach($_POST as $key => $value)
		if (substr($key, 0, strlen($id)) == $id)
			$deleteKeys .= substr($key, strlen($id)) . ',';

	if (substr($deleteKeys, strlen($deleteKeys)-1) == ',')
		$deleteKeys = substr($deleteKeys, 0, strlen($deleteKeys)-1);

	return $deleteKeys;
}


function GetDeleteQuizData()
{
	global $context, $user_info;

	// Get the key ids for the questions to delete. This function returns a string containing a comma separated list of id's

	// Get the key ids for the quiz leagues to delete. This function returns a string containing a comma separated list of id's
	$deleteKeys = GetKeysFromPost('quiz');

	// Get quiz info
	GetQuiz($context['id_quiz']);

	// Check if the user is the owner of the quiz
	if ($user_info['id'] != $context['SMFQuiz']['quiz'][0]['creator_id'] && !allowedTo('quiz_admin'))
		fatal_lang_error('no_access', false);

	if (!empty($context['id_quiz']))
		DeleteQuizzes($context['id_quiz']);

	GetUserQuizzesData();
}


function GetUpdateQuestionData()
{
	global $context, $smcFunc, $db_prefix;

	// Retrieve the form values
	// TODO - Need some validation on front end
	$questionId = isset($_POST["questionId"]) ? $_POST["questionId"] : '';
	$questionText = isset($_POST['question_text']) ? ReplaceCurlyQuotes($_POST['question_text']) : '';
	$imageUrl = isset($_POST["image"]) ? $_POST["image"] : '';
	$answerText = isset($_POST["answer_text"]) ? ReplaceCurlyQuotes($_POST["answer_text"]) : '';
	$questionTypeId = isset($_POST['id_question_type']) ? $_POST['id_question_type'] : '';

	// Update the Question
	UpdateQuestion($questionId, $questionText, $imageUrl, $answerText);

	// Update the answer
	switch ($questionTypeId)
	{
		case '1' : // Multiple Choice
			// @TODO query
			$smcFunc['db_query']('', "
				DELETE FROM {$db_prefix}quiz_answer
				WHERE id_question = {$questionId}");
			AddMultipleChoiceAnswer($questionId);
			break;

		case '2' : // Free Text
			UpdateFreeTextAnswer($questionId);
			break;

		case '3' : // True/False
			UpdateTrueFalseAnswer($questionId);
			break;
	}

	// @TODO check input
	if ($_POST["formaction"] == "updateQuestion")
		// The next page will show all the questions, so get this data
		GetQuestionsData();
	else
		GetNewQuestionData();
		// @TODO why commented?
		//$context['SMFQuiz']['Action'] = 'NewQuestion';
}

// @TODO createList
function GetQuizScoresData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	$id_quiz = isset($_GET['id_quiz']) ? $_GET['id_quiz'] : '0';
	$sort = isset($_REQUEST['sort']) && !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : 'default';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];

	$_GET['start'] = (int) isset($_GET['start']) ? $_GET['start'] : 0;

	// Set up the columns...
	$context['columns'] = array(
		'user' => array(
			'label' => $txt['SMFQuiz_Common']['Member']
		),
		'date' => array(
			'label' => $txt['SMFQuiz_Common']['Date']
		),
		'questions' => array(
			'label' => $txt['SMFQuiz_Common']['Questions'],
			'width' => '20'
		),
		'correct' => array(
			'label' => $txt['SMFQuiz_Common']['Correct'],
			'width' => '20'
		),
		'incorrect' => array(
			'label' => $txt['SMFQuiz_Common']['Incorrect'],
			'width' => '20'
		),
		'timeouts' => array(
			'label' => $txt['SMFQuiz_Common']['Timeouts'],
			'width' => '20'
		),
		'seconds' => array(
			'label' => $txt['SMFQuiz_Common']['Seconds'],
			'width' => '20'
		),
	);

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=SMFQuiz;sa=quizscores;id_quiz=' . $id_quiz . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';

	// List out the different sorting methods...
	$sort_methods = array(
		'default' => array(
			'up' => 'correct DESC, total_seconds ASC, result_date ASC'
		),
		'user' => array(
			'down' => 'real_name DESC',
			'up' => 'real_name ASC'
		),
		'date' => array(
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
		'seconds' => array(
			'down' => 'total_seconds DESC',
			'up' => 'total_seconds ASC'
		)
	);

	$query_parameters = array(
		'sort' => isset($sort_methods[$sort]) ? $sort_methods[$sort][$context['sort_direction']] : $sort_methods['default']['up'],
		'limit' => $limit,
		'id_quiz' => $id_quiz,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
	);

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM  {db_prefix}quiz_result
		WHERE id_quiz = {int:id_quiz}',
		$query_parameters
	);
	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=SMFQuiz;sa=quizscores;id_quiz=' . $id_quiz . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	$result = $smcFunc['db_query']('', '
		SELECT
			QR.id_user,
			M.real_name,
			Q.title,
			QR.result_date,
			QR.questions,
			QR.correct,
			QR.incorrect,
			QR.timeouts,
			QR.total_seconds,
			QR.auto_completed,
			QR.player_limit
		FROM {db_prefix}quiz Q
		INNER JOIN {db_prefix}quiz_result QR
			ON Q.id_quiz = QR.id_quiz
		INNER JOIN {db_prefix}members M
			ON QR.id_user = M.id_member
		WHERE QR.id_quiz = {int:id_quiz}
		ORDER BY {raw:sort}
		LIMIT {int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['quiz_results'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['SMFQuiz']['quiz_results'][] = $row;
		$context['SMFQuiz']['quiz_title'] = $row['title'];
	}


	$smcFunc['db_free_result']($result);
	$context['SMFQuiz']['Action'] = 'quiz_results';
}

// @TODO createList?
function GetUnplayedQuizzesData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	if (isset($_GET['id_user']))
		$userId = $_GET['id_user'];
	else
		$userId = $context['user']['id'];

	$starts_with = isset($_GET['starts_with']) && !empty($_GET['starts_with']) ? $_GET['starts_with'] : '';
	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'title';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];

	// Set up the columns...
	$context['columns'] = array(
		'' => array(
			'label' => '',
			'width' => '2'
		),
		'title' => array(
			'label' => $txt['SMFQuiz_Common']['Title']
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
		'auto_compleyed' => array(
			'label' => '',
			'width' => '1'
		)
	);

	// Set the filter links
	$context['letter_links'] = '<a href="' . $scripturl . '?action=SMFQuiz;sa=unplayedQuizzes;id_user=' . $userId . '">*</a> ';
	for ($i = 97; $i < 123; $i++)
		$context['letter_links'] .= '<a href="' . $scripturl . '?action=SMFQuiz;sa=unplayedQuizzes;id_user=' . $userId . ';starts_with=' . chr($i) . '">' . strtoupper(chr($i)) . '</a> ';

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=SMFQuiz;sa=unplayedQuizzes;id_user=' . $userId . ';starts_with=' . $starts_with . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';

	// List out the different sorting methods...
	$sort_methods = array(
		'title' => array(
			'down' => 'title DESC',
			'up' => 'title ASC'
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
		)
	);

	$query_parameters = array(
		'sort' => isset($sort_methods[$sort][$context['sort_direction']]) ? $sort_methods[$sort][$context['sort_direction']] : 'Q.title ASC',
		'starts_with' => $starts_with . '%',
		'limit' => $limit,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
		'id_user' => $userId
	);

	$request = $smcFunc['db_query']('','
		SELECT QR.id_quiz_result
		FROM {db_prefix}quiz Q
		LEFT JOIN	{db_prefix}quiz_category QC
			ON Q.id_category = QC.id_category
		INNER JOIN {db_prefix}quiz_question U
			ON Q.id_quiz = U.id_quiz
		INNER JOIN {db_prefix}members M
			ON Q.creator_id = M.id_member
		LEFT JOIN {db_prefix}quiz_result QR
			ON Q.id_quiz = QR.id_quiz
			AND QR.id_user = {int:id_user}
		WHERE Q.enabled = 1
			AND id_quiz_result IS NULL
		GROUP BY Q.id_quiz,QR.id_quiz_result',
		$query_parameters
	);
	$context['num_quizzes'] = $smcFunc['db_num_rows']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=SMFQuiz;id_user=' . $userId . ';sa=unplayedQuizzes;starts_with=' . $starts_with . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	// Left join on category as may be top level
	$result = $smcFunc['db_query']('', '
		SELECT
			Q.id_quiz,
			Q.title,
			Q.image,
			Q.creator_id,
			M.real_name,
			Q.description,
			Q.play_limit,
			Q.seconds_per_question,
			Q.show_answers,
			Q.enabled,
			QC.id_category,
			(CASE WHEN Q.id_category = 0 THEN \'Top Level\' ELSE QC.name END) AS category_name,
			COUNT(U.id_quiz) AS questions_per_session
		FROM {db_prefix}quiz Q
		LEFT JOIN {db_prefix}quiz_category QC
			ON Q.id_category = QC.id_category
		INNER JOIN {db_prefix}quiz_question U
			ON Q.id_quiz = U.id_quiz
		INNER JOIN {db_prefix}members M
			ON Q.creator_id = M.id_member
		LEFT JOIN {db_prefix}quiz_result QR
			ON Q.id_quiz = QR.id_quiz
			AND QR.id_user = {int:id_user}
		WHERE Q.enabled = 1' . (!empty($starts_with) ? '
			AND Q.title LIKE {string:starts_with}' : '') . '
		GROUP BY Q.id_quiz, QR.id_quiz_result,
		  Q.title,
			Q.image,
			Q.creator_id,
			M.real_name,
			Q.description,
			Q.play_limit,
			Q.seconds_per_question,
			Q.show_answers,
			Q.enabled,
			QC.id_category,
			Q.id_category,
			QC.name,
			U.id_quiz
			HAVING COUNT(QR.id_quiz_result) = 0
		ORDER BY {raw:sort}
		LIMIT {int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['quizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizzes'][] = $row;

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'quizzes';
}

// @TODO createList?
function GetPlayedQuizzesData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	// @TODO allowedTo?
	if (isset($_GET['id_user']))
		$userId = $_GET['id_user'];
	else
		$userId = $context['user']['id'];

	$starts_with = isset($_GET['starts_with']) && !empty($_GET['starts_with']) ? $_GET['starts_with'] : '';
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
		'title' => array(
			'label' => $txt['SMFQuiz_Common']['Quiz'],
		),
		'questions' => array(
			'label' => $txt['SMFQuiz_Common']['Qs']
		),
		'correct' => array(
			'label' => $txt['SMFQuiz_Common']['Crct'],
		),
		'incorrect' => array(
			'label' => $txt['SMFQuiz_Common']['Incrt'],
		),
		'timeouts' => array(
			'label' => $txt['SMFQuiz_Common']['Touts'],
		),
		'seconds' => array(
			'label' => $txt['SMFQuiz_Common']['Secs'],
		),
		'percentage_correct' => array(
			'label' => '% ' . $txt['SMFQuiz_Common']['Correct'],
		),
		'auto_compleyed' => array(
			'label' => '',
			'width' => '1'
		)
	);

	// Set the filter links
	$context['letter_links'] = '<a href="' . $scripturl . '?action=SMFQuiz;sa=playedQuizzes;id_user=' . $userId . '">*</a> ';
	for ($i = 97; $i < 123; $i++)
		$context['letter_links'] .= '<a href="' . $scripturl . '?action=SMFQuiz;sa=playedQuizzes;id_user=' . $userId . ';starts_with=' . chr($i) . '">' . strtoupper(chr($i)) . '</a> ';

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=SMFQuiz;sa=playedQuizzes;id_user=' . $userId . ';starts_with=' . $starts_with . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	// List out the different sorting methods...
	$sort_methods = array(
		'result_date' => array(
			'down' => 'result_date DESC',
			'up' => 'result_date ASC'
		),
		'title' => array(
			'down' => 'title DESC',
			'up' => 'title ASC'
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
		'seconds' => array(
			'down' => 'total_seconds DESC',
			'up' => 'total_seconds ASC'
		),
		'percentage_correct' => array(
			'down' => 'percentage_correct DESC',
			'up' => 'percentage_correct ASC'
		)
	);

	$query_parameters = array(
		'sort' => isset($sort_methods[$sort][$context['sort_direction']]) ? $sort_methods[$sort][$context['sort_direction']] : 'Q.title ASC',
		'starts_with' => $starts_with . '%',
		'limit' => $limit,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
		'id_user' => $userId
	);

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}quiz Q
		INNER JOIN {db_prefix}quiz_result QR
			ON Q.id_quiz = QR.id_quiz
		WHERE title LIKE {string:starts_with}
			AND Q.enabled = 1
			AND QR.id_user = {int:id_user}',
		$query_parameters
	);
	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=SMFQuiz;sa=playedQuizzes;id_user=' . $userId . ';starts_with=' . $starts_with . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	$result = $smcFunc['db_query']('', '
		SELECT
			Q.id_quiz,
			Q.title,
			Q.image,
			Q.description,
			QR.result_date,
			QR.questions,
			QR.correct,
			QR.incorrect,
			QR.timeouts,
			QR.total_seconds,
			QR.player_limit,
			IFNULL(round((QR.correct / QR.questions) * 100),0) AS percentage_correct,
			(CASE Q.top_user_id
			WHEN {int:id_user} THEN 1
			ELSE 0
			END) AS top_score,
			auto_completed
		FROM {db_prefix}quiz Q
		INNER JOIN {db_prefix}quiz_result QR
			ON Q.id_quiz = QR.id_quiz
		WHERE Q.enabled = 1
			AND QR.id_user = {int:id_user}' . (!empty($starts_with) ? '
			AND Q.title LIKE {string:starts_with}' : '') . '
		ORDER BY {raw:sort}
		LIMIT {int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['quizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizzes'][] = $row;

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'quizzes';
}

// @TODO createList?
function GetQuizzesInCategoryData($id_category, $id_user)
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'all';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];

	// Set up the columns...
	$context['columns'] = array(
		// @TODO '' => ???
		'' => array(
			'label' => '',
			'width' => '2'
		),
		'title' => array(
			'label' => $txt['SMFQuiz_Common']['Title']
		),
		'difficulty' => array(
			'label' => $txt['SMFQuiz_Common']['Difficulty']
		),
		'questions' => array(
			'label' => $txt['SMFQuiz_Common']['Questions']
		),
		'plays' => array(
			'label' => $txt['SMFQuiz_Common']['Plays']
		),
		'played' => array(
			'label' => $txt['SMFQuiz_Common']['Played']
		),
		'updated' => array(
			'label' => $txt['SMFQuiz_Common']['Updated']
		)
	);

	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'title';
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=SMFQuiz;sa=categories;categoryId=' . $id_category . ';type=' . $type . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;

	// List out the different sorting methods...
	$sort_methods = array(
		'title' => array(
			'down' => 'title DESC',
			'up' => 'title ASC'
		),
		'difficulty' => array(
			'down' => 'percentage DESC',
			'up' => 'percentage ASC'
		),
		'questions' => array(
			'down' => 'questions_per_session DESC',
			'up' => 'questions_per_session ASC'
		),
		'plays' => array(
			'down' => 'question_plays DESC',
			'up' => 'question_plays ASC'
		),
		'played' => array(
			'down' => 'played DESC',
			'up' => 'played ASC'
		),
		'updated' => array(
			'down' => 'updated DESC',
			'up' => 'updated ASC'
		)
	);

	$query_parameters = array(
		'sort' => isset($sort_methods[$sort][$context['sort_direction']]) ? $sort_methods[$sort][$context['sort_direction']] : 'Q.title ASC',
		'limit' => $limit,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
		'id_category' => $id_category,
		'id_user' => $id_user
	);

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}quiz Q
		WHERE Q.id_category = {int:id_category}
			AND Q.enabled = 1',
		$query_parameters
	);

	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=SMFQuiz;sa=categories;categoryId=' . $id_category . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	$result = $smcFunc['db_query']('', '
		SELECT
			Q.id_quiz,
			Q.title,
			Q.description,
			Q.image,
			Q.quiz_plays,
			Q.updated,
			Q.question_plays,
			Q.total_correct,
			round((Q.total_correct / Q.question_plays) * 100) AS percentage,
			COUNT(U.id_quiz) AS questions_per_session,
			IFNULL(QR.id_quiz_result,0) AS played
		FROM {db_prefix}quiz Q
		LEFT JOIN {db_prefix}quiz_question U
			ON Q.id_quiz = U.id_quiz
		LEFT JOIN {db_prefix}quiz_result QR
			ON Q.id_quiz = QR.id_quiz
			AND QR.id_user = {int:id_user}
		WHERE Q.id_category = {int:id_category}
			AND Q.enabled = 1
		GROUP BY
			Q.id_quiz,
			Q.title,
			Q.description,
			Q.quiz_plays,
			Q.updated,
			U.id_quiz,
			QR.id_quiz_result,
			Q.image,
			Q.question_plays,
			Q.total_correct,
			percentage
		ORDER BY {raw:sort}
		LIMIT {int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['quizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizzes'][] = $row;

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'quizzes';
}

function GetUsersActiveData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	list($mostPlayedUsers, $context['SMFQuiz']['mostActivePlayers'], $context['SMFQuiz']['total_user_wins'], $context['num_userdata']) = [[], [], [], 0];
	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}quiz_result QR
		INNER JOIN {db_prefix}members M
			ON QR.id_user = M.id_member
		WHERE QR.total_seconds > 0',
		[]
	);
	list ($context['num_userdata']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$starts_with = isset($_GET['starts_with']) && !empty($_GET['starts_with']) ? $_GET['starts_with'] : '';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';
	$sort = 'total_played';
	$context['sort_by'] = $sort;
	$sort_methods = [
		'total_played' => [
			'down' => 'total_played, percentage_correct DESC',
			'up' => 'total_played, percentage_correct ASC'
		]
	];
	$context['quiz_sort_href'] = $scripturl . '?action=SMFQuiz;sa=usersmostactive;sort=' . $sort . ';start=0' . (!isset($_REQUEST['desc']) ? ';desc' : '');
		
	$query_parameters = array(
		'sort' => isset($sort_methods[$sort][$context['sort_direction']]) ? $sort_methods[$sort][$context['sort_direction']] : 'total_played, percentage_correct ASC',
		'starts_with' => $starts_with . '%',
		'limit' => $limit,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
	);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=SMFQuiz;sa=usersmostactive;starts_with=' . $starts_with . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_userdata'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_userdata']);


	$result = $smcFunc['db_query']('', '
		SELECT  SUM(QR.questions) AS total_questions,
				SUM(QR.correct) AS total_correct,
				SUM(QR.incorrect) AS total_incorrect,
				SUM(QR.timeouts) AS total_timeouts,
				SUM(QR.total_seconds) AS total_seconds,
				COUNT(*) AS total_played,
				round((SUM(QR.correct) / SUM(QR.questions)) * 100) AS percentage_correct,
				COUNT(QR.id_user) as total_plays,
				QR.id_user,
				M.real_name
		FROM 		{db_prefix}quiz_result QR
		INNER JOIN 	{db_prefix}members M
		ON 			QR.id_user = M.id_member' . (!empty($starts_with) ? '
		WHERE M.real_name LIKE {string:starts_with}' : '') . '
		GROUP BY 	M.real_name,
					QR.id_user
		ORDER BY {raw:sort}
		LIMIT {int:start} , {int:limit}',
		$query_parameters
	);

	// Loop through the results and populate the context accordingly
	while ($row = $smcFunc['db_fetch_assoc']($result)) {
		$context['SMFQuiz']['mostActivePlayers'][] = $row;
		$mostPlayedUsers[] = $row['id_user'];
	}

	// Free the database
	$smcFunc['db_free_result']($result);
		$result = $smcFunc['db_query']('', '
		SELECT	COUNT(*) AS total_user_wins,
				Q.top_user_id
		FROM		{db_prefix}quiz Q
		WHERE		top_user_id IN ({array_int:id_users})
		GROUP BY Q.top_user_id ASC',
		[
			'id_users' => $mostPlayedUsers,
		]
	);

	// This should only be one value
	$context['SMFQuiz']['total_user_wins'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['total_user_wins'][$row['top_user_id']] = $row['total_user_wins'];

	// Free the database
	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'usersmostactive';
}

// @TODO createList
function GetQuizzesData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	$starts_with = isset($_GET['starts_with']) && !empty($_GET['starts_with']) ? $_GET['starts_with'] : '';
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'all';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];

	// Set up the columns...
	$context['columns'] = array(
		// @TODO '' => ???
		'' => array(
			'label' => '',
			'width' => '2'
		),
		'title' => array(
			'label' => $txt['SMFQuiz_Common']['Title']
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
	);

	switch ($type)
	{
		case 'unplayed':
			$context['columns']['quiz_plays'] = array(
				'label' => $txt['SMFQuiz_Common']['Plays'],
				'width' => '20'
			);
			$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'quiz_plays';
			$context['sort_direction'] = !isset($_REQUEST['asc']) ? 'up' : 'down';
			break;
		case 'all':
			$context['columns']['title'] = array(
				'label' => $txt['SMFQuiz_Common']['Title'],
				'width' => '20'
			);
			$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'title';
			$context['sort_direction'] = !isset($_REQUEST['asc']) ? 'up' : 'down';
			break;
		case 'new':
			$context['columns']['updated'] = array(
				'label' => $txt['SMFQuiz_Common']['Updated'],
				'width' => '20'
			);
			$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'updated';
			$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';
			break;
		case 'popular':
			$context['columns']['quiz_plays'] = array(
				'label' => $txt['SMFQuiz_Common']['Plays'],
				'width' => '20'
			);
			$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'quiz_plays';
			$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';
			break;
		case 'easiest':
			$context['columns']['percentage_correct'] = array(
				'label' => $txt['SMFQuiz_Common']['PercentageCorrect'],
				'width' => '20'
			);
			$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'percentage_correct';
			$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';
			break;
		case 'hardest':
			$context['columns']['percentage_correct'] = array(
				'label' => $txt['SMFQuiz_Common']['PercentageCorrect'],
				'width' => '20'
			);
			$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'percentage_correct';
			$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';
			break;
		case 'owner':
			$context['columns']['owner'] = array(
				'label' => $txt['SMFQuiz_Common']['Owner'],
				'width' => '20'
			);
			$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';
			$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'owner';
			break;
		default:
			$context['columns']['title'] = array(
				'label' => $txt['SMFQuiz_Common']['Title'],
				'width' => '20'
			);
			$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';
			$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'title';
			break;
	}

	// Set the filter links
	$context['letter_links'] = '<a href="' . $scripturl . '?action=SMFQuiz;sa=quizzes;type=' . $type . '">*</a> ';
	for ($i = 97; $i < 123; $i++)
		$context['letter_links'] .= '<a href="' . $scripturl . '?action=SMFQuiz;sa=quizzes;type=' . $type . ';starts_with=' . chr($i) . '">' . strtoupper(chr($i)) . '</a> ';

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=SMFQuiz;sa=quizzes;type=' . $type . ';starts_with=' . $starts_with . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;

	// List out the different sorting methods...
	$sort_methods = array(
		'title' => array(
			'down' => 'title DESC',
			'up' => 'title ASC'
		),
		'all' => array(
			'down' => 'title DESC',
			'up' => 'title ASC'
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
		'updated' => array(
			'down' => 'updated DESC',
			'up' => 'updated ASC'
		),
		'quiz_plays' => array(
			'down' => 'quiz_plays DESC',
			'up' => 'quiz_plays ASC'
		),
		'percentage_correct' => array(
			'down' => 'percentage_correct DESC',
			'up' => 'percentage_correct ASC'
		),
	);

	$query_parameters = array(
		'sort' => isset($sort_methods[$sort][$context['sort_direction']]) ? $sort_methods[$sort][$context['sort_direction']] : 'Q.title ASC',
		'starts_with' => $starts_with . '%',
		'limit' => $limit,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
	);

	$context['SMFQuiz']['quizzes'] = [];
	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}quiz Q
		INNER JOIN {db_prefix}members M
			ON Q.creator_id = M.id_member
		WHERE Q.title LIKE {string:starts_with}
			AND Q.enabled = 1',
		$query_parameters
	);
	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=SMFQuiz;type=' . $type . ';sa=quizzes;starts_with=' . $starts_with . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	$result = $smcFunc['db_query']('', '
		SELECT
			Q.id_quiz,
			Q.title,
			Q.image,
			Q.creator_id,
			M.real_name,
			Q.description,
			Q.play_limit,
			Q.seconds_per_question,
			Q.show_answers,
			Q.enabled,
			Q.updated,
			Q.quiz_plays,
			round(Q.total_correct / Q.question_plays * 100) AS percentage_correct,
			QC.id_category,
			(CASE WHEN Q.id_category = 0 THEN \'Top Level\' ELSE QC.name END) AS category_name,
			COUNT(U.id_quiz) AS questions_per_session
		FROM {db_prefix}quiz Q
		LEFT JOIN {db_prefix}quiz_category QC
			ON Q.id_category = QC.id_category
		INNER JOIN {db_prefix}quiz_question U
			ON Q.id_quiz = U.id_quiz
		INNER JOIN {db_prefix}members M
			ON Q.creator_id = M.id_member
		WHERE Q.enabled = 1' . (!empty($starts_with) ? '
			AND Q.title LIKE {string:starts_with}' : '') . '
		GROUP BY Q.id_quiz,
			Q.title,
			Q.image,
			Q.creator_id,
			M.real_name,
			Q.description,
			Q.total_correct,
			Q.question_plays,
			Q.play_limit,
			Q.seconds_per_question,
			Q.show_answers,
			Q.enabled,
			Q.updated,
			Q.quiz_plays,
			QC.id_category,
			Q.id_category,
			QC.name,
			U.id_quiz
		ORDER BY {raw:sort}
		LIMIT {int:start} , {int:limit}',
		$query_parameters
	);


	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizzes'][] = $row;

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'quizzes';
}

// @TODO createList
function GetQuizMastersData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'default';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];

	// Set up the columns...
	$context['columns'] = array(
		'user' => array(
			'label' => $txt['SMFQuiz_Common']['Member'],
			'width' => '2000'
		),
		'total_wins' => array(
			'label' => $txt['SMFQuiz_Common']['Wins'],
			'width' => '2'
		)
	);

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=SMFQuiz;sa=quizmasters;sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';

	// List out the different sorting methods...
	$sort_methods = array(
		'default' => array(
			'up' => 'total_wins DESC'
		),
		'user' => array(
			'down' => 'real_name DESC',
			'up' => 'real_name ASC'
		),
		'total_wins' => array(
			'down' => 'total_wins DESC',
			'up' => 'total_wins ASC'
		)
	);

	$query_parameters = array(
		'sort' => $sort_methods[$sort][$context['sort_direction']],
		'limit' => $limit,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
	);

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}quiz Q
		INNER JOIN {db_prefix}members M
			ON Q.top_user_id = M.id_member
		WHERE Q.top_user_id <> 0
		GROUP BY Q.top_user_id',
		$query_parameters
	);
	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=SMFQuiz;sa=quizmasters;sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	$result = $smcFunc['db_query']('', '
		SELECT
			Q.top_user_id AS id_user,
			M.real_name,
			COUNT(*) AS total_wins
		FROM {db_prefix}quiz Q
		INNER JOIN {db_prefix}members M
			ON Q.top_user_id = M.id_member
		WHERE Q.top_user_id <> 0
		GROUP BY Q.top_user_id, M.real_name
		ORDER BY {raw:sort}
		LIMIT {int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['quiz_masters'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quiz_masters'][] = $row;

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'quiz_masters';
}

// @TODO createList?
function GetQuizLeagueData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	$id_quiz_league = isset($_GET['id_quiz_league']) ? $_GET['id_quiz_league'] : '0';
	$current_round = isset($_GET['current_round']) ? $_GET['current_round'] : '0';
	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'default';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];

	// Set up the columns...
	$context['columns'] = array(
		'position' => array(
			'label' => $txt['SMFQuiz_Common']['Position'],
			'width' => '20'
		),
		'posmove' => array(
			'label' => '',
			'width' => '20'
		),
		'member' => array(
			'label' => $txt['SMFQuiz_Common']['Member'],
			'width' => '2000'
		),
		'plays' => array(
			'label' => $txt['SMFQuiz_Common']['Plays'],
			'width' => '20'
		),
		'correct' => array(
			'label' => $txt['SMFQuiz_Common']['Correct'],
			'width' => '20'
		),
		'incorrect' => array(
			'label' => $txt['SMFQuiz_Common']['Incorrect'],
			'width' => '20'
		),
		'timeouts' => array(
			'label' => $txt['SMFQuiz_Common']['Timeouts'],
			'width' => '20'
		),
		'seconds' => array(
			'label' => $txt['SMFQuiz_Common']['Seconds'],
			'width' => '20'
		),
		'points' => array(
			'label' => $txt['SMFQuiz_Common']['Points'],
			'width' => '20'
		)
	);

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=SMFQuiz;sa=quizleaguetable;current_round=' . $current_round . ';id_quiz_league=' . $id_quiz_league . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';

	// List out the different sorting methods...
	$sort_methods = array(
		'default' => array(
			'up' => 'QLT.current_position ASC'
		),
		'position' => array(
			'down' => 'current_position DESC',
			'up' => 'current_position ASC'
		),
		'member' => array(
			'down' => 'real_name DESC',
			'up' => 'real_name ASC'
		),
		'plays' => array(
			'down' => 'plays DESC',
			'up' => 'plays ASC'
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
		'seconds' => array(
			'down' => 'seconds DESC',
			'up' => 'seconds ASC'
		),
		'points' => array(
			'down' => 'points DESC',
			'up' => 'points ASC'
		)
	);

	$query_parameters = array(
		'sort' => $sort_methods[$sort][$context['sort_direction']],
		'limit' => $limit,
		'current_round' => $current_round - 1,
		'id_quiz_league' => $id_quiz_league,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
	);

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}quiz_league_table QLT
		INNER JOIN {db_prefix}members M
			ON QLT.id_user = M.id_member
		WHERE QLT.round = {int:current_round}
			AND QLT.id_quiz_league = {int:id_quiz_league}',
		$query_parameters
	);
	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=SMFQuiz;sa=quizleaguetable;current_round=' . $current_round . ';id_quiz_league=' . $id_quiz_league . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	// @TODO check input
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	$result = $smcFunc['db_query']('', '
		SELECT
			QLT.id_quiz_league_table,
			QLT.current_position,
			QLT.id_user,
			M.real_name,
			QLT.last_position,
			QLT.plays,
			QLT.correct,
			QLT.incorrect,
			QLT.timeouts,
			QLT.seconds,
			QLT.points,
			QL.title,
			QLT.last_position - QLT.current_position AS pos_move
		FROM {db_prefix}quiz_league_table QLT
		INNER JOIN {db_prefix}members M
			ON QLT.id_user = M.id_member
		INNER JOIN {db_prefix}quiz_league QL
			ON QLT.id_quiz_league = QL.id_quiz_league
		WHERE QLT.round = {int:current_round}
			AND QLT.id_quiz_league = {int:id_quiz_league}
		ORDER BY {raw:sort}
		LIMIT {int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['quiz_league_table'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['SMFQuiz']['quiz_league_table'][] = $row;
		$context['SMFQuiz']['quiz_league_title'] = $row['title'];
	}

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'quiz_league_table';
}

function GetPreviewQuizData()
{
	global $context, $smcFunc;

	GetQuiz($context['id_quiz']);

	// Get creator
	$creator = 0;
	foreach($context['SMFQuiz']['quiz'] as $row)
		$creator = $row['creator_id'];

	// We don't want to return a preview if the user requesting the preview is not the creator
	if ($creator == $context['user']['id'])
	{
		$result = $smcFunc['db_query']('', '
		SELECT
			QQ.id_question,
			QQ.question_text,
			QQ.answer_text AS question_answer_text,
			QA.id_answer,
			QA.answer_text,
			QA.is_correct
		FROM {db_prefix}quiz_question QQ
		LEFT JOIN {db_prefix}quiz_answer QA
			ON QQ.id_question = QA.id_question
		WHERE id_quiz = {int:id_quiz}
		ORDER BY QQ.id_question',
			array(
				'id_quiz' => $context['id_quiz']
			)
		);

		// Loop through leagues that are enabled
		while ($row = $smcFunc['db_fetch_assoc']($result))
			$context['SMFQuiz']['questions'][] = $row;
		$smcFunc['db_free_result']($result);
	}
	$context['current_subaction'] = 'preview';
}


function GetQuizLeagueResultsData()
{
	global $context, $scripturl, $smcFunc, $txt, $modSettings;

	$id_quiz_league = isset($_GET['id_quiz_league']) ? $_GET['id_quiz_league'] : '0';
	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'default';
	$limit = $modSettings['SMFQuiz_ListPageSizes'];

	// Set up the columns...
	$context['columns'] = array(
		'result_date' => array(
			'label' => $txt['SMFQuiz_Common']['ResultDate'],
			'width' => '50'
		),
		'round' => array(
			'label' => $txt['SMFQuiz_Common']['Round'],
			'width' => '20'
		),
		'member' => array(
			'label' => $txt['SMFQuiz_Common']['Member'],
			'width' => '20'
		),
		'correct' => array(
			'label' => $txt['SMFQuiz_Common']['Correct'],
			'width' => '20'
		),
		'incorrect' => array(
			'label' => $txt['SMFQuiz_Common']['Incorrect'],
			'width' => '20'
		),
		'timeouts' => array(
			'label' => $txt['SMFQuiz_Common']['Timeouts'],
			'width' => '20'
		),
		'seconds' => array(
			'label' => $txt['SMFQuiz_Common']['Seconds'],
			'width' => '20'
		),
		'points' => array(
			'label' => $txt['SMFQuiz_Common']['Points'],
			'width' => '20'
		)
	);

	// Sort out the column information.
	foreach ($context['columns'] as $col => $column_details)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=SMFQuiz;sa=quizleagueresults;id_quiz_league=' . $id_quiz_league . ';sort=' . $col . ';start=0';

		if ((!isset($_REQUEST['desc']) && $col == $sort) || ($col != $sort && !empty($column_details['default_sort_rev'])))
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '" rel="nofollow">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $sort == $col;
	}

	$context['sort_by'] = $sort;
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'up' : 'down';

	// List out the different sorting methods...
	$sort_methods = array(
		'default' => array(
			'up' => 'result_date DESC'
		),
		'result_date' => array(
			'down' => 'result_date DESC',
			'up' => 'result_date ASC'
		),
		'round' => array(
			'down' => 'round DESC',
			'up' => 'round ASC'
		),
		'member' => array(
			'down' => 'real_name DESC',
			'up' => 'real_name ASC'
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
		'seconds' => array(
			'down' => 'seconds DESC',
			'up' => 'seconds ASC'
		),
		'points' => array(
			'down' => 'points DESC',
			'up' => 'points ASC'
		)
	);

	$query_parameters = array(
		'sort' => $sort_methods[$sort][$context['sort_direction']],
		'limit' => $limit,
		'id_quiz_league' => $id_quiz_league,
		'start' => isset($_GET['start']) ? $_GET['start'] : 0,
	);

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}quiz_league_result QLR
		INNER JOIN {db_prefix}members M
			ON QLR.id_user = M.id_member
		WHERE QLR.id_quiz_league = {int:id_quiz_league}',
		$query_parameters
	);
	list ($context['num_quizzes']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=SMFQuiz;sa=quizleagueresults;id_quiz_league=' . $id_quiz_league . ';sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_quizzes'], $limit);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $limit, $context['num_quizzes']);

	$result = $smcFunc['db_query']('', '
		SELECT
			QLR.id_user,
			M.real_name,
			QLR.correct,
			QLR.incorrect,
			QLR.timeouts,
			QLR.points,
			QLR.result_date,
			QLR.round,
			QLR.seconds,
			QL.title
		FROM {db_prefix}quiz_league_result QLR
		INNER JOIN {db_prefix}members M
			ON QLR.id_user = M.id_member
		INNER JOIN {db_prefix}quiz_league QL
			ON QL.id_quiz_league = QLR.id_quiz_league
		WHERE QLR.id_quiz_league = {int:id_quiz_league}
		ORDER BY {raw:sort}
		LIMIT {int:start} , {int:limit}',
		$query_parameters
	);

	$context['SMFQuiz']['quiz_league_results'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['SMFQuiz']['quiz_league_results'][] = $row;
		$context['SMFQuiz']['quiz_league_title'] = $row['title'];
	}

	$smcFunc['db_free_result']($result);

	$context['SMFQuiz']['Action'] = 'quiz_league_results';
}

?>