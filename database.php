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

$quizVersion = '2.0.3-BETA25';


if (!isset($smcFunc['db_create_table']))
	db_extend('packages');

updateSettings(['smf_quiz_version' => $quizVersion]);

// Adding the schedule task
$smcFunc['db_insert'](
	'ignore',
	'{db_prefix}scheduled_tasks',
	[
		'time_offset' => 'int',
		'time_regularity' => 'int',
		'time_unit' => 'string',
		'disabled' => 'int',
		'task' => 'string',
		'callable' => 'string',
	],
	[
		[
			0,
			1,
			'h',
			0,
			'quiz_maintenance',
			'Quiz\Tasks\Scheduled::maintenance#',
		],
	],
	['']
);

$tables = [
	'quiz' => [
		'columns' => [
			[
				'name' => 'id_quiz',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'title',
				'type' => 'varchar',
				'size' => 191,
				'not_null' => true,
				'default' => '',
			],
			[
				'name' => 'description',
				'type' => 'text',
				'not_null' => false,
			],
			[
				'name' => 'play_limit',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'seconds_per_question',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'show_answers',
				'type' => 'tinyint',
				'size' => 3,
				'not_null' => true,
				'default' => 0
			],
			[
				'name' => 'updated',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'image',
				'type' => 'varchar',
				'size' => 191,
				'not_null' => true,
				'default' => '',
			],
			[
				'name' => 'id_category',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'quiz_plays',
				'type' => 'int',
				'not_null' => true,
				'default' => 0
			],
			[
				'name' => 'question_plays',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_correct',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'top_user_id',
				'type' => 'mediumint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'top_correct',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'top_time',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'enabled',
				'type' => 'tinyint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'creator_id',
				'type' => 'mediumint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'for_review',
				'type' => 'tinyint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'top_points',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'rating',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'order_questions',
				'type' => 'tinyint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'imported',
				'type' => 'tinyint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_quiz']
			],
			[
				'type' => 'index',
				'columns' => ['id_category'],
			],
		],
	],

	'quiz_category' => [
		'columns' => [
			[
				'name' => 'id_category',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'id_parent',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'name',
				'type' => 'varchar',
				'size' => 191,
				'not_null' => true,
				'default' => '',
			],
			[
				'name' => 'description',
				'type' => 'text',
				'not_null' => false,
			],
			[
				'name' => 'updated',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'image',
				'type' => 'varchar',
				'size' => 191,
				'not_null' => true,
				'default' => '',
			],
			[
				'name' => 'quiz_count',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_category']
			],
			[
				'type' => 'index',
				'columns' => ['id_parent'],
			],
		],
	],

	'quiz_question' => [
		'columns' => [
			[
				'name' => 'id_question',
				'type' => 'int',
				'not_null' => true,
				'unsigned'=> true,
				'auto' => true
			],
			[
				'name' => 'id_quiz',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'question_text',
				'type' => 'text',
				'not_null' => false,
			],
			[
				'name' => 'id_question_type',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'plays',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'image',
				'type' => 'varchar',
				'size' => 191,
				'not_null' => true,
				'default' => '',
			],
			[
				'name' => 'updated',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'answer_text',
				'type' => 'text',
				'not_null' => false
			],
			[
				'name' => 'order',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'order_answers',
				'type' => 'tinyint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_question']
			],
			[
				'type' => 'index',
				'columns' => ['id_quiz', 'id_question_type'],
			],
		],
	],

	'quiz_infoboard' => [
		'columns' => [
			[
				'name' => 'id_infoboard',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'entry_date',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'entry',
				'type' => 'text',
				'not_null' => false
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_infoboard']
			],
		],
	],
	'quiz_league' => [
		'columns' => [
			[
				'name' => 'id_quiz_league',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'title',
				'type' => 'varchar',
				'size' => 191,
				'not_null' => true,
				'default' => '',
			],
			[
				'name' => 'description',
				'type' => 'text',
				'not_null' => false
			],
			[
				'name' => 'day_interval',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'question_plays',
				'type' => 'smallint',
				'size' => 5,
				'not_null' => true,
				'default' => 0
			],
			[
				'name' => 'questions_per_session',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'seconds_per_question',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'points_for_correct',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'show_answers',
				'type' => 'tinyint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'updated',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'current_round',
				'type' => 'smallint',
				'size' => 5,
				'not_null' => true,
				'unsigned' => true,
				'default' => 1
			],
			[
				'name' => 'total_plays',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_correct',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_timeouts',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'state',
				'type' => 'tinyint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 1
			],
			[
				'name' => 'id_leader',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_question_category',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_rounds',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_incorrect',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'categories',
				'type' => 'text',
				'not_null' => false
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_quiz_league']
			],
			[
				'type' => 'index',
				'columns' => ['id_leader', 'id_question_category']
			]
		],
	],
	'quiz_answer' => [
		'columns' => [
			[
				'name' => 'id_answer',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'id_question',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'answer_text',
				'type' => 'text',
				'not_null' => false,
			],
			[
				'name' => 'answer_plays',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'updated',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'is_correct',
				'type' => 'tinyint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'order',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'points',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_answer']
			],
			[
				'type' => 'index',
				'columns' => ['id_question', 'is_correct']
			]
		],
	],
	'quiz_league_result' => [
		'columns' => [
			[
				'name' => 'id_quiz_league_result',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'id_user',
				'type' => 'mediumint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'round',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'correct',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'points',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'result_date',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'incorrect',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'timeouts',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_quiz_league',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'seconds',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_resumes',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_quiz_league_result']
			],
			[
				'type' => 'index',
				'columns' => ['id_user', 'round', 'result_date', 'id_quiz_league']
			],
		],
	],
	'quiz_question_type' => [
		'columns' => [
			[
				'name' => 'id_question_type',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'description',
				'type' => 'text',
				'not_null' => false,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_question_type']
			],
		],
	],
	'quiz_league_table' => [
		'columns' => [
			[
				'name' => 'id_quiz_league_table',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'current_position',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_user',
				'type' => 'mediumint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'last_position',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'plays',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'correct',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'incorrect',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'timeouts',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'points',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_quiz_league',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'round',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'seconds',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_quiz_league_table']
			],
			[
				'type' => 'index',
				'columns' => ['id_user', 'id_quiz_league', 'current_position']
			],
		],
	],
	'quiz_result' => [
		'columns' => [
			[
				'name' => 'id_quiz_result',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'id_quiz',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_user',
				'type' => 'mediumint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'result_date',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'questions',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'correct',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'incorrect',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'timeouts',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_seconds',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_points',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_resumes',
				'type' => 'smallint',
				'size' => 5,
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'auto_completed',
				'type' => 'tinyint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'player_limit',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_quiz_result']
			],
			[
				'type' => 'index',
				'columns' => ['id_quiz', 'id_user']
			],
		],
	],
	'quiz_session' => [
		'columns' => [
			[
				'name' => 'id_quiz_session',
				'type' => 'varchar',
				'size' => 191,
				'not_null' => true,
				'default' => '',
			],
			[
				'name' => 'question_count',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'timeouts',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'correct',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'incorrect',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_seconds',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'last_question_start',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'result_date',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'session_start',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_quiz',
				'type' => 'int',
				'not_null' => false,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_quiz_league',
				'type' => 'int',
				'not_null' => false,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_user',
				'type' => 'mediumint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_points',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'total_resumes',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_quiz_session']
			],
			[
				'type' => 'index',
				'columns' => ['id_quiz', 'id_user', 'id_quiz_league']
			],
		],
	],
	'quiz_dispute' => [
		'columns' => [
			[
				'name' => 'id_quiz_dispute',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'auto' => true
			],
			[
				'name' => 'id_user',
				'type' => 'mediumint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_quiz',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'id_quiz_question',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'reason',
				'type' => 'text',
				'not_null' => false
			],
			[
				'name' => 'updated',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_quiz_dispute']
			],
			[
				'type' => 'index',
				'columns' => ['id_user', 'id_quiz', 'id_quiz_question']
			],
		],
	],
	'quiz_members' => [
		'columns' => [
			[
				'name' => 'id_member',
				'type' => 'mediumint',
				'size' => 8,
				'not_null' => true,
				'unsigned' => true,
				'auto' => false
			],
			[
				'name' => 'quiz_pm_report',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
			[
				'name' => 'quiz_pm_alert',
				'type' => 'smallint',
				'not_null' => true,
				'unsigned' => true,
				'default' => 1
			],
			[
				'name' => 'quiz_count',
				'type' => 'int',
				'not_null' => true,
				'unsigned' => true,
				'default' => 0
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id_member']
			],
		],
	],
];

foreach ($tables AS $table_name => $data)
	$smcFunc['db_create_table']('{db_prefix}' . $table_name, $data['columns'], $data['indexes']);

// Insert default question types
$smcFunc['db_insert']('ignore',
	'{db_prefix}quiz_question_type',
	[
		'id_question_type' => 'int',
		'description' => 'string',
	],
	[
		[
			1,
			'Multiple Choice'
		],
		[
			2,
			'Free Text'
		],
		[
			3,
			'True/False'
		]
	],
	['id_question_type']
);