<?php

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * At the moment the function dies here because it provides xml or json outputs
 * that don't cope very well with the rest of the template... :-P
 */
function quizImageUpload ()
{
	if (empty($_GET['sa']))
		die();

	if (!allowedTo('quiz_admin'))
	{
		// @TODO implement an error handling
		$context['quiz_error'] = 'cannot_admin';
		die();
	}

	// The function to be used is specified in the sub-action querystring
	$action = $_GET['sa'];
	switch ($action)
	{
		case 'imageList':
			GetImages();
			break;
		case 'imageUpload':
			ImageUpload();
			break;
	}
	die();
}

/*
Function that handles the retrieval of images from the quiz images folder, or the specified subfolder of the quiz images folder. The result
is returned as XML for use in displaying the file listing as part of an AJAX call. The XML is in the following format:
<files>
	<file></file>
</files>
*/
function GetImages()
{
	header("Content-Type: text/xml");

	if (isset($_GET['imageFolder']))
		$imageFolder = $_GET['imageFolder'];
	else
		$imageFolder = '';

	$files = Quiz\Helper::get_image_files($imageFolder);

	sort($files);
	echo '<files>';
	foreach ($files as $file) {
		echo '<file>' . basename($file) . '</file>';
	}

	echo '</files>';
}

/*
Function used for uploading an image to the quiz images folder, or the specified sub folder of the quiz images folder.
*/
function ImageUpload()
{
	global $settings;

	$error = "";
	$msg = "";
	$fileName = "";
	$fileElementName = 'fileToUpload';
	if(!empty($_FILES[$fileElementName]['error']))
	{
	// @TODO localization
		switch($_FILES[$fileElementName]['error'])
		{
			case '1':
				$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case '2':
				$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case '3':
				$error = 'The uploaded file was only partially uploaded';
				break;
			case '4':
				$error = 'No file was uploaded.';
				break;
			case '6':
				$error = 'Missing a temporary folder';
				break;
			case '7':
				$error = 'Failed to write file to disk';
				break;
			case '8':
				$error = 'File upload stopped by extension';
				break;
			case '999':
			default:
				$error = 'No error code available';
		}
	}
	elseif(empty($_FILES['fileToUpload']['tmp_name']) || $_FILES['fileToUpload']['tmp_name'] == 'none')
	{
		$error = 'No file was uploaded..';
	}
		// @TODO check
	elseif(!preg_match('/image/', $_FILES['fileToUpload']['type']))
	{
		$msg = ('The uploaded file is not an image please upload a valid file');
		@unlink($_FILES['fileToUpload']['tmp_name']);
	}
	else
	{
		$msg .= " File Name: " . $_FILES['fileToUpload']['name'] . ", ";
		$msg .= " File Size: " . @filesize($_FILES['fileToUpload']['tmp_name']);
		$fileName = $_FILES['fileToUpload']['name'];

		// Where it will be saved?
		if (isset($_GET['imageFolder']))
			$imageFolder = $_GET['imageFolder'] . '/';
		else
			$imageFolder = '';

		$destination = $settings['default_theme_dir'] . '/images/quiz_images/' . $imageFolder . Quiz\Helper::quiz_commonImageFileFilter($_FILES['fileToUpload']['name']);
		// @TODO chmod??
		@chmod($destination, 0644);

		// It is already here
		if (file_exists($destination))
			$msg = ('Filename already exists on destination');

		// Move and make writable
		// @TODO chmod??
		move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $destination);
		@chmod($destination, 0644);
	}
		// @TODO echo
	echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg . "',\n";
	echo				"filename: '" . $fileName . "'\n";
	echo "}";

	clearstatcache();
}

?>