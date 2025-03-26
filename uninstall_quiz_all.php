<?php

/**
 * @package SMF Quiz
 * @version 2.0.3
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2022, SMF Tricks
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc, $sourcedir, $settings;

if (!isset($smcFunc['db_create_table']))
	db_extend('packages');

remove_integration_function('integrate_pre_load', '$sourcedir/Quiz/Integration.php|Quiz\Integration::init');

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}scheduled_tasks
	WHERE task = {string:name}',
	[
		'name' => 'quiz_maintenance',
	]
);

$toRemove = ['smf_quiz_version', 'SMFQuiz_AutoClean', 'SMFQuiz_enabled', 'SMFQuiz_ImportQuizzesAsUserId', 'SMFQuiz_SessionTimeLimit', 'SMFQuiz_Welcome'];

if (!empty($toRemove))
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}settings
			WHERE variable IN ({array_string:remove})',
			[
				'remove' => $toRemove,
			]
		);

$tables = ['quiz', 'quiz_category', 'quiz_question', 'quiz_infoboard', 'quiz_league', 'quiz_answer', 'quiz_league_result', 'quiz_question_type', 'quiz_league_table', 'quiz_result', 'quiz_session', 'quiz_dispute', 'quiz_members'];

foreach ($tables as $table) {
	if (check_table_existsQuizUninstall($table)) {
		$smcFunc['db_drop_table']('{db_prefix}' . $table);
	}
}

clearstatcache();
$paths = [
	$sourcedir . '/Quiz', $settings['default_theme_dir'] . '/Quiz', $settings['default_theme_dir'] . '/languages/Quiz', $settings['default_theme_dir'] . '/images/quiz_images',
	$settings['default_theme_dir'] . '/css/quiz', $settings['default_theme_dir'] . '/scripts/quiz',
];

foreach ($paths as $path) {
	if (is_dir($path)) {
		quizRmdir_uninstall($path);
	}
}
clearstatcache();

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}permissions
	WHERE permission LIKE {string:permission}',
	array('permission' => 'quiz_%')
);

function check_table_existsQuizUninstall($table)
{
	global $db_prefix, $smcFunc;

	if ($smcFunc['db_list_tables'](false, $db_prefix . $table))
		return true;

	return false;
}

function quizRmdir_uninstall($dir, $ignore = '')
{
	global $modSettings, $boarddir;

	// linux/windows compatibility
	$ignore = empty($ignore) ? '/' . uniqid('ignore_', true) . '/' : $ignore;
	$boarddirx = str_replace('\\', '/', $boarddir);
	$thisPath = str_replace('\\', '/', $dir);
	$ignore = str_replace('\\', '/', $ignore);

	$boarddirx = trim($boarddirx, '/');
	$mainPathArray = array('Sources', 'Themes', 'Packages', 'Smileys', 'cache', 'avatars', 'attachments');
	$thisPath = trim($thisPath, '/');

	// make absolutely sure the deleted path is not an essential parent path
	if ($thisPath == '.' || $thisPath == '..')
		return false;
	if ($thisPath == $boarddirx)
		return false;

	foreach ($mainPathArray as $path)
	{
		if ($thisPath == $boarddirx . '/' . $path)
			return false;
	}

	clearstatcache(false, $dir);
	if (is_dir($dir) && stripos(str_replace('\\', '/', $dir), $ignore) === FALSE && stripos(str_replace('\\', '/', $dir), rtrim($ignore, '/')) === FALSE)
	{
		$objects = scandir($dir);
		foreach ($objects as $object)
		{
			if ($object != '.' && $object != '..')
			{
				clearstatcache(false, $dir . '/' . $object);
				if (is_dir($dir . '/' . $object)) {
					quizRmdir_uninstall($dir . '/' . $object, $ignore);
				}
				else {
					@chmod($dir . '/' . $object, 0777);
					@unlink($dir . '/' . $object);
				}
			}
		}

		reset($objects);
		clearstatcache(false, $dir);
		if (is_readable($dir) && is_dir($dir)) {
			if (count(scandir($dir)) == 2) {
				@chmod($dir, 0777);
				if (@rmdir($dir)) {
					clearstatcache(false, $dir);
					return true;
				}
			}
		}
	}

	return false;
}