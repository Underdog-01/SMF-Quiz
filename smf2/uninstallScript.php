<?php

// SMFQuiz uninstall Script
global $db_prefix, $smcFunc;

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Safe to remove version information
$smcFunc['db_query']('', "DELETE FROM {$db_prefix}settings WHERE variable = 'SMFQuiz_version'");

// We need to remove the scheduled task
$smcFunc['db_query']('', "DELETE FROM {$db_prefix}scheduled_tasks WHERE task = 'quiz_maintenance'");

echo '<i><b><font color="red">IMPORTANT NOTE: The database tables/entries are not uninstalled, as you may be going to perform an upgrade. If you are not planning on continuing with this mod, please remove these manually.</font></b></i><br />';


?>