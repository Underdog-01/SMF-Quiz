<?php

if (!defined('SMF'))
	die('Hacking attempt...');

/* Retrieves the count of quizzes and stores this in the context */
function GetQuizCount()
{
	global $context, $smcFunc;

	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*) quiz_count
		FROM {db_prefix}quiz'
	);

	$context['SMFQuiz']['quizCount'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizCount'][] = $row;

	$smcFunc['db_free_result']($result);
}

/* Retrieves the count of categories and stores this in the context */
function GetCategoryCount($id_category = 0)
{
	global $context, $smcFunc;

	if (isset($id_category) && $id_category != 0)
		$categoryWhereClause = ' WHERE id_category = ' . (int)$id_category;
	else
		$categoryWhereClause = '';

	// @TODO query?
	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*) CategoryCount
		FROM {db_prefix}quiz_category' . $categoryWhereClause
	);

	$context['SMFQuiz']['categoryCount'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['categoryCount'][] = $row;

	$smcFunc['db_free_result']($result);
}

// Retrieves the question count for the user and populates the context with this
function GetUserQuestionCount($id_quiz, $id_user)
{
	global $context, $smcFunc;

	if (isset($id_quiz))
	{
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS question_count
			FROM {db_prefix}quiz_question QQ
			INNER JOIN {db_prefix}quiz Q
				ON QQ.id_quiz = Q.id_quiz
			WHERE Q.id_quiz = {int:id_quiz}
				AND Q.creator_id = {int:id_user}',
			array(
				'id_quiz' => (int)$id_quiz,
				'id_user' => (int)$id_user
			)
		);
	}
	else
	{
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS question_count
			FROM {db_prefix}quiz_question QQ
			INNER JOIN {db_prefix}quiz Q
				ON QQ.id_quiz = Q.id_quiz
			WHERE Q.creator_id = {int:id_user}',
			array(
				'id_user' => (int)$id_user
			)
		);
	}

	$context['SMFQuiz']['questionCount'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['questionCount'][] = $row;

	$smcFunc['db_free_result']($result);
}

// Retrieves the quiz question count and populates the context with this
function GetQuizQuestionCount($id_quiz)
{
	global $context, $smcFunc;

	if (isset($id_quiz) && $id_quiz != 0)
	{
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*) question_count
			FROM {db_prefix}quiz_question
			WHERE id_quiz = {int:id_quiz}',
			array(
				'id_quiz' => (int)$id_quiz
			)
		);
	}
	else
	{
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*) question_count
			FROM {db_prefix}quiz_question'
		);
	}

	$context['SMFQuiz']['questionCount'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['questionCount'][] = $row;

	$smcFunc['db_free_result']($result);
}

// Data class for question details
function GetAllQuestionDetails($page = 1, $orderBy = 'quiz_title', $orderDir = 'up', $id_quiz = 0)
{
	global $context, $smcFunc, $txt, $modSettings;

	// Work out paging
	$startPage = ($page - 1) * $modSettings['SMFQuiz_ListPageSizes'];

	// Work out orderng
	if ($orderDir == 'up')
		$orderDir = 'ASC';
	else
		$orderDir = 'DESC';

	if (empty($orderBy))
		$orderBy = 'quiz_title';

	if ($id_quiz != 0)
	{
		// @TODO query
		$result = $smcFunc['db_query']('', "
			SELECT
				Q.id_question,
				Q.question_text,
				QT.description AS question_type,
				IFNULL(QI.title, {string:none}) AS quiz_title
			FROM {db_prefix}quiz_question Q
			LEFT JOIN {db_prefix}quiz QI
				ON Q.id_quiz = QI.id_quiz
			INNER JOIN {db_prefix}quiz_question_type QT
				ON Q.id_question_type = QT.id_question_type
			WHERE Q.id_quiz = {int:id_quiz}
			ORDER BY {$orderBy} {$orderDir}
			LIMIT {$startPage}, {$modSettings['SMFQuiz_ListPageSizes']}",
			[
				'id_quiz' => (int)$id_quiz,
				'none' => $txt['SMFQuiz_Common']['NoneAssigned'],
			]
		);
	}
	else
	{
		// @TODO query
		$result = $smcFunc['db_query']('', "
			SELECT
				Q.id_question,
				Q.question_text,
				QT.description AS question_type,
				IFNULL(QI.title, {string:none}) AS quiz_title
			FROM {db_prefix}quiz_question Q
			LEFT JOIN {db_prefix}quiz QI
				ON Q.id_quiz = QI.id_quiz
			INNER JOIN {db_prefix}quiz_question_type QT
				ON Q.id_question_type = QT.id_question_type
			ORDER BY {$orderBy} {$orderDir}
			LIMIT {$startPage}, {$modSettings['SMFQuiz_ListPageSizes']}",
			[
				'none' => $txt['SMFQuiz_Common']['NoneAssigned'],
			]
		);
	}

	$context['SMFQuiz']['questions'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['questions'][] = $row;

	$smcFunc['db_free_result']($result);
}

function GetUserQuestionDetails($page = 1, $orderBy = 'quiz_title', $orderDir = false, $id_quiz = 0, $id_user = 0)
{
	global $context, $txt, $smcFunc;

	// Work out paging
	$startPage = ((int)$page - 1) * 20;

	// Work out orderng
	if ($orderDir == 'up')
		$orderDir = 'ASC';
	else
		$orderDir = 'DESC';

	if (isset($id_quiz))
	{
		// @TODO query
		$result = $smcFunc['db_query']('', '
			SELECT 		Q.id_question,
						Q.question_text,
						QT.description AS question_type,
						IFNULL(QI.title, {string:none}) AS quiz_title
			FROM 		{db_prefix}quiz_question Q
			LEFT JOIN 	{db_prefix}quiz QI
			ON 			Q.id_quiz = QI.id_quiz
			INNER JOIN 	{db_prefix}quiz_question_type QT
			ON 			Q.id_question_type = QT.id_question_type
			WHERE 		Q.id_quiz = {int:id_quiz} AND QI.creator_id = {int:id_user}
			ORDER BY 	{string:orderBy} {string:orderDir}
			LIMIT		{int:startPage}, 20',
			array(
				'id_quiz' => (int)$id_quiz,
				'id_user' => (int)$id_user,
				'orderBy' => $orderBy,
				'orderDir' => $orderDir,
				'startPage' => (int)$startPage,
				'none' => $txt['SMFQuiz_Common']['NoneAssigned'],
			)
		);
	}
	else
	{
		// @TODO query
		// @TODO localization
		$result = $smcFunc['db_query']('', '
			SELECT 		Q.id_question,
						Q.question_text,
						QT.description AS question_type,
						IFNULL(QI.title, {string:none}) AS quiz_title
			FROM 		{db_prefix}quiz_question Q
			LEFT JOIN 	{db_prefix}quiz QI
			ON 			Q.id_quiz = QI.id_quiz
			INNER JOIN 	{db_prefix}quiz_question_type QT
			ON 			Q.id_question_type = QT.id_question_type
			WHERE 		QI.creator_id = WHERE QI.creator_id = {int:id_user}
			ORDER BY 	{string:orderBy} {string:orderDir}
			LIMIT		{int:startPage}, 20',
			array(
				'id_user' => (int)$id_user,
				'orderBy' => $orderBy,
				'orderDir' => $orderDir,
				'startPage' => (int)$startPage,
				'none' => $txt['SMFQuiz_Common']['NoneAssigned'],
			)
		);
	}

	$context['SMFQuiz']['questions'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['questions'][] = $row;

	$smcFunc['db_free_result']($result);
}

// Retrieve all quiz details and populate results in the context
function GetAllQuizDetails($page = 0, $orderBy = 'Q.Title', $orderDir = 'up')
{
	global $context, $txt, $smcFunc;

	$startPage = ((int)$page - 1) * 20;

	// Work out orderng
	if ($orderDir == 'up')
		$orderDir = 'ASC';
	else
		$orderDir = 'DESC';

	// @TODO query
	// Not all calls to this require paging
	if ($page != 0)
		$limit = "LIMIT {$startPage}, 20";
	else
		$limit = "";

	// @TODO query
	$result = $smcFunc['db_query']('', "
		SELECT		Q.id_quiz,
					Q.title,
					Q.creator_id,
					M.real_name,
					Q.description,
					Q.play_limit,
					Q.seconds_per_question,
					Q.show_answers,
					Q.enabled,
					QC.id_category,
					(CASE WHEN Q.id_category = 0 THEN {string:toplvl} ELSE QC.name END) AS category_name,
					COUNT(U.id_quiz) AS questions_per_session
		FROM 		{db_prefix}quiz Q
		LEFT JOIN	{db_prefix}quiz_category QC
		ON 			Q.id_category = QC.id_category
		LEFT JOIN	{db_prefix}quiz_question U
		ON			Q.id_quiz = U.id_quiz
		LEFT JOIN	{db_prefix}members M
		ON			Q.creator_id = M.id_member
		GROUP BY	Q.id_quiz,
				    Q.title,
					Q.creator_id,
					M.real_name,
					Q.description,
					Q.play_limit,
					Q.seconds_per_question,
					Q.show_answers,
					Q.enabled,
					QC.id_category,
					Q.id_category,
					QC.name,
					U.id_quiz
		ORDER BY 	{$orderBy} {$orderDir}
		{$limit}",
		[
			'toplvl' => $txt['SMFQuiz_Common']['TopLevel'],
		]
	);

	$context['SMFQuiz']['quizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizzes'][] = $row;

	$smcFunc['db_free_result']($result);
}

function GetCategory($categoryId)
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QC.id_parent,
					QC2.name AS parent_name,
					QC.name,
					QC.description,
					QC.image,
					QC.updated
		FROM 		{db_prefix}quiz_category QC
		LEFT JOIN	{db_prefix}quiz_category QC2
		ON 			QC.id_parent = QC2.id_category
		WHERE		QC.id_category = {int:id_category}',
		array(
			'id_category' => (int)$categoryId,
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['category'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['category'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for the Question and Answers details
function GetQuestionAndAnswers($id_question = 0)
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.question_text,
					Q.image,
					Q.answer_text,
					Q.id_question_type,
					Q.updated,
					QT.description AS question_type,
					QI.id_quiz,
					QI.title AS quiz_title,
					QI.creator_id
		FROM 		{db_prefix}quiz_question Q
		INNER JOIN 	{db_prefix}quiz_question_type QT
		ON 			Q.id_question_type = QT.id_question_type
		INNER JOIN	{db_prefix}quiz QI
		ON 			Q.id_quiz = QI.id_quiz
		WHERE		Q.id_question = {int:id_question}
		LIMIT		0, 1',
		array(
			'id_question' => (int)$id_question,
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['questions'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['questions'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 	 	A.id_answer,
					A.answer_text,
					A.is_correct
		FROM 		{db_prefix}quiz_answer A
		WHERE		A.id_question = {int:id_question}
		ORDER BY	A.answer_text',
		array(
			'id_question' => (int)$id_question,
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['answers'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['answers'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetRandomQuizzes($limit, $id_user)
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 			Q.id_quiz,
						Q.title,
						Q.image
		FROM 			{db_prefix}quiz Q
		LEFT JOIN 		{db_prefix}quiz_result QR
		ON 				Q.id_quiz = QR.id_quiz
		AND 			QR.id_user = {int:id_user}
		WHERE 			id_quiz_result IS NULL
		ORDER BY		RAND()
		LIMIT			0, {int:limit}',
		array(
			'limit' => (int)$limit,
			'id_user' => (int)$id_user
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['randomQuizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['randomQuizzes'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetQuizCorrect($id_quiz)
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 			correct,
						COUNT(*) AS count_correct
		FROM 			{db_prefix}quiz_result
		WHERE			id_quiz = {int:id_quiz}
		GROUP BY 		correct',
		array(
			'id_quiz' => (int)$id_quiz,
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizCorrect'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizCorrect'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetQuizResults($id_quiz)
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 			M.real_name,
						QR.id_user,
						QR.result_date,
						QR.questions,
						QR.correct,
						QR.incorrect,
						QR.timeouts,
						QR.total_seconds,
						QR.auto_completed
		FROM 			{db_prefix}quiz_result QR
		INNER JOIN 		{db_prefix}members M
		ON 				QR.id_user = M.id_member
		WHERE			QR.id_quiz = {int:id_quiz}
		ORDER BY		QR.correct DESC,
						QR.total_seconds ASC
		LIMIT			0, 10',
		array(
			'id_quiz' => (int)$id_quiz,
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizResults'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizResults'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for the Quiz details
function GetQuiz($quizId)
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.id_quiz,
					Q.title,
					Q.description,
					Q.image,
					Q.play_limit,
					Q.seconds_per_question,
					Q.show_answers,
					Q.quiz_plays,
					Q.updated,
					Q.question_plays,
					Q.total_correct,
					Q.id_category,
					Q.enabled,
					Q.creator_id,
					IFNULL(QC.id_category,0) AS id_category,
					QC.name,
					M.real_name AS creator_name,
					round((Q.total_correct / Q.question_plays) * 100) AS percentage,
					COUNT(U.id_quiz) AS questions_per_session
		FROM 		{db_prefix}quiz Q
		LEFT JOIN	{db_prefix}quiz_question U
		ON			Q.id_quiz = U.id_quiz
		INNER JOIN	{db_prefix}members M
		ON			Q.creator_id = M.id_member
		LEFT JOIN	{db_prefix}quiz_category QC
		ON			Q.id_category = QC.id_category
		WHERE		Q.id_quiz = {int:id_quiz}
		GROUP BY	Q.id_quiz,
					Q.title,
					Q.description,
					Q.image,
					Q.play_limit,
					Q.seconds_per_question,
					Q.show_answers,
					Q.quiz_plays,
					Q.updated,
					Q.question_plays,
					Q.total_correct,
					Q.id_category,
					Q.enabled,
					Q.creator_id,
					QC.id_category,
					QC.name,
					M.real_name,
					U.id_quiz,
					percentage',
		array(
			'id_quiz' => (int)$quizId,
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quiz'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quiz'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for Quiz League details
function GetAllQuizLeagueDetails()
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QL.id_quiz_league,
					QL.title,
					QL.description,
					QL.day_interval,
					QL.questions_per_session,
					QL.seconds_per_question,
					QL.points_for_correct,
					QL.show_answers,
					QL.updated,
					QL.current_round,
					QL.total_plays,
					QL.total_correct,
					QL.total_timeouts,
					QL.state,
					QL.id_leader,
					QL.total_rounds,
					QL.state
		FROM 		{db_prefix}quiz_league QL
		LEFT JOIN	{db_prefix}members M
		ON			QL.id_leader = M.id_member
		ORDER BY 	QL.title ASC'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizLeagues'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizLeagues'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for single Quiz League details
function GetQuizLeagueDetails($id_quiz_league)
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QL.id_quiz_league,
					QL.title,
					QL.description,
					QL.day_interval,
					QL.question_plays,
					QL.questions_per_session,
					QL.seconds_per_question,
					QL.points_for_correct,
					QL.show_answers,
					QL.updated,
					QL.current_round,
					QL.total_plays,
					QL.total_correct,
					QL.total_timeouts,
					QL.state,
					QL.id_leader,
					M.real_name,
					QL.current_round,
					QL.total_rounds,
					QL.categories
		FROM 		{db_prefix}quiz_league QL
		LEFT JOIN	{db_prefix}members M
		ON			QL.id_leader = M.id_member
		WHERE		QL.id_quiz_league = {int:id_quiz_league}
		LIMIT		0, 1',
		array(
			'id_quiz_league' => (int)$id_quiz_league,
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizLeague'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizLeague'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for Quiz League results
function GetQuizLeagueResults($id_quiz_league)
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT		QLR.id_user,
					M.real_name,
					QLR.correct,
					QLR.incorrect,
					QLR.timeouts,
					QLR.points,
					QLR.result_date,
					QLR.round,
					QLR.seconds
		FROM		{db_prefix}quiz_league_result QLR
		INNER JOIN	{db_prefix}members M
		ON			QLR.id_user = M.id_member
		WHERE		QLR.id_quiz_league = {int:id_quiz_league}
		ORDER BY	QLR.result_date DESC
		LIMIT		0, 10',
		array(
			'id_quiz_league' => (int)$id_quiz_league,
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizLeagueResults'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizLeagueResults'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for single Quiz League table
function GetQuizLeagueTable($id_quiz_league, $round)
{
	global $context, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT		QLT.id_quiz_league_table,
					QLT.current_position,
					QLT.id_user,
					M.real_name,
					QLT.last_position,
					QLT.plays,
					QLT.correct,
					QLT.incorrect,
					QLT.timeouts,
					QLT.seconds,
					QLT.points
		FROM		{db_prefix}quiz_league_table QLT
		INNER JOIN	{db_prefix}members M
		ON			QLT.id_user = M.id_member
		WHERE		QLT.round = {int:current_round}
		AND			QLT.id_quiz_league = {int:id_quiz_league}
		ORDER BY	QLT.current_position ASC,
					QLT.seconds
		LIMIT		0, 10
		',
		array(
			'current_round' => (int)$round,
			'id_quiz_league' => (int)$id_quiz_league,
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizTable'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizTable'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for user Quiz League details
function GetUserQuizLeagueDetails($id_user)
{
	global $context, $txt, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QL.id_quiz_league,
					QL.title,
					QL.updated,
					QL.current_round,
					QL.state,
					QL.day_interval,
					IFNULL(QL.id_leader,0) AS id_leader,
					IFNULL(M.real_name, {string:none}) AS leader_name,
					IFNULL(QLT.points,0) AS user_points,
					IFNULL(QLT.current_position,0) AS user_position
		FROM 		{db_prefix}quiz_league QL
		LEFT JOIN	{db_prefix}members M
		ON			QL.id_leader = M.id_member
		LEFT JOIN	{db_prefix}quiz_league_table QLT
		ON			QLT.round = QL.current_round
		AND			QLT.id_quiz_league = QL.id_quiz_league
		AND			QLT.id_user = {int:id_user}
		WHERE		QL.state = 1 OR QL.state = 2
		ORDER BY 	QL.state ASC,
					QL.title ASC',
		array(
			'id_user' => (int)$id_user,
			'none' => $txt['SMFQuiz_Common']['None'],
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizLeagues'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizLeagues'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetAllQuestionTypes()
{
	global $context, $smcFunc;

	$result = $smcFunc['db_query']('', '
		SELECT id_question_type, description
		FROM {db_prefix}quiz_question_type
		ORDER BY description',
		[]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['questionTypes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['questionTypes'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for the category details
function GetAllCategoryDetails($page = 1, $orderBy = 'C.name', $orderDir = 'up', $pageSize = 5000)
{
	global $context, $txt, $smcFunc;

	// Work out paging
	$startPage = ($page - 1) * $pageSize;

	// Work out orderng
	if ($orderDir == 'up')
		$orderDir = 'ASC';
	else
		$orderDir = 'DESC';

		// @TODO query
	$result = $smcFunc['db_query']('', "
		SELECT 		C.id_category,
					C.name,
					C.description,
					C.id_parent,
					C.image,
					C.quiz_count,
					IFNULL(C2.name, {string:toplvl}) AS parent_name
		FROM 		{db_prefix}quiz_category C
		LEFT JOIN 	{db_prefix}quiz_category C2
		ON 			C.id_parent = C2.id_category
		ORDER BY 	{$orderBy} {$orderDir}
		LIMIT		{$startPage}, {$pageSize}",
		[
			'toplvl' => $txt['SMFQuiz_Common']['TopLevel'],
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['categories'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['categories'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetCategoryChildren($page = 1, $orderBy = 'C.name', $orderDir = 'up', $pageSize = 5000, $id_category = 0)
{
	global $context, $txt, $smcFunc;

	// Work out paging
	$startPage = ($page - 1) * $pageSize;

	// Work out orderng
	if ($orderDir == 'up')
		$orderDir = 'ASC';
	else
		$orderDir = 'DESC';

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		C.id_category,
					C.name,
					C.description,
					C.id_parent,
					C.image,
					C.quiz_count,
					IFNULL(C2.name, {string:toplvl}) AS parent_name
		FROM 		{db_prefix}quiz_category C
		LEFT JOIN 	{db_prefix}quiz_category C2
		ON 			C.id_parent = C2.id_category
		WHERE		C.id_parent = {int:id_category}
		ORDER BY 	{raw:orderBy} {raw:orderDir}
		LIMIT		{int:startPage}, {int:pageSize}',
		array(
			'id_category' => (int)$id_category,
			'startPage' => (int)$startPage,
			'pageSize' => (int)$pageSize,
			'orderBy' => $orderBy,
			'orderDir' => $orderDir,
			'toplvl' => $txt['SMFQuiz_Common']['TopLevel'],
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['categories'] = [];
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['categories'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function QuizGetCategoryParentsWithChild()
{
	global $txt, $context, $smcFunc;

	$result = $smcFunc['db_query']('', "
		SELECT 		C.id_category,
					C.name,
					IFNULL(C.name, {string:toplvl}) AS parent_name
		FROM 		{db_prefix}quiz_category C
		WHERE C.id_category IN (SELECT CC.id_parent FROM {db_prefix}quiz_category CC)
		ORDER BY 	C.name ASC",
		[
			'id_category' => 0,
			'toplvl' => $txt['SMFQuiz_Common']['TopLevel'],
		]
	);

	$context['SMFQuiz']['parent_categories'] = [];
	$context['SMFQuiz']['parent_categories'][] = [
		'id_category' => 0,
		'name' => $txt['SMFQuizAdmin_Categories_Page']['ParentCategory']
	];
	while ($row = $smcFunc['db_fetch_assoc']($result)) {
		$context['SMFQuiz']['parent_categories'][] = [
			'id_category' => (int)$row['id_category'],
			'name' => $row['name']
		];
	}

	$context['SMFQuiz']['parent_categories'] = array_filter($context['SMFQuiz']['parent_categories']);

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetCategoryParent($page = 1, $orderBy = 'C.name', $orderDir = 'up', $pageSize = 5000, $id_category = 0)
{
	global $context, $txt, $smcFunc;

	// Work out paging
	$startPage = ((int)$page - 1) * $pageSize;

	// Work out orderng
	if ($orderDir == 'up')
		$orderDir = 'ASC';
	else
		$orderDir = 'DESC';

		// @TODO query
	$result = $smcFunc['db_query']('', "
		SELECT 		C.id_category,
					C.name,
					C.description,
					C.id_parent,
					C.image,
					C.quiz_count,
					IFNULL(C2.name, {string:toplvl}) AS parent_name
		FROM 		{db_prefix}quiz_category C
		LEFT JOIN 	{db_prefix}quiz_category C2
		ON 			C.id_parent = C2.id_category
		WHERE		C.id_parent IN (
						SELECT 		QC.id_parent
						FROM		{db_prefix}quiz_category QC
						WHERE 		QC.id_category = {int:id_category}
					)
		ORDER BY 	{$orderBy} {$orderDir}
		LIMIT		{$startPage}, {$pageSize}",
		array(
			'id_category' => (int)$id_category,
			'toplvl' => $txt['SMFQuiz_Common']['TopLevel'],
		)
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['categories'] = [];
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['categories'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for the category details
function GetParentCategoryDetails($parentId = 0)
{
	global $context, $txt, $smcFunc;

		// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		C.id_category,
					C.name,
					C.description,
					C.id_parent,
					C.image,
					C.quiz_count,
					IFNULL(C2.name, {string:toplvl}) AS parent_name
		FROM 		{db_prefix}quiz_category C
		LEFT JOIN 	{db_prefix}quiz_category C2
		ON 			C.id_parent = C2.id_category
		WHERE		C.id_parent = {int:id_parent}
		ORDER BY	C.name',
		[
			'id_parent' => (int)$parentId,
			'toplvl' => $txt['SMFQuiz_Common']['TopLevel'],
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['categories'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['categories'][] = $row;

	// We want to return the number of rows in case some logic depends on it
	$rows = $smcFunc['db_num_rows']($result);

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Updates the answer with the specified data
function UpdateAnswer($id_answer, $answer_text, $is_correct)
{
	global $smcFunc;

	$updated = time();

	// Execute the query
		// @TODO query
	$smcFunc['db_query']('', '
		UPDATE		{db_prefix}quiz_answer
		SET			answer_text = {string:answer_text},
					is_correct = {int:is_correct},
					updated = {int:updated}
		WHERE		id_answer = {int:id_answer}',
		[
			'id_answer' => (int)$id_answer,
			'answer_text' => Quiz\Helper::quiz_commonStringFilter($answer_text),
			'is_correct' => (int)$is_correct,
			'updated' => (int)$updated,
		]
	);
}

function SaveAnswer($id_question, $answer_text, $is_correct)
{
	global $smcFunc;

	$updated = time();

		// @TODO utf8
	// Execute the query
	$smcFunc['db_insert']('insert',
		'{db_prefix}quiz_answer',
		[
			'id_question' => 'int',
			'answer_text' => 'string',
			'is_correct' => 'int',
			'updated' => 'int'
		],
		[
			(int)$id_question,
			Quiz\Helper::quiz_commonStringFilter($answer_text),
			(int)$is_correct,
			(int)$updated
		],
		['id_answer']
	);
}

// Data class for updating quizzes
function UpdateQuiz($id_quiz, $title, $description, $play_limit, $seconds, $show_answers, $image, $id_category, $oldCategoryId, $enabled, $for_review)
{
	global $smcFunc;

	// Removing this, as it would cause the quiz to be regarded as new again
	// May look into adding another datetime for this purpose, but not worth it at the moment
	//$updated = time();
		// @TODO utf8
		// @TODO query
	$result = $smcFunc['db_query']('', '
		UPDATE		{db_prefix}quiz
		SET 		title = {string:title},
					description = {string:description},
					play_limit = {int:play_limit},
					seconds_per_question = {int:seconds},
					show_answers = {int:show_answers},
					image = {string:image},
					id_category = {int:id_category},
					enabled = {int:enabled},
					for_review = {int:for_review}
		WHERE		id_quiz = {int:id_quiz}',
		[
			'title' =>  Quiz\Helper::quiz_commonStringFilter($title),
			'description' =>  Quiz\Helper::quiz_commonStringFilter($description),
			'play_limit' => (int)$play_limit,
			'seconds' => (int)$seconds,
			'show_answers' => (int)$show_answers,
			'image' =>  Quiz\Helper::quiz_commonImageFileFilter($image),
			'id_category' => (int)$id_category,
			'enabled' => (int)$enabled,
			'id_quiz' => (int)$id_quiz,
			'for_review' => (int)$for_review
		]
	);

	// If the category has changed we need to update the quiz counts on the associated trees
	if ($id_category != $oldCategoryId)
	{
		IncrementCategoryTree($id_category);
		DecrementCategoryTree($oldCategoryId);
	}
}

// Data class for saving quizzes
function SaveQuiz($title, $description, $play_limit, $seconds_per_question, $show_answers, $image, $id_category, $enabled, $creator_id, $for_review)
{
	global $smcFunc;

	$updated = time();
    $returnVal = 0;

	// Make sure at least the required fields are set before continuing
	if (!empty($title))
	{

		// Execute the query
		// @TODO utf8
		$smcFunc['db_insert']('insert',
			'{db_prefix}quiz',
			array(
				'title' => 'string',
				'description' => 'string',
				'play_limit' => 'int',
				'seconds_per_question' => 'int',
				'show_answers' => 'int',
				'image' => 'string',
				'id_category' => 'int',
				'enabled' => 'int',
				'creator_id' => 'int',
				'for_review' => 'int',
				'updated' => 'int'
			),
			[
				Quiz\Helper::quiz_commonStringFilter($title),
				Quiz\Helper::quiz_commonStringFilter($description),
				(int)$play_limit,
				(int)$seconds_per_question,
				(int)$show_answers,
				Quiz\Helper::quiz_commonImageFileFilter($image),
				(int)$id_category,
				(int)$enabled,
				(int)$creator_id,
				(int)$for_review,
				(int)$updated
			],
			['id_question']
		);

		IncrementCategoryTree($id_category);

		// Execute this query
		// @TODO query
		$result = $smcFunc['db_query']('', '
			SELECT 		id_quiz
			FROM 		{db_prefix}quiz
			ORDER BY 	id_quiz DESC
			LIMIT 0, 1'
		);

		// Loop through the results and populate the context accordingly
		$returnVal = 0;
		while ($row = $smcFunc['db_fetch_assoc']($result))
			$returnVal = $row['id_quiz'];

		// Free the database
		$smcFunc['db_free_result']($result);
	}

		// @TODO undefined?
	return $returnVal;
}

// Updates the question with the specified data
function UpdateQuestion($id_question, $question_text, $image, $answer_text)
{
	global $smcFunc;

	$updated = time();

	// Execute this query
		// @TODO utf8
	$smcFunc['db_query']('', '
		UPDATE 		{db_prefix}quiz_question
		SET			question_text = {string:question_text},
					image = {string:image},
					answer_text = {text:answer_text},
					updated = {int:updated}
		WHERE		id_question = {int:id_question}',
		[
			'question_text' => Quiz\Helper::quiz_commonStringFilter($question_text),
			'image' => Quiz\Helper::quiz_commonImageFileFilter($image),
			'answer_text' => Quiz\Helper::quiz_commonStringFilter($answer_text),
			'updated' => (int)$updated,
			'id_question' => (int)$id_question,
		]
	);
}

// Saves a new question to the database and returns the ID of this inserted record
function SaveQuestion($question_text, $id_question_type, $id_quiz, $image, $answer_text)
{
	global $smcFunc;

	$updated = time();

	// Execute the query
	$smcFunc['db_insert']('insert',
		'{db_prefix}quiz_question',
		[
			'question_text' => 'string',
			'id_question_type' => 'int',
			'id_quiz' => 'int',
			'image' => 'string',
			'answer_text' => 'string',
			'updated' => 'int'
		],
		[
			Quiz\Helper::quiz_commonStringFilter($question_text),
			(int)$id_question_type,
			(int)$id_quiz,
			Quiz\Helper::quiz_commonImageFileFilter($image),
			Quiz\Helper::quiz_commonStringFilter($answer_text),
			(int)$updated
		],
		['id_question']
	);

	// Get the ID of this insert
	$quiz_question['id_question'] = $smcFunc['db_insert_id']('{db_prefix}quiz_question', 'id_question');

	return $quiz_question['id_question'];
}


function UpdateQuizLeague($id_quiz_league, $title, $description, $day_interval, $questions_per_session, $seconds_per_question, $points_for_correct, $show_answers, $total_rounds, $state, $categories)
{
	global $smcFunc;

// @TODO query + utf8
	$result = $smcFunc['db_query']('', '
		UPDATE		{db_prefix}quiz_league
		SET 		title = {string:title},
					description = {string:description},
					questions_per_session = {int:questions_per_session},
					day_interval = {int:day_interval},
					seconds_per_question = {int:seconds_per_question},
					points_for_correct = {int:points_for_correct},
					show_answers = {int:show_answers},
					total_rounds = {int:total_rounds},
					state = {int:state},
					categories = {string:categories}
		WHERE		id_quiz_league = {int:id_quiz_league}',
		[
			'title' =>  Quiz\Helper::quiz_commonStringFilter($title),
			'description' =>  Quiz\Helper::quiz_commonStringFilter($description),
			'questions_per_session' => (int)$questions_per_session,
			'day_interval' => (int)$day_interval,
			'seconds_per_question' => (int)$seconds_per_question,
			'points_for_correct' => (int)$points_for_correct,
			'show_answers' => (int)$show_answers,
			'total_rounds' => (int)$total_rounds,
			'state' => (int)$state,
			'categories' => $categories,
			'id_quiz_league' => (int)$id_quiz_league,
		]
	);
}

// Data class for saving quiz leagues
function SaveQuizLeague($title, $description, $day_interval, $questions_per_session, $seconds_per_question, $points_for_correct, $show_answers, $total_rounds, $state, $categories)
{
	global $smcFunc;

	/*
	$smcFunc['db_query']('', "
		DELETE FROM {$db_prefix}quiz_league
		WHERE		id_question IN ({$questionInIds})",
		[]
	);
	*/

	// Execute the query
	$smcFunc['db_insert']('insert',
		'{db_prefix}quiz_league',
		[
			'title' => 'string',
			'description' => 'string',
			'day_interval' => 'int',
			'questions_per_session' => 'string',
			'seconds_per_question' => 'string',
			'points_for_correct' => 'int',
			'show_answers' => 'int',
			'total_rounds' => 'int',
			'state' => 'int',
			'updated' => 'int',
			'categories' => 'string'
		],
		[
			Quiz\Helper::quiz_commonStringFilter($title),
			Quiz\Helper::quiz_commonStringFilter($description),
			(int)$day_interval,
			(int)$questions_per_session,
			(int)$seconds_per_question,
			(int)$points_for_correct,
			(int)$show_answers,
			(int)$total_rounds,
			(int)$state,
			intval(time()),
			$categories
		],
		array('id_quiz_league')
	);
}

function UpdateCategory($id_category, $name, $description, $parent, $image)
{
	global $smcFunc;

	$updated = time();

	// Execute the query
// @TODO query
	$smcFunc['db_query']('', '
		UPDATE		{db_prefix}quiz_category
		SET			id_parent = {int:parent},
					name = {string:name},
					description = {string:description},
					image = {string:image},
					updated = {int:updated}
		WHERE		id_category = {int:id_category}',
		[
			'parent' => (int)$parent,
			'name' => Quiz\Helper::quiz_commonStringFilter($name),
			'description' => Quiz\Helper::quiz_commonStringFilter($description),
			'image' => Quiz\Helper::quiz_commonImageFileFilter($image),
			'updated' => (int)$updated,
			'id_category' => (int)$id_category,
		]
	);
}

// Data class for saving category details
function SaveCategory($name, $description, $id_parent, $image)
{
	global $smcFunc;

	// Execute the query
	$smcFunc['db_insert']('insert',
		'{db_prefix}quiz_category',
		[
			'name' => 'string',
			'description' => 'string',
			'id_parent' => 'int',
			'image' => 'string'
		],
		[
			Quiz\Helper::quiz_commonStringFilter($name),
			Quiz\Helper::quiz_commonStringFilter($description),
			(int)$id_parent,
			Quiz\Helper::quiz_commonImageFileFilter($image)
		],
		['id_category']
	);
}

// Data class for deleting questions
function DeleteQuestions($questionInIds)
{
	global $db_prefix, $smcFunc;

	// Deleted the selected questions
// @TODO query
	$smcFunc['db_query']('', "
		DELETE FROM {$db_prefix}quiz_question
		WHERE		id_question IN ({$questionInIds})
	");

	// Delete related answers
	$smcFunc['db_query']('', "
		DELETE FROM {$db_prefix}quiz_answer
		WHERE		id_question IN ({$questionInIds})
	");
}

// Data class for deleting quiz leagues
function DeleteQuizLeagues($quizLeagueInIds)
{
	global $smcFunc, $db_prefix;

	// Execute the query
// @TODO query
	$smcFunc['db_query']('', "
		DELETE FROM {$db_prefix}quiz_league
		WHERE		id_quiz_league IN ({$quizLeagueInIds})"
	);
}

// Data class for deleting quizzes
function DeleteQuizzes($quizInIds)
{
	global $smcFunc, $db_prefix;

	// What we need to do now is loop through each quiz that has been deleted and decrement the quiz count for any related quiz category
	$quizIds = array_filter(array_map('intval', explode(',', $quizInIds)));
	foreach ($quizIds as $quizId) {
		// We need to return the category associated to the quiz first - could have done this all using subqueries, but this seems
		// to be frowned upon in SMF
// @TODO query
		$result = $smcFunc['db_query']('', '
			SELECT 		id_category
			FROM 		{db_prefix}quiz Q
			WHERE		Q.id_quiz = {int:id_quiz}',
			[
				'id_quiz' => (int)$quizId
			]
		);

		while ($row = $smcFunc['db_fetch_assoc']($result)) {
			$categoryId = $row['id_category'];
			DecrementCategoryTree($categoryId);
		}
		$smcFunc['db_free_result']($result);

		// Delete questions related to this quiz
// @TODO query
		$smcFunc['db_query']('', '
			DELETE
			FROM		{db_prefix}quiz_question
			WHERE		id_quiz = {int:id_quiz}',
			[
				'id_quiz' => (int)$quizId,
			]
		);
	}

	// Delete all other data related to these quiz IDs
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}quiz
		WHERE id_quiz IN ({array_int:quizzes})',
		[
			'quizzes' => $quizIds
		]
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}quiz_dispute
		WHERE		id_quiz IN ({array_int:quizzes})',
		[
			'quizzes' => $quizIds
		]
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}quiz_result
		WHERE		id_quiz IN ({array_int:quizzes})',
		[
			'quizzes' => $quizIds
		]
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}quiz_session
		WHERE		id_quiz IN ({array_int:quizzes})',
		[
			'quizzes' => $quizIds
		]
	);

	DeleteOrphanedAnswersData();
}

// Data class for deleting quiz disputes
function DeleteQuizDisputes($quizDisputeInIds)
{
	global $smcFunc, $db_prefix;

	// Execute the query
// @TODO query
	$smcFunc['db_query']('', "
		DELETE FROM {$db_prefix}quiz_dispute
		WHERE		id_quiz_dispute IN ({$quizDisputeInIds})"
	);
}

// Data class for deleting quiz results
function DeleteQuizResults($quizResultInIds)
{
	global $smcFunc, $db_prefix;

	// Execute the query
// @TODO query
	$smcFunc['db_query']('', "
		DELETE FROM {$db_prefix}quiz_result
		WHERE		id_quiz_result IN ({$quizResultInIds})"
	);
}

function GetLatestQuizzes()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.id_quiz,
					Q.title,
					Q.image,
					Q.updated
		FROM 		{db_prefix}quiz Q
		WHERE		Q.enabled = 1
		ORDER BY	Q.updated DESC
		LIMIT		0, 8'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['latestQuizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result)) {
		$context['SMFQuiz']['latestQuizzes'][] = $row;
	}

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetPopularQuizzes($limit)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.id_quiz,
					Q.title,
					Q.image,
					Q.quiz_plays,
					Q.updated
		FROM 		{db_prefix}quiz Q
		WHERE		Q.enabled = 1
		ORDER BY	Q.quiz_plays DESC
		LIMIT		0, {int:limit}',
		[
			'limit' => (int)$limit
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['popularQuizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['popularQuizzes'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetQuizLeagueLeaders($limit)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QL.id_leader,
					QL.id_quiz_league,
					QL.title,
					M.real_name,
					QL.updated
		FROM 		{db_prefix}quiz_league QL
		LEFT JOIN 	{db_prefix}members M
		ON 			QL.id_leader = M.id_member
		ORDER BY 	QL.updated DESC
		LIMIT		0, {int:limit}',
		[
			'limit' => (int)$limit,
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizLeagueLeaders'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizLeagueLeaders'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetQuizMasters($limit)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.top_user_id AS id_user,
					M.real_name,
					COUNT(*) AS total_wins
		FROM 		{db_prefix}quiz Q
		INNER JOIN 	{db_prefix}members M
		ON 			Q.top_user_id = M.id_member
		WHERE		Q.top_user_id <> 0
		GROUP BY 	Q.top_user_id, M.real_name
		ORDER BY 	total_wins DESC
		LIMIT		0, {int:limit}',
		[
			'limit' => (int)$limit,
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizMasters'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizMasters'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetLatestInfoBoard($limit = 20)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', "
		SELECT 		I.entry_date,
					I.Entry
		FROM 		{db_prefix}quiz_infoboard I
		ORDER BY	I.entry_date DESC
		LIMIT		0, {$limit}"
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['infoBoard'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['infoBoard'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

// Data class for deleting categories
function DeleteCategories($categoryInIds)
{
	global $smcFunc, $db_prefix;

	// Execute the query
// @TODO query
	$smcFunc['db_query']('', "
		DELETE FROM {$db_prefix}quiz_category
		WHERE		id_category IN ({$categoryInIds})
		OR			id_parent IN ({$categoryInIds})"
	);
}

function IncrementCategoryTree($id_category)
{
	global $smcFunc;

	// Execute the query
// @TODO query
	$smcFunc['db_query']('', '
		UPDATE		{db_prefix}quiz_category
		SET			quiz_count = quiz_count + 1
		WHERE		id_category = {int:id_category}',
		[
			'id_category' => (int)$id_category
		]
	);

	// Now walk up the tree and increment any parent category quiz counts
	$parentId = -1;
	while ($parentId != 0)
	{
// @TODO query
		$parentIdResult = $smcFunc['db_query']('', '
			SELECT		id_parent
			FROM		{db_prefix}quiz_category
			WHERE		id_category = {int:id_category}',
			[
				'id_category' => (int)$id_category,
			]
		);
		$rows = $smcFunc['db_num_rows']($parentIdResult);
		if ($rows > 0)
		{
			while ($parentIdRow = $smcFunc['db_fetch_assoc']($parentIdResult))
				$parentId = $parentIdRow['id_parent'];

			// Free the database
			$smcFunc['db_free_result']($parentIdResult);
		}
		else
			$parentId = 0;

		if ($parentId != 0) {
// @TODO query
			$smcFunc['db_query']('', '
				UPDATE		{db_prefix}quiz_category
				SET			quiz_count = quiz_count + 1
				WHERE		id_category = {int:id_category}',
				[
					'id_category' => (int)$parentId,
				]
			);
			$id_category = $parentId;
		}
	}

}

function DecrementCategoryTree($id_category)
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		UPDATE 		{db_prefix}quiz_category QC
		SET 		QC.quiz_count = QC.quiz_count - 1
		WHERE 		QC.id_category = {int:id_category}
		AND			QC.quiz_count > 0',
		[
			'id_category' => (int)$id_category,
		]
	);

	// Now walk up the tree and decrement any parent category quiz counts
	$parentId = -1;
	while ($parentId != 0)
	{
// @TODO query
		$parentIdResult = $smcFunc['db_query']('', '
			SELECT 		C.id_parent
			FROM 		{db_prefix}quiz_category C
			WHERE		C.id_category = {int:id_category}',
			[
				'id_category' => (int)$id_category,
            ]
		);
		$rows = $smcFunc['db_num_rows']($parentIdResult);
		if ($rows > 0)
		{
			while ($parentIdRow = $smcFunc['db_fetch_assoc']($parentIdResult))
				$parentId = $parentIdRow['id_parent'];

		}
		else
			$parentId = 0;

		$smcFunc['db_free_result']($parentIdResult);

		if ($parentId != 0)
		{
// @TODO query
			$smcFunc['db_query']('', '
				UPDATE 		{db_prefix}quiz_category QC
				SET 		QC.quiz_count = QC.quiz_count - 1
				WHERE 		QC.id_category = {int:id_category}
				AND			QC.quiz_count > 0',
				[
					'id_category' => (int)$parentId,
	            ]
			);
			$id_category = $parentId;
		}
	}
}

function GetTotalQuizzes()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		COUNT(*) AS total_quiz_count
		FROM		{db_prefix}quiz
		LIMIT		0, 1'
	);

	// Loop through the results and populate the context accordingly
	while ($row = $smcFunc['db_fetch_row']($result))
		$context['SMFQuiz']['totalQuizzes'] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetTotalDisputesCount()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		COUNT(*) AS total_disputes_count
		FROM 		{db_prefix}quiz_dispute'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz_totalDisputes'] = 0;
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz_totalDisputes'] = $row['total_disputes_count'];

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetTotalReviewCount()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		COUNT(*) AS total_review_count
		FROM 		{db_prefix}quiz
		WHERE		for_review = 1'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz_totalQuizzesWaitingReview'] = 0;
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz_totalQuizzesWaitingReview'] = $row['total_review_count'];

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetDisabledQuizCount()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		COUNT(*) AS total_diabled_quizzes_count
		FROM 		{db_prefix}quiz
		WHERE		enabled = 0'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz_totalDisabledQuizzes'] = 0;
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz_totalDisabledQuizzes'] = $row['total_diabled_quizzes_count'];

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetTotalQuizStats()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		COUNT(*) AS total_quiz_count,
					SUM(quiz_plays) AS total_quiz_plays,
					SUM(question_plays) AS total_question_plays,
					SUM(total_correct) AS total_correct,
					round((SUM(total_correct) / SUM(question_plays)) * 100) AS total_percentage_correct
		FROM 		{db_prefix}quiz'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['totalQuizStats'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['totalQuizStats'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetBestQuizResult()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		M.real_name,
					QR.total_seconds,
					QR.questions,
					QR.result_date,
					QR.correct,
					round((QR.correct / QR.questions) * 100) AS percentage_correct
		FROM 		{db_prefix}quiz_result QR
		INNER JOIN 	{db_prefix}members M
		ON 			QR.id_user = M.id_member
		ORDER BY 	percentage_correct DESC,
					questions DESC,
					total_seconds ASC,
					result_date ASC
		LIMIT 		0, 1'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['bestQuizResult'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['bestQuizResult'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetWorstQuizResult()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		M.real_name,
					QR.total_seconds,
					QR.questions,
					QR.result_date,
					QR.correct,
					round((QR.correct / QR.questions) * 100) AS percentage_correct
		FROM 		{db_prefix}quiz_result QR
		INNER JOIN 	{db_prefix}members M
		ON 			QR.id_user = M.id_member
		ORDER BY 	percentage_correct ASC,
					questions ASC,
					total_seconds DESC,
					result_date ASC
		LIMIT 		0,1'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['worstQuizResult'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['worstQuizResult'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetHardestQuizzes()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.id_quiz,
					Q.title,
					round((Q.question_plays - Q.total_correct) / Q.question_plays * 100) AS percentage_incorrect
		FROM 		{db_prefix}quiz Q
		WHERE		question_plays > 0
		AND			Q.enabled = 1
		ORDER BY 	percentage_incorrect DESC
		LIMIT 		0, 10'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['hardestQuizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['hardestQuizzes'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetEasiestQuizzes()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.id_quiz,
					Q.title,
					round(Q.total_correct / Q.question_plays * 100) AS percentage_correct
		FROM 		{db_prefix}quiz Q
		WHERE		question_plays > 0
		AND			Q.enabled = 1
		ORDER BY 	percentage_correct DESC
		LIMIT 		0, 10'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['easiestQuizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['easiestQuizzes'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetNewestQuiz()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.id_quiz,
					Q.title,
					Q.updated
		FROM 		{db_prefix}quiz Q
		WHERE		Q.enabled = 1
		ORDER BY 	Q.updated ASC
		LIMIT		0, 1'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['oldestQuiz'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['oldestQuiz'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetOldestQuiz()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.id_quiz,
					Q.title,
					Q.updated
		FROM 		{db_prefix}quiz Q
		WHERE		Q.enabled = 1
		ORDER BY 	Q.updated DESC
		LIMIT		0, 1'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['newestQuiz'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['newestQuiz'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function MostQuizWins()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.top_user_id,
					M.real_name,
					COUNT(Q.top_user_id) AS TopScores
		FROM 		{db_prefix}quiz Q
		INNER JOIN	{db_prefix}members M
		ON			Q.top_user_id = M.id_member
		WHERE 		top_user_id != 0
		GROUP BY 	top_user_id,
					M.real_name
		ORDER BY	TopScores DESC
		LIMIT		0, 1'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['mostQuizWins'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['mostQuizWins'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetTotalQuestions()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		COUNT(*) AS total_question_count
		FROM		{db_prefix}quiz_question
		LIMIT		0, 1'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['totalQuestions'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['totalQuestions'] = $row['total_question_count'];

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetTotalAnswers()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		COUNT(*) AS total_answers
		FROM		{db_prefix}quiz_answer
		LIMIT		0, 1'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['totalAnswers'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['totalAnswers'] = $row['total_answers'];

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetTotalCategories()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		COUNT(*) AS total_category_count
		FROM		{db_prefix}quiz_category
		LIMIT		0, 1'
	);

	// Loop through the results and populate the context accordingly
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['totalCategories'] = $row['total_category_count'];

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetMemberStatistics($id_user)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		SUM(QR.questions) AS total_questions,
					SUM(QR.correct) AS total_correct,
					SUM(QR.incorrect) AS total_incorrect,
					SUM(QR.timeouts) AS total_timeouts,
					SUM(QR.total_seconds) AS total_seconds,
					COUNT(*) AS total_played,
					round((SUM(QR.correct) / SUM(QR.questions)) * 100) AS percentage_correct
		FROM 		{db_prefix}quiz_result QR
		WHERE 		QR.id_user = {int:id_user}',
		[
			'id_user' => (int)$id_user
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['memberStatistics'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['memberStatistics'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetUserQuizScores($id_user)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT		Q.title,
					Q.id_quiz,
					QR.result_date,
					QR.questions,
					QR.correct,
					QR.incorrect,
					QR.timeouts,
					QR.total_seconds,
					QR.player_limit,
					IFNULL(round((QR.correct / QR.questions) * 100),0) AS percentage_correct,
					QR.auto_completed
		FROM		{db_prefix}quiz_result QR
		INNER JOIN	{db_prefix}quiz Q
		ON 			QR.id_quiz = Q.id_quiz
		WHERE 		QR.id_user = {int:id_user}
		ORDER BY	QR.result_date DESC
		LIMIT		0, 10',
		[
			'id_user' => (int)$id_user
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['userQuizScores'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['userQuizScores'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetUserCorrectScores($id_user)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QR.correct,
					COUNT(QR.correct) AS count_correct
		FROM 		{db_prefix}quiz_result QR
		WHERE 		QR.id_user = {int:id_user}
		GROUP BY 	QR.correct',
		[
			'id_user' => (int)$id_user
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['userCorrectScores'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['userCorrectScores'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetUserCategoryPlays($id_user)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QC.id_category,
					QC.name,
					COUNT(*) AS category_plays
		FROM 		{db_prefix}quiz_result QR
		INNER JOIN 	{db_prefix}quiz Q
		ON 			QR.id_quiz = Q.id_quiz
		INNER JOIN 	{db_prefix}quiz_category QC
		ON 			Q.id_category = QC.id_category
		WHERE 		QR.id_user = {int:id_user}
		GROUP 		BY QC.id_category,
					QC.name
		ORDER BY 	category_plays DESC',
		[
			'id_user' => (int)$id_user
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['userCategoryPlays'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['userCategoryPlays'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetQuizSessions($id_user)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		QS.question_count,
					QS.last_question_start,
					Q.id_quiz,
					Q.title
		FROM 		{db_prefix}quiz_session QS
		INNER JOIN 	{db_prefix}quiz Q
		ON 			QS.id_quiz = Q.id_quiz
		WHERE 		id_user = {int:id_user}',
		[
			'id_user' => (int)$id_user
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['quizSessions'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['quizSessions'][] = $row;

// @TODO ??? Nothing is returned
	// We want to return the number of rows in case some logic depends on it
	$rows = $smcFunc['db_num_rows']($result);

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetUserQuizzes($id_user)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		Q.id_quiz,
					Q.title,
					Q.updated,
					Q.enabled,
					Q.for_review,
					QC.name AS category_name,
					IFNULL(COUNT(QQ.id_quiz),0) AS questions_per_session
		FROM		{db_prefix}quiz Q
		INNER JOIN	{db_prefix}quiz_category QC
		ON			Q.id_category = QC.id_category
		LEFT JOIN	{db_prefix}quiz_question QQ
		ON			Q.id_quiz = QQ.id_quiz
		WHERE 		Q.creator_id = {int:id_user}
		GROUP BY	Q.id_quiz,
					Q.title,
					Q.updated,
					Q.enabled,
					Q.for_review,
					QQ.id_quiz,
					category_name
		ORDER BY	Q.title',
		[
			'id_user' => (int)$id_user
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['userQuizzes'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['userQuizzes'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function SetQuizForReview($id_quiz)
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		UPDATE		{db_prefix}quiz Q
		SET			Q.for_review = 1
		WHERE 		Q.id_quiz = {int:id_quiz}',
		[
			'id_quiz' => (int)$id_quiz
		]
	);
}

function GetTotalUserWins($id_user)
{
	global $context, $smcFunc;

	// Retrieve the result of executing this query
// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT		COUNT(*) AS total_user_wins
		FROM		{db_prefix}quiz Q
		WHERE		top_user_id = {int:id_user}',
		[
			'id_user' => (int)$id_user
		]
	);

	// This should only be one value
	$context['SMFQuiz']['total_user_wins'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['total_user_wins'] = $row['total_user_wins'];

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetMostActivePlayers($limit = 10)
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT  	QR.id_user,
					M.real_name,
					COUNT(QR.id_user) as total_plays
		FROM 		{db_prefix}quiz_result QR
		INNER JOIN 	{db_prefix}members M
		ON 			QR.id_user = M.id_member
		GROUP BY 	QR.id_user,
					M.real_name
		ORDER BY	total_plays DESC
		LIMIT		0, {int:limit}',
		[
			'limit' => (int)$limit,
		]
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['mostActivePlayers'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['mostActivePlayers'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function GetMostQuizCreators()
{
	global $context, $smcFunc;

	$result = $smcFunc['db_query']('', '
		SELECT 		COUNT(*) AS quizzes,
					Q.creator_id,
					M.real_name
		FROM 		{db_prefix}quiz Q
		INNER JOIN 	{db_prefix}members M
		ON 			Q.creator_id = M.id_member
		GROUP BY 	Q.creator_id, M.real_name
		ORDER BY 	quizzes DESC
		LIMIT		0, 10'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['mostQuizCreators'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['mostQuizCreators'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function ImportQuiz($title, $description, $play_limit, $seconds_per_question, $show_answers, $id_category, $enabled, $image, $creator_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_quiz
		FROM {db_prefix}quiz
		WHERE title = {string:quiz_title}
		LIMIT 1',
		[
			'quiz_title' => $title,
		]
	);
	if ($smcFunc['db_num_rows']($request) > 0)
		return 'quiz_alredy_exists';

	// Add the quiz to the quiz table
	$smcFunc['db_insert']('insert',
		'{db_prefix}quiz',
		[
			'title' => 'string',
			'description' => 'string',
			'play_limit' => 'int',
			'seconds_per_question' => 'int',
			'show_answers' => 'int',
			'image' => 'string',
			'id_category' => 'int',
			'enabled' => 'int',
			'updated' => 'int',
			'creator_id' => 'int',
		],
		[
			Quiz\Helper::quiz_commonStringFilter($title),
			Quiz\Helper::quiz_commonStringFilter($description),
			intval($play_limit),
			intval($seconds_per_question),
			intval($show_answers),
			Quiz\Helper::quiz_commonImageFileFilter($image),
			(int)$id_category,
			intval($enabled),
			time(),
			intval($creator_id),
		],
		array('id_quiz')
	);

	// Retrieve the ID for the inserted quiz
	$import_quiz['id_quiz'] = $smcFunc['db_insert_id']('{db_prefix}quiz', 'id_quiz');

	// Update category count
	IncrementCategoryTree($id_category);

	return $import_quiz['id_quiz'];
}

function ImportQuizQuestion($id_quiz, $question_text, $id_question_type, $answer_text, $image, $imageData = '')
{
	global $smcFunc, $settings, $sourcedir;

	$image = trim($image);
	// These are the only valid image types for SMF.
	$validImageTypes = array(
		1 => 'gif',
		2 => 'jpeg',
		3 => 'png',
		5 => 'psd',
		6 => 'bmp',
		7 => 'tiff',
		8 => 'tiff',
		9 => 'jpeg',
		14 => 'iff'
	);

	if (!empty($image))
	{
		$dest = $settings['default_theme_dir'] . '/images/quiz_images/Questions/' . $image;
		if (!file_exists($dest) && is_writable($settings['default_theme_dir'] . '/images/quiz_images/Questions/') && !empty($imageData))
		{
			$imageData = base64_decode($imageData);
			file_put_contents($dest, $imageData);
			$size = @getimagesize($dest);
			// Default to png (3)
			$fileType = isset($validImageTypes[$size[2]]) ? $size[2] : 3;
			require_once($sourcedir . '/Subs-Graphics.php');
			if (!reencodeImage($dest, $fileType))
			{
				@unlink($dest);
				@unlink($dest . '.tmp');
			}
		}
	}

	$smcFunc['db_insert']('insert',
		'{db_prefix}quiz_question',
		[
			'question_text' => 'string',
			'id_question_type' => 'int',
			'id_quiz' => 'int',
			'answer_text' => 'string',
			'image' => 'string',
			'updated' => 'int'
		],
		[
			Quiz\Helper::quiz_commonStringFilter($question_text),
			intval($id_question_type),
			intval($id_quiz),
			Quiz\Helper::quiz_commonStringFilter($answer_text),
			Quiz\Helper::quiz_commonImageFileFilter($image),
			time()
		],
		array('id_question')
	);

	$import_question['id_question'] = $smcFunc['db_insert_id']('{db_prefix}quiz_question', 'id_question');

	return $import_question['id_question'];
}

function ImportQuizAnswer($id_question, $answer_text, $is_correct)
{
	global $smcFunc;

	$updated = time();

	$smcFunc['db_insert']('insert',
		'{db_prefix}quiz_answer',
		[
			'id_question' => 'int',
			'answer_text' => 'string',
			'is_correct' => 'int',
			'updated' => 'int'
		],
		[
			intval($id_question),
			Quiz\Helper::quiz_commonStringFilter($answer_text),
			intval($is_correct),
			(int)$updated
		],
		array('id_answer')
	);

	$import_answer['id_answer'] = $smcFunc['db_insert_id']('{db_prefix}quiz_answer', 'id_answer');

	return;
}

function ExportQuizzes($quizIds)
{
	global $smcFunc, $db_prefix, $settings;

	$exportQuizzesResult = $smcFunc['db_query']('', '
		SELECT Q.id_quiz, Q.title, Q.description, Q.play_limit, Q.seconds_per_question,
			Q.show_answers, Q.image, QC.name AS category_name
		FROM {db_prefix}quiz Q
		LEFT JOIN {db_prefix}quiz_category QC
			ON Q.id_category = QC.id_category
		WHERE id_quiz IN ({array_int:quizzes_id})',
		[
			'quizzes_id' => $quizIds,
		]
	);

	// Loop through the results and populate the context accordingly
	$exportQuizzesReturn = array();
	while ($row = $smcFunc['db_fetch_assoc']($exportQuizzesResult))
	{
		$imgDir = $settings['default_theme_dir'] . '/images/quiz_images/Quizzes/' . $row['image'];
		if (!is_dir($imgDir) && file_exists($imgDir))
			$row['image_data'] = base64_encode(file_get_contents($imgDir));
		else
			$row['image_data'] = '';
		$exportQuizzesReturn[] = $row;
	}

	// Free the database
	$smcFunc['db_free_result']($exportQuizzesResult);

	return $exportQuizzesReturn;
}

function ExportQuizQuestions($id_quiz)
{
	global $smcFunc, $settings;

// @TODO query
	$exportQuizQuestionResult = $smcFunc['db_query']('', '
		SELECT id_question, question_text, id_question_type,
			answer_text, image
		FROM {db_prefix}quiz_question
		WHERE id_quiz = {int:id_quiz}',
		[
			'id_quiz' => (int)$id_quiz
		]
	);

	// Loop through the results and populate the context accordingly
	$exportQuizQuestionsReturn = array();
	while ($row = $smcFunc['db_fetch_assoc']($exportQuizQuestionResult))
	{
		$imgDir = $settings['default_theme_dir'] . '/images/quiz_images/Questions/' . $row['image'];
		if (!is_dir($imgDir) && file_exists($imgDir))
			$row['image_data'] = base64_encode(file_get_contents($imgDir));
		else
			$row['image_data'] = '';
		$exportQuizQuestionsReturn[] = $row;
	}

	// Free the database
	$smcFunc['db_free_result']($exportQuizQuestionResult);

	return $exportQuizQuestionsReturn;
}

function ExportQuizAnswers($id_question)
{
	global $smcFunc;

// @TODO query
	$exportQuestionAnswersResult = $smcFunc['db_query']('', '
		SELECT		answer_text,
					is_correct
		FROM		{db_prefix}quiz_answer
		WHERE		id_question = {int:id_question}',
		[
			'id_question' => (int)$id_question
		]
	);

	// Loop through the results and populate the context accordingly
	$exportQuestionAnswersReturn = array();
	while ($row = $smcFunc['db_fetch_assoc']($exportQuestionAnswersResult))
		$exportQuestionAnswersReturn[] = $row;

	// Free the database
	$smcFunc['db_free_result']($exportQuestionAnswersResult);

	return $exportQuestionAnswersReturn;
}

function ResetQuizTopScores()
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		UPDATE		{db_prefix}quiz
		SET			quiz_plays = 0,
					question_plays = 0,
					total_correct = 0,
					top_user_id = 0,
					top_correct = 0,
					top_time = 0'
	);
}

function ResetQuizResults()
{
	global $smcFunc;

	if (allowedTo('admin_forum')) {
// @TODO permissions?
		$smcFunc['db_query']('', '
			TRUNCATE TABLE {db_prefix}quiz_result'
		);
		$smcFunc['db_query']('', '
			TRUNCATE TABLE {db_prefix}quiz_session'
		);
	}
}

function DeleteInfoBoardEntries($date)
{
	global $smcFunc;

	if (allowedTo('admin_forum')) {
// @TODO permissions?
		$smcFunc['db_query']('', '
			DELETE
			FROM 		{db_prefix}quiz_infoboard
			WHERE		entry_date < {int:date}',
			[
				'date' => (int)$date
			]
		);
	}
}

function CompleteQuizSessions($date)
{
	global $context, $smcFunc;

	// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT		id_quiz_session,
					question_count,
					timeouts,
					correct,
					incorrect,
					id_quiz,
					id_user,
					total_seconds
		FROM		{db_prefix}quiz_session
		WHERE		last_question_start < {int:date}',
		[
			'date' => (int)$date
		]

	);

	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		list($result_date, $quizResultData) = array(time(), array('id_quiz_result' => 0, 'player_limit' => 0));
		$dataResult = $smcFunc['db_query']('', '
			SELECT 		id_quiz_result,	id_quiz, id_user, player_limit
			FROM 		{db_prefix}quiz_result
			WHERE 		id_quiz = {int:id_quiz} AND id_user = {int:id_user}',
			[
				'id_quiz' => (int)$row['id_quiz'], 'id_user' => (int)$row['id_user']
			]
		);

		while ($dataRow = $smcFunc['db_fetch_assoc']($dataResult)) {
			$quizResultData = [
				'id_quiz_result' => $dataRow['id_quiz_result'],
				'player_limit' => $dataRow['player_limit'],
			];
		}

		// Free the database
		$smcFunc['db_free_result']($dataResult);

		if (!empty($quizResultData['id_quiz_result'])) {
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}quiz_result
				WHERE id_quiz_result = {int:id_result}',
				[
					'id_result' => (int)$quizResultData['id_quiz_result'],
				]
			);
		}
		// @TODO query+performance?
		$smcFunc['db_insert']('insert',
			'{db_prefix}quiz_result',
			[
				'id_quiz' => 'int',
				'id_user' => 'int',
				'result_date' => 'int',
				'questions' => 'int',
				'correct' => 'int',
				'incorrect' => 'int',
				'timeouts' => 'int',
				'total_seconds' => 'int',
				'auto_completed' => 'int',
				'player_limit' => 'int'
			],
			[
				(int)$row['id_quiz'],
				(int)$row['id_user'],
				time(),
				(int)$row['question_count'],
				(int)$row['correct'],
				(int)$row['incorrect'],
				(int)$row['timeouts'],
				(int)$row['total_seconds'],
				1,
				(int)$quizResultData['player_limit']
			],
			['id_quiz_result']
		);

// @TODO query
		$smcFunc['db_query']('', '
			DELETE
			FROM		{db_prefix}quiz_session
			WHERE		id_quiz_session = {string:id_quiz_session}',
			[
				'id_quiz_session' => $row['id_quiz_session']
			]
		);
	}

	$rows = $smcFunc['db_num_rows']($result);

	// Free the database
	$smcFunc['db_free_result']($result);

	return $rows;
}

function FindOrphanedAnswersData()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT  	id_answer,
					id_question,
					answer_text,
					updated
		FROM   		{db_prefix}quiz_answer
		WHERE  		id_question NOT IN
		(
			SELECT 		id_question
			FROM 		{db_prefix}quiz_question
		)'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['findOrphanedAnswers'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['findOrphanedAnswers'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function FindOrphanedQuestionsData()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		id_question,
					id_quiz,
					question_text,
					updated
		FROM 		{db_prefix}quiz_question
		WHERE 		id_quiz NOT IN (
			SELECT 		id_quiz
			FROM 		{db_prefix}quiz
		)'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['findOrphanedQuestions'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['findOrphanedQuestions'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function FindOrphanedQuizResultsData()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		id_quiz_result,
					id_quiz,
					id_user,
					result_date,
					player_limit
		FROM 		{db_prefix}quiz_result
		WHERE 		id_quiz NOT IN (
			SELECT 		id_quiz
			FROM 		{db_prefix}quiz
		)
		OR			id_user NOT IN (
			SELECT		id_member
			FROM		{db_prefix}members
		)'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['findOrphanedQuizResults'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['findOrphanedQuizResults'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function FindOrphanedCategoriesData()
{
	global $context, $smcFunc;

// @TODO query
	$result = $smcFunc['db_query']('', '
		SELECT 		id_category,
					id_parent,
					name,
					updated
		FROM 		{db_prefix}quiz_category
		WHERE 		id_parent NOT IN (
			SELECT 		id_category
			FROM 		{db_prefix}quiz_category
		)
		AND 		id_parent != 0'
	);

	// Loop through the results and populate the context accordingly
	$context['SMFQuiz']['findOrphanedCategories'] = Array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['SMFQuiz']['findOrphanedCategories'][] = $row;

	// Free the database
	$smcFunc['db_free_result']($result);
}

function DeleteOrphanedQuestionsData()
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		DELETE
		FROM 		{db_prefix}quiz_question
		WHERE 		id_quiz NOT IN (
			SELECT 		id_quiz
			FROM 		{db_prefix}quiz
		)'
	);
}

function DeleteOrphanedAnswersData()
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		DELETE
		FROM   		{db_prefix}quiz_answer
		WHERE  		id_question NOT IN
		(
			SELECT 		id_question
			FROM 		{db_prefix}quiz_question
		)'
	);
}

function DeleteOrphanedQuizResultsData()
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		DELETE
		FROM 		{db_prefix}quiz_result
		WHERE 		id_quiz NOT IN (
			SELECT 		id_quiz
			FROM 		{db_prefix}quiz
		)
		OR			id_user NOT IN (
			SELECT		id_member
			FROM		{db_prefix}members
		)'
	);
}

function DeleteOrphanedCategoriesData()
{
	global $smcFunc;

	// We can't delete like the other ones, as the query would reference itself and it can't delete in
	// that scenario
// @TODO query
	$findOrphanedCategoriesResult = $smcFunc['db_query']('', '
		SELECT 		id_category
		FROM 		{db_prefix}quiz_category
		WHERE 		id_parent NOT IN (
			SELECT 		id_category
			FROM 		{db_prefix}quiz_category
		)
		AND 		id_parent != 0'
	);

	// Loop through the session results
// @TODO query
	while ($row = $smcFunc['db_fetch_assoc']($findOrphanedCategoriesResult))
		// Delete each category
		$smcFunc['db_query']('', '
			DELETE
			FROM 		{db_prefix}quiz_category
			WHERE 		id_category = {int:id_category}',
			[
				'id_category' => (int)$row['id_category']
			]
		);

	// Free the database
	$smcFunc['db_free_result']($findOrphanedCategoriesResult);
}

function CanUserPlayQuizLeagueData($id_quiz_league, $id_user)
{
	global $smcFunc, $context;

// @TODO query
	$canUserPlayQuizLeagueResult = $smcFunc['db_query']('', '
		SELECT 		QLR.correct,
					QLR.result_date,
					QLR.seconds
		FROM 		{db_prefix}quiz_league_result QLR
		INNER JOIN 	{db_prefix}quiz_league QL
		ON 			QLR.id_quiz_league = QL.id_quiz_league
		WHERE 		QLR.id_quiz_league = {int:id_quiz_league}
		AND 		QLR.id_user = {int:id_user}
		AND 		QLR.round = QL.current_round
		LIMIT		0, 1',
		[
			'id_quiz_league' => (int)$id_quiz_league,
			'id_user' => (int)$id_user,
		]
	);

	// Loop through leagues that are enabled
	while ($row = $smcFunc['db_fetch_assoc']($canUserPlayQuizLeagueResult))
		$context['SMFQuiz']['CanPlayQuizLeague'][] = $row;

	$smcFunc['db_free_result']($canUserPlayQuizLeagueResult);
}

/*
Removes any orphaned quiz disputes. This can happen if the quiz or user is no longer
part of the forum. So this function just removes these entries.
*/
function CleanDisputes()
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		DELETE		QD.*
		FROM		{db_prefix}quiz_dispute QD
		LEFT JOIN 	{db_prefix}quiz Q
		ON 			QD.id_quiz = Q.id_quiz
		LEFT JOIN 	{db_prefix}members M
		ON 			QD.id_user = M.id_member
		LEFT JOIN 	{db_prefix}quiz_question QQ
		ON 			QD.id_quiz_question = QQ.id_question
		WHERE 		Q.id_quiz IS NULL
		OR 			M.id_member IS NULL
		OR 			QQ.id_question IS NULL'
	);
}

/*
Removes any orphaned quiz answers. This could happen if a quiz question was removed,
although the code should be cleaning up this scenario anyway
*/
function CleanAnswers()
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		DELETE		QA.*
		FROM 		{db_prefix}quiz_answer QA
		LEFT JOIN 	{db_prefix}quiz_question QQ
		ON 			QA.id_question = QQ.id_question
		WHERE 		QQ.id_question IS NULL'
	);
}

/*
Removes any orphaned quiz results. This could happen if a quiz or member was removed
*/
function CleanResults()
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		DELETE		QR.*
		FROM 		{db_prefix}quiz_result QR
		LEFT JOIN 	{db_prefix}quiz Q
		ON 			QR.id_quiz = Q.id_quiz
		LEFT JOIN 	{db_prefix}members M
		ON 			QR.id_user = M.id_member
		WHERE 		Q.id_quiz IS NULL
		OR 			M.id_member IS NULL'
	);
}

/*
Removes any orphaned quiz questions. This could happen if a quiz was removed, but should
be picked up in the code
*/
function CleanQuestions()
{
	global $smcFunc;

// @TODO query
	$smcFunc['db_query']('', '
		DELETE		QQ.*
		FROM 		{db_prefix}quiz_question QQ
		LEFT JOIN 	{db_prefix}quiz Q
		ON 			QQ.id_quiz = Q.id_quiz
		WHERE 		Q.id_quiz IS NULL'
	);
}

function CleanQuizMembers()
{
	global $smcFunc;

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
			[
				'memIDs' => $nonMembers,
			]
		);
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}quiz_session
			WHERE id_user IN({array_int:memIDs})',
			[
				'memIDs' => $nonMembers,
			]
		);
	}
}

?>