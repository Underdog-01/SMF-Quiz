<?php

namespace Quiz;

use Quiz\Integration;

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

	public static function quiz_usersAcknowledge($profileField)
	{
		global $smcFunc;

		$members = [];
		if (!empty($profileField))
		{

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

	public static function quiz_pmFilter($msg)
	{
		global $sourcedir;
		
		require_once($sourcedir . '/Subs-Post.php');
		// Strip all tags then convert line breaks to \n
		$msg = str_replace(['\r', '\n'], ['', '__CR__'], $msg);
		$msg = str_replace(['<br>', '<br/>', '<br />'], "__CR__", $msg);
		$msg = stripcslashes(html_entity_decode(htmlspecialchars_decode($msg, ENT_NOQUOTES|ENT_HTML5), ENT_QUOTES, 'UTF-8'));
		$msg = str_replace("'", "\'", $msg);		
		$htmlmessage = htmlspecialchars(str_replace(array("&apos;", "\'"), "'", $msg), ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
		preparsecode($htmlmessage);
		$htmlmessage = str_replace('__CR__', chr(13), $htmlmessage);

		return $htmlmessage;
	}

	public static function quiz_commonStringFilter($string)
	{
		$string = stripcslashes(htmlspecialchars_decode($string, ENT_QUOTES|ENT_HTML5));
		return htmlspecialchars($string, ENT_QUOTES|ENT_HTML5, 'UTF-8');
	}

	public static function quiz_commonImageFileFilter($image_filename)
	{
		return preg_replace('#[^a-zA-Z0-9\-\._\/]#','', $image_filename);
	}

	public static function format_infostring($stringToFormat, $toHtml = true)
	{
		// Filter all input to raw HTML5 with only escaped single quotes
		$stringToFormat = self::quiz_pmFilter(htmlspecialchars_decode($stringToFormat, ENT_QUOTES|ENT_HTML5));
		$stringToFormat = html_entity_decode($stringToFormat, ENT_QUOTES, 'UTF-8');
		$stringToFormat = str_replace(array("\'", "'"), "&apos;", $stringToFormat);
		return str_replace('\n', '<br>', $stringToFormat);
	}

	public static function format_string($stringToFormat, $toHtml = true)
	{
		global $smcFunc;

		// Remove any slashes. These should not be here, but it has been known to happen
		$returnString = str_replace("\\", "", $smcFunc['db_unescape_string']($stringToFormat));
		$returnString = stripcslashes($returnString);

		// We only want to convert from carriage returns to HTML breaks if the output is HTML
		if ($toHtml)
			$returnString = str_replace(chr(13), "<br>", $returnString);

		//return html_entity_decode($returnString, ENT_QUOTES, 'UTF-8');
		return $returnString;
	}

}