<?php

namespace Quiz\Tasks;

class Scheduled
{
	/**
	 * Scheduled::maintenance()
	 *
	 *
	 * @return bool
	 */
	public function maintenance()
	{
		global $smcFunc, $modSettings, $sourcedir;

		require_once($sourcedir . '/Quiz/Admin.php');

		// Get leagues dates to check for update required
		$getLeagueDatesResult = $smcFunc['db_query']('', '
			SELECT
				QL.id_quiz_league,
				QL.updated,
				QL.day_interval,
				QL.current_round,
				QL.total_rounds,
				QL.title,
				COUNT(QLR.id_quiz_league_result) AS plays
			FROM {db_prefix}quiz_league QL
			LEFT JOIN {db_prefix}quiz_league_result QLR
				ON QL.id_quiz_league = QLR.id_quiz_league
			WHERE state = 1
			GROUP BY
				QL.id_quiz_league,
				QL.updated,
				QL.day_interval,
				QL.current_round,
				QL.total_rounds,
				QL.title,
				QLR.id_quiz_league_result',
			array()
		);

		// Loop through leagues that are enabled
		while ($row = $smcFunc['db_fetch_assoc']($getLeagueDatesResult))
		{
			// Check if the league needs the round updating
			$nextUpdate = strtotime("+" . $row['day_interval'] . " days", $row['updated']);

			// If an update is required
			if ($nextUpdate < time())
			{
				// Retrieve last weeks results
				$lastWeekResultsResult = $smcFunc['db_query']('', '
					SELECT
						QLR.id_user,
						QLR.correct,
						QLR.incorrect,
						QLR.timeouts,
						QLR.seconds,
						QLR.points
					FROM {db_prefix}quiz_league_result QLR
					WHERE QLR.id_quiz_league = {int:id_quiz_league}
						AND QLR.round = {int:lastWeekRound}',
					array(
						'id_quiz_league' => $row['id_quiz_league'],
						'lastWeekRound' => $row['current_round'],
					)
				);

				// If there were some results from last week
				if ($smcFunc['db_num_rows']($lastWeekResultsResult) > 0)
				{
					while ($lastWeekResultsRow = $smcFunc['db_fetch_assoc']($lastWeekResultsResult))
					{
						// See if this user has table entry for this week (note that table entries for the forthcoming week are
						// added at the end of each update)
						$userTableEntryResult = $smcFunc['db_query']('', '
							SELECT
								QLT.id_user,
								QLT.id_quiz_league_table
							FROM {db_prefix}quiz_league_table QLT
							WHERE QLT.id_quiz_league = {int:id_quiz_league}
								AND QLT.id_user = {int:id_user}
								AND QLT.round = {int:lastWeekRound}
							LIMIT 0, 1',
							array(
								'id_quiz_league' => $row['id_quiz_league'],
								'id_user' => $lastWeekResultsRow['id_user'],
								'lastWeekRound' => $row['current_round']
							)
						);

						// If the user does have a table entry for this week
						if ($smcFunc['db_num_rows']($userTableEntryResult) > 0)
						{
							// Update the table entry to include the quiz results from this week
							while ($userTableEntryRow = $smcFunc['db_fetch_assoc']($userTableEntryResult))
							{
								$smcFunc['db_query']('', '
									UPDATE {db_prefix}quiz_league_table
									SET
										correct = correct + {int:correct},
										incorrect = incorrect + {int:incorrect},
										timeouts = timeouts + {int:timeouts},
										points = points + {int:points},
										seconds = seconds + {int:seconds},
										plays = plays + 1
									WHERE round = {int:round}
										AND id_quiz_league_table = {int:id_quiz_league_table}
										AND id_user = {int:id_user}',
									array(
										'correct' => $lastWeekResultsRow['correct'],
										'incorrect' => $lastWeekResultsRow['incorrect'],
										'timeouts' => $lastWeekResultsRow['timeouts'],
										'points' => $lastWeekResultsRow['points'],
										'seconds' => $lastWeekResultsRow['seconds'],
										'round' => $row['current_round'],
										'id_quiz_league_table' => $userTableEntryRow['id_quiz_league_table'],
										'id_user' => $lastWeekResultsRow['id_user'],
									)
								);
							}
						}
						else
						{
							// Otherwise a table entry doesn't exist for this week for this user. This means it is the first time they have
							// played, so simply add a new table entry with the result data
							$smcFunc['db_insert']('insert',
								'{db_prefix}quiz_league_table',
								array(
									'current_position' => 'int',
									'id_user' => 'int',
									'last_position' => 'int',
									'plays' => 'int',
									'correct' => 'int',
									'incorrect' => 'int',
									'timeouts' => 'int',
									'points' => 'int',
									'id_quiz_league' => 'int',
									'round' => 'int',
									'seconds' => 'int',
								),
								array(
									0,
									$lastWeekResultsRow['id_user'],
									0,
									1,
									$lastWeekResultsRow['correct'],
									$lastWeekResultsRow['incorrect'],
									$lastWeekResultsRow['timeouts'],
									$lastWeekResultsRow['points'],
									$row['id_quiz_league'],
									$row['current_round'],
									$lastWeekResultsRow['seconds']
								),
								array('id_quiz_league_table')
							);
						}
					}
					$smcFunc['db_free_result']($lastWeekResultsResult);
				}

				// Now we need to calculate positions and movement. If this is the first week of the league we don't have a previous week to do some
				// calculations on, so we just need to return the entries for the table normally
				if ($row['current_round'] < 2 || $row['plays'] < 2)
				{
					$quizLeaguePosResult = $smcFunc['db_query']('', '
						SELECT
							QLT.id_quiz_league_table,
							QLT.current_position as last_position,
							QLT.id_user
						FROM {db_prefix}quiz_league_table QLT
						WHERE QLT.round = {int:current_round}
							AND QLT.id_quiz_league = {int:id_quiz_league}
						ORDER BY
							QLT.points DESC,
							QLT.seconds ASC,
							QLT.plays ASC',
						array(
							'current_round' => $row['current_round'],
							'id_quiz_league' => $row['id_quiz_league'],
						)
					);
				}
				else
				{
					// Otherwise this league has already got some result so join on the previous round
					$quizLeaguePosResult = $smcFunc['db_query']('', '
						SELECT
							QLT1.id_quiz_league_table,
							QLT1.id_user,
							IFNULL(QLT2.current_position,0) AS last_position
						FROM {db_prefix}quiz_league_table QLT1
						LEFT JOIN {db_prefix}quiz_league_table QLT2
							ON QLT1.round = QLT2.round+1
							AND QLT1.id_user = QLT2.id_user
						WHERE QLT1.id_quiz_league = {int:id_quiz_league}
							AND QLT1.round = {int:current_round}
						ORDER BY
							QLT1.Points DESC,
							QLT1.seconds ASC,
							QLT1.plays ASC',
						array(
							'current_round' => $row['current_round'],
							'id_quiz_league' => $row['id_quiz_league'],
						)
					);
				}

				// Now we have our ordered results, go through them an update the positions
				$position = 1;
				$id_leader = 0;
				while ($quizLeaguePosRow = $smcFunc['db_fetch_assoc']($quizLeaguePosResult))
				{
					// If this is the top position, save this at the league level
					if ($position == 1)
					{
						// Store the leader id, this may be required later
						$id_leader = $quizLeaguePosRow['id_user'];

						$smcFunc['db_query']('', '
							UPDATE {db_prefix}quiz_league
							SET id_leader = {int:id_leader}
							WHERE id_quiz_league = {int:id_quiz_league}',
							array(
								'id_leader' => $id_leader,
								'id_quiz_league' => $row['id_quiz_league'],
							)
						);
					}

					// Update position and last position
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}quiz_league_table
						SET
							current_position = {int:position},
							last_position = {int:last_position}
						WHERE id_quiz_league_table = {int:id_quiz_league_table}',
						array(
							'position' => $position,
							'last_position' => $quizLeaguePosRow['last_position'],
							'id_quiz_league_table' => $quizLeaguePosRow['id_quiz_league_table'],
						)
					);

					// Send PM to user if enabled
					if ($modSettings['SMFQuiz_SendPMOnLeagueRoundUpdate'])
					{
						require_once($sourcedir . '/Subs-Post.php');
						require_once($sourcedir . '/Quiz/Helper.php');
						$usersPrefs = Quiz\Helper::quiz_usersAcknowledge('quiz_pm_alert');

						if (in_array($quizLeaguePosRow['id_user'], $userPrefs)) {
							$pmto = array(
								'to' => array(),
								'bcc' => array($quizLeaguePosRow['id_user'])
							);

							$subject = ParseLeagueMessage($modSettings['SMFQuiz_PMLeagueRoundUpdateSubject'], $row['title'], $quizLeaguePosRow['last_position'], $position, ($position-$quizLeaguePosRow['last_position']), $row['id_quiz_league']);
							$message = ParseLeagueMessage($modSettings['SMFQuiz_PMLeagueRoundUpdateMsg'], $row['title'], $quizLeaguePosRow['last_position'], $position, ($position-$quizLeaguePosRow['last_position']), $row['id_quiz_league']);

							$pmfrom = array(
								'id' => $modSettings['SMFQuiz_ImportQuizzesAsUserId'],
								'name' => 'Quiz Notifier',
								'username' => 'Quiz Notifier'
							);

							// Send message
							sendpm($pmto, $subject, Quiz\Helper::quiz_pmFilter($message), 0, $pmfrom);
						}
					}
					$position++;
				}
				$smcFunc['db_free_result']($quizLeaguePosResult);

				// Delete any sessions related to this league
				$smcFunc['db_query']('', '
					DELETE
					FROM {db_prefix}quiz_session
					WHERE id_quiz_league = {int:id_quiz_league}',
					array(
						'id_quiz_league' => $row['id_quiz_league'],
					)
				);

				// Check whether this should be the final round
				if ($row['current_round'] > $row['total_rounds'] - 1)
				{
					// Finish up the league and set winner
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}quiz_league QL
						SET
							current_round = total_rounds,
							state = 2,
							id_leader = {int:id_leader}
						WHERE id_quiz_league = {int:id_quiz_league}',
						array(
							'id_quiz_league' => $row['id_quiz_league'],
							'id_leader' => $id_leader,
						)
					);
				}
				else
				{
					// Finally, if this is not the end of the league insert a new entry for the next week for all current contestants
					$smcFunc['db_query']('', '
						INSERT INTO {db_prefix}quiz_league_table
						(
							current_position,
							id_user,
							last_position,
							plays,
							correct,
							incorrect,
							timeouts,
							points,
							id_quiz_league,
							round,
							seconds
						)
						SELECT
							current_position,
							id_user,
							last_position,
							plays,
							correct,
							incorrect,
							timeouts,
							points,
							id_quiz_league,
							{int:round}+1,
							seconds
						FROM {db_prefix}quiz_league_table
						WHERE round = {int:round}
							AND id_quiz_league = {int:id_quiz_league}',
						array(
							'round' => $row['current_round'],
							'id_quiz_league' => $row['id_quiz_league'],
						)
					);

					// Update the league round
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}quiz_league QL
						SET
							current_round = current_round + 1,
							updated = {int:newUpdateTime}
						WHERE id_quiz_league = {int:id_quiz_league}',
						array(
							'id_quiz_league' => $row['id_quiz_league'],
							'newUpdateTime' => $nextUpdate
						)
					);
				}
			}
		}

		// Free the database
		$smcFunc['db_free_result']($getLeagueDatesResult);

		// Remove any rogue temp Quiz files
		$tempPaths = glob($sourcedir . '/Quiz/Temp/*');
		foreach ($tempPaths as $tempPath) {
			if (is_dir($tempPath)) {
				quizRmdir($tempPath);
			}
			elseif (!in_array(basename($tempPath), array('index.php', '.htaccess'))) {
				@unlink($tempPath);
			}
		}

		$this->quiz_clean();

		return true;
	}

	public function quiz_clean()
	{
		global $modSettings, $sourcedir;

		// If the auto clean flag is set, do the cleanup now
		if ($modSettings['SMFQuiz_AutoClean'] == 'on')
		{
			require_once($sourcedir . '/Quiz/Db.php');

			// Default to clean 7 days worth of InfoBoard entries
			$date = mktime(0, 0, 0, date("m")  , date("d") - 7, date("Y"));
			$rows = DeleteInfoBoardEntries($date);

			// Clean up any disputes
			CleanDisputes();

			// Clean up any orphaned answers
			CleanAnswers();

			// Clean up any orhpaned quiz results
			CleanResults();

			// Clean up any orhpaned quiz questions
			CleanQuestions();

			// Auto complete quiz sessions
			CompleteQuizSessions($date);

			// Clean up rogue Quiz user IDs
			CleanQuizMembers();
		}
	}
}