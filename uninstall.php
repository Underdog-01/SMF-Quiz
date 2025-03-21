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

global $smcFunc;

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}scheduled_tasks
	WHERE task = {string:name}',
	[
		'name' => 'quiz_maintenance',
	]
);

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable = {string:quiz}',
	[
		'quiz' => 'smf_quiz_version',
	]
);