<?php

function template_main()
{
	global $modSettings, $scripturl, $context, $txt, $sourcedir, $context, $settings;

	// Only use default form if not importing - this has its own special form
	if ($context['current_subaction'] != 'quizimporter')
		echo '	<form action="', $scripturl, '?action=' . $context['current_action'] . ';area=' . $context['admin_area'] . ';sa=' , $context['current_subaction'] , '" method="post" name="SMFQuizAdmin" id="SMFQuizAdmin">
				<input type="hidden" name="formaction"/>
		';

	// Show appropriate block of template here depending on which sub action is being taken
	switch ($context['current_subaction'])
	{
		case 'maintenance' : // User is in maintenance section
			template_maintenance();
			break;

		case 'quizimporter' : // User is in quiz importer section
			template_quiz_importer();
			break;

		case 'quizes' : // User is in quiz section
			template_quizes();
			break;

		case 'quizleagues' : // User is in quiz league section
			template_quiz_leagues();
			break;

		case 'categories' : // User is in category section
			template_categories();
			break;

		case 'questions' : // User is in question section
			template_questions();
			break;

// @DONE replaced
// 		case 'settings' : // User is in the admin center section
// 			template_settings();
// 			break;

		case 'results' : // User is in the results section
			template_show_results();
			break;

		case 'disputes' : // User is in the disputes section
			template_show_disputes();
			break;

			default : // Use the admin section as the default
			template_admin_center();

			break;
	}

	if ($context['current_subaction'] != 'quizimporter')
		echo '	</form>
<table width="100%"><tr><td align="center"><a href="http://custom.simplemachines.org/mods/index.php?mod=1650" title="Free SMF Mods" target="_blank" class="smalltext">SMFQuiz ' . $modSettings["SMFQuiz_version"] . ' &copy; 2009, SMFModding</a></td></tr></table>';
}

function template_maintenance()
{
	global $context, $txt, $smcFunc;

	// @TODO visual improvement needed
	if (!empty($_POST) && !isset($_POST['admin_pass']))
		echo '
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
				<tr>
					<td>
						<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
							<tbody>
								<tr class="titlebg" valign="top">
									<td align="left" colspan="2">' , $txt['SMFQuizAdmin_Maintenance_Page']['Maintenance'] , '</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td class="windowbg" style="line-height: 1.3; padding-bottom: 2ex;">

										<table border="0" width="100%">
	';
	if (isset($_POST['btnFindOrphanQuestions']) || isset($_POST['btnDeleteOrphanedQuestions']))
	{
		echo '
											<tr class="titlebg">
												<td colspan="4"><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['OrphanedQuestions'] , '</b></td>
											</tr>
		';
		$orphanCount = isset($context['SMFQuiz']['findOrphanedQuestions']) ? sizeof($context['SMFQuiz']['findOrphanedQuestions']) : 0;

		if ($orphanCount > 0)
		{
			echo '						
											<tr>
												<td><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['QuestionId'] , '</b></td>
												<td><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['QuizId'] , '</b></td>
												<td><b>' , $txt['SMFQuiz_Common']['Question'] , '</b></td>
												<td><b>' , $txt['SMFQuiz_Common']['Updated'] , '</b></td>
											</tr>
			';
			foreach ($context['SMFQuiz']['findOrphanedQuestions'] as $row)
				echo '
											<tr>
												<td>' , $row['id_question'] , '</td>
												<td>' , $row['id_quiz'] , '</td>
												<td>' , format_string($row['question_text']) , '</td>
												<td>' , date("F j, Y, g:i a", $row['updated']) , '</td>
											</tr>
				';

			echo '
											<tr>
												<td colspan="4">
													<input type="submit" value="' , $txt['SMFQuiz_Common']['Delete'] , '" name="btnDeleteOrphanedQuestions" id="btnDeleteOrphanedQuestions"/>
												</td>
											</tr>
			';
		}
		else
			echo '							
											<tr>
												<td colspan="4">' , $txt['SMFQuizAdmin_Maintenance_Page']['NoOrphansFound'] , '</td>
											</tr>';
	}
	elseif (isset($_POST['btnFindOrphanAnswers']) || isset($_POST['btnDeleteOrphanedAnswers']))
	{
		echo '
											<tr class="titlebg">
												<td colspan="4"><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['OrphanedAnswers'] , '</b></td>
											</tr>
		';
		$orphanCount = isset($context['SMFQuiz']['findOrphanedAnswers']) ? sizeof($context['SMFQuiz']['findOrphanedAnswers']) : 0;

		if ($orphanCount > 0)
		{
			echo '						
											<tr>
												<td><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['AnswerId'] , '</b></td>
												<td><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['QuestionId'] , '</b></td>
												<td><b>' , $txt['SMFQuiz_Common']['AnswerText'] , '</b></td>
												<td><b>' , $txt['SMFQuiz_Common']['Updated'] , '</b></td>
											</tr>
			';
			foreach ($context['SMFQuiz']['findOrphanedAnswers'] as $row)
				echo '
											<tr>
												<td>' , $row['id_answer'] , '</td>
												<td>' , $row['id_question'] , '</td>
												<td>' , format_string($row['answer_text']) , '</td>
												<td>' , date("F j, Y, g:i a", $row['updated']) , '</td>
											</tr>
				';

			echo '
											<tr>
												<td colspan="4">
													<input type="submit" value="' , $txt['SMFQuiz_Common']['Delete'] , '" name="btnDeleteOrphanedAnswers" id="btnDeleteOrphanedAnswers"/>
												</td>
											</tr>
			';
		}
		else
			echo '							
											<tr>
												<td colspan="4">' , $txt['SMFQuizAdmin_Maintenance_Page']['NoOrphansFound'] , '</td>
											</tr>';
	}
	elseif (isset($_POST['btnFindOrphanQuizResults']) || isset($_POST['btnDeleteOrphanedQuizResults']))
	{
		echo '
											<tr class="titlebg">
												<td colspan="4"><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['OrphanedQuizResults'] , '</b></td>
											</tr>
		';
		$orphanCount = isset($context['SMFQuiz']['findOrphanedQuizResults']) ? sizeof($context['SMFQuiz']['findOrphanedQuizResults']) : 0;

		if ($orphanCount > 0)
		{
			echo '						
											<tr>
												<td><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['QuizResultId'] , '</b></td>
												<td><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['QuizId'] , '</b></td>
												<td><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['UserId'] , '</b></td>
												<td><b>' , $txt['SMFQuiz_Common']['ResultDate'] , '</b></td>
											</tr>
			';
			foreach ($context['SMFQuiz']['findOrphanedQuizResults'] as $row)
				echo '
											<tr>
												<td>' , $row['id_quiz_result'] , '</td>
												<td>' , $row['id_quiz'] , '</td>
												<td>' , $row['id_user'] , '</td>
												<td>' , date("F j, Y, g:i a", $row['result_date']) , '</td>
											</tr>
				';

			echo '
											<tr>
												<td colspan="4">
													<input type="submit" value="' , $txt['SMFQuiz_Common']['Delete'] , '" name="btnDeleteOrphanedQuizResults" id="btnDeleteOrphanedQuizResults"/>
												</td>
											</tr>
			';
		}
		else
			echo '							
											<tr>
												<td colspan="4">' , $txt['SMFQuizAdmin_Maintenance_Page']['NoOrphansFound'] , '</td>
											</tr>';
	}
	elseif (isset($_POST['btnFindOrphanCategories']) || isset($_POST['btnDeleteOrphanedCategories']))
	{
		echo '
											<tr class="titlebg">
												<td colspan="4"><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['OrphanedCategories'] , '</b></td>
											</tr>
		';
		$orphanCount = isset($context['SMFQuiz']['findOrphanedCategories']) ? sizeof($context['SMFQuiz']['findOrphanedCategories']) : 0;

		if ($orphanCount > 0)
		{
			echo '						
											<tr>
												<td><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['CategoryId'] , '</b></td>
												<td><b>' , $txt['SMFQuizAdmin_Maintenance_Page']['ParentId'] , '</b></td>
												<td><b>' , $txt['SMFQuiz_Common']['Title'] , '</b></td>
												<td><b>' , $txt['SMFQuiz_Common']['Updated'] , '</b></td>
											</tr>
			';
			foreach ($context['SMFQuiz']['findOrphanedCategories'] as $row)
				echo '
											<tr>
												<td>' , $row['id_category'] , '</td>
												<td>' , $row['ParentId'] , '</td>
												<td>' , format_string($row['name']) , '</td>
												<td>' , date("F j, Y, g:i a", $row['updated']) , '</td>
											</tr>
				';

			echo '
											<tr>
												<td colspan="4">
													<input type="submit" value="' , $txt['SMFQuiz_Common']['Delete'] , '" name="btnDeleteOrphanedCategories" id="btnDeleteOrphanedCategories"/>
												</td>
											</tr>
			';
		}
		else
			echo '							
											<tr>
												<td colspan="4">' , $txt['SMFQuizAdmin_Maintenance_Page']['NoOrphansFound'] , '</td>
											</tr>
											<tr>';
	}

	if (!empty($_POST) && !isset($_POST['admin_pass']))
		echo '
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</table>';

	if (!empty($context['MaintenanceResult']))
		echo '
			<div class="maintenance_finished">
				', sprintf($txt['maintain_done'], $context['MaintenanceResult']), '
			</div>';

	echo '
			<div id="manage_maintenance">
				<div class="cat_bar">
					<h3 class="catbg">', $txt['SMFQuiz_Common']['Results'], '</h3>
				</div>
				<div class="windowbg">
					<span class="topslice"><span></span></span>
					<div class="content">
							<p>', $txt['SMFQuizAdmin_Maintenance_Page']['ResetQuizResults'], '</p>
							<span><input type="submit" name="btnClearResults" id="btnClearResult" value="', $txt['maintain_run_now'], '" onclick="return clearResults(this.form);" class="button_submit" /></span>
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
					<span class="botslice"><span></span></span>
				</div>
				<div class="cat_bar">
					<h3 class="catbg">', $txt['SMFQuizAdmin_Maintenance_Page']['OrphanedData'], '</h3>
				</div>
				<div class="windowbg">
					<span class="topslice"><span></span></span>
					<div class="content">
							<p>', $txt['SMFQuizAdmin_Maintenance_Page']['FindOrphanedQuestions'], '</p>
							<span><input type="submit" name="btnFindOrphanQuestions" id="btnFindOrphanQuestions" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
					<div class="content">
							<p>', $txt['SMFQuizAdmin_Maintenance_Page']['FindOrphanedAnswers'], '</p>
							<span><input type="submit" name="btnFindOrphanAnswers" id="btnFindOrphanAnswers" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
					<div class="content">
							<p>', $txt['SMFQuizAdmin_Maintenance_Page']['FindOrphanedQuizResults'], '</p>
							<span><input type="submit" name="btnFindOrphanQuizResults" id="btnFindOrphanQuizResults" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
					<div class="content">
							<p>', $txt['SMFQuizAdmin_Maintenance_Page']['FindOrphanedCategories'], '</p>
							<span><input type="submit" name="btnFindOrphanCategories" id="btnFindOrphanCategories" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
					<span class="botslice"><span></span></span>
				</div>
				<div class="cat_bar">
					<h3 class="catbg">', $txt['SMFQuiz_Common']['Sessions'], '</h3>
				</div>
				<div class="windowbg">
					<span class="topslice"><span></span></span>
					<div class="content">
							<p>', $txt['SMFQuizAdmin_Maintenance_Page']['CompleteQuizSessions'], ' ' , $txt['SMFQuiz_Common']['Over'] , ' <input type="text" name="txtSessionDays" id="txtSessionDays" value="7" size="3" /> ' , $txt['SMFQuiz_Common']['daysold'] , '</p>
							<span><input type="submit" name="btnCompleteSessions" id="btnCompleteSessions" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
					<span class="botslice"><span></span></span>
				</div>
				<div class="cat_bar">
					<h3 class="catbg">', $txt['SMFQuiz_Common']['InfoBoard'], '</h3>
				</div>
				<div class="windowbg">
					<span class="topslice"><span></span></span>
					<div class="content">
							<p>', $txt['SMFQuizAdmin_Maintenance_Page']['CleanInformationBoard'], ' ' , $txt['SMFQuiz_Common']['Over'] , ' <input type="text" name="txtInfoBoardDays" id="txtInfoBoardDays" value="7" size="3" /> ' , $txt['SMFQuiz_Common']['daysold'] , '</p>
							<span><input type="submit" name="btnCleanInfoBoard" id="btnCleanInfoBoard" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>';

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

function template_categories()
{
	global $context;

	// Although we know we are in the category section, we need to see what action is being taken to determine which template to 
	// show in the category section
	$context['SMFQuiz']['Action'] = isset($context['SMFQuiz']['Action']) ? $context['SMFQuiz']['Action'] : 'default';
	switch ($context['SMFQuiz']['Action'])
	{
		case 'SaveCategory' :
		case 'NewCategory' :
			template_new_category();
			break;

		case 'EditCategory' :
			template_edit_category();
			break;

		default:
			template_show_categories();
	}
}

function template_new_question()
{
	global $context, $txt, $smcFunc, $settings;

	echo '
		<table width="90%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
				<tr>
					<td>
						<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
							<tbody>
								<tr class="titlebg" valign="top">
									<td align="left" colspan="2">' , $txt['SMFQuizAdmin_NewQuestion_Page']['NewQuestion'] , '</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionText'] , ':</b></td>
									<td align="left"><input type="text" name="question_text" maxlength="400" size="90"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionType'] , ':</b></td>
									<td align="left">' , template_question_type_dropdown('changeQuestionType(this)') , '</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Quiz'] , ':</b></td>
									<td align="left">' , template_quiz_dropdown(isset($context['SMFQuiz']['id_quiz']) ? $context['SMFQuiz']['id_quiz'] : -1) , '</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['AnswerText'] , ':</b></td>
									<td align="left"><textarea name="question_answer_text" cols="70" rows="5"></textarea></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageURL'] , ':</b></td>
									<td align="left">' , template_quiz_image_dropdown('', '', 'Questions') , '</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageUpload'] , ':</b></td>
									<td>
										<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input">
										<button class="button" id="buttonUpload" onclick="return ajaxFileUpload(\'Questions\');">Upload</button>
										<img id="loading" src="' , $settings['default_images_url'] , '/quiz/loading.gif" style="display:none;">
									</td>
								</tr>
								<tr class="windowbg">
									<td align="left" valign="top"><b>' , $txt['SMFQuiz_Common']['Answer'] , ':</b></td>
									<td align="left">
										<div id="freeTextAnswer" style="display:none">
											<input type="text" name="freeTextAnswer" size="50" maxlength="400"/>
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
											<input type="button" value="Add Row" onclick="addRow()"/>
											<input type="button" value="Delete Row" onclick="deleteRow()"/>
										</div>

										<div id="trueFalseAnswer" style="display:none">
											<input type="radio" name="trueFalseAnswer" value="true" checked="checked"></option> ' , $txt['SMFQuiz_Common']['True'] , '
											<br/><input type="radio" name="trueFalseAnswer" value="false"></option> ' , $txt['SMFQuiz_Common']['False'] , '
										</div>
									</td>
								</tr>
								<tr class="windowbg">
									<td colspan="7">
										<input type="submit" name="SaveQuestion" value="' , $txt['SMFQuizAdmin_NewQuestion_Page']['SaveQuestion'] , '"/>
										<input type="submit" name="SaveAndAddMore" value="' , $txt['SMFQuizAdmin_NewQuestion_Page']['SaveAndAddMore'] , '"/>
										<input type="submit" name="Done" value="' , $txt['SMFQuizAdmin_NewQuestion_Page']['Done'] , '"/>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</table>

	';
}

// @TODO createList?
function template_edit_question()
{
	global $context, $txt, $smcFunc, $settings;

	foreach ($context['SMFQuiz']['questions'] as $row)
	{
// @TODO check input?
		echo '
			<input type="hidden" name="questionId" value="' , $_GET['id'] , '"/>
			<table width="90%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
					<tr>
						<td>
							<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
								<tbody>
									<tr class="titlebg" valign="top">
										<td align="left" colspan="2">' , $txt['SMFQuizAdmin_EditQuestion_Page']['EditQuestion'] , '</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionText'] , ':</b></td>
										<td align="left"><input type="text" name="question_text" maxlength="400" size="90" value="' , format_string($row['question_text']) , '"/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionType'] , ':</b></td>
										<td align="left"><input type="hidden" name="id_question_type" value="' , $row['id_question_type'] , '"/>' , $row['question_type'] , '</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Quiz'] , ':</b></td>
										<td align="left"><input type="hidden" name="id_quiz" value="' , $row['id_quiz'] , '"/>' , format_string($row['quiz_title']) , '</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['AnswerText'] , ':</b></td>
										<td align="left"><textarea name="quiz_answer_text" cols="70" rows="5">' , format_string($row['answer_text']) , '</textarea></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageURL'] , ':</b></td>
										<td align="left">' , template_quiz_image_dropdown('', $row['image'], 'Questions') , '</td>
									</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageUpload'] , ':</b></td>
									<td>
										<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input">
										<button class="button" id="buttonUpload" onclick="return ajaxFileUpload(\'Questions\');">Upload</button>
										<img id="loading" src="' , $settings['default_images_url'] , '/quiz/loading.gif" style="display:none;">
									</td>
								</tr>
									<tr class="windowbg">
										<td align="left" valign="top"><b>' , $txt['SMFQuiz_Common']['Answer'] , ':</b></td>
										<td align="left">
		';
		if ($row['id_question_type'] == 1)
		{ // Multiple Choice
			echo '							<div id="multipleChoiceAnswer" style="display:block">
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

			echo '									</tbody>
												</table>
											<input type="button" value="Add Row" onclick="addRow()"/>
											<input type="button" value="Delete Row" onclick="deleteRow()"/>
											</div>
			';
		}
		elseif ($row['id_question_type'] == 2)
		{ // Free Text
			foreach($context['SMFQuiz']['answers'] as $answerRow)
				echo '
											<input type="hidden" name="answerId" value="' , $answerRow['id_answer'] , '"/>
											<input type="text" name="freeTextAnswer" size="50" maxlength="400" value="' , format_string($answerRow['answer_text']) , '"/>
				';
		}
		else
		{ // True False
			foreach($context['SMFQuiz']['answers'] as $answerRow)
				echo '
											<input type="radio" name="trueFalseAnswer" value="' , $answerRow['id_answer'] , '"' , $answerRow['is_correct'] == 1 ? ' checked="checked"' : '' , '></option> ' , $answerRow['answer_text'] , '<br/>
											<input type="hidden" name="answerId' , $answerRow['id_answer'] , '" value="' , format_string($answerRow['answer_text']) ,'"/>
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
										<td colspan="7">
											<input type="submit" name="UpdateQuestion" value="' , $txt['SMFQuizAdmin_EditQuestion_Page']['UpdateQuestion'] , '"/>
											<input type="submit" name="UpdateQuestionAndAddMore" value="' , $txt['SMFQuizAdmin_EditQuestion_Page']['UpdateQuestionAndAddMore'] , '"/>
											<input type="submit" name="Done" value="' , $txt['SMFQuizAdmin_EditQuestion_Page']['Done'] , '"/>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>

		';
	}
}

function template_edit_category()
{
	global $context, $txt, $smcFunc, $settings;

	foreach ($context['SMFQuiz']['category'] as $row)
	{
// @TODO check input?
		echo '
			<input type="hidden" name="id_category" value="' , $_GET['id'] , '"/>
			<table width="90%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
					<tr>
						<td>
							<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
								<tbody>
									<tr class="titlebg" valign="top">
										<td align="left" colspan="2">' , $txt['SMFQuizAdmin_EditCategory_Page']['EditCategory'] , '</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Title'] , ':</b></td>
										<td align="left"><input type="text" name="name" maxlength="250" size="50" value="' , format_string($row["name"]) , '"/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Description'] , ':</b></td>
										<td align="left"><textarea name="description" cols="50" rows="5">' , format_string($row["description"]) , '</textarea></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageURL'] , ':</b></td>
										<td align="left">' , template_quiz_image_dropdown('', $row['image']) , '</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageUpload'] , ':</b></td>
										<td>
											<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input">
											<button class="button" id="buttonUpload" onclick="return ajaxFileUpload(\'Quizes\');">', $txt['SMFQuiz_Upload'], '</button>
											<img id="loading" src="' , $settings['default_images_url'] , '/quiz/loading.gif" style="display:none;">
										</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Parent'] , ':</b></td>
										<td align="left">' , template_category_dropdown($row["id_parent"], 'parentId') , '</td>
									</tr>
									<tr class="windowbg">
										<td colspan="7">
											<input type="submit" name="UpdateCategory" value="' , $txt['SMFQuizAdmin_EditCategory_Page']['UpdateCategory'] , '"/>
											<input type="submit" name="Done" value="' , $txt['SMFQuizAdmin_EditCategory_Page']['Done'] , '"/>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
		';
	}
}

function template_new_category()
{
	global $context, $txt, $settings;

	echo '
		<table width="90%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
				<tr>
					<td>
						<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
							<tbody>
								<tr class="titlebg" valign="top">
									<td align="left" colspan="2">' , $txt['SMFQuizAdmin_NewCategory_Page']['NewCategory'] , '</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Title'] , ':</b></td>
									<td align="left"><input type="text" name="name" maxlength="250" size="50"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Description'] , ':</b></td>
									<td align="left"><textarea name="description" cols="50" rows="5"></textarea></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageURL'] , ':</b></td>
									<td align="left">' , template_quiz_image_dropdown() , '</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageUpload'] , ':</b></td>
									<td>
										<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input">
										<button class="button" id="buttonUpload" onclick="return ajaxFileUpload(\'Quizes\');">', $txt['SMFQuiz_Upload'], '</button>
										<img id="loading" src="' , $settings['default_images_url'] , '/quiz/loading.gif" style="display:none;">
									</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Parent'] , ':</b></td>
									<td align="left">' , template_category_dropdown(-1, 'parentId') , '</td>
								</tr>
								<tr class="windowbg">
									<td colspan="7">
										<input type="submit" name="SaveCategory" value="' , $txt['SMFQuizAdmin_NewCategory_Page']['SaveCategory'] , '"/>
										<input type="submit" name="Done" value="' , $txt['SMFQuizAdmin_NewCategory_Page']['Done'] , '"/>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</table>
	';
}

// @TODO createList?
function template_show_questions()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;
	$pageCount = 1;
	$page = isset($context['SMFQuiz']['page']) ? $context['SMFQuiz']['page'] : 1;
	$orderDir = isset(	$context['SMFQuiz']['orderDir']) ? $context['SMFQuiz']['orderDir'] : 0;

	if (isset($context['SMFQuiz']['questionCount']))
		foreach ($context['SMFQuiz']['questionCount'] as $row)
		{
			$questionCount = $row['question_count'];
			$pageCount = ceil($questionCount / 20) > 0 ? ceil($questionCount / 20) : 1;
		}

	echo '
						<input type="hidden" name="id_quiz"	 value="' , isset($context['SMFQuiz']['id_quiz']) ? $context['SMFQuiz']['id_quiz'] : 0 , '"/>
						<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
							<tbody>
								<tr class="titlebg">
									<td align="center"><input type="checkbox" name="chkAll" onclick="checkAll(this.form, this.form.chkAll.checked);"/></td>
									<td align="left"><a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';orderBy=Question;orderDir=' , $orderDir , '">', isset($context['SMFQuiz']['orderBy']) && $context['SMFQuiz']['orderBy'] == 'Question' ? '<img alt="" src="' . $settings['images_url'] . '/sort_' . $orderDir . '.gif"/> ' : '' , $txt['SMFQuiz_Common']['Question'] , '</a></td>
									<td align="left"><a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';orderBy=Type;orderDir=' , $orderDir , '">', isset($context['SMFQuiz']['orderBy']) && $context['SMFQuiz']['orderBy'] == 'Type' ? '<img alt="" src="' . $settings['images_url'] . '/sort_' . $orderDir . '.gif"/> ' : '' , $txt['SMFQuiz_Common']['Type'] , '</a></td>
									<td align="left"><a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';orderBy=Quiz;orderDir=' , $orderDir , '">', isset($context['SMFQuiz']['orderBy']) && $context['SMFQuiz']['orderBy'] == 'Quiz' ? '<img alt="" src="' . $settings['images_url'] . '/sort_' . $orderDir . '.gif"/> ' : '' , $txt['SMFQuiz_Common']['Quiz'] , '</a></td>
								</tr>
	';
	echo '
									<tr class="windowbg">
										<td colspan="8">
											<b>' , $txt['SMFQuiz_Common']['Page'] , ':</b> ' , $page , '/' , $pageCount , ' ';
	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';page=1;orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '"><<</a> ';

	if ($page > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';page=' , $page - 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '"><</a> ';

	if ($page < $pageCount)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';page=' , $page + 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '">></a> ';

	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';page=' , $pageCount , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '">>></a>';

	echo '
										</td>
									</tr>
	';
	if (sizeof($context['SMFQuiz']['questions']) > 0)
	{
		foreach ($context['SMFQuiz']['questions'] as $row)
			echo '					<tr class="windowbg">
										<td align="center" width="5%"><input type="checkbox" name="question' , $row['id_question'] , '"/></td>
										<td align="left">' , format_string($row['question_text']) , ' [<a href="', $scripturl, '?action=' . $context['current_action'] . ';area=' . $context['admin_area'] . ';sa=' . $context['current_subaction'] . ';id=' , $row['id_question'] , '">edit</a>]</td>
										<td align="left">' , $row['question_type'] , '</td>
										<td align="center">' , $row['quiz_title'] == 'None Assigned' ? '<font color="red">None Assigned</font>' :  format_string($row['quiz_title']) , '</td>
									</tr>';
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="9" align="left">' , $txt['SMFQuizAdmin_Questions_Page']['NoQuestions'] , '</td></tr>';

	echo '
									<tr class="windowbg">
										<td colspan="8">
											<b>' , $txt['SMFQuiz_Common']['Page'] , ':</b> ' , $page , '/' , $pageCount , ' ';
	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=1;orderBy=' , $context['SMFQuiz']['orderBy'] , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '"><<</a> ';

	if ($page > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page - 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '"><</a> ';

	if ($page < $pageCount)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page + 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '">></a> ';

	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $pageCount , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';id_quiz=' , $context['SMFQuiz']['id_quiz'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '">>></a>';

	echo '
										</td>
									</tr>
									<tr class="windowbg">
										<td colspan="8">
											<input type="submit" name="NewQuestion" value="' , $txt['SMFQuizAdmin_Questions_Page']['NewQuestion'] , '"/>
											<input type="submit" name="DeleteQuestion" value="' , $txt['SMFQuizAdmin_Questions_Page']['DeleteQuestion'] , '"/>
										</td>
									</tr>
							</tbody>
						</table>
	';
}

// @TODO createList?
function template_show_categories()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

	$pageCount = 1;
	$page = isset($context['SMFQuiz']['page']) ? $context['SMFQuiz']['page'] : 1;
	$orderDir = isset($context['SMFQuiz']['orderDir']) ? $context['SMFQuiz']['orderDir'] : 0;

	if (isset($context['SMFQuiz']['categoryCount']))
		foreach ($context['SMFQuiz']['categoryCount'] as $row)
		{
			$categoryCount = $row['CategoryCount'];
			$pageCount = ceil($categoryCount / 20) > 0 ? ceil($categoryCount / 20) : 1;
		}

	echo '
						<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
							<tbody>
								<tr class="titlebg">
									<td align="center"><input type="checkbox" name="chkAll" onclick="checkAll(this.form, this.form.chkAll.checked);"/></td>
									<td align="left"><a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page , ';orderBy=Name;orderDir=' , $orderDir , '">', isset($context['SMFQuiz']['orderBy']) && $context['SMFQuiz']['orderBy'] == 'name' ? '<img src="' . $settings['images_url'] . '/sort_' . $orderDir . '.gif"/> ' : '' , $txt['SMFQuiz_Common']['Title'] , '</a></td>
									<td align="left"><a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page , ';orderBy=Description;orderDir=' , $orderDir , '">', isset($context['SMFQuiz']['orderBy']) && $context['SMFQuiz']['orderBy'] == 'description' ? '<img src="' . $settings['images_url'] . '/sort_' . $orderDir . '.gif"/> ' : '' , $txt['SMFQuiz_Common']['Description'] , '</a></td>
									<td align="left"><a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page , ';orderBy=Parent;orderDir=' , $orderDir , '">', isset($context['SMFQuiz']['orderBy']) && $context['SMFQuiz']['orderBy'] == 'Parent' ? '<img src="' . $settings['images_url'] . '/sort_' . $orderDir . '.gif"/> ' : '' , $txt['SMFQuiz_Common']['Parent'] , '</a></td>
								</tr>
	';
	echo '
									<tr class="windowbg">
										<td colspan="4">
											<b>' , $txt['SMFQuiz_Common']['Page'] , ':</b> ' , $page , '/' , $pageCount , ' ';
	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=1;orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '"><<</a> ';

	if ($page > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page - 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '"><</a> ';

	if ($page < $pageCount)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page + 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '">></a> ';

	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $pageCount , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '">>></a>';

	echo '
										</td>
									</tr>
	';
	if (sizeof($context['SMFQuiz']['categories']) > 0)
	{
		foreach ($context['SMFQuiz']['categories'] as $row)
			echo '					<tr class="windowbg">
										<td align="center" width="5%"><input type="checkbox" name="cat' , $row['id_category'] , '"/></td>
										<td align="left">' , format_string($row['name']) , ' [<a href="', $scripturl, '?action=' . $context['current_action'] . ';area=' . $context['admin_area'] . ';sa=' . $context['current_subaction'] . ';id=' , $row['id_category'] , '">edit</a>] [<a href="', $scripturl, '?action=' . $context['current_action'] . ';area=' . $context['admin_area'] . ';sa=' . $context['current_subaction'] . ';children=' , $row['id_category'] , '">children</a>]</td>
										<td align="left">' , format_string($row['description']) , '</td>
										<td align="center">' , format_string($row['parent_name']) , '</td>
									</tr>';
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="9" align="left">There are no Categories defined</td></tr>';

	echo '
									<tr class="windowbg">
										<td colspan="4">
											<b>' , $txt['SMFQuiz_Common']['Page'] , ':</b> ' , $page , '/' , $pageCount , ' ';
	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=1;orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '"><<</a> ';

	if ($page > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page - 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '"><</a> ';

	if ($page < $pageCount)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $page + 1 , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '">></a> ';

	if ($pageCount > 1)
		echo '<a href="' , $scripturl , '?action=' , $context['current_action'] , ';area=' , $context['admin_area'] , ';sa=' , $context['current_subaction'] , ';page=' , $pageCount , ';orderBy=' , $context['SMFQuiz']['orderBy'] , ';orderDir=' , $orderDir == 'up' ? 'down' : 'up' , '">>></a>';

	echo '
										</td>
									</tr>
	';
// @TODO check input?
	$parentLink = !empty($_GET['children']) ? $_GET['children'] : 0;
	echo '
									<tr class="windowbg">
										<td colspan="8">
											<input type="submit" name="NewCategory" value="' , $txt['SMFQuizAdmin_Categories_Page']['NewCategory'] , '"/>
											<input type="submit" name="DeleteCategory" value="' , $txt['SMFQuizAdmin_Categories_Page']['DeleteCategory'] , '"/>
											<input type="button" name="CategoryAction" value="' , $txt['SMFQuizAdmin_Categories_Page']['ParentCategory'] , '" onclick="window.location=\'', $scripturl, '?action=' . $context['current_action'] . ';area=' . $context['admin_area'] . ';sa=' . $context['current_subaction'] . ';parent=' , $parentLink , '\'"/>
										</td>
									</tr>
							</tbody>
						</table>
	';
}

// @TODO createList?
function template_quizes()
{
	global $context;

	// Although we know we are in the quiz section, we need to see what action is being taken to determine which template to 
	// show in the quiz section
	switch ($context['SMFQuiz']['Action'])
	{
		case 'EditQuiz' :
			template_edit_quiz();
			break;

		case 'SaveQuiz' :
		case 'NewQuiz' :
			template_new_quiz();
			break;

		default:
			template_show_quizes();
	}
}

// Template for all quiz functions
function template_quiz_leagues()
{
	global $context;

	// Although we know we are in the quiz section, we need to see what action is being taken to determine which template to 
	// show in the quiz section
	switch ($context['SMFQuiz']['Action'])
	{
		case 'EditQuizLeague' :
			template_edit_quiz_league();
			break;

		case 'SaveQuizLeague' :
		case 'NewQuizLeague' :
			template_new_quiz_league();
			break;

		default:
			template_show_quiz_leagues();
	}
}

function template_new_quiz()
{
	global $txt, $settings;

	echo '
		<table width="90%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
				<tr>
					<td>
						<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
							<tbody>
								<tr class="titlebg" valign="top">
									<td align="left" colspan="2">' , $txt['SMFQuizAdmin_NewQuiz_Page']['NewQuiz'] , '</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Title'] , ':</b></td>
									<td align="left"><input type="text" name="title" maxlength="400" size="50"/></td>
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
									<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageUpload'] , ':</b></td>
									<td>
										<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input">
										<button class="button" id="buttonUpload" onclick="return ajaxFileUpload(\'Quizes\');">', $txt['SMFQuiz_Upload'], '</button>
										<img id="loading" src="' , $settings['default_images_url'] , '/quiz/loading.gif" style="display:none;">
									</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['PlayLimit'] , ':</b></td>
									<td align="left"><input name="limit" type="text" size="5" maxlength="5" value="1"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['SecondsPerQuestion'] , ':</b></td>
									<td align="left"><input name="seconds" type="text" size="5" maxlength="5" value="20"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['ShowAnswers'] , ':</b></td>
									<td align="left"><input type="checkbox" name="showanswers" checked="checked"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Enabled'] , ':</b></td>
									<td align="left"><input type="checkbox" name="enabled"/> (' , $txt['SMFQuizAdmin_NewQuiz_Page']['BestNotToSelect'] , ')</td>
								</tr>
								<tr class="windowbg">
									<td colspan="7">
										<input type="submit" name="SaveQuiz" value="' , $txt['SMFQuizAdmin_NewQuiz_Page']['SaveQuiz'] , '"/>
										<input type="submit" name="SaveQuizAndAddQuestions" value="' , $txt['SMFQuizAdmin_NewQuiz_Page']['SaveQuizAndAddQuestions'] , '"/>
										<input type="submit" name="' , $txt['SMFQuizAdmin_NewQuiz_Page']['Done'] , '" value="Done"/>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</table>
	';
}

function template_edit_quiz()
{
	global $context, $txt, $smcFunc, $settings;

	foreach ($context['SMFQuiz']['quiz'] as $row)
	{
		echo '
			<input type="hidden" name="id_quiz" value="' , $_GET['id'] , '"/>
			<table width="90%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
					<tr>
						<td>
							<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
								<tbody>
									<tr class="titlebg" valign="top">
										<td align="left" colspan="2">' , $txt['SMFQuizAdmin_EditQuiz_Page']['EditQuiz'] , '</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Title'] , ':</b></td>
										<td align="left"><input type="text" name="title" maxlength="400" size="50" value="' , format_string($row['title']) , '"/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Category'] , ':</b></td>
										<td align="left"><input type="hidden" name="oldCategoryId" value="' , $row['id_category'] , '"/>' , template_category_dropdown($row['id_category'], 'id_category') , '</td>
									</td>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Description'] , ':</b></td>
										<td align="left"><textarea name="description" cols="50" rows="5">' , format_string($row['description']) , '</textarea></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageURL'] , ':</b></td>
										<td align="left">' , template_quiz_image_dropdown('', $row['image']) , '</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['ImageUpload'] , ':</b></td>
										<td>
											<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input">
											<button class="button" id="buttonUpload" onclick="return ajaxFileUpload(\'Quizes\');">', $txt['SMFQuiz_Upload'], '</button>
											<img id="loading" src="' , $settings['default_images_url'] , '/quiz/loading.gif" style="display:none;">
										</td>
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
										<td align="left"><input type="checkbox" name="show_answers"' , $row['show_answers'] == 1 ? ' checked="checked"' : '' , '/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['LastUpdated'] , ':</b></td>
										<td align="left">' , date("F j, Y, g:i a", $row['updated']) , ' </td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionsPlayed'] , ':</b></td>
										<td align="left">' , $row['question_plays'] , ' </td>
									</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Enabled'] , ':</b></td>
									<!-- // @TODO localization -->
									<td align="left"><input type="checkbox" name="enabled"' , $row['enabled'] == 1 ? ' checked="checked"' : '' , '/> (best not to select this until after all questions/answers are added)</td>
								</tr>
									<tr class="windowbg">
										<td colspan="7">
											<input type="submit" name="UpdateQuiz" value="' , $txt['SMFQuizAdmin_EditQuiz_Page']['UpdateQuiz'] , '"/>
											<input type="submit" name="QuizQuestions" value="' , $txt['SMFQuizAdmin_EditQuiz_Page']['QuizQuestions'] , '"/>
											<input type="submit" name="UpdateQuizAndAddQuestions" value="' , $txt['SMFQuizAdmin_EditQuiz_Page']['UpdateQuizAndAddQuestions'] , '"/>
											<input type="submit" name="Done" value="' , $txt['SMFQuizAdmin_EditQuiz_Page']['Done'] , '"/>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
		';
	}
}

// Template to show the new quiz league page
function template_new_quiz_league()
{
	global $txt, $context;

	echo '
		<table width="90%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
				<tr>
					<td>
						<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
							<tbody>
								<tr class="titlebg" valign="top">
									<td align="left" colspan="2">' , $txt['SMFQuizAdmin_NewQuizLeague_Page']['NewQuizLeague'] , '</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Title'] , ':</b></td>
									<td align="left"><input type="text" name="title" maxlength="400" size="50"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Description'] , ':</b></td>
									<td align="left"><textarea name="description" cols="50" rows="5"></textarea></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['Categories'] , ':</b></td>
									<td>
										<select name="categories[]" multiple="multiple" size="10">
											<option value="0" selected="selected">All</option>
	';		
	foreach ($context['SMFQuiz']['categories'] as $row)
		echo '<option value="' , $row['id_category'] , '">' , format_string($row['name']) , ' (' , $row['parent_name'] , ')</option>';

	echo '
										</select>
									</td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['IntervalBetweenPlays'] , ':</b></td>
									<td align="left"><input name="interval" type="text" size="5" maxlength="5" value="7"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionsPerSession'] , ':</b></td>
									<td align="left"><input name="questions" type="text" size="5" maxlength="5" value="25"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['SecondsPerQuestion'] , ':</b></td>
									<td align="left"><input name="seconds" type="text" size="5" maxlength="5" value="20"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['PointsForCorrectAnswer'] , ':</b></td>
									<td align="left"><input name="points" type="text" size="5" maxlength="5" value="1"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['TotalRounds'] , ':</b></td>
									<td align="left"><input name="totalRounds" type="text" size="5" maxlength="5" value="20"/></td>
								</tr>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['State'] , ':</b></td>
									<td>
										<select name="state">
											<option value="0">' , $txt['SMFQuiz_Common']['Disabled'] , '</option>
											<option value="1" selected="selected">' , $txt['SMFQuiz_Common']['Enabled'] , '</option>
											<option value="2">' , $txt['SMFQuiz_Common']['Completed'] , '</option>
										</select>
								<tr class="windowbg" valign="top">
									<td align="left"><b>' , $txt['SMFQuiz_Common']['ShowAnswers'] , ':</b></td>
									<td align="left"><input type="checkbox" name="showanswers" checked="checked"/></td>
								</tr>
								<tr class="windowbg">
									<td colspan="7">
										<input type="submit" name="QuizLeagueAction" value="' , $txt['SMFQuizAdmin_NewQuizLeague_Page']['SaveQuizLeague'] , '"/>
										<input type="submit" name="Done" value="' , $txt['SMFQuizAdmin_NewQuizLeague_Page']['Done'] , '"/>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</table>
	';
}

// Template to show the new quiz league page
function template_edit_quiz_league()
{
	global $context, $txt, $smcFunc, $settings;

	foreach ($context['SMFQuiz']['quizLeague'] as $row)
	{
		$categoriesArray = explode(',', $row['categories']); 
		echo '
                <input type="hidden" name="id_quiz_league" value="\' , $_GET[\'id\'] , \'"/>
			<table width="90%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
					<tr>
						<td>
							<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
								<tbody>
									<tr class="titlebg" valign="top">
										<td align="left" colspan="2">' , $txt['SMFQuizAdmin_NewQuizLeague_Page']['NewQuizLeague'] , '</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Title'] , ':</b></td>
										<td align="left"><input type="text" name="title" maxlength="400" size="50" value="' , format_string($row['title']) , '"/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Description'] , ':</b></td>
										<td align="left"><textarea name="description" cols="50" rows="5">' , format_string($row['description']) , '</textarea></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['Categories'] , ':</b></td>
										<td>
											<select name="categories[]" multiple="multiple" size="10">
												<option value="0" ' , in_array(0, $categoriesArray) ? 'selected' : '' , '>All</option>
		';		
		foreach ($context['SMFQuiz']['categories'] as $categoryRow)
			echo '<option value="' , $categoryRow['id_category'] , '" ' , in_array($categoryRow['id_category'], $categoriesArray) ? 'selected' : '' , '>' , format_string($categoryRow['name']) , ' (' , format_string($categoryRow['parent_name']) , ')</option>';

		echo '
											</select>
										</td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['IntervalBetweenPlays'] , ':</b></td>
										<td align="left"><input name="interval" type="text" size="5" maxlength="5" value="' , $row['day_interval'] , '"/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['QuestionsPerSession'] , ':</b></td>
										<td align="left"><input name="questions" type="text" size="5" maxlength="5" value="' , $row['questions_per_session'] , '"/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['SecondsPerQuestion'] , ':</b></td>
										<td align="left"><input name="seconds" type="text" size="5" maxlength="5" value="' , $row['seconds_per_question'] , '"/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['PointsForCorrectAnswer'] , ':</b></td>
										<td align="left"><input name="points" type="text" size="5" maxlength="5" value="' , $row['points_for_correct'] , '"/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['TotalRounds'] , ':</b></td>
										<td align="left"><input name="totalRounds" type="text" size="5" maxlength="5" value="' , $row['total_rounds'] , '"/></td>
									</tr>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['State'] , ':</b></td>
										<td>
											<select name="state">
												<option value="0" ' , $row['state'] == 0 ? 'selected' : '' , '>' , $txt['SMFQuiz_Common']['Disabled'] , '</option>
												<option value="1" ' , $row['state'] == 1 ? 'selected' : '' , '>' , $txt['SMFQuiz_Common']['Enabled'] , '</option>
												<option value="2" ' , $row['state'] == 2 ? 'selected' : '' , '>' , $txt['SMFQuiz_Common']['Completed'] , '</option>
											</select>
									<tr class="windowbg" valign="top">
										<td align="left"><b>' , $txt['SMFQuiz_Common']['ShowAnswers'] , ':</b></td>
										<td align="left"><input type="checkbox" name="showanswers"' , $row['show_answers'] == 1 ? ' checked="checked"' : '' , '/></td>
									</tr>
									<tr class="windowbg">
										<td colspan="7">
											<input type="submit" name="QuizLeagueAction" value="' , $txt['SMFQuizAdmin_EditQuizLeague_Page']['UpdateQuizLeague'] , '"/>
											<input type="submit" name="Done" value="' , $txt['SMFQuizAdmin_EditQuizLeague_Page']['Done'] , '"/>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
		';
	}
}

function template_show_quizes()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

	if (isset($context['SMFQuiz']['uploadResponse']))
		echo '
		<table width="100%" cellpadding="3" cellspacing="0" border="0">
			<tr class="windowbg">
				<td style="background-color:#FFFFCC">' , $context['SMFQuiz']['uploadResponse'] , '</td>
			</tr>
		</table>
		';

	echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td>', $txt['pages'], ': ', $context['page_index'], '</td>
				<td align="right">', $context['letter_links'] . '</td>
			</tr>
		</table>
	';

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
				<a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" /></a></td>';
		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '</tr>';
	if (sizeof($context['SMFQuiz']['quizes']) > 0)
	{
		foreach ($context['SMFQuiz']['quizes'] as $row)
			echo '					<tr class="windowbg">
										<td align="center" width="5%"><input type="checkbox" name="quiz' , $row['id_quiz'] , '"/></td>
										<td align="center"><img src="' . $settings['default_images_url'] . '/quiz/Quizes/' , $row['image'] , '" height="24" width="24" align="top" alt="" /></td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=categories;id_quiz=' , $row['id_quiz'] , '">' , format_string($row['title']) , '</a></td>
										<td class="nobr" >' , date("M j Y, H:i",$row['updated']) , '</td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $row['creator_id'] , '">' , $row['real_name'] , '</a></td>
										<td align="left">' , format_string($row['description']) , '</td>
										<td align="left">' , format_string($row['category_name']) , '</td>
										<td align="center">' , $row['play_limit'] , '</td>
										<td align="center">' , $row['questions_per_session'] , '</td>
										<td align="center">' , $row['seconds_per_question'] , '</td>
										<td align="center">' , $row['show_answers'] == 1 ? '<img src="' . $settings['default_images_url'] . '/quiz/tick.png" alt="yes" title="Yes" align="top" />' : '<img src="' . $settings['default_images_url'] . '/quiz/cross.png" alt="no" title="No" align="top" />' , '</td>
										<td align="left" class="nobr" >
											<a href="', $scripturl, '?action=' . $context['current_action'] . ';area=' . $context['admin_area'] . ';sa=' . $context['current_subaction'] . ';id=' , $row['id_quiz'] , '"><img src="' . $settings['default_images_url'] . '/quiz/edit.png" alt="edit" title="' , $txt['SMFQuizAdmin_EditQuiz_Page']['EditQuiz'] , '" align="top" /></a>
<!--											<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;upload_quiz_id=' . $row['id_quiz'] . formatQueryString() . '"><img src="' . $settings['default_images_url'] . '/quiz/upload.png" alt="upload" title="' , $txt['SMFQuizAdmin_Quizes_Page']['UploadQuiz'] , '" align="top" /></a>
-->											' , $row['enabled'] == 1 ? '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;disable_quiz_id=' . $row['id_quiz'] . formatQueryString() . '"><img src="' . $settings['default_images_url'] . '/quiz/unlock.png" alt="enabled" title="' . $txt['SMFQuizAdmin_Quizes_Page']['UploadEnabled'] . '" align="top" />' : '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;enable_quiz_id=' . $row['id_quiz'] . formatQueryString() . '"><img src="' . $settings['default_images_url'] . '/quiz/lock.png" alt="disabled" title="' . $txt['SMFQuizAdmin_Quizes_Page']['UploadDisabled'] . '" align="top" /></a>' , '
											' , $row['for_review'] == 1 ? '<img src="' . $settings['default_images_url'] . '/quiz/review.png" alt="for review" title="' . $txt['SMFQuizAdmin_Quizes_Page']['WaitingReview'] . '" align="top" /> <a href="#" onclick="window.open(\'' . $scripturl . '?action=SMFQuiz;sa=play;id_quiz=' . $row['id_quiz'] . '\',\'playnew\',\'height=625,width=720,toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=yes\')"><img src="' . $settings['default_images_url'] . '/quiz/preview.png" alt="Preview Quiz" title="' . $txt['SMFQuizAdmin_Quizes_Page']['PreviewQuiz'] . '" align="top" /></a>' : '' , '
										</td>
									</tr>';
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="10" align="left">', $txt['quiz_xml_error_no_quizzes'], '</td></tr>';

	echo '
									<tr class="windowbg">
										<td colspan="12">
											<input type="submit" name="NewQuiz" value="' , $txt['SMFQuizAdmin_Quizes_Page']['NewQuiz'] , '"/>
											<input type="submit" name="DeleteQuiz" value="' , $txt['SMFQuizAdmin_Quizes_Page']['DeleteQuiz'] , '"/>
										</td>
									</tr>
									<tr class="windowbg">
										<td colspan="12">
											<table>
												<tr>
													<td><b>' , $txt['SMFQuizAdmin_Quizes_Page']['PackageName'] , ':</b></td>
													<td><input type="text" name="packageName" id="packageName" size="50" maxlength="50"/></td>
												</tr>
												<tr>
													<td><b>' , $txt['SMFQuizAdmin_Quizes_Page']['PackageDescription'] , ':</b></td>
													<td><input type="text" name="packageDescription" id="packageDescription" size="60" maxlength="250"/></td>
												</tr>
												<tr>
													<td><b>' , $txt['SMFQuizAdmin_Quizes_Page']['PackageAuthor'] , ':</b></td>
													<td><input type="text" name="packageAuthor" id="packageAuthor" size="50" maxlength="80"/></td>
												</tr>
												<tr>
													<td><b>' , $txt['SMFQuizAdmin_Quizes_Page']['PackageSiteAddress'] , ':</b></td>
													<td><input type="text" name="packageSiteAddress" id="packageSiteAddress" size="60" maxlength="250"/></td>
												</tr>
												<tr>
													<td colspan="2"><input type="button" name="QuizAction" value="' , $txt['SMFQuizAdmin_Quizes_Page']['PackageQuiz'] , '" onclick="return verifyQuizesChecked(this.form);"/></td>
												</tr>
											</table>
										</td>
									</tr>
							</tbody>
						</table>
	';
}

function template_show_disputes()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

	echo '
	<div id="disputeDialog" title="Dispute Response" style="display:none; font-size: 65%" class="ui-dialog-content ui-widget-content">
			<p>Enter your response in the area below and click the appropriate button. A PM will be sent to the member.</p>
			<!-- @TODO localization + action for the form! -->
			<form>
				<label for="disputeText">Response:</label>
				<textarea rows="10" cols="40" id="disputeText"></textarea>
				<input type="hidden" id="disputeId"/>
			</form>
		</div>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td>', $txt['pages'], ': ', $context['page_index'], '</td>
			</tr>
		</table>
	';

	echo '
		<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
			<tbody>
				<tr class="', empty($settings['use_tabs']) ? 'titlebg' : 'catbg3', '">
	';

	// Display each of the column headers of the table.
	foreach ($context['columns'] as $column)
	{
		// We're not able (through the template) to sort the search disputes right now...
		if (isset($context['old_search']))
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['label'], '</td>';
		// This is a selected solumn, so underline it or some such.
		elseif ($column['selected'])
			echo '
			<td style="width: auto;"' . (isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '') . ' nowrap="nowrap">
				<a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" /></a></td>';
		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '</tr>';
	if (sizeof($context['SMFQuiz']['disputes']) > 0)
	{
		foreach ($context['SMFQuiz']['disputes'] as $row)
			echo '					<tr class="windowbg">
										<td align="center" width="5%"><input type="checkbox" name="quiz_dispute' , $row['id_quiz_dispute'] , '"/></td>
										<td align="left">' , date("M j, H:i", $row['updated']) , '</td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $row['id_member'] , '">' , $row['real_name'] , '</a></td>
										<td align="left"><a href="' , $scripturl , '?action=admin;area=quiz;sa=quizes;id=' , $row['id_quiz'] , '">' , format_string($row['title']) , '</a></td>
										<td align="left"><a href="' , $scripturl , '?action=admin;area=quiz;sa=questions;id=' , $row['id_question'] , '">' , format_string($row['question_text']) , '</a></td>
										<td align="left" id="reason' , $row['id_quiz_dispute'] , '">' , format_string($row['reason']) , '</td>
										<td align="left"><img id="' , $row['id_quiz_dispute'] , '" src="' , $settings['default_images_url'] , '/quiz/comments.png" class="disputeDialog" style="cursor:pointer" alt="respond" title="' , $txt['SMFQuizAdmin_QuizDisputes_Page']['RespondToDispute'] , '" align="top" /></td>
									</tr>';
	}
	// @TODO localization
	else
		echo ' 						<tr class="windowbg"><td colspan="10" align="left">There are no Quiz Disputes</td></tr>';

	echo '
									<tr class="windowbg">
										<td colspan="10">
											<input type="submit" name="DeleteQuizDispute" value="' , $txt['SMFQuizAdmin_QuizDisputes_Page']['DeleteQuizDispute'] , '"/>
										</td>
									</tr>
							</tbody>
						</table>
	';
}

function template_show_results()
{
	global $txt, $context, $settings, $scripturl, $smcFunc;

	echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td>', $txt['pages'], ': ', $context['page_index'], '</td>
			</tr>
		</table>
	';

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
				<a href="' . $column['href'] . '" rel="nofollow">' . $column['label'] . ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" /></a></td>';
		// This is just some column... show the link and be done with it.
		else
			echo '
			<td', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', isset($column['colspan']) ? ' colspan="' . $column['colspan'] . '"' : '', '>
				', $column['link'], '</td>';
	}
	echo '</tr>';
	if (sizeof($context['SMFQuiz']['results']) > 0)
	{
		foreach ($context['SMFQuiz']['results'] as $row)
			echo '					<tr class="windowbg">
										<td align="center" width="5%"><input type="checkbox" name="quiz_result' , $row['id_quiz_result'] , '"/></td>
										<td align="left">' , date("M j, H:i", $row['result_date']) , '</td>
										<td align="left"><a href="' , $scripturl , '?action=SMFQuiz;sa=userdetails;id_user=' , $row['id_member'] , '">' , $row['real_name'] , '</a></td>
										<td align="left">' , format_string($row['title']) , '</td>
										<td align="center">' , $row['questions'] , '</td>
										<td align="center">' , $row['correct'] , '</td>
										<td align="center">' , $row['incorrect'] , '</td>
										<td align="center">' , $row['timeouts'] , '</td>
										<td align="center">' , $row['total_seconds'] , '</td>
										<td align="center">' , $row['total_resumes'] , '</td>
									</tr>';
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="10" align="left">There are no Quiz Results</td></tr>';

	echo '
									<tr class="windowbg">
										<td colspan="10">
											<input type="submit" name="QuizAction" value="' , $txt['SMFQuizAdmin_QuizResults_Page']['DeleteQuizResult'] , '"/>
										</td>
									</tr>
							</tbody>
						</table>
	';
}

// Template to show the quizes
function template_show_quiz_leagues()
{
	global $txt, $context, $settings, $smcFunc, $scripturl;

	echo '
						<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
							<tbody>
								<tr class="titlebg">
									<td align="center"><input type="checkbox" name="chkAll" onclick="checkAll(this.form, this.form.chkAll.checked);"/></td>
									<td align="left">' , $txt['SMFQuiz_Common']['Title'] , '</td>
									<td align="left">' , $txt['SMFQuiz_Common']['Description'] , '</td>
									<td align="center">' , $txt['SMFQuiz_Common']['Interval'] , '</td>
									<td align="center">' , $txt['SMFQuiz_Common']['Qs'] , '</td>
									<td align="center">' , $txt['SMFQuiz_Common']['Secs'] , '</td>
									<td align="center">' , $txt['SMFQuiz_Common']['Points'] , '</td>
									<td align="center">' , $txt['SMFQuiz_Common']['Answers'] , '</td>
									<td align="center">' , $txt['SMFQuiz_Common']['CurrentRound'] , '</td>
									<td align="center">' , $txt['SMFQuiz_Common']['TotalRounds'] , '</td>
									<td align="center">' , $txt['SMFQuiz_Common']['Functions'] , '</td>
								</tr>
	';
	if (sizeof($context['SMFQuiz']['quizLeagues']) > 0)
	{
		foreach ($context['SMFQuiz']['quizLeagues'] as $row)
		{
			echo '					<tr class="windowbg">
										<td align="center" width="5%"><input type="checkbox" name="quiz' , $row['id_quiz_league'] , '"/></td>
										<td align="left">' , format_string($row['title']) , '</td>
										<td align="left">' , format_string($row['description']) , '</td>
										<td align="center">' , $row['day_interval'] , '</td>
										<td align="center">' , $row['questions_per_session'] , '</td>
										<td align="center">' , $row['seconds_per_question'] , '</td>
										<td align="center">' , $row['points_for_correct'] , '</td>
										<td align="center">' , $row['show_answers'] == 1 ? '<img src="' . $settings['default_images_url'] . '/quiz/tick.png" alt="yes" title="Yes" align="top" />' : '<img src="' . $settings['default_images_url'] . '/quiz/cross.png" alt="no" title="No" align="top" />' , '</td>
										<td align="center">' , $row['current_round'] , '</td>
										<td align="center">' , $row['total_rounds'] , '</td>
										<td align="left" class="nobr" >
											<a href="', $scripturl, '?action=' . $context['current_action'] . ';area=' . $context['admin_area'] . ';sa=' . $context['current_subaction'] . ';id=' , $row['id_quiz_league'] , '"><img src="' . $settings['default_images_url'] . '/quiz/edit.png" alt="edit" title="' , $txt['SMFQuizAdmin_EditQuizLeague_Page']['EditQuizLeague'] , '" align="top" /></a>
			';
			switch ($row['state'])
			{
				case 0 :
					echo '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizleagues;enable_quizleague_id=' . $row['id_quiz_league'] . formatQueryString() . '"><img src="' . $settings['default_images_url'] . '/quiz/lock.png" alt="disabled" title="' . $txt['SMFQuizAdmin_QuizLeagues_Page']['LeagueDisabled'] . '" align="top" /></a>';
					break;
				case 1 :
					echo '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizleagues;disable_quizleague_id=' . $row['id_quiz_league'] . formatQueryString() . '"><img src="' . $settings['default_images_url'] . '/quiz/unlock.png" alt="enabled" title="' . $txt['SMFQuizAdmin_QuizLeagues_Page']['LeagueEnabled'] . '" align="top" />';
					break;
				default:
					echo '<img src="' . $settings['default_images_url'] . '/quiz/time.png" alt="completed" title="' . $txt['SMFQuizAdmin_QuizLeagues_Page']['LeagueCompleted'] . '" align="top" />';
					break;
			}
			echo '
										</td>
									</tr>';
		}
	}
	else
		echo ' 						<tr class="windowbg"><td colspan="11" align="left">' , $txt['SMFQuizAdmin_QuizLeagues_Page']['NoQuizLeagues'] , '</td></tr>';

	echo '
									<tr class="windowbg">
										<td colspan="11">
											<input type="submit" name="QuizLeagueAction" value="' , $txt['SMFQuizAdmin_QuizLeagues_Page']['NewQuizLeague'] , '"/>
											<input type="submit" name="QuizLeagueAction" value="' , $txt['SMFQuizAdmin_QuizLeagues_Page']['DeleteQuizLeague'] , '"/>
										</td>
									</tr>
							</tbody>
						</table>
	';
}

// Template that provides the category dropdown
function template_category_dropdown($selectedCategoryId, $identifier)
{
	global $context, $txt, $smcFunc;

	if (empty($selectedCategoryId))
		$selectedCategoryId = -1;

	echo '<select name="' , $identifier , '" id="' , $identifier , '"><option value="0">' , $txt['SMFQuiz_Common']['TopLevel'] , '</option>';
	foreach ($context['SMFQuiz']['categories'] as $row)
	{
		if ($selectedCategoryId == $row['id_category'])
			echo '<option value="' , $row['id_category'] , '" selected="selected">' , format_string($row['name']) , ' (' , $row['parent_name'] , ')</option>';
		else
			echo '<option value="' , $row['id_category'] , '">' , format_string($row['name']) , ' (' , $row['parent_name'] , ')</option>';
	}
	echo '</select>';
}

// Template that provides the quiz dropdown
function template_quiz_dropdown($selectedId = -1)
{
	global $context, $smcFunc;

	if (sizeof($context['SMFQuiz']['quizes']) > 0)
	{
		echo '<select name="id_quiz">';
		foreach ($context['SMFQuiz']['quizes'] as $row)
		{
			if ($selectedId == $row['id_quiz'])
				echo '<option value="' , $row['id_quiz'] , '" selected="selected">' , format_string($row['title']) , '</option>';
			else
				echo '<option value="' , $row['id_quiz'] , '">' , format_string($row['title']) , '</option>';
		}
		echo '</select>';
	}
}

function template_question_type_dropdown($onChange = null)
{
	global $context, $smcFunc;

	if (sizeof($context['SMFQuiz']['questionTypes']) > 0)
	{
		if ($onChange == null)
			echo '<select name="id_question_type">';
		else
			echo '<select name="id_question_type" onChange="' , $onChange , '">';

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

function template_admin_center()
{
	global $scripturl, $settings, $modSettings, $context, $txt;
/*
	echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 0.5em;"><tr>
			<td valign="top">
				<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor">
					<tr>
						<td class="catbg">
							<a href="' . $scripturl . '?action=helpadmin;help=quiz_live_feed" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['default_images_url'] . '/helptopics.gif" alt="?" align="top" /></a> ' , $txt['SMFQuizAdmin_AdminCenter_Page']['LiveFromSMFModding'] , '
						</td>
					</tr><tr>
						<td class="windowbg" valign="top" style="height: 20ex; padding: 0;">
							<div id="smfAnnouncements" style="height: 20ex; overflow: auto; padding-right: 1ex;">
								<table>
	';
	foreach ($context['SMFQuiz_latestNews'] as $latestNews)
		echo '						<tr>
										<td style="font-weight: bold; font-size: 0.8em;">' , $latestNews->date , '</td>
									</tr>
									<tr>
										<td style="font-weight: bold; font-size: 0.8em; color: green">' , $latestNews->title , '</td>
									</tr>
									<tr>
										<td style="font-size: 0.8em;">' , $latestNews->description , '</td>
									</tr>
									<tr>
										<td><hr size="1"></td>
									</tr>
		';

	echo '
								</table>
							</div>
						</td>
					</tr>
				</table>
			</td>
			<td style="width: 1ex;">&nbsp;</td>
			<td valign="top" style="width: 40%;">*/
	echo '
	<div id="admincenter">
		<div id="admin_main_section">
			<!-- @TODO in-line style -->
			<div style="width: 64%;" class="floatleft">
				<div class="cat_bar">
					<h3 class="catbg">
						<span class="ie6_header floatleft">', $txt['quiz_mod_statistics'], '</span>
					</h3>
				</div>
				<div class="windowbg nopadding">
					<span class="topslice"><span></span></span>
					<div class="content">
						<div id="version_details">
							<b>' , $txt['SMFQuizAdmin_AdminCenter_Page']['Statistics'] , ':</b><br />
							' , $txt['SMFQuizAdmin_AdminCenter_Page']['TotalQuizes'] , ':
							<i id="yourVersion" style="white-space: nowrap;">' , $context['SMFQuiz_totalQuizes']  , '</i><br />
							' , $txt['SMFQuizAdmin_AdminCenter_Page']['QuizesNotEnabled'] , ':
							<i id="yourVersion" style="white-space: nowrap;">' , $context['SMFQuiz_totalDisabledQuizes'] > 0 ? '<font color="red">' . $context['SMFQuiz_totalDisabledQuizes'] . '</font> [<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;disabled">' . $txt['SMFQuiz_Common']['ViewAll'] . '</a>]' : '<font color="green">0</font>' , '</i><br />
							' , $txt['SMFQuizAdmin_AdminCenter_Page']['QuizesWaitingReview'] , ':
							<i id="yourVersion" style="white-space: nowrap;">' , $context['SMFQuiz_totalQuizesWaitingReview'] > 0 ? '<font color="red">' . $context['SMFQuiz_totalQuizesWaitingReview'] . '</font> [<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes;review">' . $txt['SMFQuiz_Common']['ViewAll'] . '</a>]' : '<font color="green">0</font>' , '</i><br />
							' , $txt['SMFQuizAdmin_AdminCenter_Page']['TotalResults'] , ':
							<i id="yourVersion" style="white-space: nowrap;">' , $context['SMFQuiz_totalResults']  , '</i><br />
							' , $txt['SMFQuizAdmin_AdminCenter_Page']['OutstandingDisputes'] , ':
							<i id="yourVersion" style="white-space: nowrap;">' , $context['SMFQuiz_totalDisputes'] > 0 ? '<font color="red">' . $context['SMFQuiz_totalDisputes'] . '</font> [<a href="' . $scripturl . '?action=admin;area=quiz;sa=disputes">' . $txt['SMFQuiz_Common']['ViewAll'] . '</a>]' : '<font color="green">0</font>' , '</i><br />
						</div>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>
			<div id="supportVersionsTable" class="floatright">
				<div class="cat_bar">
					<h3 class="catbg">
						<a href="' . $scripturl . '?action=helpadmin;help=quiz_info_summary" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['default_images_url'] . '/helptopics.gif" alt="?" class="icon" /></a> ', $txt['quiz_mod_summary'], '
					</h3>
				</div>
				<div class="windowbg nopadding">
					<span class="topslice"><span></span></span>
					<div class="content">
						<div id="version_details">
							<b>' , $txt['SMFQuizAdmin_AdminCenter_Page']['VersionInformation'] , ':</b><br />
							' , $txt['SMFQuizAdmin_AdminCenter_Page']['ModVersion'] , ':
							<i id="yourVersion" style="white-space: nowrap;">' , $modSettings['SMFQuiz_version'] , '</i><br />
							' , !empty($context['SMFQuiz_currentVersion']) ? $txt['SMFQuizAdmin_AdminCenter_Page']['CurrentSMFQuizVersion'] . ': 
							<i id="smfVersion" style="white-space: nowrap;">' . ($context['SMFQuiz_currentVersion'] == $modSettings['SMFQuiz_version'] ? '<font color="green">' : '<font color="red">') . $context['SMFQuiz_currentVersion'] . '</font>' . '</i>' : '', '
						</div>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>
		</div>
	</div>
	<br class="clear" />';/*
			</td>
		</tr></table>';*/
	echo '
		<div class="windowbg clear_right">
			<span class="topslice"><span></span></span>
			<div class="content">
				<ul id="quick_tasks" class="flow_hidden">
					<li>
						<a href="' . $scripturl . '?action=admin;area=quiz;sa=settings"><img src="' , $settings['default_images_url'] , '/quiz/Settings-48.png" alt="" class="home_image png_fix" /></a>
						<h5><a href="' . $scripturl . '?action=admin;area=quiz;sa=settings">' , $txt['SMFQuizAdmin_Titles']['Settings'] , '</a></h5>
						<span class="task">' , $txt['SMFQuizAdmin_Title_Blurbs']['Settings'] , '</span>
					</li>
					<li>
						<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes"><img src="' , $settings['default_images_url'] , '/quiz/Quizes-48.png" alt="" class="home_image png_fix" /></a>
						<h5><a href="' . $scripturl . '?action=admin;area=quiz;sa=quizes">' , $txt['SMFQuizAdmin_Titles']['Quizes'] , '</a></h5>
						<span class="task">' , $txt['SMFQuizAdmin_Title_Blurbs']['Quizes'] , '</span>
					</li>
					<li>
						<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizleagues"><img src="' , $settings['default_images_url'] , '/quiz/Leagues-48.png" alt="" class="home_image png_fix" /></a>
						<h5><a href="' . $scripturl . '?action=admin;area=quiz;sa=quizleagues">' , $txt['SMFQuizAdmin_Titles']['QuizLeagues'] , '</a></h5>
						<span class="task">' , $txt['SMFQuizAdmin_Title_Blurbs']['QuizLeagues'] , '</span>
					</li>
					<li>
						<a href="' . $scripturl . '?action=admin;area=quiz;sa=categories"><img src="' , $settings['default_images_url'] , '/quiz/Categories-48.png" alt="" class="home_image png_fix" /></a>
						<h5><a href="' . $scripturl . '?action=admin;area=quiz;sa=categories">' , $txt['SMFQuizAdmin_Titles']['Categories'] , '</a></h5>
						<span class="task">' , $txt['SMFQuizAdmin_Title_Blurbs']['Categories'] , '</span>
					</li>
					<li>
						<a href="' . $scripturl . '?action=admin;area=quiz;sa=questions"><img src="' , $settings['default_images_url'] , '/quiz/Questions-48.png" alt="" class="home_image png_fix" /></a>
						<h5><a href="' . $scripturl . '?action=admin;area=quiz;sa=questions">' , $txt['SMFQuizAdmin_Titles']['Questions'] , '</a></h5>
						<span class="task">' , $txt['SMFQuizAdmin_Title_Blurbs']['Questions'] , '</span>
					</li>
					<li>
						<a href="' . $scripturl . '?action=admin;area=quiz;sa=results"><img src="' , $settings['default_images_url'] , '/quiz/Results-48.png" alt="" class="home_image png_fix" /></a>
						<h5><a href="' . $scripturl . '?action=admin;area=quiz;sa=results">' , $txt['SMFQuizAdmin_Titles']['Results'] , '</a></h5>
						<span class="task">' , $txt['SMFQuizAdmin_Title_Blurbs']['Results'] , '</span>
					</li>
					<li>
						<a href="' . $scripturl . '?action=admin;area=quiz;sa=disputes"><img src="' , $settings['default_images_url'] , '/quiz/Disputes-48.png" alt="" class="home_image png_fix" /></a>
						<h5><a href="' . $scripturl . '?action=admin;area=quiz;sa=disputes">' , $txt['SMFQuizAdmin_Titles']['Disputes'] , '</a></h5>
						<span class="task">' , $txt['SMFQuizAdmin_Title_Blurbs']['Disputes'] , '</span>
					</li>
<!--					<li>
						<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizimporter"><img src="' , $settings['default_images_url'] , '/quiz/Importer-48.png" alt="" class="home_image png_fix" /></a>
						<h5><a href="' . $scripturl . '?action=admin;area=quiz;sa=quizimporter">' , $txt['SMFQuizAdmin_Titles']['QuizImporter'] , '</a></h5>
						<span class="task">' , $txt['SMFQuizAdmin_Title_Blurbs']['QuizImporter'] , '</span>
					</li>-->
					<li>
						<a href="' . $scripturl . '?action=admin;area=quiz;sa=maintenance"><img src="' , $settings['default_images_url'] , '/quiz/Maintenance-48.png" alt="" class="home_image png_fix" /></a>
						<h5><a href="' . $scripturl . '?action=admin;area=quiz;sa=maintenance">' , $txt['SMFQuizAdmin_Titles']['Maintenance'] , '</a></h5>
						<span class="task">' , $txt['SMFQuizAdmin_Title_Blurbs']['Maintenance'] , '</span>
					</li>
				</ul>
			</div>
			<span class="botslice clear"><span></span></span>
		</div>';
}

function template_quiz_importer()
{
	global $scripturl, $context, $settings, $txt;

	// Get the local image files into an array, as we need to see if the quiz
	// images already exist
// 	$imageFiles = get_image_files();

	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['SMFQuizAdmin_Titles']['QuizImporter'], '</h3>
		</div>';

	$info_error = array(
		'successful_import' => 'infobox',
		'unsuccessful_import' => 'errorbox',
	);

	foreach ($info_error as $key => $box)
		if (!empty($context[$key]))
		{
			echo '
				<div class="', $box, '">' . $txt['quiz_mod_' . $key] . '
					<ul>';

			foreach ($context[$key] as $message)
				echo '
						<li>' . $message . '</li>';
			echo '
					</ul>
				</div>';
		}

	echo '
			<form action="', $scripturl, '?action=' . $context['current_action'] . ';area=' . $context['admin_area'] . ';sa=' , $context['current_subaction'] , '" accept-charset="', $context['character_set'], '" method="post" name="SMFQuizAdmin" id="SMFQuizAdmin" enctype="multipart/form-data">
				<table id="moreQuizzes" class="table_grid" cellspacing="0" width="100%">
					<thead>
						<tr class="catbg">
							<th class="first_th">', $txt['SMFQuizAdmin_Quizes_Page']['PackageQuiz'], '</th>
							<th>', $txt['SMFQuiz_Common']['ImageUpload'], '</th>
						</tr>
					</thead>
					<tbody>';

	for ($i = 0; $i < 5; $i++)
	{
		echo '
						<tr class="windowbg">
							<td>
								<input type="file" size="30" name="imported_quiz[]" id="imported_quiz', $i, '" class="input_file" />
							</td>
							<td>
								(<a href="javascript:void(0);" onclick="cleanFileInput(\'imported_quiz', $i, '\');">', $txt['quiz_mod_clean_quiz'], '</a>)
							</td>
						</tr>';
	}

		echo '
					</tbody>
				</table>
			<div class="flow_auto">
				<div class="floatright">
					<div class="additional_row">
						[<a href="#" onclick="addQuiz(); return false;">', $txt['quiz_mod_more_quizzes'], '</a>] <input type="submit" value="Import" style="float: right;" class="button_submit" />
					</div>
				</div>
			</div>

				<script type="text/javascript"><!-- // --><![CDATA[
					current_quiz = 5
					function addQuiz()
					{
						$("#moreQuizzes tr:last").after(', JavaScriptEscape('
							<tr class="windowbg">
								<td>
									<input type="file" size="30" name="imported_quiz[]" id="imported_quiz'), ' + current_quiz + ', JavaScriptEscape('" class="input_file" />
								</td>
								<td>
									(<a href="javascript:void(0);" onclick="cleanFileInput(\'imported_quiz\''), ' + current_quiz + ', JavaScriptEscape(');">' . $txt['quiz_mod_clean_quiz'] . '</a>)
								</td>
							</tr>'), ');
						current_quiz++;

						return true;
					}
				// ]]></script>
		';

	echo '
			</form>
		</div>';
}

function formatQueryString()
{
	$extraQuerystring = '';
	if (isset($_GET['starts_with']))
		$extraQuerystring = ';starts_with=' . $_GET['starts_with'];

	if (isset($_GET['start']))
		$extraQuerystring .= ';start=' . $_GET['start'];

	if (isset($_GET['sort']))
		$extraQuerystring .= ';sort=' . $_GET['sort'];

	if (isset($_GET['desc']))
		$extraQuerystring .= ';desc';

	if (isset($_GET['review']))
		$extraQuerystring = ';review';

	if (isset($_GET['disabled']))
		$extraQuerystring = ';disabled';

	if (isset($_GET['enabled']))
		$extraQuerystring = ';enabled';

	return $extraQuerystring;
}

function importItemEnabled($premium, $for_review)
{
	if ($premium == 1 || $for_review == 1)
		return false;
	else
		return true;
}

function importRowStyle($importItemEnabled, $alreadyInstalled)
{
	if ($importItemEnabled == false)
		return 'background-color: #E5E1E1;';
	elseif ($alreadyInstalled == true)
		return 'background-color: #DDF8D9;';

	return 'background-color: #F8D9D9;';
}

// @TODO remove?
function formatDisabledImage($premium, $for_review)
{
	global $settings, $scripturl;

	if ($premium == 1)
		$formattedImage = '&nbsp;<a href="http://www.smfmodding.com/index.php?action=profile;area=subscriptions"><img src="' . $settings['default_images_url'] . '/quiz/dollar.png" border="0" title="You must be a premium member to import this quiz"/></a>';
	else
		$formattedImage = '&nbsp;<img src="' . $settings['default_images_url'] . '/quiz/review.png" title="This quiz is currently being reviewed and will become available once it has been approved"/>';

	return $formattedImage;
}

function formatImportImage($importItemEnabled, $alreadyInstalled, $id_quiz)
{
	global $settings, $scripturl;

	if ($importItemEnabled == false || $alreadyInstalled == true)
		$formattedImage = '<img src="' . $settings['default_images_url'] . '/quiz/download-disabled.png" title="Import Disabled"/>';
	else
		$formattedImage = '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizimporter;id_quiz=' . $id_quiz . formatQueryString() . '"><img border="0" src="' . $settings['default_images_url'] . '/quiz/download.png" title="Import Quiz"/></a>';

	return $formattedImage;
}

function formatQuizImage($image, $imageFiles)
{
	global $settings, $scripturl;

	if (empty($image))
		$formattedImage = '<img src="' . $settings['default_images_url'] . '/quiz/cross.png" title="No image available for this Quiz"/>';
	elseif (!in_array($image, $imageFiles))
		$formattedImage = '<a href="' . $scripturl . '?action=admin;area=quiz;sa=quizimporter;image=' . $image . formatQueryString() . '"><img border="0" src="' . $settings['default_images_url'] . '/quiz/download.png" title="Import Image" /></a>';
	else
		$formattedImage = '<img src="' . $settings['default_images_url'] . '/quiz/Quizes/' . $image . '" width="24" height="24"/>';

	return $formattedImage;
}

function template_quiz_image_dropdown($index = "", $selectedValue = "", $imageFolder = "Quizes")
{
	global $boardurl;

	echo '<select id="imageList' , $index , '" name="image' , $index , '" onchange="show_image(\'icon' , $index , '\', this, \'' , $imageFolder , '\')">';

	if ($selectedValue == '')
		echo '<option selected>-</option>';
	else
		echo '<option>-</option>';

	$files = get_image_files($imageFolder);

	if (isset($files))
	{
		sort($files);
		for ($i = 0; $i < sizeof($files); $i++)
		{
		// @TODO double quotes
			if ($files[$i] == $selectedValue)
				echo "<option selected>$files[$i]</option>";
			else
				echo "<option>$files[$i]</option>";
		}
	}
	echo '</select>&nbsp;';

	if (trim($selectedValue) == '-')
		echo '<img id="icon' , $index , '" name="icon' , $index , '" src="', $boardurl, '/Themes/default/images/quiz/blank.gif" width="24" height="24" border="0"/>';
	else
		echo '<img id="icon' , $index , '" name="icon' , $index , '" src="', $boardurl, '/Themes/default/images/quiz/' , $imageFolder , '/' , $selectedValue , '" width="24" height="24" border="0"/>';
}

function get_image_files($imageFolder = 'Quizes')
{
	global $settings;

	//define the path as relative
	$path = $settings['default_theme_dir'] . '/images/quiz/' . $imageFolder . '/';

	//using the opendir function
		// @TODO fatal_lang?
	$dir_handle = @opendir($path) or die("Unable to open $path");

	$files = array();
	while ($file = readdir($dir_handle))
		if($file != "." && $file != "..")
			$files[] = $file;

	//closing the directory
	closedir($dir_handle);

	return $files;
}

function format_string($stringToFormat)
{
	global $smcFunc;

	// Remove any slashes. These should not be here, but it has been known to happen
	$returnString = str_replace("\\", "", $smcFunc['db_unescape_string']($stringToFormat));

	//return html_entity_decode($returnString, ENT_QUOTES, 'UTF-8');
	return $returnString;
}

?>