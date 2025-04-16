<?php

namespace Quiz;

use Quiz\Integration;
use Quiz\ForceEncoding;

if (!defined('SMF'))
	die('Hacking attempt...');

class Helper
{
	// Useful helper functions for various tasks
	public static function quiz_usersAllowedTo($permission)
	{
		global $smcFunc;

		$members = [];
		if (!empty($permission))
		{

			$request = $smcFunc['db_query']('', '
				SELECT p.id_group, p.permission, p.add_deny, m.id_member
				FROM {db_prefix}permissions p
				LEFT JOIN {db_prefix}members m ON m.id_group = {int:adminGroup} OR (m.id_group = p.id_group OR FIND_IN_SET(p.id_group, m.additional_groups))
				WHERE permission = {string:perm}',
				[
					'perm' => $permission, 'adminGroup' => 1,
				]
			);

			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				$members[] = $row['id_member'];
			}

			$smcFunc['db_free_result']($request);
		}

		return array_filter($members);
	}

	public static function quiz_usersAcknowledge($profileField, $default = false)
	{
		global $smcFunc;

		$members = [];
		if (!empty($profileField))
		{
			// fields that require enabled as default
			if (in_array($profileField, ['quiz_pm_alert']) || !empty($default)) {
				$request = $smcFunc['db_query']('', '
					SELECT m.id_member
					FROM {db_prefix}members m
					LEFT JOIN {db_prefix}quiz_members qm ON qm.id_member = m.id_member
					WHERE qm.id_member IS NULL',
					[]
				);

				while ($row = $smcFunc['db_fetch_assoc']($request)) {
					$members[] = $row['id_member'];
				}

				$smcFunc['db_free_result']($request);
			}

			$request = $smcFunc['db_query']('', '
				SELECT qm.id_member, qm.quiz_pm_report, qm.quiz_pm_alert, qm.quiz_count
				FROM {db_prefix}quiz_members qm
				INNER JOIN {db_prefix}members m ON m.id_member = qm.id_member
				WHERE qm. ' . $profileField . ' = {int:val}',
				[
					'val' => 1,
				]
			);

			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				$members[] = $row['id_member'];
			}

			$smcFunc['db_free_result']($request);
		}

		return array_filter($members);
	}

	public static function quiz_userInfoName($id = 0)
	{
		global $modSettings, $smcFunc;

		if (!empty($id)) {
			$request = $smcFunc['db_query']('', '
				SELECT real_name, member_name
				FROM {db_prefix}members
				WHERE id_member = {int:val}',
				[
					'val' => $id,
				]
			);

			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				$quiz_name = !empty($row['real_name']) ? $row['real_name'] : (!empty($row['member_name']) ? $row['member_name'] : '');
			}

			$smcFunc['db_free_result']($request);
		}

		return !empty($quiz_name) ? $quiz_name : '';
	}

	public static function get_image_files($imageFolder = 'Quizzes')
	{
		global $settings;

		//define the path as relative
		list($files, $path) = [[], rtrim($settings['default_theme_dir'] . '/images/quiz_images/' . $imageFolder, '/')];

		if (is_dir($path)) {
			clearstatcache($settings['default_theme_dir'] . '/images/quiz_images');
			$getFiles = glob($path . '/*.{jpg,png,gif,bmp,jpeg}', GLOB_BRACE);
			foreach ($getFiles as $file) {
				$files[] = basename($file);
			}
		}

		return $files;
	}

	public static function quiz_pmFilter($msg)
	{
		global $sourcedir;

		// Strip all tags then convert line breaks & apostophes to character codes
		$msg = str_replace(['\r', '\n'], ['', '__CR__'], $msg);
		$msg = str_replace(['<br>', '<br/>', '<br />'], "__CR__", $msg);
		$msg = stripcslashes(html_entity_decode(htmlspecialchars_decode($msg, ENT_NOQUOTES|ENT_HTML5), ENT_QUOTES, 'UTF-8'));
		$htmlmessage = str_replace(["&apos;", "'", "__CR__"], [chr(39), chr(39), chr(13)], $msg);

		return $htmlmessage;
	}

	public static function quiz_commonStringFilter($string)
	{
		$string = stripcslashes(htmlspecialchars_decode($string, ENT_QUOTES|ENT_HTML5));
		return htmlspecialchars($string, ENT_QUOTES|ENT_HTML5, 'UTF-8');
	}

	public static function quiz_commonImageFileFilter($image_filename)
	{
		$image_filename = str_replace(' ', '_', $image_filename);
		return preg_replace('#[^a-zA-Z0-9\-\._\/]#', '', $image_filename);
	}

	public static function format_infostring($stringToFormat, $toHtml = true)
	{
		// Filter all input to raw HTML5 with only escaped single quotes
		$stringToFormat = self::quiz_pmFilter(htmlspecialchars_decode($stringToFormat, ENT_QUOTES|ENT_HTML5));
		$stringToFormat = html_entity_decode($stringToFormat, ENT_QUOTES, 'UTF-8');
		$stringToFormat = str_replace(array("\'", chr(39), "'"), "&apos;", $stringToFormat);
		$stringToFormat = self::format_entities($stringToFormat, true);
		return str_replace('\n', '<br>', $stringToFormat);
	}

	public static function format_string($stringToFormat, $inputType = '', $toHtml = true)
	{
		global $smcFunc;

		$stringToFormat = str_replace(['quizes', 'Quizes'], ['quizzes', 'Quizzes'], $stringToFormat);
		$stringToFormat = self::format_entities($stringToFormat, false);

		// Remove any slashes. These should not be here, but it has been known to happen
		$returnString = str_replace("\\", "", stripcslashes($stringToFormat));
		$returnString = stripcslashes($returnString);

		// We only want to convert from carriage returns to HTML breaks if the output is HTML
		if ($toHtml) {
			$returnString = str_replace(chr(13), "<br>", $returnString);
			$returnString = html_entity_decode(htmlspecialchars_decode($returnString, ENT_QUOTES|ENT_HTML5), ENT_QUOTES, 'UTF-8');
			$returnString = str_replace(["'", '"'], [chr(39), chr(34)], $returnString);
		}

		switch ($inputType){
			case 'input':
				$returnString = strip_tags($returnString);
				$returnString = str_replace(['"'], ['&quot;'], $returnString);
				break;
			case 'textarea':
				$returnString = str_replace(['<br>', '<br/>', '<br />'], chr(13), $returnString);
				$returnString = str_replace(['"'], ['&quot;'], $returnString);
				break;
		}

		return $returnString;
	}

	public static function format_string_subedit($stringToFormat)
	{
		global $smcFunc;

		// Remove any backslashes
		$stringToFormat = str_replace(array("\\", "quizes", "Quizes"), array("", "quizzes", "Quizzes"), stripcslashes($stringToFormat));

		// Ensure double|single quotes are explicitly HTML5 entities
		$stringToFormat = self::format_entities($stringToFormat, true);
		$stringToFormat = str_replace(["'", '"'], ['&apos;', '&quot;'],  htmlspecialchars_decode($stringToFormat, ENT_QUOTES));
		$returnString = str_replace(["'", '"'], ['&apos;', '&quot;'], html_entity_decode(self::format_entities($stringToFormat, true), ENT_QUOTES|ENT_HTML5, 'UTF-8'));

		return $returnString;
	}

	public static function format_entities($string, $decoded = true)
	{
		// this should remove the character padding from mixed 3 & 4 Byte strings
		$encodedString = ForceEncoding::toUTF8(stripcslashes($string));
		$string = ForceEncoding::fixUTF8($encodedString, ForceEncoding::QUIZ_ICONV_IGNORE);

		// Literal replacements ~ this needs refinement and may have more characters added for any older Quiz formats
		// .. ST/OSC padding was removed therefore left|right/single|double quotes become standard quotes
		$find = [
			'â', 'Ã¢ÂÂ', 'ÃƒÂ¡', 'ÃƒÂ¤', 'Ãƒâ€ž', 'ÃƒÂ§', 'ÃƒÂ©', 'Ãƒâ€°', 'ÃƒÂ¨', 'ÃƒÂ¬', 'ÃƒÂª', 'ÃƒÂ­', 'ÃƒÂ¯', 'Ã„Â©', 'ÃƒÂ³', 'ÃƒÂ¸', 'ÃƒÂ¶', 'Ãƒâ€“', 'Ã…Â¡', 'ÃƒÂ¼', 'LÃƒÂº', 'Ã…Â©', 'ÃƒÂ±', 'Ã¥',
			'Ã¤', 'Ã¶', 'Ã…', 'Ã„', 'Ã–', 'Ã©', 'Ã¸', 'Ã¦', 'Ã˜', 'Ãµ', 'â€¢', 'Ãº', 'Ã', 'Ãƒ', 'Ã‡', 'â€', 'â€œ', 'Ã‰', 'Â”', 'Ã™', 'Â„', 'Â´', 'Â†', 'Ã¿', 'Ã«', 'Â›', 'Ã€', 'Ã‚', 'Ãƒ', 'Ãˆ', 'Ã‰', 'ÃŠ', 'Ã‹', 'ÃŒ',
		];
		$replace = [
			'"', '"', 'á', 'ä', 'ä', 'ç', 'é', 'É', 'è', 'ě', 'ê', 'í', 'ï', 'ĩ', 'ó', 'ø', 'ö', 'ö', 'š', 'ü', 'ú', 'ũ', 'ñ', 'å', 'ä', 'ö', 'Å', 'Ä', 'Ö', '©', 'œ', 'æ', 'Ø', 'õ', '-', 'ú', 'À', 'Ã',
			'Ç', '"', '"', 'É', 'ö', 'Ù', 'Ä', 'ô', 'Æ', 'ÿ', 'ë', 'Û', 'À', 'Â', 'Ã', 'È', 'É', 'Ê', 'Ë', 'Ì',
		];

		if (empty($decoded)) {
			array_walk($replace, function ($value, $key) {
				$replace[$key] = htmlspecialchars($value);
			});
		}

		$string = str_replace($find, $replace, $string);

		// any other non-utf8 characters can be ignored
		return iconv('UTF-8', 'UTF-8//IGNORE', $string);
	}

	public static function view_all($link = '')
	{
		global $txt, $scripturl;

		$output = '
											<button style="font-size: x-small;" title="' . $txt['SMFQuiz_Common']['ViewAll'] . '" type="button" class="quiz_viewall_button" onclick="window.location.href=\'' . (!empty($link) ? $link : $scripturl . '?action=SMFQuiz;sa=home') . '\'">
												<svg class="quiz_viewall_svg" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 18 18">
													<path fill="currentColor" fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 8M7.646.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 1.707V5.5a.5.5 0 0 1-1 0V1.707L6.354 2.854a.5.5 0 1 1-.708-.708zM8 10a.5.5 0 0 1 .5.5v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L7.5 14.293V10.5A.5.5 0 0 1 8 10"></path>
												</svg>
												' . $txt['SMFQuiz_Common']['ViewAll'] . '
											</button>';

		return $output;
	}

	public static function resize_image_max(object $image, int $max_width, int $max_height, array &$errors=array())
	{
		$w = imagesx($image);
		$h = imagesy($image);
		if (!$w || !$h) {
			$errors[] = 'Image could not be resized because it was not a valid image.';
			return false;
		}

		if (($w <= $max_width) && ($h <= $max_height)) {
			return $image;
		}

		$ratio = $max_width / $w;
		$new_w = $max_width;
		$new_h = $h * $ratio;


		if ($new_h > $max_height) {
			$ratio = $max_height / $h;
			$new_h = $max_height;
			$new_w = $w * $ratio;
		}

		$new_w = round($new_w);
		$new_h = round($new_h);
		$new_image = imagecreatetruecolor($new_w, $new_h);
		imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

		return $new_image;
	}

	public static function resize_image($image_filename, $new_image_filename, $width, $height)
	{
		$errors = [];

		if (!$image_filename) {
			$errors[] = 'No source image location specified.';
		}
		else {
			if (!file_exists($image_filename)) {
				$errors[] = 'Image source file does not exist.';
			}
			$extension = strtolower(pathinfo(basename($image_filename), PATHINFO_EXTENSION));
			if (!in_array($extension, array('jpg','jpeg','png','gif','bmp'))) {
				$errors[] = 'Invalid source file extension!';
			}
		}

		if (!$new_image_filename) {
			$errors[] = 'No destination image location specified.';
		}
		else {
			$new_extension = strtolower(pathinfo(basename($new_image_filename), PATHINFO_EXTENSION));
			if (!in_array($new_extension,array('jpg','jpeg','png','gif','bmp'))) {
				$errors[] = 'Invalid destination file extension!';
			}
		}

		$width = abs(intval($width));
		if (!$width) {
			$errors[] = 'No width specified!';
		}

		$height = abs(intval($height));
		if (!$height) {
			$errors[] = 'No height specified!';
		}

		if (count($errors) > 0) {
			return $errors;
		}

		$mimeType = @getimagesize($image_filename);

		if (!empty($mimeType)) {
			switch ($mimeType[2]) {
				case IMAGETYPE_GIF:
					$image = @imagecreatefromgif($image_filename);
					break;
				case IMAGETYPE_BMP:
					$image = @imagecreatefromwbmp($image_filename);
					break;
				case IMAGETYPE_PNG:
					$image = @imagecreatefrompng($image_filename);
					break;
				default:
					$image = @imagecreatefromjpeg($image_filename);
			}
		}

		if (empty($image)) {
			$errors[] = 'Image could not be generated!';
		}
		else {
			$current_width = imagesx($image);
			$current_height = imagesy($image);
			if ((!$current_width) || (!$current_height)) {
				$errors[] = 'Generated image has invalid dimensions!';
			}
		}
		if (count($errors) > 0) {
			error_log(json_encode($errors));
			@imagedestroy($image);
			return $errors;
		}

		$new_image = self::resize_image_max($image, $width, $height, $errors);

		if (!$new_image && !count($errors)) {
			$errors[] = 'New image could not be generated!';
		}
		if (count($errors)) {
			@imagedestroy($image);
			return $errors;
		}

		$save_error = false;

		switch ($extension) {
			case 'gif':
				@imagegif($new_image, $new_image_filename) or ($save_error = true);
				break;
			case 'bmp':
				@imagewbmp($new_image, $new_image_filename) or ($save_error = true);
				break;
			case 'png':
				@imagepng($new_image, $new_image_filename) or ($save_error = true);
				break;
			default:
				@imagejpeg($new_image, $new_image_filename) or ($save_error = true);
		}

		if ($save_error) {
			$errors[] = 'New image could not be saved!';
		}
		if (count($errors) > 0) {
			if (!empty($image)) {
				unset($image);
			}
			@imagedestroy($new_image);
			return $errors;
		}

		if (!empty($image)) {
			unset($image);
		}
		@imagedestroy($new_image);

		return [];
	}
}