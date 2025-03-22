<?php

namespace Quiz;

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
		// Strip all tags then convert line breaks to \n
		$msg = str_replace(array('\r', ''), array('__CR__', '\n'), $msg);
		$msg = stripcslashes(html_entity_decode($msg, ENT_QUOTES, 'UTF-8'));
		$msg = str_replace('__CR__', '\n', $msg);

		return $msg;
	}
}