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
	
	// Give them all their new permissions
    $smcFunc['db_query']('', "
      INSERT IGNORE INTO {$db_prefix}permissions
         (permission, ID_GROUP, add_deny)
      VALUES
         ('quiz_view', " . implode(", 1),
         ('quiz_view', ", $groups) . ", 1)");

    $smcFunc['db_query']('', "
      INSERT IGNORE INTO {$db_prefix}permissions
         (permission, ID_GROUP, add_deny)
      VALUES
         ('quiz_play', " . implode(", 1),
         ('quiz_play', ", $groups) . ", 1)");

    $smcFunc['db_query']('', "
      INSERT IGNORE INTO {$db_prefix}permissions
         (permission, ID_GROUP, add_deny)
      VALUES
         ('quiz_submit', " . implode(", 1),
         ('quiz_submit', ", $groups) . ", 1)");
}

// Creates the default install i.e. dbversion 1. Any changes to this default database install are tackled in the upgrade section
function CreateDefaultInstall($debug) {

	global $db_prefix, $smcFunc;
	
	if ($debug == true)
		echo '<li>Adding Tables';
	
	// Quiz Table
	if ($debug == true)
		echo '<li>Adding Quiz Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz', 
		array(
			array(
				'name' => 'id_quiz',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'title',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => 'Quiz Title'
			),
			array(
				'name' => 'description',
				'type' => 'text',
				'null' => true
			),
			array(
				'name' => 'play_limit',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'seconds_per_question',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'show_answers',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'updated',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'image',
				'type' => 'varchar',
				'size' => 255,
				'null' => true
			),
			array(
				'name' => 'id_category',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'quiz_plays',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'question_plays',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'total_correct',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'top_user_id',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'top_correct',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'top_time',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'enabled',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'creator_id',
				'type' => 'int',
				'null' => false,
				'default' => 1
			),
			array(
				'name' => 'for_review',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0
			),
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_quiz')
			),
			array(
				'name' => 'title',
				'type' => 'unique',
				'columns' => array('title')
			),
			array(
				'name' => 'id_category',
				'type' => 'index',
				'columns' => array('id_category')
			),
		),
	array(),
	'ignore');	

	// Quiz Category Table
	if ($debug == true)
		echo '<li>Adding Quiz Category Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_category', 
		array(
			array(
				'name' => 'id_category',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'id_parent',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => 'category name'
			),
			array(
				'name' => 'description',
				'type' => 'text',
				'null' => true
			),
			array(
				'name' => 'plays',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'question_count',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'updated',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'image',
				'type' => 'varchar',
				'size' => 255,
				'null' => true
			),
			array(
				'name' => 'quiz_count',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_category')
			),
			array(
				'name' => 'name',
				'type' => 'unique',
				'columns' => array('name')
			),
			array(
				'name' => 'id_parent',
				'type' => 'index',
				'columns' => array('id_parent')
			)
		),
	array(),
	'ignore');	

	// Quiz Question Table
	if ($debug == true)
		echo '<li>Adding Quiz Question Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_question', 
		array(
			array(
				'name' => 'id_question',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'id_quiz',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'question_text',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => 'Question Text'
			),
			array(
				'name' => 'id_question_type',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'plays',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'image',
				'type' => 'varchar',
				'size' => 255,
				'null' => true
			),
			array(
				'name' => 'updated',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'answer_text',
				'type' => 'text',
				'null' => true
			),
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_question')
			),
			array(
				'name' => 'id_quiz',
				'type' => 'index',
				'columns' => array('id_quiz')
			)
		),
	array(),
	'ignore');	

	// Quiz Infoboard Table
	if ($debug == true)
		echo '<li>Adding Quiz Infoboard Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_infoboard', 
		array(
			array(
				'name' => 'id_infoboard',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'entry_date',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'entry',
				'type' => 'text',
				'null' => false
			),
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_infoboard')
			)
		),
	array(),
	'ignore');	
	
	// Quiz League Table
	if ($debug == true)
		echo '<li>Adding Quiz League Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_league', 
		array(
			array(
				'name' => 'id_quiz_league',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'title',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => 'Quiz League Title'
			),
			array(
				'name' => 'description',
				'type' => 'text',
				'null' => true
			),
			array(
				'name' => 'day_interval',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'question_plays',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'questions_per_session',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'seconds_per_question',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'points_for_correct',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'show_answers',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'updated',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'current_round',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 1
			),
			array(
				'name' => 'total_plays',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'total_correct',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'total_timeouts',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'state',
				'type' => 'tinyint',
				'size' => 1,
				'null' => false,
				'default' => 1
			),
			array(
				'name' => 'id_leader',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'id_question_category',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'total_rounds',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'total_incorrect',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_quiz_league')
			),
			array(
				'name' => 'title',
				'type' => 'unique',
				'columns' => array('title')
			),
			array(
				'name' => 'id_leader',
				'type' => 'index',
				'columns' => array('id_leader')
			),
			array(
				'name' => 'id_question_category',
				'type' => 'index',
				'columns' => array('id_question_category')
			)
		),
	array(),
	'ignore');
	
	// Quiz Answer Table
	if ($debug == true)
		echo '<li>Adding Quiz Answer Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_answer', 
		array(
			array(
				'name' => 'id_answer',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'id_question',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'answer_text',
				'type' => 'varchar',
				'size' => 255,
				'null' => false,
				'default' => 'Answer Text'
			),
			array(
				'name' => 'answer_plays',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'updated',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'is_correct',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0
			),
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_answer')
			),
			array(
				'name' => 'id_question',
				'type' => 'index',
				'columns' => array('id_question')
			)
		)
	,
	array(),
	'ignore');	
	
	// Quiz League Questions Table
	if ($debug == true)
		echo '<li>Adding Quiz League Questions Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_league_questions', 
		array(
			array(
				'name' => 'id_quiz_league',
				'type' => 'int',
				'null' => false
			),
			array(
				'name' => 'round',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'id_question',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_quiz_league', 'round', 'id_question')
			)
		),
	array(),
	'ignore');
	
	// Quiz League Result Table
	if ($debug == true)
		echo '<li>Adding Quiz League Result Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_league_result', 
		array(
			array(
				'name' => 'id_quiz_league_result',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'id_user',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'round',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'correct',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'points',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'result_date',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'incorrect',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'timeouts',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'id_quiz_league',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'seconds',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_quiz_league_result')
			),
			array(
				'name' => 'id_user',
				'type' => 'index',
				'columns' => array('id_user')
			),
			array(
				'name' => 'id_quiz_league',
				'type' => 'index',
				'columns' => array('id_quiz_league')
			)
		),
	array(),
	'ignore');

	// Quiz Question Type Table
	if ($debug == true)
		echo '<li>Adding Quiz Question Type Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_question_type', 
		array(
			array(
				'name' => 'id_question_type',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'description',
				'type' => 'varchar',
				'size' => 100,
				'null' => false,
				'default' => 'Description'
			)
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_question_type')
			)
		),
	array(),
	'ignore');
	
	// Quiz Question League Table Table
	if ($debug == true)
		echo '<li>Adding Quiz League Table Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_league_table', 
		array(
			array(
				'name' => 'id_quiz_league_table',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'current_position',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'id_user',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'last_position',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'plays',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'correct',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'incorrect',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'timeouts',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'points',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'id_quiz_league',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'round',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'seconds',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),

		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_quiz_league_table')
			),
			array(
				'name' => 'id_user',
				'type' => 'index',
				'columns' => array('id_user')
			),
			array(
				'name' => 'id_quiz_league',
				'type' => 'index',
				'columns' => array('id_quiz_league')
			)
		),
	array(),
	'ignore');
	
	// Quiz Result Table
	if ($debug == true)
		echo '<li>Adding Quiz Result Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_result', 
		array(
			array(
				'name' => 'id_quiz_result',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'id_quiz',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'id_user',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'result_date',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'questions',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'correct',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'incorrect',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'timeouts',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'total_seconds',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			)
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_quiz_result')
			),
			array(
				'name' => 'id_user',
				'type' => 'index',
				'columns' => array('id_user')
			),
			array(
				'name' => 'id_quiz',
				'type' => 'index',
				'columns' => array('id_quiz')
			)
		),
	array(),
	'ignore');

	// Quiz Session Table
	if ($debug == true)
		echo '<li>Adding Quiz Session Table';
	$smcFunc['db_create_table'] ('{db_prefix}quiz_session', 
		array(
			array(
				'name' => 'id_quiz_session',
				'type' => 'varchar',
				'size' => 38,
				'null' => false
			),
			array(
				'name' => 'question_count',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'timeouts',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'correct',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'incorrect',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'total_seconds',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'last_question_start',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'result_date',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'session_start',
				'type' => 'int',
				'null' => false,
				'default' => 0
			),
			array(
				'name' => 'id_quiz',
				'type' => 'int',
				'null' => true
			),
			array(
				'name' => 'id_quiz_league',
				'type' => 'int',
				'null' => true
			),
			array(
				'name' => 'id_user',
				'type' => 'int',
				'null' => false,
				'default' => 0
			)
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_quiz_session')
			),
			array(
				'name' => 'id_user',
				'type' => 'index',
				'columns' => array('id_user')
			),
			array(
				'name' => 'id_quiz',
				'type' => 'index',
				'columns' => array('id_quiz')
			),
			array(
				'name' => 'id_quiz_league',
				'type' => 'index',
				'columns' => array('id_quiz_league')
			)
		),
	array(),
	'ignore');	
	
}

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


/*
Applies changes to database from v3 to v4
*/
function UpdateDbFrom4To5() {
	global $smcFunc, $db_prefix;

	// Quiz Result table updates
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_result", 
			array(
				'name' => 'auto_completed',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0
				)
			);
			
	// These columns are no longer required
	$smcFunc['db_remove_column'] ("{$db_prefix}quiz", 'import_name');
	$smcFunc['db_remove_column'] ("{$db_prefix}quiz_category", 'plays');
	$smcFunc['db_remove_column'] ("{$db_prefix}quiz_category", 'question_count');
	
	// This table is no longer required
	$smcFunc['db_drop_table'] ("{$db_prefix}quiz_league_questions");
}

/*
Applies changes to database from v3 to v4
*/
function UpdateDbFrom3To4() {
	global $smcFunc, $db_prefix;
	
	// Quiz League table updates
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_league", 
			array(
				'name' => 'categories',
				'type' => 'text',
				'null' => true
				)
			);

}

/*
Applies changes to database from v2 to v3
*/
function UpdateDbFrom2To3() {

	global $smcFunc, $db_prefix;

	// Remove index from quiz results table
	// THIS IS NEEDED FOR BUG IN SMF WHERE IT FAILS IF TRYING TO RE ADD INDEX
	$smcFunc['db_remove_index'](
		"{$db_prefix}quiz_result", 
		'id_quiz'
		);

	// Add index to quiz results table
	$smcFunc['db_add_index']("{$db_prefix}quiz_result", 
		array(
		'columns' => array('id_quiz'),
		'type' => 'index',
		)
	);

	// Remove index from quiz answer table
	// THIS IS NEEDED FOR BUG IN SMF WHERE IT FAILS IF TRYING TO RE ADD INDEX
	$smcFunc['db_remove_index'](
		"{$db_prefix}quiz_answer", 
		'id_question'
		);

	// Add index to quiz answers table
	$smcFunc['db_add_index']("{$db_prefix}quiz_answer", 
		array(
		'columns' => array('id_question'),
		'type' => 'index',
		)
	);

	// Remove index from quiz table
	// THIS IS NEEDED FOR BUG IN SMF WHERE IT FAILS IF TRYING TO RE ADD INDEX
	$smcFunc['db_remove_index'](
		"{$db_prefix}quiz", 
		'id_category'
		);

	// Add index to quiz table
	$smcFunc['db_add_index']("{$db_prefix}quiz", 
		array(
		'columns' => array('id_category'),
		'type' => 'index',
		)
	);
	
	// Quiz Session table updates
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_session", 
			array(
				'name' => 'total_resumes',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);

	// Quiz Result table updates
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_result", 
			array(
				'name' => 'total_resumes',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);
	
	// Quiz League Result table updates
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_league_result", 
			array(
				'name' => 'total_resumes',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);

	// New table for recording disputes
	$smcFunc['db_create_table'] ('{db_prefix}quiz_dispute', 
		array(
			array(
				'name' => 'id_quiz_dispute',
				'type' => 'int',
				'null' => false,
				'auto' => true
			),
			array(
				'name' => 'id_user',
				'type' => 'int',
				'null' => false
			),
			array(
				'name' => 'id_quiz',
				'type' => 'int',
				'null' => false
			),
			array(
				'name' => 'id_quiz_question',
				'type' => 'int',
				'null' => false
			),
			array(
				'name' => 'reason',
				'type' => 'text',
				'null' => false
			),
			array(
				'name' => 'updated',
				'type' => 'int',
				'null' => false,
				'default' => 0
			)
		),
		array(
			array(
				'type' => 'primary',
				'columns' => array('id_quiz_dispute')
			),
			array(
				'name' => 'id_user',
				'type' => 'index',
				'columns' => array('id_user')
			),
			array(
				'name' => 'id_quiz',
				'type' => 'index',
				'columns' => array('id_quiz')
			),
			array(
				'name' => 'id_quiz_question',
				'type' => 'index',
				'columns' => array('id_quiz_question')
			)
		),
	array(),
	'ignore');
	
	// Change infoboard entry to text
	// $smcFunc['db_change_column']( 
			// "{$db_prefix}quiz_answer", 
			// 'answer_text', 
			// array('type' => 'text')
			// );
			
	// The above sometimes doesn't work for some reason, so force it this way
	$smcFunc['db_query']('', "ALTER TABLE {$db_prefix}quiz_answer MODIFY COLUMN answer_text TEXT");

}

/*
applies changes to database from v1 to v2
*/
function updatedbfrom1to2() {

	global $smcFunc, $db_prefix;
	
	// quiz session table updates
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_session", 
			array(
				'name' => 'total_points',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);
	
	// quiz table updates
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz", 
			array(
				'name' => 'top_points',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);
	
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz", 
			array(
				'name' => 'rating',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);
	
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz", 
			array(
				'name' => 'order_questions',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0
				)
			);
	
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz", 
			array(
				'name' => 'imported',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0
				)
			);
	
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz", 
			array(
				'name' => 'import_name',
				'type' => 'varchar',
				'size' => 255,
				'null' => true
				)
			);		
	
	// quiz question table updates
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_question", 
			array(
				'name' => 'order',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);
	
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_question", 
			array(
				'name' => 'order_answers',
				'type' => 'tinyint',
				'size' => 3,
				'null' => false,
				'default' => 0
				)
			);		
	
	// quiz answer table updates		
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_answer", 
			array(
				'name' => 'order',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);
	
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_answer", 
			array(
				'name' => 'points',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);
	
	// quiz result table updates		
	$smcFunc['db_add_column'] (
			"{$db_prefix}quiz_result", 
			array(
				'name' => 'total_points',
				'type' => 'smallint',
				'size' => 5,
				'null' => false,
				'default' => 0
				)
			);
	
	// change infoboard entry to text
	$smcFunc['db_change_column']( 
			"{$db_prefix}quiz_infoboard", 
			'entry', 
			array('type' => 'text')
			);
			
	// the above sometimes doesn't work for some reason, so force it this way
	$smcFunc['db_query']('', "alter table {$db_prefix}quiz_infoboard modify column entry text");
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