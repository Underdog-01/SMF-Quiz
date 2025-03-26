<?php

function template_main()
{
	global $context, $scripturl, $modSettings, $txt;

	if (!empty($modSettings['SMFQuiz_enabled']))
	{
		echo '
			<form action="' . $scripturl . '?action=' . $context['current_action'] . ';sa=' . $context['current_subaction'] . '" method="post">
			<input type="hidden" name="formaction" id="formaction"/>
		';
		template_topTabs();

		switch ($context['current_subaction'])
		{
			case 'statistics':
				template_statistics();
				break;
			case 'categories':
				if (empty($_GET['id_quiz']))
					template_categories();
				else
					template_quiz_details();
				break;
			case 'quizleagues':
				if (isset($_GET['id']))
					template_quiz_league();
				else
					template_quiz_leagues();
				break;
			case 'userquizzes':
				template_user_quizzes();
				break;
			case 'userdetails':
				template_user_details();
				break;
			case 'addquiz':
				template_add_quiz();
				break;
			case 'questions' : // User is in question section
				template_questions();
				break;
			case 'editquiz':
				template_edit_quiz();
				break;
			case 'quizQuestions':
				template_show_questions();
				break;
			case 'editQuestion':
				template_edit_question();
				break;
			case 'quizscores':
				template_quiz_scores();
				break;
			case 'quizzes':
				template_show_quizzes();
				break;
			case 'quizmasters':
				template_quiz_masters();
				break;
			case 'quizleaguetable':
				template_quiz_league_table();
				break;
			case 'quizleagueresults':
				template_quiz_league_results();
				break;
			case 'unplayedQuizzes':
				template_show_quizzes();
				break;
			case 'playedQuizzes':
				template_played_quizzes();
				break;
			case 'preview':
				template_preview_quiz();
				break;
			default:
				template_home();
				break;
		}
	}
	else
	{
		echo '
					<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
						<tr class="titlebg">
							<td align="left" style="padding-right: 1ex;">' , $txt['SMFQuiz_Home_Page']['QuizDisabled'] , '</td>
						</tr>
					</table>
		';
	}

	echo '
		<table width="100%"><tr><td align="center"><a href="http://custom.simplemachines.org/mods/index.php?mod=1650" title="Free SMF Mods" target="_blank" class="smalltext">SMFQuiz ' , isset($modSettings["smf_quiz_version"]) ? $modSettings["smf_quiz_version"] : '' , ' &copy; 2009, SMFModding</a></td></tr></table>
		</form>'
	;
}

function template_quiz_play()
{
	global $settings, $boardurl, $modSettings, $txt, $context, $scripturl;

	$qv = !empty($modSettings['smf_quiz_version']) && (stripos($modSettings['smf_quiz_version'], '-beta') !== FALSE || stripos($modSettings['smf_quiz_version'], '-rc') !== FALSE) ? bin2hex(random_bytes(12/2)) : 'stable';
	$quizVarsJS = 'let smfQuizVersion = "' . $modSettings['smf_quiz_version'] . '";';
	foreach ((array_merge($txt['quizLocalizationTextJS'], $txt['quizLocalizationAlertsJS'])) as $key => $val) {
		$quizVarsJS .= '
			let ' . $key . ' = "' . $val . '";';
	}
	echo '
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/quiz/jquery-ui-1.14.1.css?v=' . $qv . '"/>
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/quiz/quiz.css?v=' . $qv . '"/>
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/quiz/lightbox.css?v=' . $qv . '" />
		<script src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery-3.7.0.min.js?v=' . $qv . '"></script>
		<script src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery-ui-1.14.1.min.js?v=' . $qv . '"></script>
		<script>
			' . ($quizVarsJS) . '
			var id_user = "' . $context['user']['id'] . '";
			var SMFQuiz_0to19 = "' . $modSettings['SMFQuiz_0to19'] . '";
			var SMFQuiz_20to39 = "' . $modSettings['SMFQuiz_20to39'] . '";
			var SMFQuiz_40to59 = "' . $modSettings['SMFQuiz_40to59'] . '";
			var SMFQuiz_60to79 = "' . $modSettings['SMFQuiz_60to79'] . '";
			var SMFQuiz_80to99 = "' . $modSettings['SMFQuiz_80to99'] . '";
			var SMFQuiz_99to100 = "' . $modSettings['SMFQuiz_99to100'] . '";
			var SessionTimeLimit =  "' . $modSettings['SMFQuiz_SessionTimeLimit'] . '";
			var quizImageFolder = "' . $settings['default_images_url'] . '/quiz_images/Quizzes/";
			var questionImageFolder = "' . $settings['default_images_url'] . '/quiz_images/Questions/";
			var quizImageRootFolder = "' . $settings['default_images_url'] . '/quiz_images/";
			var smf_scripturl = "' . $scripturl . '";
			var textLoggedIn = \'' . $txt['SMFQuiz_Javascript']['MustBeLoggedIn'] . '\';
			var textBrowserNotSupportHttp = \'' . $txt['SMFQuiz_Javascript']['BrowserNotSupportHttp'] . '\';
			var textNoLeagueOrQuizSpecified = \'' . $txt['SMFQuiz_Javascript']['NoQuizSpecified'] . '\';
			var textPlayedQuizOverMaximum = \'' . $txt['SMFQuiz_Javascript']['QuizMaximum'] . '\';
			var textPlayedQuizOverLimit = \'' . $txt['SMFQuiz_Javascript']['QuizLimit'] . '\';
			var textPlayedQuizTopScore = \'' . $txt['SMFQuiz_Javascript']['QuizHiScore'] . '\';
			var textPlayedQuizLeagueOverMaximum = \'' . $txt['SMFQuiz_Javascript']['QuizLeagueMaximum'] . '\';
			var textBrowserBroke = \'' . $txt['SMFQuiz_Javascript']['BrowserBroke'] . '\';
			var textProblemGettingQuestion = \'' . $txt['SMFQuiz_Javascript']['ProblemGettingQuestions'] . '\';
			var textTimeout = \'' . $txt['SMFQuiz_Javascript']['Timeout'] . '\';
			var textSessionTime = \'' . $txt['SMFQuiz_Javascript']['SessionTime'] . ' (' . $modSettings['SMFQuiz_SessionTimeLimit'] . ' ' . $txt['SMFQuiz_Common']['minutes'] . ')\';
			function quizPageTitle() {
				document.title = "' . $txt['SMFQuiz_Common']['Quiz'] . '";
			}
			$(document).ready(function(){
				quizPageTitle();
			});
		</script>
		<script src="' . $settings['default_theme_url'] . '/scripts/quiz/QuizClient.js?v=' . $qv . '"></script>
		<script src="' . $settings['default_theme_url'] . '/scripts/quiz/jquery.lightbox.js?v=' .$qv . '"></script>
	</head>';

	echo '
	<body>
		<div id="quizDiv">
			<div id="wrapperDiv">
				<div id="titleDiv">
					<table border="0" cellpadding="0" cellspacing="2">
						<tr>
							<td rowspan="2" valign="top">
								<img width="64" height="64" id="quizImage" src="' , $settings['default_theme_url'] , '/images/quiz_images/Quizzes/Default-64.png" alt="Default Image"\>
							</td>
							<td valign="top">
								<span id="quizTitleSpan" class="quizTitle"></span>
							</td>
							<td rowspan="2" class="quizDescription" align="center">&nbsp;</td>
						</tr>
						<tr>
							<td valign="top"><span id="quizDescriptionSpan" class="quizDescription"></span></td>
						</tr>
					</table>
				</div>
				<div id="questionDiv">
					<div id="questionSubDiv1" class="subDiv">
						<div style="padding:5px">
							<table style="height:150px; width:680px; -moz-border-radius: 5px; -webkit-border-radius: 5px; background-color:#000000; color:#ffffff">
								<tr>
									<td valign="top"><span id="firstQuestionSpan" class="question"></span></td>
								</tr>
								<tr>
									<td><div id="zoomImageWrapper" class="small"><a id="zoomImage" class="lightbox" title="' , $txt['SMFQuiz_Common']['ClickToZoom'] , '"><img id="zoomImageThumb" src="' , $settings['default_images_url'] , '/quiz_images/Preview-64.png" width="64" height="64" alt="' , $txt['SMFQuiz_Common']['ClickToZoom'] , '" /></a><br/>' , $txt['SMFQuiz_Common']['ClickToZoom'] , '</div></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div id="answerDiv">
					<div id="answerSubDiv1" class="subDiv">
						<div style="padding:5px">
							<table style="height:250px; width:680px; -moz-border-radius: 5px; -webkit-border-radius: 5px; background-color:#000000; color:#ffffff">
								<tr>
									<td valign="top" colspan="2">
										<span id="firstAnswerSpan" class="answer"></span>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div id="progressBarWrapperDiv">
					<table>
						<tr>
							<td><div id="progressBarDiv"></div></td>
							<td><div id="countDownDiv">&nbsp;</div></td>
						</tr>
					</table>
				</div>
				<div id="buttonWrapperDiv">
					<table cellpadding="1" cellspacing="0">
						<tr>
							<td>
								<button class="ui-state-default ui-corner-all" type="button" id="startQuizButton">' , $txt['SMFQuiz_Common']['StartQuiz'] , '</button>
								<button class="ui-state-default ui-corner-all" type="button" id="nextQuestionButton">' , $txt['SMFQuiz_Common']['NextQuestion'] , '</button>
								<button class="ui-state-default ui-corner-all" type="button" id="exitQuizButton">' , $txt['SMFQuiz_Common']['ExitQuiz'] , '</button>
								<button class="ui-state-default ui-corner-all" type="button" id="disputeButton">' , $txt['SMFQuiz_Common']['Dispute'] , '</button>
							</td>
						</tr>
					</table>
				</div>
				<div style="padding-right:20px">
					<div id="totalSeconds" class="pointTab" style="background:#99CCFF;">0 ' , $txt['SMFQuiz_Common']['seconds'] , '</div>
					<div style="float:right;">&nbsp;</div>
					<div id="totalTimeouts" class="pointTab" style="background:#FFCC66;">0 ' , $txt['SMFQuiz_Common']['Timeouts'] , '</div>
					<div style="float:right;">&nbsp;</div>
					<div id="totalIncorrect" class="pointTab" style="background:#FF3333;">0 ' , $txt['SMFQuiz_Common']['Incorrect'] , '</div>
					<div style="float:right;">&nbsp;</div>
					<div id="totalCorrect" class="pointTab" style="background:#99FF66;">0 ' , $txt['SMFQuiz_Common']['Correct'] , '</div>
					<div style="float:right;">&nbsp;</div>
					<div id="currentQuestion" class="pointTab" style="background:#CCFFFF;">' , $txt['SMFQuiz_Common']['Question'] , ' 1</div>
				</div>
				<div id="ajaxLoading" class="loading">
					<img src="' , $settings['default_theme_url'] , '/images/quiz_images/ajax-loading.gif" alt="' , $txt['SMFQuiz_Common']['Loading'] , '"/><br/>' , $txt['SMFQuiz_Common']['Loading'] , '
				</div>
			</div>
		</div>
		<div id="disputeDialog" title="' , $txt['SMFQuiz_Common']['DisputeQuizQuestion'] , '" style="display:none">
			<p>' , $txt['SMFQuiz_Common']['DisputeQuizQuestionDesc'] , '</p>
			<form id="disputeTextReason">
				<fieldset>
					<label for="disputeText">' , $txt['SMFQuiz_Common']['Reason'] , ':</label>
					<textarea cols="40" rows="5" wrap="hard" maxlength="200" id="disputeText" name="disputeText"></textarea>
				</fieldset>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			</form>
		</div>
		</body>
	</html>';
}

function template_add_quiz()
{
	global $context, $settings, $scripturl, $txt;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td align="left" colspan="2">' , $txt['SMFQuiz_AddQuiz_Page']['Title'] , '</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['Title'] , ':</b></td>
				<td align="left" width="100%"><input type="text" id="title" name="title" maxlength="400" size="50"/></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['Category'] , ':</b></td>
				<td align="left">' , template_category_dropdown(-1, 'id_category') , '</td>
			</td>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['Description'] , ':</b></td>
				<td align="left"><textarea name="description" cols="50" rows="5"></textarea></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageURL'] , ':</b></td>
				<td align="left">' , template_quiz_image_dropdown() , '</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['PlayLimit'] , ':</b></td>
				<td align="left"><input name="limit" type="text" size="5" maxlength="5" value="1"/></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left" nowrap="nowrap"><b>' , $txt['SMFQuiz_Common']['SecondsPerQuestion'] , ':</b></td>
				<td align="left"><input name="seconds" type="text" size="5" maxlength="5" value="20"/></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['ShowAnswers'] , ':</b></td>
				<td align="left"><input type="checkbox" name="showanswers" checked="checked"/></td>
			</tr>
			<tr class="windowbg">
				<td colspan="2" align="left">
					<input type="button" name="SaveQuiz" value="' , $txt['SMFQuiz_Common']['SaveQuiz'] , '" onclick="validateQuiz(this.form, \'saveQuiz\')"/>
					<input type="button" name="SaveQuizAndAddQuestions" value="' , $txt['SMFQuiz_Common']['SaveQuizAndAddQuestions'] , '" onclick="validateQuiz(this.form, \'saveQuizAndAddQuestions\')"/>
				</td>
			</tr>
		</table>';
}

			// @TODO createList?
function template_quiz_league()
{
	global $context, $settings, $scripturl, $txt, $smcFunc;

	foreach($context['SMFQuiz']['quizLeague'] as $quizLeagueRow)
	{
		echo '
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="bordercolor">
				<tr>
					<td colspan="6" class="titlebg">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td align="left">' , $txt['SMFQuiz_Common']['SMFQuiz'] , ' - ' , format_string($quizLeagueRow['title']) , '</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="catbg" colspan="6" align="left"><b>' , $txt['SMFQuiz_QuizLeague_Page']['QuizLeagueDetails'] , '</b></td>
				</tr>
				<tr>
					<td class="windowbg" valign="top" align="left"><b>' , $txt['SMFQuiz_Common']['Description'] , ':</b></td>
					<td class="windowbg" width="100%" align="left" colspan="5">' , format_string($quizLeagueRow['description']) , '</td>
				</tr>
				<tr>
					<td class="windowbg" align="left"><b>' , $txt['SMFQuiz_Common']['Interval'] , ':</b></td>
					<td class="windowbg nobr" align="left">' , $quizLeagueRow['day_interval'] , ' ' , $txt['SMFQuiz_Common']['Days'] , '</td>
					<td class="windowbg nobr" align="left"><b>' , $txt['SMFQuiz_Common']['QuestionsPerSession'] , ':</b></td>
					<td class="windowbg" align="left">' , $quizLeagueRow['questions_per_session'] , '</td>
					<td class="windowbg nobr" align="left"><b>' , $txt['SMFQuiz_Common']['SecondsPerQuestion'] , ':</b></td>
					<td class="windowbg" align="left" width="100%">' , $quizLeagueRow['seconds_per_question'] , '</td>
				</tr>
				<tr>
					<td class="windowbg nobr" align="left"><b>' , $txt['SMFQuiz_Common']['PointsForCorrectAnswer'] , ':</b></td>
					<td class="windowbg" align="left">' , $quizLeagueRow['points_for_correct'] , '</td>
					<td class="windowbg" align="left"><b>' , $txt['SMFQuiz_Common']['ShowAnswers'] , ':</b></td>
					<td class="windowbg" align="left">' , $quizLeagueRow['show_answers'] == 1 ? 'yes' : 'no' , '</td>
					<td class="windowbg" align="left"><b>' , $txt['SMFQuiz_Common']['CurrentRound'] , ':</b></td>
					<td class="windowbg" align="left">' , $quizLeagueRow['current_round'] , '</td>
				</tr>
				<tr>
					<td class="windowbg" align="left"><b>' , $txt['SMFQuiz_Common']['TotalRounds'] , ':</b></td>
					<td class="windowbg" align="left">' , $quizLeagueRow['total_rounds'] , '</td	>
					<td class="windowbg" align="left"><b>' , $txt['SMFQuiz_Common']['State'] , ':</b></td>
					<td class="windowbg" align="left">';

		// @TODO css
		if ($quizLeagueRow['state'] == 0)
			echo '<font color="red">' , $txt['SMFQuiz_Common']['Disabled'] , '</font>';
		elseif ($quizLeagueRow['state'] == 1)
			echo '<font color="green">' , $txt['SMFQuiz_Common']['Enabled'] , '</font>';
		elseif ($quizLeagueRow['state'] == 2)
			echo '<font color="blue">' , $txt['SMFQuiz_Common']['Completed'] , '</font>';

		$nextUpdate = strtotime("+" . $quizLeagueRow['day_interval'] . " day", $quizLeagueRow['updated']);
		echo '
					</td>
					<td class="windowbg" align="left"><b>' , $txt['SMFQuiz_Common']['NextUpdate'] , ':</b></td>
					<td class="windowbg" align="left">' , date("M d Y H:i", $nextUpdate) , '</td>
				</tr>
				<tr>
					<td colspan="6" class="windowbg" align="left">
		';
		if (isset($context['SMFQuiz']['CanPlayQuizLeague']))
			// @TODO css
			foreach($context['SMFQuiz']['CanPlayQuizLeague'] as $row)
				echo '<font color="red">' , $txt['SMFQuiz_QuizLeague_Page']['PlayedQuiz'] , ' ' , date("M d Y H:i", $row['result_date']) , ' ' , $txt['SMFQuiz_Common']['with'] , ' ' , $row['correct'] , ' ' , $txt['SMFQuiz_QuizLeague_Page']['QuestionsCorrectIn'] , ' ' , $row['seconds'] , ' ' , $txt['SMFQuiz_Common']['Seconds'] , '.';
		elseif ($quizLeagueRow['state'] == 1)
			echo '		' , $txt['SMFQuiz_QuizLeague_Page']['NotPlayedQuiz'] , ' <a href="#" onclick="window.open(\'' , $scripturl , '?action=SMFQuiz;sa=play;id_quiz_league=' , $quizLeagueRow['id_quiz_league'] , '\',\'playnew\',\'height=625,width=720,toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=yes\')"><img src="' , $settings['default_images_url'] , '/quiz_images/Play-24.png" alt="' , $txt['SMFQuiz_Common']['PlayQuizLeague'] , '" border="0" height="24" width="24"/></a>';

		echo '
					</td>
				</tr>
				<tr align="left">
					<td class="catbg" colspan="6"><b>' , $txt['SMFQuiz_QuizLeague_Page']['QuizLeagueTable'] , '</b></td>
				</tr>
				<tr>
					<td class="windowbg" colspan="6">
						<table border="0" cellspacing="1" cellpadding="4" align="center" class="bordercolor" width="100%">
							<tr class="titlebg" align="left">
								<td>' , $txt['SMFQuiz_Common']['Position'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Member'] , '</td>
								<td>&nbsp;</td>
								<td>' , $txt['SMFQuiz_Common']['Plays'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Correct'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Incorrect'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Timeouts'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Seconds'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Points'] , '</td>
							</tr>
		';
		foreach ($context['SMFQuiz']['quizTable'] as $quizTableRow)
		{
			echo '
							<tr align="left">
								<td class="windowbg">' , $quizTableRow['current_position'] , '</td>
								<td class="windowbg"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $quizTableRow['id_user'] , '">' , $quizTableRow['real_name'] , '</a></td>
								<td class="windowbg">';
			// @TODO css
			$movement = $quizTableRow['last_position'] - $quizTableRow['current_position'];
			if ($movement > 0)
				echo  '				<font color="green"> ' , $movement , '</font>';
			elseif ($movement < 0 && $quizTableRow['last_position'] > 0)
				echo  '				<font color="red"> ' , $movement , '</font>';
			else
				echo  '				-';

			echo ' 				</td>
								<td class="windowbg">' , $quizTableRow['plays'] , '</td>
								<td class="windowbg">' , $quizTableRow['correct'] , '</td>
								<td class="windowbg">' , $quizTableRow['incorrect'] , '</td>
								<td class="windowbg">' , $quizTableRow['timeouts'] , '</td>
								<td class="windowbg">' , $quizTableRow['seconds'] , '</td>
								<td class="windowbg">' , $quizTableRow['points'] , '</td>
							</tr>
			';
		}
		echo '
							<tr class="windowbg">
								<td colspan="9">[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizleaguetable;current_round=' , $quizLeagueRow['current_round'] , ';id_quiz_league=' , $quizLeagueRow['id_quiz_league'] , '">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
							</tr>
							<tr align="left">
								<td class="windowbg" colspan="9"><span class="smalltext"><i>' , $txt['SMFQuiz_QuizLeague_Page']['TableGeneratedOn'] , ' ' , date("M d Y H:i", $quizLeagueRow['updated'])  , '. ' , $txt['SMFQuiz_QuizLeague_Page']['DoesNotIncludeRoundResults'] , '.</i></span></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr align="left">
					<td class="catbg" colspan="6"><b>' , $txt['SMFQuiz_Common']['RecentQuizLeagueResults'] , '</b></td>
				</tr>
				<tr align="left">
					<td class="windowbg" colspan="6">
						<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="bordercolor">
							<tr class="titlebg">
								<td>' , $txt['SMFQuiz_Common']['ResultDate'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Round'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Member'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Correct'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Incorrect'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Timeouts'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Seconds'] , '</td>
								<td>' , $txt['SMFQuiz_Common']['Points'] , '</td>
							</tr>
		';
		foreach ($context['SMFQuiz']['quizLeagueResults'] as $quizLeagueResultsRow)
		{
			echo '
							<tr>
								<td class="windowbg">' , date("M d Y H:i", $quizLeagueResultsRow['result_date']) , '</td>
								<td class="windowbg">' , $quizLeagueResultsRow['round'] , '</td>
								<td class="windowbg"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $quizLeagueResultsRow['id_user'] , '">' , $quizLeagueResultsRow['real_name'] , '</a></td>
								<td class="windowbg">' , $quizLeagueResultsRow['correct'] , '</td>
								<td class="windowbg">' , $quizLeagueResultsRow['incorrect'] , '</td>
								<td class="windowbg">' , $quizLeagueResultsRow['timeouts'] , '</td>
								<td class="windowbg">' , $quizLeagueResultsRow['seconds'] , '</td>
								<td class="windowbg">' , $quizLeagueResultsRow['points'] , '</td>
							</tr>
			';
		}
		echo '
							<tr class="windowbg">
								<td colspan="9">[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizleagueresults;id_quiz_league=' , $quizLeagueRow['id_quiz_league'] , '">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		';
	}
}

			// @TODO createList?
function template_quiz_leagues()
{
	global $context, $scripturl, $txt, $smcFunc;

		echo '
			<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
				<div class="title_bar">
					<h4 class="titlebg">
						<span class="left"></span>
						' , format_string($txt['SMFQuiz_QuizLeagues_Page']['Title']) , '
					</h4>
				</div>
				<div class="blockcontent windowbg" style="margin-top:2px; ">
					<div style="padding:4px;">
						<div class="windowbg">
							<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
								<tr>
									<td colspan="12" class="titlebg">
										<table width="100%" cellpadding="0" cellspacing="0" border="0">
											<tr>
												<td align="left"></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr class="catbg3" align="left">
									<td>' , $txt['SMFQuiz_Common']['QuizLeagueName'] , '</td>
									<td>' , $txt['SMFQuiz_Common']['Status'] , '</td>
									<td>' , $txt['SMFQuiz_Common']['CurrentRound'] , '</td>
									<td>' , $txt['SMFQuiz_Common']['Leader'] , '</td>
									<td>' , $txt['SMFQuiz_Common']['YourPosition'] , '</td>
									<td>' , $txt['SMFQuiz_Common']['YourPoints'] , '</td>
									<td>' , $txt['SMFQuiz_Common']['NextUpdate'] , '</td>
								</tr>
	';
	foreach ($context['SMFQuiz']['quizLeagues'] as $quizLeaguesRow)
	{
		$nextUpdate = strtotime("+" . $quizLeaguesRow['day_interval'] . " day", $quizLeaguesRow['updated']);
		echo '
								<tr align="left">
									<td class="windowbg"><a href="' , $scripturl , '?action=' , $context['current_action'] , '&sa=quizleagues&id=' , $quizLeaguesRow['id_quiz_league'] , '">' , format_string($quizLeaguesRow['title']) , '</a></td>
									<td class="windowbg">' , $quizLeaguesRow['state'] == 1 ? '<font color="green">' . $txt['SMFQuiz_Common']['Inprogress'] . '</font>' : '<font color="blue">' . $txt['SMFQuiz_Common']['Complete'] . '</font>' , '</td>
									<td class="windowbg">' , $quizLeaguesRow['current_round'] , '</td>
									<td class="windowbg">
		';
		if ($quizLeaguesRow['id_leader'] == 0)
			echo '				' , $txt['SMFQuiz_Common']['NoLeader'] , '';
		else
			echo '						<a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $quizLeaguesRow['id_leader'] , '">' , $quizLeaguesRow['leader_name'] , '</a>';

		echo '
									</td>
									<td class="windowbg">' , $quizLeaguesRow['user_position'] , '</td>
									<td class="windowbg">' , $quizLeaguesRow['user_points'] , '</td>
									<td class="windowbg">' , date("M d Y H:i", $nextUpdate) , '</td>
								</tr>
		';
	}
	echo '
							</table>
						</div>
					</div>
				</div>
			</div>
	';
}

			// @TODO createList?
function template_user_quizzes()
{
	global $context, $settings, $scripturl, $txt;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td align="left">' , $txt['SMFQuiz_UserQuizzes_Page']['Title'] , '</td>
			</tr>
			<tr class="windowbg">
				<td align="left">' , $txt['SMFQuiz_UserQuizzes_Page']['Description'] , '</td>
			</tr>
			<tr class="catbg">
				<td align="left">' , $txt['SMFQuiz_UserQuizzes_Page']['YourQuizzes'] , '</td>
			</tr>
		</table>
		<div class="righttext">
			<a class="button" href="' , $scripturl . '?action=' , $context['current_action'] , ';sa=addquiz">
				<img src="' . $settings['default_images_url'] . '/quiz_images/add.png" alt="' , $txt['SMFQuiz_AddQuiz_Page']['Title'] , '" title="' , $txt['SMFQuiz_AddQuiz_Page']['Title'] , '" style="vertical-align: middle;margin-bottom: 3px;" /> ' , $txt['SMFQuiz_Common']['AddNewQuiz'] , '
			</a>
		</div>';

	if (!empty($context['SMFQuiz']['userQuizzes']))
	{
		echo '
		<table width="100%">
			<tr class="titlebg">
				<td align="left">' , format_string($txt['SMFQuiz_Common']['Title']) , '</td>
				<td align="left">' , $txt['SMFQuiz_Common']['Category'] , '</td>
				<td align="left">' , $txt['SMFQuiz_Common']['Questions'] , '</td>
				<td align="left">' , $txt['SMFQuiz_Common']['Updated'] , '</td>
				<td align="left">' , $txt['SMFQuiz_Common']['Enabled'] , '</td>
				<td align="left">' , $txt['SMFQuiz_Common']['ForReview'] , '</td>
				<td>&nbsp;</td>
			</tr>';

		$counter = 0;
		foreach($context['SMFQuiz']['userQuizzes'] as $userQuizzesRow)
		{
			echo '
			<tr class="windowbg" height="24">
				<td align="left">' , format_string($userQuizzesRow['title']) , '</td>
				<td align="left">' , format_string($userQuizzesRow['category_name']) , '</td>
				<td align="left">' , $userQuizzesRow['questions_per_session'] , '</td>
				<td align="left">' , date("M d Y H:i", $userQuizzesRow['updated']) , '</td>
				<td align="left">' , $userQuizzesRow['enabled'] != 0 ? '<img src="' . $settings['default_images_url'] . '/quiz_images/tick.png" alt="Yes" title="Yes" border="0" align="middle"/>' : '<img src="' . $settings['default_images_url'] . '/quiz_images/cross.png" alt="No" title="No" border="0" align="middle"/>' , '</td>
				<td align="left">' , $userQuizzesRow['for_review'] != 0 ? '<img src="' . $settings['default_images_url'] . '/quiz_images/tick.png" alt="Yes" title="Yes" border="0" align="middle"/>' : '<img src="' . $settings['default_images_url'] . '/quiz_images/cross.png" alt="No" title="No" border="0" align="middle"/>' , '</td>';

			// @TODO localization
			if ($userQuizzesRow['enabled'] == 0 && $userQuizzesRow['for_review'] == 0)
					echo '
				<td align="left">
					<a href="' , $scripturl . '?action=' , $context['current_action'] , ';sa=userquizzes;review=' , $userQuizzesRow['id_quiz'] , '"><img src="' . $settings['default_images_url'] . '/quiz_images/upload.png" alt="Submit" title="Submit Quiz for Review" border="0" align="middle"/></a>
					<a href="' , $scripturl . '?action=' , $context['current_action'] , ';sa=editQuiz;id_quiz=' , $userQuizzesRow['id_quiz'] , '"><img src="' . $settings['default_images_url'] . '/quiz_images/edit.png" alt="Edit" title="Edit Quiz" border="0" align="middle"/></a>
					<a href="' , $scripturl . '?action=' , $context['current_action'] , ';sa=quizQuestions;id_quiz=' , $userQuizzesRow['id_quiz'] , '"><img src="' . $settings['default_images_url'] . '/quiz_images/comments.png" alt="Questions" title="Quiz Questions" border="0" align="middle"/></a>
					<a href="' , $scripturl . '?action=' , $context['current_action'] , ';sa=preview;id_quiz=' , $userQuizzesRow['id_quiz'] , '"><img src="' . $settings['default_images_url'] . '/quiz_images/preview.png" alt="Preview" title="Preview Quiz" border="0" align="middle"/></a>
					<a href="' , $scripturl . '?action=' , $context['current_action'] , ';sa=deleteQuiz;id_quiz=' , $userQuizzesRow['id_quiz'] , '"><img src="' . $settings['default_images_url'] . '/quiz_images/delete.png" alt="Delete" title="Delete Quiz" border="0" align="middle"/></a>
				</td>';

			else
				echo '
				<td align="left">
					<a href="' , $scripturl . '?action=' , $context['current_action'] , ';sa=preview;id_quiz=' , $userQuizzesRow['id_quiz'] , '"><img src="' . $settings['default_images_url'] . '/quiz_images/preview.png" alt="Preview" title="Preview Quiz" border="0" align="middle"/></a>
				</td>';

			echo '
			</tr>';
			$counter++;
		}
			echo '
		</table>';
	}
}

function template_topTabs()
{
	global $context, $scripturl;

	if (isset($context['tab_links']))
	{
		echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td align="right" style="padding-right: 1ex;">
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td><div id="admin_menu"><ul class="dropmenu" id="dropdown_menu_1">
		';

		foreach ($context['tab_links'] as $link)
		{
			if (isset($link['show']) && empty($link['show']))
				continue;

			if ($link['action'] == $context['current_subaction'])
				echo '
								<li><a class="active firstlevel" href="' , $scripturl . '?action=' , $context['current_action'] , (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '"><span class="firstlevel">', $link['label'], '</span></a></li>
				';
			else
				echo '
								<li><a class="firstlevel" href="' , $scripturl . '?action=' , $context['current_action'] , (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '"><span class="firstlevel">', $link['label'], '</span></a></li>
				';
		}

		echo '
							</ul></div></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		';

	}
}

function template_home()
{
	global $context, $scripturl, $settings, $txt, $smcFunc, $modSettings;

	echo '
<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
	<div class="title_bar">
		<h4 class="titlebg">
			<span class="left"></span>
			' , $txt['SMFQuiz_Common']['QuizHome'] , '
		</h4>
	</div>
	<div style="margin-top:2px; ">
		<div style="padding:4px;">
			<div class="smalltext">
				<img src="' , $settings["default_images_url"] , '/quiz_images/Quizzes/quiz.png"/>
				<br/>' , $txt['SMFQuiz_Home_Page']['Welcome'];
	if (!empty($context['SMFQuiz']['quizSessions']))
	{
		echo '
				<br/><font color="red">' , $txt['SMFQuiz_Home_Page']['OutstandingQuizzes'] , ':</font>
		';
		if (isset($context['SMFQuiz']['quizSessions']))
			foreach($context['SMFQuiz']['quizSessions'] as $quizSessions)
				echo '
					<li>' , date("M d Y", $quizSessions['last_question_start']) , ' - <a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $quizSessions['id_quiz'] , '">' , format_string($quizSessions['title']) , '</a> -' , $txt['SMFQuiz_Common']['Question'] , ' ' , $quizSessions['question_count'] , '</li>
				';
	}
	echo '
				</div>
		</div>
	</div>
</div>


<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
	<div class="title_bar">
		<h4 class="titlebg">
			<span class="left"></span>
			' , $txt['SMFQuiz_Home_Page']['QuizSearch'] , '
		</h4>
	</div>
	<div class="blockcontent windowbg" style="margin-top:2px; ">
		<div style="padding:4px;">
			<div class="smalltext">
				<div class="tborder clearfix" id="latestQuizFrame">
					<div class="windowbg">
						<form action="', $scripturl, '?action=SMFQuiz" onsubmit="QuizQuickSearch(); return false;" method="post">
							<b>' , $txt['SMFQuiz_Common']['EnterQuizNametosearchfor'] , ':</b> <input id="quick_name" size="20" type="text" value="" name="name[', rand(0, 1000), ']" />
						<div id="quick_div" class="smalltext"></div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
	<div class="title_bar">
		<h4 class="titlebg">
			<span class="left"></span>
			' , $txt['SMFQuiz_Home_Page']['RandomUnplayedQuizzes'] , '
		</h4>
	</div>
	<div class="blockcontent windowbg" style="margin-top:2px; ">
		<div style="padding:4px;">
			<div class="smalltext">
				<table><tr>
';
	if (isset($context['SMFQuiz']['randomQuizzes']) && sizeof($context['SMFQuiz']['randomQuizzes']) > 0)
	{
		$counter = 1;
		foreach ($context['SMFQuiz']['randomQuizzes'] as $randomQuizRow)
		{
			echo '
				<td align="left" width="20%" valign="top" class="windowbg">
					<table>
						<tr>
							<td>
								<img width="64" height="64" src="' , !empty($randomQuizRow['image']) ? $settings["default_images_url"] . '/quiz_images/Quizzes/' . $randomQuizRow['image'] : $settings["default_images_url"] . '/quiz_images/Quizzes/Default-64.png' , '"/>
							</td>
							<td>
								<a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $randomQuizRow['id_quiz'] , '">' , format_string($randomQuizRow['title']) , '</a>
							</td>
						</tr>
					</table>


				</td>
			';
			$counter++;
		}
	}
	else
		echo '<td>' , $txt['SMFQuiz_Home_Page']['PlayedAllQuizzes'] , '</td>';

echo '
				</tr></table>
			</div>
		</div>
	</div>
</div>


<table border="0" cellpadding="1" cellspacing="1" width="100%">
	<tr>
		<td width="50%" valign="top">
			<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
				<div class="title_bar">
					<h4 class="titlebg">
						<span class="left"></span>
						' , $txt['SMFQuiz_Home_Page']['NewQuizzes'] , '
					</h4>
				</div>
				<div class="blockcontent windowbg" style="margin-top:2px; ">
					<div style="padding:4px;">
								<table border="0" width="100%" class="windowbg">
									<tr class="titlebg">
										<td>&nbsp;</td>
										<td>' , $txt['SMFQuiz_Common']['Quiz'] , '</td>
										<td>' , $txt['SMFQuiz_Common']['Updated'] , '</td>
									</tr>

	';
	$counter = 1;
	$newDate = strtotime("-2 day", time());
	if (isset($context['SMFQuiz']['latestQuizzes']))
		foreach($context['SMFQuiz']['latestQuizzes'] as $latestQuizRow)
		{
			echo '						<tr class="windowbg">
											<td width="8%"><img width="25" height="25" src="' , !empty($latestQuizRow['image']) ? $settings["default_images_url"] . '/quiz_images/Quizzes/' . $latestQuizRow['image'] : $settings["default_images_url"] . '/quiz_images/Quizzes/Default-64.png' , '"/></td>
											<td width="100%"><table border="0" cellpadding="0" cellspacing="0"><tr><td><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $latestQuizRow['id_quiz'] , '">' , format_string($latestQuizRow['title']) , '</a>
			';
			if ($latestQuizRow['updated'] > $newDate)
				echo '<td>&nbsp;<img src="' , $settings['default_images_url'] , '/quiz_images/new.gif"/></td>';

			echo '
											</td></tr></table></td>
											<td class="nobr">' , date("M d Y", $latestQuizRow['updated']) , '</td>
										</tr>
			';
			$counter++;
		}

	echo '
										<tr>
											<td colspan="3">[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizzes;type=new">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
										</td>
								</table>					</div>
				</div>
			</div>
		</td>
		<td width="50%" valign="top">
			<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
				<div class="title_bar">
					<h4 class="titlebg">
						<span class="left"></span>
						' , $txt['SMFQuiz_Home_Page']['QuizMasters'] , '
					</h4>
				</div>
				<div class="blockcontent windowbg" style="margin-top:2px; ">
					<div style="padding:4px;">
								<table border="0" width="100%" class="windowbg">
									<tr class="titlebg">
										<td>' , $txt['SMFQuiz_Common']['Member'] , '</td>
										<td>' , $txt['SMFQuiz_Common']['Wins'] , '</td>
										<td class="nobr" >% ' , $txt['SMFQuiz_Common']['TotalWins'] , '</td>
									</tr>
	';
	$counter = 1;
	if (isset($context['SMFQuiz']['quizMasters']))
		foreach ($context['SMFQuiz']['quizMasters'] as $quizMastersRow)
		{
			$totalWinPerc = ($quizMastersRow['total_wins'] / $context['SMFQuiz']['totalQuizzes'][0]) * 100;
			echo '						<tr height="27" class="' , $counter % 2 == 1 ? 'windowbg' : 'windowbg' , '">
											<td width="100%">
			';
			if ($counter == 1)
				echo '<img src="' , $settings['default_images_url'] , '/quiz_images/cup_g.gif"/>';
			elseif ($counter == 2)
				echo '<img src="' , $settings['default_images_url'] , '/quiz_images/cup_s.gif"/>';
			else if ($counter == 3)
				echo '<img src="' , $settings['default_images_url'] , '/quiz_images/cup_b.gif"/>';

			echo '
												<a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $quizMastersRow['id_user'] , '">' , $quizMastersRow['real_name'] , '</a>
											</td>
											<td align="center">' , $quizMastersRow['total_wins'] , '</td>
											<td align="center">' , round($totalWinPerc,2) , '%</td>
										</tr>
			';
			$counter++;
		}

	echo '
										<tr>
											<td colspan="3">[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizmasters">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
										</td>
								</table>					</div>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td width="50%" valign="top">
			<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
				<div class="title_bar">
					<h4 class="titlebg">
						<span class="left"></span>
						' , $txt['SMFQuiz_Home_Page']['PopularQuizzes'] , '
					</h4>
				</div>
				<div class="blockcontent windowbg" style="margin-top:2px; ">
					<div style="padding:4px;">
								<table border="0" width="100%" class="windowbg">
									<tr class="titlebg">
										<td>&nbsp;</td>
										<td>' , $txt['SMFQuiz_Common']['Quiz'] , '</td>
										<td>' , $txt['SMFQuiz_Common']['Plays'] , '</td>
									</tr>

	';

	$counter = 1;
	if (isset($context['SMFQuiz']['popularQuizzes']))
		foreach ($context['SMFQuiz']['popularQuizzes'] as $popularQuizRow)
		{
			echo '						<tr class="windowbg">
											<td width="8%"><img width="25" height="25" src="' , !empty($popularQuizRow['image']) ? $settings["default_images_url"] . '/quiz_images/Quizzes/' . $popularQuizRow['image'] : $settings["default_images_url"] . '/quiz_images/Quizzes/Default-64.png' , '"/></td>
											<td width="100%"><table border="0" cellpadding="0" cellspacing="0"><tr><td><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $popularQuizRow['id_quiz'] , '">' , format_string($popularQuizRow['title']) , '</a>
			';
			if ($popularQuizRow['updated'] > $newDate)
				echo '<td>&nbsp;<img src="' , $settings['default_images_url'] , '/quiz_images/new.gif"/></td>';

			echo '
											</td></tr></table></td>
											<td align="center" class="nobr" >' , $popularQuizRow['quiz_plays'] , '</td>
										</tr>
			';
			$counter++;
		}

	echo '
										<tr>
											<td colspan="3">[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizzes;type=popular">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
										</tr>
								</table>					</div>
				</div>
			</div>
		</td>
		<td width="50%" valign="top">
			<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
				<div class="title_bar">
					<h4 class="titlebg">
						<span class="left"></span>
						' , $txt['SMFQuiz_Home_Page']['QuizLeagueLeaders'] , '
					</h4>
				</div>
				<div class="blockcontent windowbg" style="margin-top:2px; ">
					<div style="padding:4px;">
								<table border="0" width="100%" class="windowbg">
									<tr class="titlebg">
										<td colspan="2">' , $txt['SMFQuiz_Common']['QuizLeague'] , '</td>
										<td>' , $txt['SMFQuiz_Common']['CurrentLeader'] , '</td>
									</tr>
	';

	if (isset($context['SMFQuiz']['quizLeagueLeaders']))
		foreach ($context['SMFQuiz']['quizLeagueLeaders'] as $quizLeagueLeadersRow)
		{
			echo '						<tr class="windowbg">
											<td width="8%">
												' , $quizLeagueLeadersRow['updated']  > $newDate ? '<img src="' . $settings['default_images_url'] . '/quiz_images/new.gif"/>' : '&nbsp;' , '
											</td>
											<td width="100%">
												<a href="' , $scripturl , '?action=SMFQuiz;sa=quizleagues;id=' , $quizLeagueLeadersRow["id_quiz_league"] , '">' , format_string($quizLeagueLeadersRow['title']) , '</a>
											</td>
											<td align="left" class="nobr">' , $quizLeagueLeadersRow['id_leader'] > 0 ? '<img src="' . $settings['default_images_url'] . '/quiz_images/cup_g.gif"/> <a href="' . $scripturl . '?action=SMFQuiz;sa=userdetails;id_user=' . $quizLeagueLeadersRow['id_leader'] . '">' . $quizLeagueLeadersRow['real_name'] . '</a>' : $txt['SMFQuiz_Common']['NoLeader'] , '</td>
										</tr>
			';
			$counter++;
		}

	echo '
								</table>					</div>
				</div>
			</div>
		</td>
	</tr>
</table>

<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
	<div class="title_bar">
		<h4 class="titlebg">
			<span class="left"></span>
			' , $txt['SMFQuiz_Home_Page']['InfoBoard'] , '
		</h4>
	</div>
	<div class="blockcontent windowbg" style="margin-top:2px; ">
		<div style="padding:4px;">
					<div id="smfAnnouncements" style="height: 30ex; overflow: auto; padding-right: 1ex;">

						<div id="upshrinkHeaderIB">
							<div class="windowbg">
								<table border="0" width="100%" class="windowbg">
									<tr class="titlebg">
										<td>' , $txt['SMFQuiz_Common']['Date'] , '</td>
										<td>' , $txt['SMFQuiz_Common']['Notice'] , '</td>
									</tr>
	';
	$counter = 1;
	if (isset($context['SMFQuiz']['infoBoard']))
		foreach ($context['SMFQuiz']['infoBoard'] as $infoboardRow)
		{
			echo '						<tr class="windowbg">
											<td align="left" width="12%" valign="top" class="nobr" >' , date("M d Y H:i", $infoboardRow['entry_date']) , '</td>
											<td align="left" width="100%" valign="top">' , format_string($infoboardRow['Entry']) , '</td>
										</tr>
			';
			$counter++;
		}

	echo '
								</table>
							</div>
						</div>
					</div>		</div>
	</div>
</div>
';

}

			// @TODO createList?
function template_statistics()
{
	global $context, $settings, $scripturl, $txt, $smcFunc;

	foreach ($context['SMFQuiz']['totalQuizStats'] as $quizStatsRow)
	{
		echo '
			<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
				<tr>
					<td colspan="4" align="left">
						<div class="title_bar">
							<h4 class="titlebg">
								<span class="left"></span>
								' , $txt['SMFQuiz_Statistics_Page']['GeneralStatistics'] , '
							</h4>
						</div>
					</td>
				</tr>
				<tr>
					<td class="centertext" style="width: 20px;vertical-align: middle;"><img class="icon" style="width: 20px;height: 20px;" src="' , $settings['default_images_url'] , '/quiz_images/stats_info.gif" alt=""></td>
					<td class="windowbg" valign="top" width="50%">
						<table border="0" cellpadding="1" cellspacing="0" width="100%">
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['TotalQuizzes'] , ':</td>
								<td align="left" width="100%">' , $quizStatsRow['total_quiz_count'] , ' ' , $txt['SMFQuiz_Common']['quizzes'] , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['TotalQuestions'] , ':</b></td>
								<td align="left">' , $context['SMFQuiz']['totalQuestions'] , ' ' , $txt['SMFQuiz_Common']['questions'] , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['TotalAnswers'] , ':</b></td>
								<td align="left">' , $context['SMFQuiz']['totalAnswers'] , ' ' , $txt['SMFQuiz_Common']['answers'] , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['TotalCategories'] , ':</b></td>
								<td align="left">' , $context['SMFQuiz']['totalCategories'] , ' ' , $txt['SMFQuiz_Common']['categories'] , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['TotalQuizzesPlayed'] , ':</b></td>
								<td align="left">' , $quizStatsRow['total_quiz_plays'] , ' ' , $txt['SMFQuiz_Common']['quizzesplayed'] , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['TotalQuestionsPlayed'] , ':</b></td>
								<td align="left">' , $quizStatsRow['total_question_plays'] , ' ' , $txt['SMFQuiz_Common']['questionsplayed'] , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['TotalCorrectQuestions'] , ':</b></td>
								<td align="left">' , $quizStatsRow['total_correct'] , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['TotalPercentageCorrect'] , ':</b></td>
								<td align="left">' , $quizStatsRow['total_percentage_correct'] , '%</td>
							</tr>
						</table>
					</td>
					<td class="centertext" style="width: 20px;vertical-align: middle;"><img class="icon" style="width: 20px;height: 20px;" src="' , $settings['default_images_url'] , '/quiz_images/stats_info.gif" alt=""></td>
					<td class="windowbg" valign="top" width="50%">
						<table border="0" cellpadding="1" cellspacing="0" width="100%">
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['MostTopScores'] , ':</b></td>
								<td width="100%"><img src="' , $settings['default_images_url'] , '/star.gif" width="12" height="12" alt="" /> ' , isset($context['SMFQuiz']['mostQuizWins'][0]) ? $context['SMFQuiz']['mostQuizWins'][0]['real_name'] . ' ' . $txt['SMFQuiz_Common']['with'] . ' ' . $context['SMFQuiz']['mostQuizWins'][0]['TopScores'] . ' ' . $txt['SMFQuiz_Common']['topscores'] . '' : '' , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['BestQuizResult'] , ':</b></td>
								<td><img src="' , $settings['default_images_url'] , '/star.gif" width="12" height="12" alt="" /> ' , isset($context['SMFQuiz']['bestQuizResult'][0]) ? $context['SMFQuiz']['bestQuizResult'][0]['real_name'] . ' ' . $txt['SMFQuiz_Common']['with'] . ' ' . $context['SMFQuiz']['bestQuizResult'][0]['percentage_correct'] . '% ' . $txt['SMFQuiz_Common']['in'] . ' ' . $context['SMFQuiz']['bestQuizResult'][0]['total_seconds'] . ' ' . $txt['SMFQuiz_Common']['secs'] . '' : '' , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['WorstQuizResult'] , ':</b></td>
								<td>' , isset($context['SMFQuiz']['bestQuizResult'][0]) ? $context['SMFQuiz']['bestQuizResult'][0]['real_name'] . ' ' . $txt['SMFQuiz_Common']['with'] . ' ' . $context['SMFQuiz']['worstQuizResult'][0]['percentage_correct'] . '% in ' . $context['SMFQuiz']['worstQuizResult'][0]['total_seconds'] . ' ' . $txt['SMFQuiz_Common']['secs'] . '' : '' , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['NewestQuiz'] , ':</b></td>
								<td>' , isset($context['SMFQuiz']['newestQuiz'][0]) ? format_string($context['SMFQuiz']['newestQuiz'][0]['title']) . ' ' . $txt['SMFQuiz_Common']['updatedon'] . ' ' . date("M d Y H:i", $context['SMFQuiz']['newestQuiz'][0]['updated']) : '' , '</td>
							</tr>
							<tr>
								<td nowrap="nowrap"><b>' , $txt['SMFQuiz_Statistics_Page']['OldestQuiz'] , ':</b></td>
								<td>' , isset($context['SMFQuiz']['oldestQuiz'][0]) ? format_string($context['SMFQuiz']['oldestQuiz'][0]['title']) . ' ' . $txt['SMFQuiz_Common']['updatedon'] . ' ' . date("M d Y H:i", $context['SMFQuiz']['oldestQuiz'][0]['updated']) : '' , '</td>
							</tr>
						</table>
					</td>
				</tr>
		';
	}
	echo '
			<tr>
				<td colspan="2" width="50%" align="left">
					<div class="title_bar">
						<h4 class="titlebg">
							<span class="left"></span>
							' , $txt['SMFQuiz_Statistics_Page']['Top10QuizWinners'] , '
						</h4>
					</div>
				</td>

				<td colspan="2" width="50%" align="left">
					<div class="title_bar">
						<h4 class="titlebg">
							<span class="left"></span>
							' , $txt['SMFQuiz_Statistics_Page']['Top10Quizzes'] , '
						</h4>
					</div>
				</td>
			</tr>
			<tr>
				<td class="centertext" style="width: 20px;vertical-align: middle;"><img class="icon" style="width: 20px;height: 20px;" src="' , $settings['default_images_url'] , '/quiz_images/stats_posters.gif" alt=""></td>
				<td class="windowbg" width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<table border="0" cellpadding="1" cellspacing="0" width="100%">
	';
	$counter = 1;
	$maxScore = 0;
	foreach($context['SMFQuiz']['quizMasters'] as $quizStatsRow)
	{
		if ($counter == 1)
		{
			$percentage = 100;
			$maxScore = $quizStatsRow['total_wins'];
		}
		else
			$percentage = ($quizStatsRow['total_wins'] / $maxScore) * 100;

		echo '			<tr>
							<td align="left" nowrap="nowrap">
		';
		if ($counter == 1)
			echo '<img src="' , $settings['default_images_url'] , '/quiz_images/cup_g.gif"/>';
		elseif ($counter == 2)
			echo '<img src="' , $settings['default_images_url'] , '/quiz_images/cup_s.gif"/>';
		elseif ($counter == 3)
			echo '<img src="' , $settings['default_images_url'] , '/quiz_images/cup_b.gif"/>';

		echo '
								<a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $quizStatsRow['id_user'] , '">' , $quizStatsRow['real_name'] , '</a>
							</td>
							<td align="left" width="100%"><img src="' , $settings['default_images_url'] , '/quiz_images/bar_stats.png" width="' , $percentage , '%" height="15" alt="" /></td>
							<td align="right" class="nobr" >' , $quizStatsRow['total_wins'] , ' ' , $txt['SMFQuiz_Common']['Wins'] , '</td>
						</tr>
		';
		$counter++;
	}

	echo '
						<tr>
							<td colspan="3">[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizmasters">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
						</tr>
					</table>
				</td>
				<td class="centertext" style="width: 20px;vertical-align: middle;"><img class="icon" style="width: 20px;height: 20px;" src="' , $settings['default_images_url'] , '/quiz_images/stats_replies.gif" alt=""></td>

				<td class="windowbg" width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
	';
	$max = 0;
	$percentage = 0;
	foreach($context['SMFQuiz']['popularQuizzes'] as $popularQuizzesRow)
	{
		if ($max == 0)
			$max = $popularQuizzesRow['quiz_plays'];
		elseif ($max > 0)
			$percentage = ($popularQuizzesRow['quiz_plays'] / $max) * 100;

		echo '			<tr>
							<td align="left" nowrap="nowrap"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $popularQuizzesRow['id_quiz'] , '">' , format_string($popularQuizzesRow['title']) , '</a></td>
							<td align="left" width="100%"><img src="' , $settings['default_images_url'] , '/quiz_images/bar_stats.png" width="' , $percentage , '%" height="15" alt="" /></td>
							<td align="right" nowrap="nowrap">' , $popularQuizzesRow['quiz_plays'] , ' plays</td>
						</tr>
		';
	}
	echo '
						<tr>
							<td colspan="3">[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizzes;type=popular">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
						</tr>
					</table>

				</td>
			</tr>
			<tr>
				<td colspan="2" width="50%" align="left">
					<div class="title_bar">
						<h4 class="titlebg">
							<span class="left"></span>
							' , $txt['SMFQuiz_Statistics_Page']['10HardestQuizzes'] , '
						</h4>
					</div>
				</td>

				<td colspan="2" width="50%" align="left">
					<div class="title_bar">
						<h4 class="titlebg">
							<span class="left"></span>
							' , $txt['SMFQuiz_Statistics_Page']['10EasiestQuizzes'] , '
						</h4>
					</div>
				</td>
			</tr>
				<tr>
					<td class="centertext" style="width: 20px;vertical-align: middle;"><img class="icon" style="width: 20px;height: 20px;" <img src="' , $settings['default_images_url'] , '/quiz_images/stats_replies.gif" alt=""></td>
					<td class="windowbg" valign="top" width="50%">
						<table border="0" cellpadding="1" cellspacing="0" width="100%">
	';
	$max = 0;
	foreach($context['SMFQuiz']['hardestQuizzes'] as $hardestQuizzesRow)
	{
		if ($max == 0)
			$max = $hardestQuizzesRow['percentage_incorrect'];

		$percentage = ($hardestQuizzesRow['percentage_incorrect'] / $max) * 100;
		echo '			<tr>
							<td align="left" nowrap="nowrap"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $hardestQuizzesRow['id_quiz'] , '">' , format_string($hardestQuizzesRow['title']) , '</a></td>
							<td align="left" width="100%"><img src="' , $settings['default_images_url'] , '/quiz_images/bar_stats.png" width="' , $percentage , '%" height="15" alt="" /></td>
							<td align="right" nowrap="nowrap">' , $hardestQuizzesRow['percentage_incorrect'] , '%</td>
						</tr>
		';
	}
	echo '
						<tr>
							<td colspan="3">[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizzes;type=hardest">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
						</tr>
						</table>
					</td>
					<td class="centertext" style="width: 20px;vertical-align: middle;"><img class="icon" style="width: 20px;height: 20px;" src="' , $settings['default_images_url'] , '/quiz_images/stats_replies.gif" alt=""></td>
					<td class="windowbg" valign="top" width="50%">
						<table border="0" cellpadding="1" cellspacing="0" width="100%">
	';
	$max = 0;
	foreach($context['SMFQuiz']['easiestQuizzes'] as $easiestQuizzesRow)
	{
		if ($max == 0)
			$max = $easiestQuizzesRow['percentage_correct'];

		$percentage = ($easiestQuizzesRow['percentage_correct'] / $max) * 100;
		echo '			<tr>
							<td align="left" nowrap="nowrap"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $easiestQuizzesRow['id_quiz'] , '">' , format_string($easiestQuizzesRow['title']) , '</a></td>
							<td align="left" width="100%"><img src="' , $settings['default_images_url'] , '/quiz_images/bar_stats.png" width="' , $percentage , '%" height="15" alt="" /></td>
							<td align="right" nowrap="nowrap">' , $easiestQuizzesRow['percentage_correct'] , '%</td>
						</tr>
		';
	}
	echo '
						<tr>
							<td colspan="3">[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizzes;type=easiest">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
						</tr>
					</table>
					</td>
				</tr>
			<tr>
				<td colspan="2" width="50%" align="left">
					<div class="title_bar">
						<h4 class="titlebg">
							<span class="left"></span>
							' , $txt['SMFQuiz_Statistics_Page']['10MostActivePlayers'] , '
						</h4>
					</div>
				</td>

				<td colspan="2" width="50%" align="left">
					<div class="title_bar">
						<h4 class="titlebg">
							<span class="left"></span>
							' , $txt['SMFQuiz_Statistics_Page']['10MostQuizCreators'] , '
						</h4>
					</div>
				</td>
			</tr>
				<tr>
					<td class="centertext" style="width: 20px;vertical-align: middle;"><img class="icon" style="width: 20px;height: 20px;" src="' , $settings['default_images_url'] , '/quiz_images/stats_replies.gif" alt=""></td>
					<td class="windowbg" valign="top" width="50%">
						<table border="0" cellpadding="1" cellspacing="0" width="100%">
	';
	$counter = 1;
	$maxPlays = 0;
	foreach($context['SMFQuiz']['mostActivePlayers'] as $mostActivePlayersRow)
	{
		if ($counter == 1)
		{
			$percentage = 100;
			$maxPlays = $mostActivePlayersRow['total_plays'];
		}
		else
			$percentage = ($mostActivePlayersRow['total_plays'] / $maxPlays) * 100;

		echo '			<tr>
							<td align="left" nowrap="nowrap">
		';
		echo '
								<a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $mostActivePlayersRow['id_user'] , '">' , $mostActivePlayersRow['real_name'] , '</a>
							</td>
							<td align="left" width="100%"><img src="' , $settings['default_images_url'] , '/quiz_images/bar_stats.png" width="' , $percentage , '%" height="15" alt="" /></td>
							<td align="right" class="nobr" >' , $mostActivePlayersRow['total_plays'] , ' plays</td>
						</tr>
		';
		$counter++;
	}
	echo '
						</table>
					</td>
					<td class="centertext" style="width: 20px;vertical-align: middle;"><img class="icon" style="width: 20px;height: 20px;" src="' , $settings['default_images_url'] , '/quiz_images/stats_replies.gif" alt=""></td>
					<td class="windowbg" valign="top" width="50%">
						<table border="0" cellpadding="1" cellspacing="0" width="100%">
	';
	$max = 0;
	foreach($context['SMFQuiz']['mostQuizCreators'] as $mostQuizCreatorsRow)
	{
		if ($max == 0)
			$max = $mostQuizCreatorsRow['quizzes'];

		$percentage = ($mostQuizCreatorsRow['quizzes'] / $max) * 100;
		echo '			<tr>
							<td align="left" nowrap="nowrap"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $mostQuizCreatorsRow['creator_id'] , '">' , $mostQuizCreatorsRow['real_name'] , '</a></td>
							<td align="left" width="100%"><img src="' , $settings['default_images_url'] , '/quiz_images/bar_stats.png" width="' , $percentage , '%" height="15" alt="" /></td>
							<td align="right" nowrap="nowrap">' , $mostQuizCreatorsRow['quizzes'] , ' ' , $txt['SMFQuiz_Common']['quizzes'] , '</td>
						</tr>
		';
	}
	echo '				</table>
					</td>
				</tr>
		</table>

	';
}

function template_quiz_details()
{
	global $context, $scripturl, $settings, $boardurl, $txt, $smcFunc;

	list($counter, $cups) = [0, ['cup_g', 'cup_s', 'cup_b']];
	echo '
<div class="quiz_details_flex">
	<div class="quiz_details_flex_cell">
		<div class="title_bar">
			<h4 class="titlebg">
				<span class="left"></span>
				' , $txt['SMFQuiz_QuizDetails_Page']['GeneralInformation'] , '
			</h4>
		</div>
		<div class="blockcontent windowbg quizdetailwindow" style="height: 100%;">
			<div>
				<div>
					<div class="quiz_details_flex_play">';

	foreach($context['SMFQuiz']['quiz'] as $quizRow)
	{
		echo '
						<div class="quiz_details_flex_row">' . (!empty($quizRow['image']) ? '
							<img class="quiz_details_flex_play_img" src="' . $settings['default_images_url'] . '/quiz_images/Quizzes/' . $quizRow['image'] . '">' : '
							&nbsp;') . '
						</div>
						<div class="quiz_details_flex_play_title"><b>' , $txt['SMFQuiz_Common']['Title'] , ':</b></div>
						<div class="quiz_details_flex_play_descript">' , format_string($quizRow['title']) , '</div>
						<div class="quiz_details_flex_play_title"><b>' , $txt['SMFQuiz_Common']['Description'] , ':</b></div>
						<div class="quiz_details_flex_play_descript">' , format_string($quizRow['description']) , '</div>
						<div class="quiz_details_flex_play_title" ><b>' , $txt['SMFQuiz_Common']['Category'] , ':</b></div>
						<div class="quiz_details_flex_play_descript"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;categoryId=' , $quizRow['id_category'] , '">' , format_string($quizRow['name']) , '</a></div>
						<div class="quiz_details_flex_play_title"><b>' , $txt['SMFQuiz_Common']['CreatedBy'] , ':</b></div>
						<div class="quiz_details_flex_play_descript"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $quizRow['creator_id'] , '">' , $quizRow['creator_name'] , '</a></div>
						<div class="quiz_details_flex_play_title"><b>' , $txt['SMFQuiz_Common']['Questions'] , ':</b></div>
						<div class="quiz_details_flex_play_descript">' , $quizRow['questions_per_session'] , '</div>
						<div class="quiz_details_flex_play_title"><b>' , $txt['SMFQuiz_Common']['PlayLimit'] , ':</b></div>
						<div class="quiz_details_flex_play_descript">' , $quizRow['play_limit'] , '</div>
						<div class="quiz_details_flex_play_title"><b>' , $txt['SMFQuiz_Common']['SecondsPerQuestion'] , ':</b></div>
						<div class="quiz_details_flex_play_descript">' , $quizRow['seconds_per_question'] , '</div>
						<div class="quiz_details_flex_play_title"><b>' , $txt['SMFQuiz_Common']['ShowAnswers'] , ':</b></div>
						<div class="quiz_details_flex_play_descript">' , $quizRow['show_answers'] == 1 ? 'Yes' : 'No' , '</div>
						<div class="quiz_details_flex_play_title"><b>' , $txt['SMFQuiz_Common']['LastUpdated'] , ':</b></div>
						<div class="quiz_details_flex_play_descript">' , date("M d Y", $quizRow['updated']) , '</div>';
	}
	echo '

						<div class="quiz_details_flex_row" style="padding-top: 0.5rem;">
							<span style="padding-right: 0.25rem;">
								<a href="#" onclick="this.style.visibility=\'hidden\';window.open(\'' , $scripturl , '?action=SMFQuiz;sa=play;id_quiz=' , (!empty($quizRow['id_quiz']) ? (int)$quizRow['id_quiz'] : '0') , '\',\'playnew\',\'height=625,width=720,toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=yes\')"><img src="' , $settings['default_images_url'] , '/quiz_images/Play-24.png" alt="Play Quiz" border="0" height="24" width="24"/></a>
							</span>
							<span>
								<a href="' . $scripturl . '?action=' . $context['current_action'] . (!empty($link['action']) ? ';sa=' . $link['action'] : '') . '"><img src="' . $settings['default_images_url'] . '/quiz_images/Home-24.png" alt="Go Home" border="0" height="24" width="24"/></a>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="quiz_details_flex_cell">
		<div class="title_bar">
			<h4 class="titlebg">
				<span class="left"></span>
				' , $txt['SMFQuiz_Common']['QuizScores'] , '
			</h4>
		</div>
		<div class="blockcontent windowbg quizdetailwindow">
			<div style="padding:4px;height: 100%;">
				<div style="height: 100%;">
					<div class="quiz_details_flex_scores" style="height: 100%;">
						<div class="titlebg quiz_details_flex_scores_title">
							<div class="quiz_details_flex_scores_small" ><span style="width: 16px;">&nbsp;</span></div>
							<div class="quiz_details_flex_scores_med">' , $txt['SMFQuiz_Common']['Date'] , '</div>
							<div class="quiz_details_flex_scores_lge">' , $txt['SMFQuiz_Common']['Member'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $txt['SMFQuiz_Common']['Qs'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $txt['SMFQuiz_Common']['Crct'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $txt['SMFQuiz_Common']['Incrt'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $txt['SMFQuiz_Common']['Touts'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $txt['SMFQuiz_Common']['Secs'] , '</div>
							<div class="quiz_details_flex_scores_small"><span style="width: 16px;">&nbsp;</span></div>';


	foreach ($context['SMFQuiz']['quizResults'] as $quizResultsRow)
	{
		echo '
							<div class="quiz_details_flex_scores_small" >' . ($counter <= count($cups) ? '
								<img src="' . $settings['default_images_url'] . '/quiz_images/' . $cups[$counter] . '.gif">' : '
								<span style="width: 16px;">&nbsp;</span>') . '
							</div>
							<div class="quiz_details_flex_scores_med" style="cursor: pointer;" title="' , date("M d Y H:i", $quizResultsRow['result_date']) , '">' , date("m/d/Y", $quizResultsRow['result_date']) , '</div>
							<div class="quiz_details_flex_scores_lge" style="text-overflow: ellipsis;"><a href="' . $scripturl . '?action=SMFQuiz;sa=userdetails;id_user=' . $quizResultsRow['id_user'] . '">' . $quizResultsRow['real_name'] . '</a></div>
							<div class="quiz_details_flex_scores_small">' , $quizResultsRow['questions'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $quizResultsRow['correct'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $quizResultsRow['incorrect'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $quizResultsRow['timeouts'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $quizResultsRow['total_seconds'] , '</div>
							<div class="quiz_details_flex_scores_small">' , $quizResultsRow['auto_completed'] == 1 ? '<img src="' . $settings['default_images_url'] . '/quiz_images/time.png" title="' . $txt['SMFQuiz_Common']['AutoCompleted'] . '"/>' : '<span style="width: 16px;">&nbsp;</span>' , '</div>';
		$counter++;
	}
	echo '
						</div>
						<div class="quiz_details_viewall">
							<div><a href="' , $scripturl , '?action=SMFQuiz;sa=quizscores;id_quiz=', (!empty($quizRow['id_quiz']) ? (int)$quizRow['id_quiz'] : '0'), '">' , $txt['SMFQuiz_Common']['ViewAll'] , '</a></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="quiz_details_flex_cell">
		<div class="title_bar">
			<h4 class="titlebg">
				<span class="left"></span>
				' , $txt['SMFQuiz_Common']['Statistics'] , '
			</h4>
		</div>
		<div class="blockcontent windowbg quizdetailwindow">
			<div style="height: 100%;">
				<div style="height: 100%;">
					<div class="quiz_details_flex_stat" style="height: 100%;">';

	if (!isset($context['SMFQuiz']['quiz']) || empty($context['SMFQuiz']['quiz']))
	{
		echo '
						<div class="quiz_details_flex_row">
							<div class="centertext">' , $txt['SMFQuiz_Categories_Page']['NoQuizzesInCategory'] , '</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>';

		return;
	}

	foreach ($context['SMFQuiz']['quiz'] as $quizRow) {
	{
		$pt = !empty($quizRow['percentage']) ? (int)$quizRow['percentage'] : 0;
		switch($pt){
			case ($pt > 80):
				$level = '<span class="quizLevelVeryEasy">' . $txt['SMFQuiz_Common']['VeryEasy'] . '</span>';
				break;
			case ($pt > 60):
				$level = '<span class="quizLevelEasy">' . $txt['SMFQuiz_Common']['Easy'] . '</span>';
				break;
			case ($pt > 40):
				$level = '<span class="quizLevelAverage">' . $txt['SMFQuiz_Common']['Average'] . '</span>';
				break;
			case ($pt > 20):
				$level = '<span class="quizLevelDifficult">' . $txt['SMFQuiz_Common']['Difficult'] . '</span>';
				break;
			default:
				if ($quizRow['quiz_plays'] == 0)
					$level = '<span class="quizLevelNA">' . $txt['SMFQuiz_n_a'] . '</span>';
				else
					$level = '<span class="quizLevelTough">' . $txt['SMFQuiz_Common']['Tough'] . '</span>';
				}
		}

		echo '
						<div class="quiz_details_flex_stat_title"><b>', $txt['SMFQuiz_Common']['TimesPlayed'] , ':</b></div>
						<div class="quiz_details_flex_stat_descript">', format_string($quizRow['quiz_plays']) , '</div>
						<div class="quiz_details_flex_stat_title"><b>', $txt['SMFQuiz_Common']['QuestionsPlayed'] , ':</b></div>
						<div class="quiz_details_flex_stat_descript">', format_string($quizRow['question_plays']) , '</div>
						<div class="quiz_details_flex_stat_title" ><b>', $txt['SMFQuiz_Common']['TotalCorrect'] , ':</b></div>
						<div class="quiz_details_flex_stat_descript">', format_string($quizRow['total_correct']), '</div>
						<div class="quiz_details_flex_stat_title"><b>' , $txt['SMFQuiz_Common']['PercentageCorrect'] , ':</b></div>
						<div class="quiz_details_flex_stat_descript">', format_string($quizRow['percentage']), '</div>
						<div class="quiz_details_flex_stat_title"><b>' , $txt['SMFQuiz_Common']['Rating'] , ':</b></div>
						<div class="quiz_details_flex_stat_descript">' , $level , '</div>';
	}
	echo '
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="quiz_details_flex_cell">
		<div class="title_bar">
			<h4 class="titlebg">
				<span class="left"></span>
				' , $txt['SMFQuiz_Common']['ScoreChart'] , '
			</h4>
		</div>
		<div class="blockcontent windowbg quizdetailwindow">
			<div style="padding:4px;height: 100%;">
				<div style="height: 100%;">
					<div class="quiz_details_flex_scores" style="height: 100%;">
						<div class="titlebg quiz_details_flex_scores_title">
							<div class="quiz_details_flex_scores_med">' , $txt['SMFQuiz_Common']['Score'] , '</div>
							<div class="quiz_details_flex_scores_bar_title">' , $txt['SMFQuiz_Common']['Percentage'] , '</div>
							<div class="quiz_details_flex_scores_med">' , $txt['SMFQuiz_Common']['No'] , '</div>';


	$maxScoreCount = 0;
	$totalScoreCount = 0;
	$counts = array();
	foreach($context['SMFQuiz']['quizCorrect'] as $quizCorrectRow)
	{
		if ($quizCorrectRow['count_correct'] > $maxScoreCount)
			$maxScoreCount = $quizCorrectRow['count_correct'];

		$totalScoreCount = $totalScoreCount + $quizCorrectRow['count_correct'];
		$counts[$quizCorrectRow['correct']] = $quizCorrectRow['count_correct'];
	}

	for ($counter = 0; $counter < $quizRow['questions_per_session'] + 1; $counter++)
	{
		$countPercentage = 0;
		if (isset($counts[$counter]))
			$countPercentage = $counts[$counter] == null ? 0 : ($counts[$counter] / $totalScoreCount) * 100;

		echo '
							<div class="quiz_details_flex_scores_med">' .  $counter . '</div>
							<div class="quiz_details_flex_scores_bar"><img style="width: ' . ((int)$countPercentage) . '%;height: 10px;" src="' . $settings['default_images_url'] . '/quiz_images/bar_stats.png"></div>
							<div class="quiz_details_flex_scores_med">' . (isset($counts[$counter]) ? $counts[$counter] : '') . '</div>';
	}

	echo '
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>';
}

			// @TODO createList?
function template_categories()
{
	global $context, $scripturl, $settings, $boardurl, $txt, $smcFunc;

	$newDate = strtotime("-2 day", time());

	foreach ($context['SMFQuiz']['category'] as $categoryrow)
	{
		echo '

<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
	<div class="title_bar">
		<h4 class="titlebg">
			<span class="left"></span>
			' , $categoryrow['name'] , '
		</h4>
	</div>
	<div class="blockcontent windowbg" style="margin-top:2px; ">
		<div style="padding:4px;">
					<div class="windowbg">
						<table border="0">
							<tr>
								<td align="left" valign="top">
		';
		if (isset($_GET['categoryId']) && $_GET['categoryId'] == 0)
			echo '					<img src="' , $settings["default_images_url"] , '/quiz_images/Quizzes/quiz.png"/>';
		elseif (!empty($categoryrow['image']))
			echo '					<img src="' , $settings['default_images_url'] , '/quiz_images/Quizzes/' , $categoryrow['image'] , '"/>';
		else
			echo '					<img width="64" height="64" src="' , $settings["default_images_url"] , '/quiz_images/Quizzes/Default-64.png"/>';

		echo '
								</td>
								<td align="left" valign="top">' , format_string($categoryrow['description']) , '</td>
							</tr>
		';
		if (isset($_GET['categoryId']) && $_GET['categoryId'] != 0)
			echo'
							<tr>
								<td align="left" colspan="2">' , $txt['SMFQuiz_Common']['ReturnTo'] , ' <a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';categoryId=' , $categoryrow['id_parent'] , '">[' , !empty($categoryrow['ParentName']) ? $categoryrow['ParentName'] : $txt['SMFQuiz_Categories_Page']['TopLevel'] , ']</a></td>
							</tr>
			';

		echo '
						</table>
					</div>
		</div>
	</div>
</div>

<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
	<div class="title_bar">
		<h4 class="titlebg">
			<span class="left"></span>
			' , $txt['SMFQuiz_Common']['SubCategoriesOf'] , ' ' , $categoryrow['name'] , '
		</h4>
	</div>
	<div class="blockcontent windowbg" style="margin-top:2px; ">
		<div style="padding:4px;">
					<div class="windowbg">
						<table border="0" width="100%">
		';
		$counter = 0;
		foreach ($context['SMFQuiz']['categories'] as $row)
		{
			if ($counter % 2 == 0)
				echo '	<tr class="windowbg">';

			echo '
							<td width="8%">
			';
			if (!empty($row['image']))
				echo '			<img width="32" height="32" src="' , $settings['default_images_url'] , '/quiz_images/Quizzes/' , $row['image'] , '"/>';
			elseif (!empty($categoryrow['image']))
				echo '			<img width="32" height="32" src="' , $settings['default_images_url'] , '/quiz_images/Quizzes/' , $categoryrow['image'] , '"/>';
			else
				echo '			<img width="32" height="32" src="' , $settings["default_images_url"] , '/quiz_images/Quizzes/Default-64.png"/>';

			echo '
							</td><td align="left" width="50%" valign="middle" class="nobr" ><a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';categoryId=' , $row['id_category'] , '">' , $row['name'] , '</a> (' , $row['quiz_count'] , ')</td>
			';
			if ($counter % 2 != 0 && $counter != sizeof($row))
				echo '	</tr">';

			$counter++;
		}

		if ($counter % 2 != 0)
			echo '			<td colspan="2" align="left" width="100%">&nbsp;</td></tr>';

		echo '
						</table>
					</div>
		</div>
	</div>
</div>

<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
	<div class="title_bar">
		<h4 class="titlebg">
			<span class="left"></span>
			' , $txt['SMFQuiz_Common']['QuizzesIn'] , ' ' , $categoryrow["name"] , '
		</h4>
	</div>
	<div class="blockcontent windowbg" style="margin-top:2px; ">
		<div style="padding:4px;">
					<div class="windowbg">
		';
		if (isset($context['SMFQuiz']['quizzes']) && sizeof($context['SMFQuiz']['quizzes']) > 0)
		{
			echo '
				<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
					<tbody>
						<tr class="', empty($settings['use_tabs']) ? 'titlebg' : 'catbg3', '">
			';

			// Display each of the column headers of the table.
			foreach ($context['columns'] as $column)
			{
				// We're not able (through the template) to sort the search results right now...
				if (isset($context['old_search']))
					echo '
					<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
						', $column['label'], '</td>';
				// This is a selected solumn, so underline it or some such.
elseif ($column['selected'])
            echo '
            <td style="width: auto;"' . (isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '') . ' nowrap="nowrap">
                <a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <span class="main_icons sort_' . (!empty($context['sort_direction']) ? $context['sort_direction'] : 'down') . '"></span></a></td>';				// This is just some column... show the link and be done with it.
				else
					echo '
					<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
						', $column['link'], '</td>';
			}
			echo '</tr>';
			if (sizeof($context['SMFQuiz']['quizzes']) > 0)
			{
				foreach ($context['SMFQuiz']['quizzes'] as $row)
				{
					echo '					<tr class="windowbg">
												<td><img width="25" height="25" src="' , !empty($row['image']) ? $settings["default_images_url"] . '/quiz_images/Quizzes/' . $row['image'] : $settings["default_images_url"] . '/quiz_images/Quizzes/Default-64.png' , '"/></td>
												<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $row['id_quiz'] , '">' , format_string($row['title']) , '</a></td>
												<td align="left">';
				if ($row['percentage'] > 80)
					echo '<font color="green">' , $txt['SMFQuiz_Common']['VeryEasy'] , '</font>';
				elseif ($row['percentage'] > 60)
					echo '<font color="green">' , $txt['SMFQuiz_Common']['Easy'] , '</font>';
				elseif ($row['percentage'] > 40)
					echo '<font color="orange">' , $txt['SMFQuiz_Common']['Average'] , '</font>';
				elseif ($row['percentage'] > 20)
					echo '<font color="red">' , $txt['SMFQuiz_Common']['Difficult'] , '</font>';
				elseif ($row['quiz_plays'] == 0)
					echo '<font color="red">' , $txt['SMFQuiz_n_a'] , '</font>';
				else
					echo '<font color="red">' , $txt['SMFQuiz_Common']['Tough'] , '</font>';

				echo '
												</td>
												<td align="center">' , $row['questions_per_session'] , '</td>
												<td align="center">' , $row['quiz_plays'] , '</td>
												<td align="center">' , $row['played'] > 0 ? '<img src="' . $settings['default_images_url'] . '/quiz_images/tick.png" alt="Yes" title="Yes" border="0" align="middle"/>' : '<img src="' . $settings['default_images_url'] . '/quiz_images/cross.png" alt="No" title="No" border="0" align="middle"/>' , '</td>
												<td align="center">' , date("M d Y H:i", $row['updated']) , '</td>
											</tr>
					';
				}
			}
			else
				echo ' 						<tr class="windowbg"><td colspan="10" align="left">', $txt['quiz_xml_error_no_quizzes'], '</td></tr>';

			echo '
									</tbody>
								</table>
								<table width="100%" cellpadding="0" cellspacing="0" border="0">
									<tr>
										<td>', $txt['pages'], ': ', $context['page_index'], '</td>
									</tr>
								</table>
			';
		}
		else
			echo '				<tr><td align="left" class="windowbg">' , $txt['SMFQuiz_Categories_Page']['NoQuizzesInCategory'] , '</td></tr>';

		echo '
						</table>
					</div>
		</div>
	</div>
</div>
		';
	}
}

			// @TODO createList?
function template_user_details()
{
	global $context, $scripturl, $settings, $boardurl, $txt, $smcFunc, $modSettings;
	echo '
		<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr class="windowbg">
				<td align="left" valign="top" width="50%">
					<div class="tborder clearfix" id="latestQuizFrame">
						<div class="title_bar">
							<h4 class="titlebg">
								<span class="left"></span>
								' , $txt['SMFQuiz_UserDetails_Page']['GeneralInformation'] , '
							</h4>
						</div>
						<div id="upshrinkHeaderLQ">
							<div class="windowbg">
								<table border="0" width="100%" class="windowbg">
	';
	echo '
								<tr>
									<td rowspan="6">
										' , $context['member']['avatar']['image'] , '
									</td>
								</tr>
								<tr>
									<td><b>' , $txt['SMFQuiz_UserDetails_Page']['Name'] , ':</b></td>
									<td width="100%">' , $context['member']['name'] , '</td>
								</tr>
								<tr>
									<td nowrap="nowrap"><b>' , $txt['SMFQuiz_UserDetails_Page']['DateRegistered'] , ':</b></td>
									<td width="100%">' , $context['member']['registered'] , '</td>
								</tr>
								<tr>
									<td><b>' , $txt['SMFQuiz_UserDetails_Page']['LastSeen'] , ':</b></td>
									<td width="100%">' , $context['member']['last_login'] , '</td>
								</tr>
								<tr>
									<td><b>' , $txt['SMFQuiz_UserDetails_Page']['Location'] , ':</b></td>
									<td width="100%">
	';
	if (!isset($context['disabled_fields']['location']) && !empty($context['member']['location']))
		echo $context['member']['location'];

	echo '
									</td>
								</tr>
								<tr>
									<td><b>' , $txt['SMFQuiz_UserDetails_Page']['Website'] , ':</b></td>
									<td width="100%">
	';

	if ($context['member']['website']['url'] != '')
		echo '
										<a href="', $context['member']['website']['url'], '" title="' . $context['member']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['www'] . '" border="0" />' : $txt['www']), '</a>
									</td>
								</tr>
		';

	echo '

									</tr>
	';
	if (isset($_GET['id_user']))
		echo '
									<tr>
										<td colspan="3">
											<a href="' , $scripturl , '?action=profile;u=' , $_GET['id_user'] , '"><img src="' , $settings["default_images_url"] , '/quiz_images/Info-24.png" border="0" alt="Details"/></a>
											<a href="' , $scripturl , '?action=pm;sa=send;u=' , $_GET['id_user'] , '"><img src="' , $settings["default_images_url"] , '/quiz_images/Chat-24.png" border="0" alt="Send PM"/></a>
										</td>
									</tr>
		';

	echo '
								</table>
							</div>
						</div>
					</div>
				</td>
				<td align="left" valign="top" width="50%">
					<div class="tborder clearfix" id="infoBoardFrame">
						<div class="title_bar">
							<h4 class="titlebg">
								<span class="left"></span>
								' , $txt['SMFQuiz_UserDetails_Page']['Statistics'] , '
							</h4>
						</div>
						<div id="upshrinkHeaderIB">
							<div class="windowbg">
								<table border="0" width="100%" class="windowbg">
	';
	foreach ($context['SMFQuiz']['memberStatistics'] as $statisticsRow)
	{
		echo '
									<tr>
										<td nowrap="nowrap"><b>' , $txt['SMFQuiz_UserDetails_Page']['TotalQuizzesPlayed'] , ':</b></td>
										<td width="100%">' , $statisticsRow['total_played'] , ' [<b><a href="' , $scripturl , '?action=SMFQuiz;sa=unplayedQuizzes;id_user=' , $context['id_user'] , '">' , $txt['SMFQuiz_UserDetails_Page']['ViewUnplayedQuizzes'] , '</a></b>]</td>
									</tr>
									<tr>
										<td nowrap="nowrap"><b>' , $txt['SMFQuiz_UserDetails_Page']['TotalWins'] , ':</b></td>
										<td align="left">' , $context['SMFQuiz']['total_user_wins'] , ' ' , $txt['SMFQuiz_Common']['Wins'] , '</td>
									</tr>
									<tr>
										<td nowrap="nowrap"><b>' , $txt['SMFQuiz_UserDetails_Page']['TotalQuestionsPlayed'] , ':</b></td>
										<td width="100%">' , $statisticsRow['total_questions'] , '</td>
									</tr>
									<tr>
										<td nowrap="nowrap"><b>' , $txt['SMFQuiz_UserDetails_Page']['TotalCorrect'] , ':</b></td>
										<td width="100%">' , $statisticsRow['total_correct'] , '</td>
									</tr>
									<tr>
										<td nowrap="nowrap"><b>' , $txt['SMFQuiz_UserDetails_Page']['TotalIncorrect'] , ':</b></td>
										<td width="100%">' , $statisticsRow['total_incorrect'] , '</td>
									</tr>
									<tr>
										<td nowrap="nowrap"><b>' , $txt['SMFQuiz_UserDetails_Page']['TotalTimeouts'] , ':</b></td>
										<td width="100%">' , $statisticsRow['total_timeouts'] , '</td>
									</tr>
									<tr>
										<td nowrap="nowrap"><b>' , $txt['SMFQuiz_UserDetails_Page']['PercentageCorrect'] , ':</b></td>
										<td width="100%">' , $statisticsRow['percentage_correct'] , '%</td>
									</tr>
		';
		if ($modSettings['SMFQuiz_showUserRating'] == 'on')
		{
			echo '
									<tr>
										<td nowrap="nowrap"><b>' , $txt['SMFQuiz_UserDetails_Page']['UserRating'] , ':</b></td>
										<td>
			';
			if ($statisticsRow['percentage_correct'] > 90)
				echo '<font color="green">' , $txt['SMFQuiz_UserRatings']['QuizMaster'] , '</font> <img src="' , $settings['default_images_url'] , '/star.gif" width="12" height="12" alt="" /><img src="' , $settings['default_images_url'] , '/star.gif" width="12" height="12" alt="" /><img src="' , $settings['default_images_url'] , '/star.gif" width="12" height="12" alt="" />';
			elseif ($statisticsRow['percentage_correct'] > 80)
				echo '<font color="green">' , $txt['SMFQuiz_UserRatings']['VeryGood'] , '</font> <img src="' , $settings['default_images_url'] , '/star.gif" width="12" height="12" alt="" /><img src="' , $settings['default_images_url'] , '/star.gif" width="12" height="12" alt="" />';
			elseif ($statisticsRow['percentage_correct'] > 60)
				echo '<font color="green">' , $txt['SMFQuiz_UserRatings']['Good'] , '</font> <img src="' , $settings['default_images_url'] , '/star.gif" width="12" height="12" alt="" />';
			elseif ($statisticsRow['percentage_correct'] > 40)
				echo '<font color="orange">' , $txt['SMFQuiz_UserRatings']['Average'] , '</font>';
			elseif ($statisticsRow['percentage_correct'] > 20)
				echo '<font color="red">' , $txt['SMFQuiz_UserRatings']['Poor'] , '</font>';
			else
				echo '<font color="red">' , $txt['SMFQuiz_UserRatings']['Dumb'] , '</font>';

			echo '							</td>
										</tr>
			';
		}
	}

	echo '
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</td>			</tr>
			<tr class="windowbg">
				<td align="left" valign="top" colspan="2">
					<div class="tborder clearfix" id="popularQuizzesFrame">
						<div class="title_bar">
							<h4 class="titlebg">
								<span class="left"></span>
								' , $txt['SMFQuiz_UserDetails_Page']['LatestQuizScores'] , '
							</h4>
						</div>
						<div id="upshrinkHeaderPQ">
							<div class="windowbg">
								<table border="0" width="100%" class="windowbg">
									<tr class="titlebg">
										<td align="left">' , $txt['SMFQuiz_Common']['Date'] , '</td>
										<td align="left">' , $txt['SMFQuiz_Common']['Quiz'] , '</td>
										<td align="center">' , $txt['SMFQuiz_Common']['Qs'] , '</td>
										<td align="center">' , $txt['SMFQuiz_Common']['Crct'] , '</td>
										<td align="center">' , $txt['SMFQuiz_Common']['Incrt'] , '</td>
										<td align="center">' , $txt['SMFQuiz_Common']['Touts'] , '</td>
										<td align="center">' , $txt['SMFQuiz_Common']['Secs'] , '</td>
										<td align="center">% ' , $txt['SMFQuiz_Common']['Correct'] , '</td>
										<td align="center">&nbsp;</td>
									</tr>
	';
	foreach ($context['SMFQuiz']['userQuizScores'] as $userScoresRow)
		echo '

									<tr>
										<td align="left">' , date("M d Y H:i", $userScoresRow['result_date']) , '</td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $userScoresRow['id_quiz'] , '">' , format_string($userScoresRow['title']) , '</a></td>
										<td align="center">' , $userScoresRow['questions'] , '</td>
										<td align="center">' , $userScoresRow['correct'] , '</td>
										<td align="center">' , $userScoresRow['incorrect'] , '</td>
										<td align="center">' , $userScoresRow['timeouts'] , '</td>
										<td align="center">' , $userScoresRow['total_seconds'] , '</td>
										<td align="center">' , $userScoresRow['percentage_correct'] , '</td>
										<td align="center">' , $userScoresRow['auto_completed'] == 1 ? '<img src="' . $settings['default_images_url'] . '/quiz_images/time.png" title="' . $txt['SMFQuiz_Common']['AutoCompleted'] . '"/>' : '&nbsp;' , '</td>
									</tr>

		';

	echo '
									<tr>
										<td colspan="8">[<a href="' , $scripturl , '?action=SMFQuiz;sa=playedQuizzes;id_user=' , $context['id_user'] , '">' , $txt['SMFQuiz_Common']['ViewAll'] , ']</a></td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</td>
			</tr>
			<tr class="windowbg">
				<td align="left" valign="top">
					<div class="tborder clearfix" id="quizMastersFrame">
						<div class="title_bar">
							<h4 class="titlebg">
								<span class="left"></span>
								' , $txt['SMFQuiz_UserDetails_Page']['ScoreChart'] , '
							</h4>
						</div>
						<div id="upshrinkHeaderQM">
							<div class="windowbg">
								<table border="0" width="100%" class="windowbg">
									<tr class="titlebg">
										<td align="left">' , $txt['SMFQuiz_Common']['Score'] , '</td>
										<td align="left">' , $txt['SMFQuiz_Common']['Percentage'] , '</td>
										<td align="left">' , $txt['SMFQuiz_Common']['No'] , '</td>
									</tr>
	';
	$maxScore = 0;
	$totalScoreCount = 0;
	$counts = array();
	foreach($context['SMFQuiz']['userCorrectScores'] as $userCorrectRow)
	{
		$totalScoreCount = $totalScoreCount + $userCorrectRow['count_correct'];
		$counts[$userCorrectRow['correct']] = $userCorrectRow['count_correct'];

		if ($userCorrectRow['correct'] > $maxScore)
			$maxScore = $userCorrectRow['correct'];
	}

	for ($counter = 0; $counter < $maxScore + 1; $counter++)
	{
		$countPercentage = 0;
		if (isset($counts[$counter]))
			$countPercentage = $counts[$counter] == null ? 0 : ($counts[$counter] / $totalScoreCount) * 100;

		echo '						<tr>
										<td width="10%">' , $counter , '</td>
										<td width="80%"><img src="' , $settings['default_images_url'] , '/quiz_images/bar_stats.png" width="' , $countPercentage , '%" height="10"/></td>
										<td width="10%">' , isset($counts[$counter]) ? $counts[$counter] : '' , '</td>
									</tr>';
	}

	echo '
								</table>
							</div>
						</div>
					</div>
				</td>
				<td align="left" valign="top">
					<div class="tborder clearfix" id="favouriteCatsFrame">
						<div class="title_bar">
							<h4 class="titlebg">
								<span class="left"></span>
								' , $txt['SMFQuiz_UserDetails_Page']['FavouriteCategories'] , '
							</h4>
						</div>
						<div id="upshrinkHeaderFC">
							<div class="windowbg">
								<table border="0" width="100%" class="windowbg">
									<tr class="titlebg">
										<td align="left">' , $txt['SMFQuiz_Common']['Score'] , '</td>
										<td align="left">' , $txt['SMFQuiz_Common']['Percentage'] , '</td>
										<td align="left">' , $txt['SMFQuiz_Common']['No'] , '</td>
									</tr>
	';
	// TODO: Need to revisit this, needs to be improved
	$maxPlays = 0;
	$totalPlaysCount = 0;
	$counts = array();
	$names = array();
	$ids = array();
	$counter1 = 0;
	foreach ($context['SMFQuiz']['userCategoryPlays'] as $userCategoryPlaysRow)
	{
		$totalPlaysCount = $totalPlaysCount + $userCategoryPlaysRow['category_plays'];
		$counts[$userCategoryPlaysRow['name']] = $userCategoryPlaysRow['category_plays'];
		$counts[$counter1] = $userCategoryPlaysRow['category_plays'];
		$names[$counter1] = $userCategoryPlaysRow['name'];
		$ids[$counter1] = $userCategoryPlaysRow['id_category'];

		if ($userCategoryPlaysRow['category_plays'] > $maxPlays)
			$maxPlays = $userCategoryPlaysRow['category_plays'];

		$counter1++;
	}
	for ($counter = 0; $counter < $counter1; $counter++)
	{
		$countPercentage = $counts[$counter] == null ? 0 : ($counts[$counter] / $totalPlaysCount) * 100;

		echo '						<tr>
										<td width="10%"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;categoryId=' , $ids[$counter] , '">' , $names[$counter] , '</a></td>
										<td width="80%"><img src="' , $settings['default_images_url'] , '/quiz_images/bar_stats.png" width="' , $countPercentage , '%" height="10"/></td>
										<td width="10%">' , $counts[$counter] , '</td>
									</tr>';
	}
	echo '
								</table>
							</div>
						</div>
					</div>
				</td>
			</tr>
		</table>
	';

}

// Template that provides the category dropdown
function template_category_dropdown($selectedCategoryId, $identifier)
{
	global $context;

	if (empty($selectedCategoryId))
		$selectedCategoryId = -1;

	echo '<select name="' , $identifier , '">';
	foreach ($context['SMFQuiz']['categories'] as $row)
	{
		if ($selectedCategoryId == $row['id_category'])
			echo '<option value="' , $row['id_category'] , '" selected="selected">' , $row['name'] , '</option>';
		else
			echo '<option value="' , $row['id_category'] , '">' , $row['name'] , '</option>';
	}
	echo '</select>';
}

function template_questions()
{
	global $context;

	// Although we know we are in the category section, we need to see what action is being taken to determine which template to
	// show in the category section
	switch ($context['SMFQuiz']['Action'])
	{
		case 'SaveQuestion' :
		case 'NewQuestion' :
			template_new_question();
			break;

		case 'EditQuestion' :
			template_edit_question();
			break;

		default:
			template_show_questions();
	}
}

function template_new_question()
{
	global $context, $scripturl, $txt;

	echo '
			<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<input type="hidden" name="id_quiz" value="' , $context['id_quiz'] , '"/>
			<tr class="titlebg">
				<td align="left" colspan="2">' , $txt['SMFQuiz_NewQuestion_Page']['Title'] , '</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionText'] , ':</b></td>
				<td align="left"><input type="text" name="question_text" id="question_text" maxlength="400" size="90"/></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionType'] , ':</b></td>
				<td align="left">' , template_question_type_dropdown('changeQuestionType(this)') , '</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['Quiz'] , ':</b></td>
				<td align="left">' , template_quiz_dropdown(isset($context['id_quiz']) ? $context['id_quiz'] : -1) , '</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['AnswerText'] , ':</b></td>
				<td align="left"><textarea name="answer_text" cols="70" rows="5"></textarea></td>
			</tr>
			<tr class="windowbg">
				<td align="left" valign="top"><b>' , $txt['SMFQuiz_Common']['Answer'] , ':</b></td>
				<td align="left">
					<div id="freeTextAnswerdiv" style="display:none">
						<input type="text" name="freeTextAnswer" id="freeTextAnswer" size="50" maxlength="400"/>
					</div>

					<div id="multipleChoiceAnswer" style="display:block">
						<table id="answerTable">
							<tbody>
								<tr>
									<td><span class="SmallText">' , $txt['SMFQuiz_Common']['Correct'] , '</span></td>
									<td><span class="SmallText">' , $txt['SMFQuiz_Common']['Answer'] , '</span></td>
								</tr>
								<tr>
									<td><input type="radio" name="correctAnswer" value="1" checked="checked"/></td>
									<td><input type="text" name="answer1" size="50"/></td>
								</tr>
								<tr>
									<td><input type="radio" name="correctAnswer" value="2"/></td>
									<td><input type="text" name="answer2" size="50"/></td>
								</tr>
								<tr>
									<td><input type="radio" name="correctAnswer" value="3"/></td>
									<td><input type="text" name="answer3" size="50"/></td>
								</tr>
								<tr>
									<td><input type="radio" name="correctAnswer" value="4"/></td>
									<td><input type="text" name="answer4" size="50"/></td>
								</tr>
							</tbody>
						</table>
						<input type="button" value="' , $txt['SMFQuiz_Common']['AddRow'] , '" onclick="addRow()"/>
						<input type="button" value="' , $txt['SMFQuiz_Common']['DeleteRow'] , '" onclick="deleteRow()"/>
					</div>

					<div id="trueFalseAnswer" style="display:none">
						<input type="radio" name="trueFalseAnswer" value="true" checked="checked"></option> ' , $txt['SMFQuiz_Common']['True'] , '
						<br/><input type="radio" name="trueFalseAnswer" value="false"></option> ' , $txt['SMFQuiz_Common']['False'] , '
					</div>
				</td>
			</tr>
			<tr class="windowbg">
				<td colspan="7" align="left">
					<input type="button" name="SaveQuestion" value="' , $txt['SMFQuiz_Common']['SaveQuestion'] , '" onClick="validateQuestion(this.form, \'saveQuestion\')"/>
					<input type="button" name="SaveAndAddMore" value="' , $txt['SMFQuiz_Common']['SaveAndAddMore'] , '" onClick="validateQuestion(this.form, \'saveQuestionAndAddMore\')">
					<input type="button" name="Done" value="' , $txt['SMFQuiz_Common']['Done'] , '" onclick="window.location = \'' , $scripturl , '?action=' , $context['current_action'] , ';sa=quizQuestions;id_quiz=' , $context['id_quiz'] , '\';"/>
				</td>
			</tr>
		</tbody>
	</table>
	';
}

function template_question_type_dropdown($onChange = null)
{
	global $context, $smcFunc;

	if (sizeof($context['SMFQuiz']['questionTypes']) > 0)
	{
		if ($onChange == null)
			echo '<select name="id_question_type" id="id_question_type">';
		else
			echo '<select name="id_question_type" id="id_question_type" onChange="' , $onChange , '">';

		foreach ($context['SMFQuiz']['questionTypes'] as $row)
		{
			// Make multiple choice default
			if ($row['id_question_type'] == 1)
				echo '<option value="' , $row['id_question_type'] , '" selected="selected">' , format_string($row['description']) , '</option>';
			else
				echo '<option value="' , $row['id_question_type'] , '">' , format_string($row['description']) , '</option>';
		}
		echo '</select>';
	}
}

// Template that provides the quiz dropdown
function template_quiz_dropdown($selectedId = -1)
{
	global $context, $smcFunc;

	if (sizeof($context['SMFQuiz']['userQuizzes']) > 0)
	{
		echo '<select name="id_quiz">';
		foreach ($context['SMFQuiz']['userQuizzes'] as $row)
		{
			if ($selectedId == $row['id_quiz'])
				echo '<option value="' , $row['id_quiz'] , '" selected="selected">' , format_string($row['title']) , '</option>';
			else
				echo '<option value="' , $row['id_quiz'] , '">' , format_string($row['title']) , '</option>';
		}
		echo '</select>';
	}
}

function template_edit_quiz()
{
	global $context, $txt, $smcFunc;

	foreach ($context['SMFQuiz']['quiz'] as $row)
	{
		echo '
			<input type="hidden" name="id_quiz" value="' , $context['id_quiz'] , '"/>
			<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td align="left" colspan="2">' , $txt['SMFQuiz_EditQuiz_Page']['Title'] , '</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['Title'] , ':</b></td>
				<td align="left"><input type="text" name="title" id="title" maxlength="400" size="50" value="' , format_string($row['title'], false) , '"/></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['Category'] , ':</b></td>
				<td align="left"><input type="hidden" name="oldCategoryId" value="' , $row['id_category'] , '"/>' , template_category_dropdown($row['id_category'], 'id_category') , '</td>
			</td>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['Description'] , ':</b></td>
				<td align="left"><textarea name="description" cols="50" rows="5">' , format_string($row['description'], false) , '</textarea></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageURL'] , ':</b></td>
				<td align="left">' , template_quiz_image_dropdown('', $row['image']) , '</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['PlayLimit'] , ':</b></td>
				<td align="left"><input name="limit" type="text" size="5" maxlength="5" value="' , $row['play_limit'] , '"/></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['SecondsPerQuestion'] , ':</b></td>
				<td align="left"><input name="seconds" type="text" size="5" maxlength="5" value="' , $row['seconds_per_question'] , '"/></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['ShowAnswers'] , ':</b></td>
				<td align="left"><input type="checkbox" name="showanswers"' , $row['show_answers'] == 1 ? ' checked="checked"' : '' , '/></td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['LastUpdated'] , ':</b></td>
				<td align="left">' , date("F j, Y, g:i a", $row['updated']) , ' </td>
			</tr>
			<tr class="windowbg" valign="top">
				<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionsPlayed'] , ':</b></td>
				<td align="left">' , $row['question_plays'] , ' </td>
			</tr>
			<tr class="windowbg">
				<td colspan="7" align="left">
					<input type="button" name="UpdateQuiz" value="' , $txt['SMFQuiz_Common']['UpdateQuiz'] , '" onclick="validateQuiz(this.form, \'updateQuiz\')"/>
					<input type="button" name="UpdateQuizAndAddQuestions" value="' , $txt['SMFQuiz_Common']['UpdateQuizAndAddQuestions'] , '" onclick="validateQuiz(this.form, \'updateQuizAndAddQuestions\')"/>
					<input type="button" name="QuizQuestions" value="' , $txt['SMFQuiz_Common']['QuizQuestions'] , '" onclick="this.form.formaction.value=\'quizQuestions\';this.form.submit();"/>
					<input type="button" name="QuizAction" value="' , $txt['SMFQuiz_Common']['Preview'] , '" onclick="this.form.formaction.value=\'preview\';this.form.submit();"/>
				</td>
			</tr>
		</tbody>
	</table>
		';
	}
}

function template_edit_question()
{
	global $context, $scripturl, $txt, $smcFunc;

	foreach ($context['SMFQuiz']['questions'] as $row)
	{
		// Only the creator should be able to edit the question
		if ($context['user']['id'] != $row['creator_id'])
			break;

		echo '
					<input type="hidden" name="questionId" value="' , $_GET['questionId'] , '"/>
					<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
						<tr class="titlebg">
							<td align="left" colspan="2">' , $txt['SMFQuiz_EditQuestion_Page']['Title'] , '</td>
						</tr>
						<tr class="windowbg" valign="top">
							<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionText'] , ':</b></td>
							<td align="left"><input type="text" name="question_text" id="question_text" maxlength="400" size="90" value="' , format_string($row['question_text'], false) , '"/></td>
						</tr>
						<tr class="windowbg" valign="top">
							<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionType'] , ':</b></td>
							<td align="left"><input type="hidden" name="id_question_type" id="id_question_type" value="' , $row['id_question_type'] , '"/>' , $row['question_type'] , '</td>
						</tr>
						<tr class="windowbg" valign="top">
							<td align="left"><b>' , $txt['SMFQuiz_Common']['Quiz'] , ':</b></td>
							<td align="left"><input type="hidden" name="id_quiz" value="' , $row['id_quiz'] , '"/>' , format_string($row['quiz_title']) , '</td>
						</tr>
						<tr class="windowbg" valign="top">
							<td align="left"><b>' , $txt['SMFQuiz_Common']['AnswerText'] , ':</b></td>
							<td align="left"><textarea name="answer_text" cols="70" rows="5">' , format_string($row['answer_text'], false) , '</textarea></td>
						</tr>
						<tr class="windowbg">
							<td align="left" valign="top"><b>' , $txt['SMFQuiz_Common']['Answer'] , ':</b></td>
							<td align="left">
		';

		if ($row['id_question_type'] == 1)
		{ // Multiple Choice
			echo '				<div id="multipleChoiceAnswer" style="display:block">
									<table id="answerTable">
										<tbody>
			';

			foreach ($context['SMFQuiz']['answers'] as $answerRow)
				echo '
											<tr>
												<td><input type="radio" name="correctAnswer" value="' , $answerRow['id_answer'] , '"' , $answerRow['is_correct'] ? ' checked="checked"' : '' , '/></td>
												<td><input type="text" name="answer' , $answerRow['id_answer'] , '" size="50" value="', format_string($answerRow['answer_text']) , '"/></td>
											</tr>
				';

			echo '						</tbody>
									</table>
									<input type="button" value="Add Row" onclick="addRow()"/>
									<input type="button" value="Delete Row" onclick="deleteRow()"/>
								</div>
			';
		}
		elseif ($row['id_question_type'] == 2)
		{ // Free Text
			foreach ($context['SMFQuiz']['answers'] as $answerRow)
				echo '
								<input type="hidden" name="id_answer" value="' , $answerRow['id_answer'] , '"/>
								<input type="text" name="freeTextAnswer" id="freeTextAnswer" size="50" maxlength="400" value="' , format_string($answerRow['answer_text']) , '"/>
				';
		}
		else
		{ // True False
			foreach($context['SMFQuiz']['answers'] as $answerRow)
				echo '
								<input type="radio" name="trueFalseAnswer" value="' , $answerRow['id_answer'] , '"' , $answerRow['is_correct'] == 1 ? ' checked="checked"' : '' , '></option> ' , format_string($answerRow['answer_text']) , '<br/>
								<input type="hidden" id="id_answer' , $answerRow['id_answer'] , '" name="id_answer' , $answerRow['id_answer'] , '" value="' , format_string($answerRow['answer_text']) ,'"/>
				';
		}
		echo '
							</td>
						</tr>
						<tr class="windowbg" valign="top">
							<td align="left"><b>' , $txt['SMFQuiz_Common']['LastUpdated'] , ':</b></td>
							<td align="left">' , date("F j, Y, g:i a", $row['updated']) , ' </td>
						</tr>
						<tr class="windowbg">
							<td colspan="7" align="left">
								<input type="button" name="UpdateQuestion" value="' , $txt['SMFQuiz_Common']['UpdateQuestion'] , '" onClick="validateQuestion(this.form, \'updateQuestion\')"/>
								<input type="button" name="UpdateAndAddMore" value="' , $txt['SMFQuiz_Common']['UpdateAndAddMore'] , '" onClick="validateQuestion(this.form, \'updateQuestionAndAddMore\')"/>
								<input type="button" name="Done" value="' , $txt['SMFQuiz_Common']['Done'] , '" onclick="window.location = \'' , $scripturl , '?action=' , $context['current_action'] , ';sa=quizQuestions;id_quiz=' , $context['id_quiz'] , '\';"/>
							</td>
						</tr>
					</table>
		';
	}
}


function template_show_questions()
{
	global $txt, $context, $settings, $scripturl, $txt, $smcFunc;

	$pageCount = 0;
	foreach ($context['SMFQuiz']['questionCount'] as $row)
	{
		$questionCount = $row['question_count'];
		$pageCount = ceil($questionCount / 20);
	}

	echo '
						<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
							<tbody>
								<tr class="titlebg">
									<td align="center"><input type="checkbox" name="chkAll" onclick="checkAll(this.form, this.form.chkAll.checked);"/></td>
									<td align="left"><a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=' , $context['SMFQuiz']['page'] , ';orderBy=Question;orderDir=' , $context['SMFQuiz']['orderDir'] , ';id_quiz=' , $context['id_quiz'] , '">', $context['SMFQuiz']['orderBy'] == 'Question' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['SMFQuiz']['orderDir'] . '.gif"/> ' : '' , '' , $txt['SMFQuiz_Common']['Question'] , '</a></b></td>
									<td align="left"><a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=' , $context['SMFQuiz']['page'] , ';orderBy=Type;orderDir=' , $context['SMFQuiz']['orderDir'] , ';id_quiz=' , $context['id_quiz'] , '">', $context['SMFQuiz']['orderBy'] == 'Type' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['SMFQuiz']['orderDir'] . '.gif"/> ' : '' , '' , $txt['SMFQuiz_Common']['Type'] , '</a></b></td>
									<td align="left"><a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=' , $context['SMFQuiz']['page'] , ';orderBy=Quiz;orderDir=' , $context['SMFQuiz']['orderDir'] , ';id_quiz=' , $context['id_quiz'] , '">', $context['SMFQuiz']['orderBy'] == 'Quiz' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['SMFQuiz']['orderDir'] . '.gif"/> ' : '' , '' , $txt['SMFQuiz_Common']['Quiz'] , '</a></b></td>
								</tr>
	';

	echo '
									<tr class="windowbg">
										<td colspan="8">
											<input type="hidden" name="id_quiz" value="' , $context['id_quiz'] , '"/>
											<b>' , $txt['SMFQuiz_Common']['Page'] , ':</b> ' , $context['SMFQuiz']['page'] , '/' , $pageCount , ' ';
	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=1;orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $context['SMFQuiz']['orderDir'] == 'up' ? 'down' : 'up' , ';id_quiz=' , $context['id_quiz'] , '"><<</a> ';

	if ($context['SMFQuiz']['page'] > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=' , $context['SMFQuiz']['page'] - 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $context['SMFQuiz']['orderDir'] == 'up' ? 'down' : 'up' , ';id_quiz=' , $context['id_quiz'] , '"><</a> ';

	if ($context['SMFQuiz']['page'] < $pageCount)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=' , $context['SMFQuiz']['page'] + 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $context['SMFQuiz']['orderDir'] == 'up' ? 'down' : 'up' , ';id_quiz=' , $context['id_quiz'] , '">></a> ';

	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=' , $pageCount , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $context['SMFQuiz']['orderDir'] == 'up' ? 'down' : 'up' , ';id_quiz=' , $context['id_quiz'] , '">>></a>';

	echo '
										</td>
									</tr>
	';

	if (sizeof($context['SMFQuiz']['questions']) > 0)
	{
		foreach($context['SMFQuiz']['questions'] as $row)
			echo '					<tr class="windowbg">
										<td align="center" width="8%"><input type="checkbox" name="question' , $row['id_question'] , '"/></td>
										<td align="left">' , format_string($row['question_text']) , ' [<a href="', $scripturl, '?action=' . $context['current_action'] . ';sa=' . $context['current_subaction'] . ';questionId=' , $row['id_question'] , ';id_quiz=' , $context['id_quiz'] , '">edit</a>]</td>
										<td align="left">' , $row['question_type'] , '</td>
										<td align="left">' , $row['quiz_title'] == 'None Assigned' ? '<font color="red">' . $txt['SMFQuiz_Common']['NoneAssigned'] . '</font>' :  format_string($row['quiz_title']) , '</td>
									</tr>';
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="9" align="left">' , $txt['SMFQuiz_QuizQuestions_Page']['NoQuestions'] , '</td></tr>';

	echo '
									<tr class="windowbg">
										<td colspan="8">
											<b>' , $txt['SMFQuiz_Common']['Page'] , ':</b> ' , $context['SMFQuiz']['page'] , '/' , $pageCount , ' ';
	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=1;orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $context['SMFQuiz']['orderDir'] == 'up' ? 'down' : 'up' , ';id_quiz=' , $context['id_quiz'] , '"><<</a> ';

	if ($context['SMFQuiz']['page'] > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=' , $context['SMFQuiz']['page'] - 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $context['SMFQuiz']['orderDir'] == 'up' ? 'down' : 'up' , ';id_quiz=' , $context['id_quiz'] , '"><</a> ';

	if ($context['SMFQuiz']['page'] < $pageCount)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=' , $context['SMFQuiz']['page'] + 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $context['SMFQuiz']['orderDir'] == 'up' ? 'down' : 'up' , ';id_quiz=' , $context['id_quiz'] , '">></a> ';

	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';sa=' , $context['current_subaction'] , ';page=' , $pageCount , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $context['SMFQuiz']['orderDir'] == 'up' ? 'down' : 'up' , ';id_quiz=' , $context['id_quiz'] , '">>></a>';

	echo '
										</td>
									</tr>
									<tr class="windowbg">
										<td colspan="8" align="left">
											<input type="button" name="NewQuestion" value="' , $txt['SMFQuiz_Common']['NewQuestion'] , '" onclick="window.location = \'' , $scripturl , '?action=' , $context['current_action'] , ';sa=newQuestion;id_quiz=' , $context['id_quiz'] , '\';"/>
											<input type="button" name="DeleteQuestion" value="' , $txt['SMFQuiz_Common']['DeleteQuestion'] , '" onclick="this.form.formaction.value=\'deleteQuestion\';this.form.submit();"/>
										</td>
									</tr>
							</tbody>
						</table>
	';
}

function template_quiz_scores()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

			// @TODO should go Source-side?
	$id_quiz = isset($_GET['id_quiz']) ? $_GET['id_quiz'] : 0;

	if (empty($context['SMFQuiz']['quiz_results']))
	{
		echo '
			<div class="windowbg">There are no Quiz Results	</div>';

			return;
	}

	echo '
		<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
			<tbody>
				<tr class="titlebg">
					<td colspan="8">', $context['SMFQuiz']['quiz_title'] , '</td>
				</tr>
				<tr class="', empty($settings['use_tabs']) ? 'titlebg' : 'catbg3', '">
	';

	// Display each of the column headers of the table.
	foreach ($context['columns'] as $column)
	{
		// We're not able (through the template) to sort the search results right now...
		if (isset($context['old_search']))
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['label'], '</td>';
		// This is a selected solumn, so underline it or some such.
		elseif ($column['selected'])
            echo '
            <td style="width: auto;"' . (isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '') . ' nowrap="nowrap">
                <a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <span class="main_icons sort_' . (!empty($context['sort_direction']) ? $context['sort_direction'] : 'down') . '"></span></a></td>';		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '</tr>';

		foreach($context['SMFQuiz']['quiz_results'] as $row)
			echo '					<tr class="windowbg">
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $row['id_user'] , '">' , $row['real_name'] , '</a></td>
										<td align="left">' , date("M d Y H:i", $row['result_date']) , '</td>
										<td align="center">' , $row['questions'] , '</td>
										<td align="center">' , $row['correct'] , '</td>
										<td align="center">' , $row['incorrect'] , '</td>
										<td align="center">' , $row['timeouts'] , '</td>
										<td align="center">' , $row['total_seconds'] , '</td>
										<td align="center">' , $row['auto_completed'] == 1 ? '<img src="' . $settings['default_images_url'] . '/quiz_images/time.png" title="' . $txt['SMFQuiz_Common']['AutoCompleted'] . '"/>' : '&nbsp;' , '</td>
									</tr>';


	echo '
							</tbody>
						</table>
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td>', $txt['pages'], ': ', $context['page_index'], '</td>
							</tr>
							<tr>
								<td>[<a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $id_quiz , '">', $txt['SMFQuiz_Common']['Back'] , '</a>]</td>
							</td>
						</table>
	';
}

			// @TODO createList?
function template_show_quizzes()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'all';

	echo '
		<div class="cat_bar">
			<h4 class="catbg">
				<span class="floatleft">
	';

	// Title to show depends on the quiz listing type we are showing
	switch ($type)
	{
		case 'unplayed':
			echo $txt['SMFQuiz_Common']['UnplayedQuizzes'];
			break;
		case 'all':
			echo $txt['SMFQuiz_Common']['AllQuizzes'];
			break;
		case 'new':
			echo $txt['SMFQuiz_Home_Page']['NewQuizzes'];
			break;
		case 'popular':
			echo $txt['SMFQuiz_Home_Page']['PopularQuizzes'];
			break;
		case 'easiest':
			echo $txt['SMFQuiz_Common']['EasiestQuizzes'];
			break;
		case 'hardest':
			echo $txt['SMFQuiz_Common']['HardestQuizzes'];
			break;
	}
	echo '</span>
				<span class="floatright">', $context['letter_links'] . '</span>
			</h4>
		</div>

		<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
			<tbody>
				<tr class="', empty($settings['use_tabs']) ? 'titlebg' : 'catbg3', '">
	';

	// Display each of the column headers of the table.
	foreach ($context['columns'] as $column)
	{
		// We're not able (through the template) to sort the search results right now...
		if (isset($context['old_search']))
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['label'], '</td>';
		// This is a selected solumn, so underline it or some such.
		elseif ($column['selected'])
            echo '
            <td style="width: auto;"' . (isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '') . ' nowrap="nowrap">
                <a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <span class="main_icons sort_' . (!empty($context['sort_direction']) ? $context['sort_direction'] : 'down') . '"></span></a></td>';		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '</tr>';

	if (sizeof($context['SMFQuiz']['quizzes']) > 0)
	{
		foreach ($context['SMFQuiz']['quizzes'] as $row)
		{
			echo '					<tr class="windowbg">
										<td><img width="25" height="25" src="' , !empty($row['image']) ? $settings["default_images_url"] . '/quiz_images/Quizzes/' . $row['image'] : $settings["default_images_url"] . '/quiz_images/Quizzes/Default-64.png' , '"/></td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $row['id_quiz'] , '">' , format_string($row['title']) , '</a></td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $row['creator_id'] , '">' , $row['real_name'] , '</a></td>
										<td align="left">' , format_string($row['description'], false) , '</td>
										<td align="left">' , format_string($row['category_name']) , '</td>
										<td align="center">' , $row['play_limit'] , '</td>
										<td align="center">' , $row['questions_per_session'] , '</td>
										<td align="center">' , $row['seconds_per_question'] , '</td>
										<td align="center">
			';

			switch ($type)
			{
				case 'unplayed':
					break;
				case 'all':
					break;
				case 'new':
					echo date("M d Y H:i", $row['updated']);
					break;
				case 'popular':
					echo $row['quiz_plays'];
					break;
				case 'easiest':
				case 'hardest':
					echo empty($row['percentage_correct']) ? $txt['SMFQuiz_Common']['NotPlayed'] : $row['percentage_correct'] . '%';
					break;
			}

			echo '
										</td>
									</tr>
			';
		}
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="10" align="left">', $txt['quiz_xml_error_no_quizzes'], '</td></tr>';

	echo '
							</tbody>
						</table>
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td>', $txt['pages'], ': ', $context['page_index'], '</td>
							</tr>
						</table>
	';
}


function template_played_quizzes()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

	echo '
		<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
			<tbody>
				<tr class="titlebg">
					<td colspan="3">' , $txt['SMFQuiz_Common']['PlayedQuizzes'] , '</td>
					<td colspan="7" align="right">', $context['letter_links'] , '</td>
				</tr>
				<tr class="', empty($settings['use_tabs']) ? 'titlebg' : 'catbg3', '">
	';

	// Display each of the column headers of the table.
	foreach ($context['columns'] as $column)
	{
		// We're not able (through the template) to sort the search results right now...
		if (isset($context['old_search']))
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['label'], '</td>';
		// This is a selected solumn, so underline it or some such.
		elseif ($column['selected'])
            echo '
            <td style="width: auto;"' . (isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '') . ' nowrap="nowrap">
                <a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <span class="main_icons sort_' . (!empty($context['sort_direction']) ? $context['sort_direction'] : 'down') . '"></span></a></td>';		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '</tr>';

	if (sizeof($context['SMFQuiz']['quizzes']) > 0)
	{
		foreach($context['SMFQuiz']['quizzes'] as $row)
			echo '					<tr class="windowbg">
										<td><img width="25" height="25" src="' , !empty($row['image']) ? $settings["default_images_url"] . '/quiz_images/Quizzes/' . $row['image'] : $settings["default_images_url"] . '/quiz_images/Quizzes/Default-64.png' , '"/></td>
										<td align="left">' , $row['top_score'] == 1 ? '<img src="' . $settings['default_images_url'] . '/quiz_images/cup_g.gif"/>' : '' , date("M d Y H:i", $row['result_date']) , '</td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $row['id_quiz'] , '">' , format_string($row['title']) , '</a></td>
										<td align="center">' , $row['questions'] , '</td>
										<td align="center">' , $row['correct'] , '</td>
										<td align="center">' , $row['incorrect'] , '</td>
										<td align="center">' , $row['timeouts'] , '</td>
										<td align="center">' , $row['total_seconds'] , '</td>
										<td align="center">' , $row['percentage_correct'] , '</td>
										<td align="center">' , $row['auto_completed'] == 1 ? '<img src="' . $settings['default_images_url'] . '/quiz_images/time.png" title="' . $txt['SMFQuiz_Common']['AutoCompleted'] . '"/>' : '&nbsp;' , '</td>
									</tr>';
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="10" align="left">' , $txt['SMFQuiz_Common']['Noquizzesplayedwiththisfilter'] , '</td></tr>';

	echo '
							</tbody>
						</table>
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td>', $txt['pages'], ': ', $context['page_index'], '</td>
							</tr>
						</table>
	';
}

function template_quiz_masters()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

	echo '
		<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
			<tbody>
				<tr class="titlebg">
					<td colspan="7">', $txt['SMFQuiz_Common']['QuizMasters'] , '</td>
				</tr>
				<tr class="', empty($settings['use_tabs']) ? 'titlebg' : 'catbg3', '">
	';

	// Display each of the column headers of the table.
	foreach ($context['columns'] as $column)
	{
		// We're not able (through the template) to sort the search results right now...
		if (isset($context['old_search']))
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['label'], '</td>';
		// This is a selected solumn, so underline it or some such.
		elseif ($column['selected'])
            echo '
            <td style="width: auto;"' . (isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '') . ' nowrap="nowrap">
                <a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <span class="main_icons sort_' . (!empty($context['sort_direction']) ? $context['sort_direction'] : 'down') . '"></span></a></td>';		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '</tr>';

	if (sizeof($context['SMFQuiz']['quiz_masters']) > 0)
	{
		foreach($context['SMFQuiz']['quiz_masters'] as $row)
			echo '					<tr class="windowbg">
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $row['id_user'] , '">' , $row['real_name'] , '</a></td>
										<td align="center">' , $row['total_wins'] , '</td>
									</tr>';
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="10" align="left">There are no Quiz Masters</td></tr>';

	echo '
							</tbody>
						</table>
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td>', $txt['pages'], ': ', $context['page_index'], '</td>
							</tr>
						</table>
	';
}

// @TODO createList?
function template_quiz_league_table()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

	$id_quiz_league = isset($_GET['id_quiz_league']) ? $_GET['id_quiz_league'] : 0;

	echo '
<div style="margin-top:2px; margin-bottom:4px; overflow:hidden;">
	<div class="title_bar">
		<h4 class="titlebg">
			<span class="left"></span>
			', isset($context['SMFQuiz']['quiz_league_title']) ? $context['SMFQuiz']['quiz_league_title'] : '' , '
		</h4>
	</div>
	<div class="blockcontent windowbg" style="margin-top:2px; ">
		<div style="padding:4px;">
					<div class="windowbg">
		<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
			<tbody>
				<tr class="', empty($settings['use_tabs']) ? 'titlebg' : 'catbg3', '">
	';

	// Display each of the column headers of the table.
	foreach ($context['columns'] as $column)
	{
		// We're not able (through the template) to sort the search results right now...
		if (isset($context['old_search']))
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['label'], '</td>';
		// This is a selected solumn, so underline it or some such.
		elseif ($column['selected'])
            echo '
            <td style="width: auto;"' . (isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '') . ' nowrap="nowrap">
                <a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <span class="main_icons sort_' . (!empty($context['sort_direction']) ? $context['sort_direction'] : 'down') . '"></span></a></td>';		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '</tr>';

	if (sizeof($context['SMFQuiz']['quiz_league_table']) > 0)
	{
		foreach ($context['SMFQuiz']['quiz_league_table'] as $row)
		{
			echo '					<tr class="windowbg">
										<td align="center">';
			switch ($row['current_position'])
			{
				case 1:
					echo '<img src="' . $settings['default_images_url'] . '/quiz_images/cup_g.gif"/> ';
					break;
				case 2:
					echo '<img src="' . $settings['default_images_url'] . '/quiz_images/cup_s.gif"/> ';
					break;
				case 3:
					echo '<img src="' . $settings['default_images_url'] . '/quiz_images/cup_b.gif"/> ';
					break;
				default:
					break;
			}
			echo $row['current_position'] , '</td>
										<td align="center">
			';
			if ($row['pos_move'] == 0 || $row['plays'] < 2)
				echo '-';
			elseif ($row['pos_move'] > 0)
				echo '<font color="green">+' , $row['pos_move'] , '</font>';
			else
				echo '<font color="red">' , $row['pos_move'] , '</font>';

			echo '						</td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $row['id_user'] , '">' , $row['real_name'] , '</a></td>
										<td align="center">' , $row['plays'] , '</td>
										<td align="center">' , $row['correct'] , '</td>
										<td align="center">' , $row['incorrect'] , '</td>
										<td align="center">' , $row['timeouts'] , '</td>
										<td align="center">' , $row['seconds'] , '</td>
										<td align="center">' , $row['points'] , '</td>
									</tr>';
		}
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="10" align="left">There are no Quiz League Results</td></tr>';

	echo '
							</tbody>
						</table>
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td>', $txt['pages'], ': ', $context['page_index'], '</td>
							</tr>
							<tr>
								<td>[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizleagues;id=' , $id_quiz_league , '">', $txt['SMFQuiz_Common']['Back'] , '</a>]</td>
							</td>
						</table>
					</div>
				</div>
			</div>
	';
}

function template_quiz_league_results()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

// @TODO move source side
	$id_quiz_league = isset($_GET['id_quiz_league']) ? $_GET['id_quiz_league'] : 0;

	echo '
		<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
			<tbody>
				<tr>
					<td colspan="8">
						<div class="title_bar">
							<h4 class="titlebg">
								<span class="left"></span>
								' , $context['SMFQuiz']['quiz_league_title'] , '
							</h4>
						</div>
					</td>
				</tr>
				<tr class="', empty($settings['use_tabs']) ? 'titlebg' : 'catbg3', '">
	';

	// Display each of the column headers of the table.
	foreach ($context['columns'] as $column)
	{
		// We're not able (through the template) to sort the search results right now...
		if (isset($context['old_search']))
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['label'], '</td>';
		// This is a selected solumn, so underline it or some such.
		elseif ($column['selected'])
            echo '
            <td style="width: auto;"' . (isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '') . ' nowrap="nowrap">
                <a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <span class="main_icons sort_' . (!empty($context['sort_direction']) ? $context['sort_direction'] : 'down') . '"></span></a></td>';		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '</tr>';

	if (sizeof($context['SMFQuiz']['quiz_league_results']) > 0)
	{
		foreach($context['SMFQuiz']['quiz_league_results'] as $row)
			echo '					<tr class="windowbg">
										<td align="left">' , date("M d Y H:i", $row['result_date']) , '</td>
										<td align="left">' , $row['round'] , '</td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $row['id_user'] , '">' , $row['real_name'] , '</a></td>
										<td align="left">' , $row['correct'] , '</td>
										<td align="left">' , $row['incorrect'] , '</td>
										<td align="left">' , $row['timeouts'] , '</td>
										<td align="left">' , $row['seconds'] , '</td>
										<td align="left">' , $row['points'] , '</td>
									</tr>';
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="10" align="left">There are no Quiz League Results</td></tr>';

	echo '
							</tbody>
						</table>
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td>', $txt['pages'], ': ', $context['page_index'], '</td>
							</tr>
							<tr>
								<td>[<a href="' , $scripturl , '?action=SMFQuiz;sa=quizleagues;id=' , $id_quiz_league , '">', $txt['SMFQuiz_Common']['Back'] , '</a>]</td>
							</td>
						</table>
	';
}

function template_preview_quiz()
{
	global $txt, $context, $settings;

	echo '
		<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
			<tbody>
				<tr class="titlebg">
					<td colspan="2">', $txt['SMFQuiz_Common']['QuizPreview'] , '</td>
				</tr>
	';
	foreach ($context['SMFQuiz']['quiz'] as $row)
		echo '
				<tr class="windowbg">
					<td><b>', $txt['SMFQuiz_Common']['Quiz'] , ':</b></td>
					<td width="100%"><img width="25" height="25" src="' , !empty($row['image']) ? $settings["default_images_url"] . '/quiz_images/Quizzes/' . $row['image'] : $settings["default_images_url"] . '/quiz_images/Quizzes/Default-64.png' , '"/> ' , $row['title'] , '</td>
				</tr>
		';

	if (!isset($context['SMFQuiz']['questions']))
		echo '	<tr class="windowbg"><td colspan="2"><font color="red">No questions have been added</font></td></tr>';
	else
	{
		$lastQuestion = 0;
		foreach ($context['SMFQuiz']['questions'] as $row)
		{
			if ($row['id_question'] != $lastQuestion)
			{
				$count = 1;
				echo '
					<tr class="windowbg">
						<td width="100%" colspan="2"><b>' , format_string($row['question_text']) , '</b></td>
					</tr>
					<tr class="windowbg">
						<td width="100%" colspan="2"><i>' , format_string($row['question_answer_text']) , '</i></td>
					</tr>
				';
			}
			echo '
				<tr class="windowbg"><td colspan="2">', $row['is_correct'] == 1 ? '<font color="red">' : '' , $count , '.', format_string($row['answer_text']) , $row['is_correct'] == 1 ? '</font>' : '' , '</td></tr>
			';
			$count++;
			$lastQuestion = $row['id_question'];
		}
	}
	echo '
			</tbody>
		</table>
	';
}

// @TODO source-side?
function format_string($stringToFormat, $toHtml = true)
{
	global $smcFunc;

	// Remove any slashes. These should not be here, but it has been known to happen
	$returnString = str_replace("\\", "", $smcFunc['db_unescape_string']($stringToFormat));

	// We only want to convert from carriage returns to HTML breaks if the output is HTML
	if ($toHtml)
		$returnString = str_replace(chr(13), "<br/>", $returnString);

	//return html_entity_decode($returnString, ENT_QUOTES, 'UTF-8');
	return $returnString;
}

function template_quiz_image_dropdown($index = "", $selectedValue = "", $imageFolder = "Quizzes")
{
	global $boarddir, $boardurl;

	//define the path as relative
	$path = $boarddir . '/Themes/default/images/quiz_images/' . $imageFolder . '/';

	//using the opendir function
	$dir_handle = @opendir($path) or die("Unable to open $path");

	echo '<select id="imageList' , $index , '" name="image' , $index , '" onchange="show_image(\'icon' , $index , '\', this, \'' , $imageFolder , '\')">';

	if ($selectedValue == '')
		echo '<option selected>-</option>';
	else
		echo '<option>-</option>';

	//running the while loop
	while ($file = readdir($dir_handle))
		if($file!="." && $file!="..")
			$files[] = $file;

	if (isset($files))
	{
		sort($files);
		for ($i = 0; $i < sizeof($files); $i++)
		{
			if ($files[$i] == $selectedValue)
				echo "<option selected>$files[$i]</option>";
			else
				echo "<option>$files[$i]</option>";
		}
	}
	echo '</select>&nbsp;';

	if (trim($selectedValue) == '-' || trim($selectedValue) == '')
		echo '<img id="icon' , $index , '" name="icon' , $index , '" src="', $boardurl, '/Themes/default/images/quiz_images/blank.gif" width="24" height="24" border="0"/>';
	else
		echo '<img id="icon' , $index , '" name="icon' , $index , '" src="', $boardurl, '/Themes/default/images/quiz_images/' , $imageFolder , '/' , $selectedValue , '" width="24" height="24" border="0"/>';

	//closing the directory
	closedir($dir_handle);
}

?>