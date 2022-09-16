<?php

function quizExport()
{
	global $sourcedir;

	isAllowedTo('quiz_admin');

	// Include the SMF2 specific database file
	require_once($sourcedir . '/Quiz/Db.php');

	PackageQuiz();
	die();
}

function PackageQuiz()
{
	global $context, $modSettings;
	// Get the key ids for the quizes to package. This function returns a string containing a comma separated list of id's
	// @TODO check and validate inputs
	if (empty($_GET['quizIds']))
		return;

	$quizKeys = explode(',', $_GET['quizIds']);
	$quizKeys = array_map(function($id) { return (int) $id; }, $quizKeys);
	$quizKeys = array_unique($quizKeys);

	if (empty($quizKeys))
		return;

	$packageName = (!empty($_GET['packageName']) ? $_GET['packageName'] : 'NoNameEntered') . '.xml';

// @TODO localization?
	$packageDescription = 'No description entered';
	if (!empty($_GET['packageDescription']))
		$packageDescription = $_GET['packageDescription'];

// @TODO localization?
	$packageAuthor = 'No author entered';
	if (!empty($_GET['packageAuthor']))
		$packageAuthor = $_GET['packageAuthor'];

// @TODO localization?
	$packageSiteAddress = 'No site entered';
	if (!empty($_GET['packageSiteAddress']))
		$packageSiteAddress = $_GET['packageSiteAddress'];

	if (sizeof($quizKeys) > 0)
	{
		$quizRows = ExportQuizes($quizKeys);
		header('Content-Disposition: attachment; filename="' . $packageName . '"');
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
		echo '<quizes>
				<description>' , $packageDescription , '</description>
				<author>' , $packageAuthor , '</author>
				<siteAddress>' , $packageSiteAddress , '</siteAddress>
				<packageDate>' , date("F j, Y, g:i a", time()) , '</packageDate>
				<smfQuizVersion>' , $modSettings["SMFQuiz_version"] , '</smfQuizVersion>
				<smfVersion>' , $modSettings["smfVersion"] , '</smfVersion>
		';

		foreach ($quizRows as $row)
		{
// @TODO double quotes
			echo " 				
				<quiz>
					<title><![CDATA[{$row['title']}]]></title>
					<categoryName><![CDATA[{$row['category_name']}]]></categoryName>
					<description><![CDATA[{$row['description']}]]></description>
					<playLimit>{$row['play_limit']}</playLimit>
					<secondsPerQuestion>{$row['seconds_per_question']}</secondsPerQuestion>
					<showAnswers>{$row['show_answers']}</showAnswers>
					<image><![CDATA[{$row['image']}]]></image>
					<imageData><![CDATA[{$row['image_data']}]]></imageData>
					<questions>
			";

			$quizQuestionRows = ExportQuizQuestions($row['id_quiz']);

// @TODO double quotes
			foreach ($quizQuestionRows as $questionRow)
			{
				echo "
						<question>
							<questionText><![CDATA[{$questionRow['question_text']}]]></questionText>
							<questionTypeId>{$questionRow['id_question_type']}</questionTypeId>
							<image>{$questionRow['image']}</image>
							<imageData>{$questionRow['image_data']}</imageData>
							<answerText><![CDATA[{$questionRow['answer_text']}]]></answerText>
							<answers>
				";

				$quizAnswerRows = ExportQuizAnswers($questionRow['id_question']);

// @TODO double quotes
				foreach ($quizAnswerRows as $answerRow)
					echo "
								<answer>
									<answerText><![CDATA[{$answerRow['answer_text']}]]></answerText>
									<isCorrect>{$answerRow['is_correct']}</isCorrect>
								</answer>
					";

// @TODO double quotes
				echo "
							</answers>
						</question>
				";
			}

// @TODO double quotes
			echo "
					</questions>
				</quiz>
			";
		}

		echo '</quizes>';
	}
}
?>