<?php

// SMFQuiz Install Script
global $modSettings, $scripturl, $context;

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

$modVersion = '1.1.0';
$dbVersion = '6';
$debug = false;

// Start the database extras
db_extend('extra');
db_extend('packages');

if ($debug == true)
	echo '<li>Create default db1 install';	
CreateDefaultInstall($debug);

if ($debug == true)
	echo '<li>Adding updates for db1 to db2';	
UpdateDbFrom1To2();

if ($debug == true)
	echo '<li>Adding updates for db2 to db3';
UpdateDbFrom2To3();

if ($debug == true)
	echo '<li>Adding updates for db3 to db4';
UpdateDbFrom3To4();

if ($debug == true)
	echo '<li>Adding updates for db4 to db5';
UpdateDbFrom4To5();
	
if ($debug == true)
	echo '<li>Adding default data';
AddDefaultData($debug);

// Script that applies to all versions
if ($debug == true)
	echo '<li>updating version information - modversion = <font color="red">' , $modversion , '</font> dbversion = <font color="red">' , $dbversion , '</font>';
updateallversions($modVersion, $dbVersion);

// // Sort out permissions
if ($debug == true)
	echo '<li>Assigning permissions';
SetPermissions();

// Clean cache to make sure updates take immidiate affect
if ($debug == true)
	echo "<li>cleaning cache<br />";
CleanCache();
	
//Done
if ($debug == true) {
	echo '<li><b><font color="green">Install script complete</font><li><font color="blue">You will now be redirected to the settings page in 10 seconds, please wait...</font></b><br/>';
	echo '<li>Any issues please go to <a href="http://www.simplemachines.org/community/index.php?topic=293949.0">the support topic</a>';
}	

// Adds default data into the database
function AddDefaultData($debug) {

	global $db_prefix, $smcFunc;

	if ($debug == true)
		echo '<li>Inserting scheduled task';
	// Add scheduled task
	$smcFunc['db_insert']('ignore',
		'{db_prefix}scheduled_tasks',
		array(
			'next_time' => 'int', 
			'time_offset' => 'int',
			'time_regularity' => 'int',
			'time_unit' => 'string',
			'disabled' => 'int',
			'task' => 'string'
		),
		array(
			time(), 
			0,
			1,
			'h',
			0,
			'quiz_maintenance'
		),
		array('id_task')
    );	
	
	if ($debug == true)
		echo '<li>Inserting Question Types';
	// Add default question types
	$insertArray[0] = array(1, 'Multiple Choice');
	$insertArray[1] = array(2, 'Free Text');
	$insertArray[2] = array(3, 'True/False');
	
	// Coming in next release
	//$insertArray[3] = array(4, 'Multi Select');
	//$insertArray[4] = array(5, 'Ordered Answers');
	
	$smcFunc['db_insert']('ignore',
		'{db_prefix}quiz_question_type',
		array(
			'id_question_type' => 'int', 
			'description' => 'string',
		),
		$insertArray,
		array('id_question_type')
    );
	
    $groups = array(0);

	if ($debug == true)
		echo '<li>Inserting Permissions';
	// Get all the non-postcount based groups.
	$request = $smcFunc['db_query']('', '
		SELECT		id_group
		FROM 		{db_prefix}membergroups
		WHERE 		min_posts = -1'
	);
	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$groups[] = $row['id_group'];
	}
	$smcFunc['db_free_result']($request);
	


// Updates the version information, both mod and database
function UpdateAllVersions($modVersion, $dbVersion) {

	global $db_prefix, $smcFunc;
	
	// Set the version we are installing. The dbVersion is used for knowing which version of the database is installed. The SMFQuiz version is mainly for display purposes and knowing
	// which script is installed
	$result = $smcFunc['db_query']('', "DELETE FROM {$db_prefix}settings WHERE variable = 'SMFQuiz_version'");
	$result = $smcFunc['db_query']('', "DELETE FROM {$db_prefix}settings WHERE variable = 'SMFQuiz_dbVersion'");
	
	// Set default settings
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_version', '{$modVersion}')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_dbVersion', '{$dbVersion}')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_enabled', 'on')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_AutoClean', 'on')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_showUserRating', 'on')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_0to19', 'Oh dear, you really were poor in that quiz.')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_20to39', 'That was not your best effort now was it?')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_40to59', 'Well - You could have done better. Mediocrity is not the end of the world!')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_60to79', 'That is a pretty good score, well done.')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_80to99', 'Good score, we like that!')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_99to100', 'WOW - You are simply amazing. That is a Perfect Score! Did you Google those answers?')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_ImportQuizesAsUserId', '1')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_SendPMOnBrokenTopScore', 'on')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_SendPMOnLeagueRoundUpdate', 'on')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_SessionTimeLimit', '30')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_InfoBoardItemsToDisplay', '20')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_Welcome', 'Welcome to the SMF Quiz')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_ListPageSizes', '20')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_PMBrokenTopScoreMsg', 'Your top score of [b]{old_score}[/b] in [b]{old_score_seconds}[/b] seconds in the quiz [b][url={quiz_link}]{quiz_name}[/url][/b] has been broken!

{member_name} has just scored {new_score} in {new_score_seconds} seconds!

{quiz_image}
	')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_PMBrokenTopScoreSubject', 'Your top score in the quiz {quiz_name} has been broken!')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_PMLeagueRoundUpdateMsg', 'The quiz league [b]{quiz_league_name}[/b] has been updated and your position has changed from [b]{old_position}[/b] to [b]{new_position}[/b], which is a movement of [b]{position_movement}[/b].

{quiz_league_link}')");
	$result = $smcFunc['db_query']('', "INSERT IGNORE INTO {$db_prefix}settings (`variable`,`value`) VALUES ('SMFQuiz_PMLeagueRoundUpdateSubject', 'The Quiz League {quiz_league_name} has been updated')");
}

// Cleans out the file cache so changes can bee seen immediately
function CleanCache() {

	// We need to clean the cache out, otherwise the mod settings don't appear
	clean_cache();
}

// sets some extra permissions required by the mod
function setpermissions() {
	global $sourcedir;

	// we need to set these permissions up, otherwise some of the calls won't work.
	chmod($sourcedir . '/SMFQuizStart.php', 0755);
	chmod($sourcedir . '/SMFQuizQuestions.php', 0755);
	chmod($sourcedir . '/SMFQuizEnd.php', 0755);
	chmod($sourcedir . '/SMFQuizAnswers.php', 0755);
	chmod($sourcedir . '/SMFQuizExport.php', 0755);
	chmod($sourcedir . '/SMFQuizAjax.php', 0755);
	chmod($sourcedir . '/SMFQuizDispute.php', 0755);
}

?>