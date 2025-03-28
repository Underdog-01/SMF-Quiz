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
	global $context, $modSettings, $sourcedir;
	// Get the key ids for the quizzes to package. This function returns a string containing a comma separated list of id's
	// @TODO check and validate inputs

	if (empty($_POST['quizIds']))
		return;

	require_once($sourcedir . '/Quiz/Admin.php');
	$quizKeys = explode(',', urldecode($_POST['quizIds']));
	$quizKeys = array_map(function($id) { return (int) $id; }, $quizKeys);
	$quizKeys = array_unique($quizKeys);

	if (empty($quizKeys))
		return;

	$packageName = (!empty($_POST['packageName']) ? urldecode($_POST['packageName']) : 'No-Name-Entered');
	$packageName = preg_replace('/[^a-zA-Z0-9\-\_]/','', $packageName) . '.xml';

// @TODO localization?
	$packageDescription = 'No description entered';
	if (!empty($_POST['packageDescription']))
		$packageDescription = urldecode($_POST['packageDescription']);

// @TODO localization?
	$packageAuthor = 'No author entered';
	if (!empty($_POST['packageAuthor']))
		$packageAuthor = urldecode($_POST['packageAuthor']);

// @TODO localization?
	$packageSiteAddress = 'No site entered';
	if (!empty($_POST['packageSiteAddress']))
		$packageSiteAddress = urldecode($_POST['packageSiteAddress']);

	// Remove any rogue temp Quiz files
	$tempPaths = glob($sourcedir . '/Quiz/Temp/*');
	foreach ($tempPaths as $tempPath) {
		if (is_dir($tempPath)) {
			quizRmdir($tempPath);
		}
		elseif (!in_array(basename($tempPath), array('index.php', '.htaccess'))) {
			@unlink($tempPath);
		}
	}

	if (sizeof($quizKeys) > 0 && empty($modSettings['SMFQuiz_ZipExport'])) {
		$quizRows = ExportQuizzes($quizKeys);
		if ($file = BuildQuizFileXML(pathinfo($packageName, PATHINFO_FILENAME), $quizRows, $packageDescription, $packageAuthor, $packageSiteAddress)) {
			clearstatcache();
			exit(basename($file));
		}
		exit(basename($xmlFile));
	}
	elseif (sizeof($quizKeys) > 0) {
		$quizRows = ExportQuizzes($quizKeys);
		if ($file = BuildQuizPHP(pathinfo($packageName, PATHINFO_FILENAME), $quizRows, $packageDescription, $packageAuthor, $packageSiteAddress)) {
			clearstatcache();
			exit(basename($file));
		}
	}
}

function quiz_format_array($quizArray)
{
	if(is_string($quizArray)) {
		$quizArray = format_string2($quizArray);
	}
	else {
		$quizData = ['title', 'description', 'category_name', 'question_text', 'answer_text'];
		foreach ($quizData as $index => $data) {
			if (!empty($quizArray[$index]) && is_string($quizArray[$index])) {
				$quizArray[$index] = format_string2($quizArray[$index]);
			}
		}
	}

	return $quizArray;
}

function quiz_format_xml_array($quizArray)
{
	global $smcFunc;

	if(is_string($quizArray)) {
		$quizArray = html_entity_decode($smcFunc['db_unescape_string']($quizArray), ENT_QUOTES|ENT_HTML5, 'UTF-8');
		$quizArray = htmlspecialchars($quizArray, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}
	else {
		$quizData = ['title', 'description', 'category_name', 'question_text', 'answer_text'];
		foreach ($quizData as $index => $data) {
			if (!empty($quizArray[$index]) && is_string($quizArray[$index])) {
				$quizArray[$index] = html_entity_decode($smcFunc['db_unescape_string']($quizArray[$index]), ENT_QUOTES|ENT_HTML5, 'UTF-8');
				$quizArray[$index] = htmlspecialchars($quizArray[$index], ENT_XML1 | ENT_QUOTES, 'UTF-8');
			}
		}
	}

	return $quizArray;
}

function BuildQuizFileXML($packageName, $quizRows, $packageDescription, $packageAuthor, $packageSiteAddress)
{
	global $context, $modSettings, $user_settings, $settings, $sourcedir;

	$tempPath = 'temp_' . substr(md5(rand(1000, 9999999)), 0, 10);
	$quizRows = quiz_format_xml_array($quizRows);
	$quizXML = '<?xml version="1.0" encoding="utf-8"?>
<quizzes>
	<description>' . quiz_format_xml_array($packageDescription) . '</description>
	<author>' . quiz_format_xml_array($packageAuthor) . '</author>
	<siteAddress>' . quiz_format_xml_array($packageSiteAddress) . '</siteAddress>
	<packageDate>' . quiz_format_xml_array(date("F j, Y, g:i a", time())) . '</packageDate>
	<smfQuizVersion>' . quiz_format_xml_array($modSettings["smf_quiz_version"]) . '</smfQuizVersion>
	<smfVersion>' . quiz_format_xml_array($modSettings["smfVersion"]) . '</smfVersion>';

		foreach ($quizRows as $row)
		{
// @TODO double quotes
			$quizXML .= "
	<quiz>
		<title><![CDATA[" . $row['title'] . "]]></title>
		<categoryName><![CDATA[" . $row['category_name'] . "]]></categoryName>
		<description><![CDATA[" . $row['description'] . "]]></description>
		<playLimit>" . $row['play_limit'] . "</playLimit>
		<secondsPerQuestion>" . $row['seconds_per_question'] . "</secondsPerQuestion>
		<showAnswers>" . $row['show_answers'] . "</showAnswers>
		<image><![CDATA[" . $row['image'] . "]]></image>
		<imageData><![CDATA[" . $row['image_data'] . "]]></imageData>
		<questions>";

			$quizQuestionRows = quiz_format_xml_array(ExportQuizQuestions($row['id_quiz']));

// @TODO double quotes
			foreach ($quizQuestionRows as $questionRow)
			{
				$quizXML .= "
			<question>
				<questionText><![CDATA[" . $questionRow['question_text'] . "]]></questionText>
				<questionTypeId>" . $questionRow['id_question_type'] . "</questionTypeId>
				<image>" . $questionRow['image'] . "</image>
				<imageData>" . $questionRow['image_data'] . "</imageData>
				<answerText><![CDATA[" . $questionRow['answer_text'] . "]]></answerText>
				<answers>";

				$quizAnswerRows = quiz_format_xml_array(ExportQuizAnswers($questionRow['id_question']));

// @TODO double quotes
				foreach ($quizAnswerRows as $answerRow)
					$quizXML .= "
					<answer>
						<answerText><![CDATA[" . $answerRow['answer_text'] . "]]></answerText>
						<isCorrect>" . $answerRow['is_correct'] . "</isCorrect>
					</answer>";

// @TODO double quotes
				$quizXML .= "
				</answers>
			</question>";
			}

// @TODO double quotes
			$quizXML .= "
		</questions>
	</quiz>";
		}

		$quizXML .= '
</quizzes>';
		clearstatcache();
		if (is_dir($sourcedir . '/Quiz/Temp/' . $tempPath)) {
			quizRmdir($sourcedir . '/Quiz/Temp/' . $tempPath);
		}
		@mkdir($sourcedir . '/Quiz/Temp/' . $tempPath, 0755);
		$fw = fopen($sourcedir . '/Quiz/Temp/' . $tempPath . '/' . $packageName . '.xml', 'wb', true);
		fwrite($fw, $quizXML);
		fclose($fw);

		@chmod($sourcedir . '/Quiz/Temp/' . $tempPath . '/' . $packageName . '.xml', 0644);
		$xmlFile = BuildQuizZipFile($packageName, $sourcedir . '/Quiz/Temp/' . $tempPath, [], []);

		return !empty($xmlFile) && file_exists($sourcedir . '/Quiz/Temp/' . $packageName . '.zip') ? $sourcedir . '/Quiz/Temp/' . $packageName . '.zip' : '';
}

function BuildQuizPHP($packageName, $quizRows, $packageDescription, $packageAuthor, $packageSiteAddress)
{
	global $context, $modSettings, $user_settings, $settings, $sourcedir;

	$tempPath = 'temp_' . substr(md5(rand(1000, 9999999)), 0, 10);
	$quizRows = quiz_format_array($quizRows);
	list($images, $qImages) = [[], []];
	$quizPHP = '<?php
/*---------------------------------------------------------------*/
/* File Created by SMF-Quiz ' . $modSettings['smf_quiz_version'] . '
/* File Generated: ' . gmdate("D, d M Y H:i:s") . ' GMT
/*---------------------------------------------------------------*/
namespace Quiz;

if (!defined("SMF"))
	die("Hacking attempt...");

class QuizImport
{
	public static function quizImportData()
	{
		$newQuizLoadData = [
			"quizzes" => [
				"description" => "' .  quiz_format_array($packageDescription) . '",
				"author" => "' . quiz_format_array($packageAuthor) . '",
				"siteAddress" => "' . quiz_format_array($packageSiteAddress) . '",
				"packageDate" => "' . quiz_format_array(date("F j, Y, g:i a", time())) . '",
				"smfQuizVersion" => "' . quiz_format_array($modSettings["smf_quiz_version"]) . '",
				"smfVersion" => "' . quiz_format_array($modSettings["smfVersion"]) . '",';

	foreach ($quizRows as $key0 => $row) {
		$images[] = $row['image'];
		$quizPHP .= '
				"quiz' . $key0 . '" => [
					"title" => "' . $row['title'] . '",
					"description" => "' . $row['description'] . '",
					"playLimit" => "' . $row['play_limit'] . '",
					"secondsPerQuestion" => "' . $row['seconds_per_question'] . '",
					"showAnswers" => "' . $row['show_answers'] . '",
					"categoryName" => "' . $row['category_name'] . '",
					"image" => "' . $row['image'] . '",
					"user_name" => "' . $user_settings['member_name'] . '",
					"email_address" => "' . $user_settings['email_address'] . '",
					"theme_url" => "' . $settings['theme_url'] . '",
					"questions" => [';

		$quizQuestionRows = quiz_format_array(ExportQuizQuestions($row['id_quiz']));

		foreach ($quizQuestionRows as $key1 => $questionRow) {
			$qImages[] = $questionRow['image'];
			$quizPHP .= '
						"question' . $key1 . '" => [
							"questionText" => "' . $questionRow['question_text'] . '",
							"questionTypeId" => "' . $questionRow['id_question_type'] . '",
							"answerText" => "' . $questionRow['answer_text'] . '",
							"image" => "' . $questionRow['image'] . '",
							"answers" => [';

			$quizAnswerRows = quiz_format_array(ExportQuizAnswers($questionRow['id_question']));

			foreach ($quizAnswerRows as $key2 => $answerRow)	{
				$quizPHP .= '
								"answer' . $key2 . '" => [
									"answerText" => "' . $answerRow['answer_text'] . '",
									"isCorrect" => "' . $answerRow['is_correct'] . '",
								],';
			}

			$quizPHP .= '
							],
						],';
		}

		$quizPHP .= '
					],
				],';
	}

	$quizPHP .= '
			],
		];

		return $newQuizLoadData;
	}
}

?>';
	clearstatcache();
	if (is_dir($sourcedir . '/Quiz/Temp/' . $tempPath)) {
		quizRmdir($sourcedir . '/Quiz/Temp/' . $tempPath);
	}
	@mkdir($sourcedir . '/Quiz/Temp/' . $tempPath, 0755);
	@mkdir($sourcedir . '/Quiz/Temp/' . $tempPath . '/Questions', 0755);
	@mkdir($sourcedir . '/Quiz/Temp/' . $tempPath . '/Quizzes', 0755);

	$fw = fopen($sourcedir . '/Quiz/Temp/' . $tempPath . '/' . $packageName . '.php', 'wb', true);
	fwrite($fw, $quizPHP);
	fclose($fw);
	@chmod($sourcedir . '/Quiz/Temp/' . $tempPath . '/' . $packageName . '.php', 0644);
	$zipFile = BuildQuizZipFile($packageName, $sourcedir . '/Quiz/Temp/' . $tempPath, $images, $qImages);

	return !empty($zipFile) && file_exists($sourcedir . '/Quiz/Temp/' . $packageName . '.zip') ? $sourcedir . '/Quiz/Temp/' . $packageName . '.zip' : '';
}

function BuildQuizZipFile($archiveName, $tempPath, $images, $qImages)
{
	global $sourcedir, $settings;

	clearstatcache();
	if (file_exists($sourcedir . '/Quiz/Temp/' . $archiveName . '.zip')) {
		unlink($sourcedir . '/Quiz/Temp/' . $archiveName . '.zip');
	}

	foreach ($images as $image) {
		if (file_exists($settings['default_theme_dir'] . '/images/quiz_images/Quizzes/' . $image)) {
			@copy($settings['default_theme_dir'] . '/images/quiz_images/Quizzes/' . $image, $tempPath . '/Quizzes/' . $image);
		}
	}
	foreach ($qImages as $image) {
		if (file_exists($settings['default_theme_dir'] . '/images/quiz_images/Questions/' . $image)) {
			@copy($settings['default_theme_dir'] . '/images/quiz_images/Questions/' . $image, $tempPath . '/Questions/' . $image);
		}
	}

	$zip = new ZipArchive();
	if ($zip->open($sourcedir . '/Quiz/Temp/' . $archiveName . '.zip', ZipArchive::CREATE) !== TRUE) {
		log_error("cannot open <$filename>\n");
		return false;
	}

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($tempPath),
		RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ($files as $file)
	{
		if (!$file->isDir())
		{
			$filePath = $file->getRealPath();
			$ext = pathinfo($filePath, PATHINFO_EXTENSION);
			$relativePath = in_array($ext, ['php', 'xml']) ? basename($filePath) : basename(dirname($filePath)) . '/' . basename($filePath);
			$zip->addFile($filePath, $relativePath);
		}
	}
	$zip->close();

	quizRmdir($tempPath);
	clearstatcache();
	return true;
}

?>