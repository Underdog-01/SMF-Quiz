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

if (!isset($smcFunc['db_list_columns']) || !isset($smcFunc['db_add_column'])) {
	db_extend('packages');
}

$check = $smcFunc['db_list_columns'] ('{db_prefix}quiz_result', false, array());
if (!in_array('player_limit', $check)) {
	$smcFunc['db_add_column'](
		'{db_prefix}quiz_result',
		array('name' => 'player_limit', 'type' => 'smallint', 'size' => 5, 'null' => false, 'default' => 0, 'unsigned' => true, 'auto' => false),
		array(),
		'update',
		'fatal'
	);
}

// member #1 will get Quiz PMs as default when Quiz is initially installed
$member = [
	'quiz_pm_report' => 1,
	'quiz_pm_alert' => 1,
	'quiz_count' => 0,
];
$request = $smcFunc['db_query']('', '
	SELECT qm.id_member, qm.quiz_pm_report, qm.quiz_pm_alert, qm.quiz_count
	FROM {db_prefix}quiz_members qm
	INNER JOIN {db_prefix}members m ON m.id_member = qm.id_member
	WHERE qm.id_member = {int:val}',
	[
		'val' => 1,
	]
);

while ($row = $smcFunc['db_fetch_assoc']($request)) {
	$member = [
		'quiz_pm_report' => $row['quiz_pm_report'],
		'quiz_pm_alert' => $row['quiz_pm_alert'],
		'quiz_count' => $row['quiz_count'],
	];
}
$smcFunc['db_free_result']($request);

// this table may change from version upgrades, therefore redo the entry
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}quiz_members
	WHERE id_member = {int:memid}',
	['memid' => 1]
);

$smcFunc['db_insert']('insert',
	'{db_prefix}quiz_members',
	[
		'id_member' => 'int',
		'quiz_pm_report' => 'int',
		'quiz_pm_alert' => 'int',
		'quiz_count' => 'int',
	],
	[
		1,
		$member['quiz_pm_report'],
		$member['quiz_pm_alert'],
		$member['quiz_count']
	],
	['id_member']
);

// remove rogue member ids from quiz_members
$nonMembers = [];
$request = $smcFunc['db_query']('', '
	SELECT qm.id_member
	FROM {db_prefix}quiz_members qm
	LEFT JOIN {db_prefix}members m ON m.id_member = qm.id_member
	WHERE m.id_member IS NULL',
	[]
);

while ($row = $smcFunc['db_fetch_assoc']($request))
{
	$nonMembers[] = $row['id_member'];
}
$smcFunc['db_free_result']($request);

if (!empty($nonMembers)) {
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}quiz_members
		WHERE id_member IN({array_int:memIDs})',
		array(
			'memIDs' => $nonMembers,
		)
	);
}