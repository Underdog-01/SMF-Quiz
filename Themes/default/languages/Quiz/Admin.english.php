<?php

// @TODO not needed
if (!defined('SMF'))
	die('Hacking attempt...');

// @TODO to remove
// Load the common language file
loadLanguage('SMFQuizCommon');

$txt['SMFQuiz'] = 'SMF Quiz';
$txt['SMFQuizMod'] = 'The SMF Quiz Mod';
$txt['SMFQuizModDescription'] = 'This page allows you to configure the SMF Quiz modification';
$txt['AlertOnePackage'] = 'You must select at least one Quiz to package';
	
// Text for any titles that appear in the admin section
$txt['SMFQuizAdmin_Titles'] = array(
	'Settings' => 'Settings',
	'Quizes' => 'Quizzes',
	'QuizLeagues' => 'Quiz Leagues',
	'Categories' => 'Categories',
	'Questions' => 'Questions',
	'QuizImporter' => 'Quiz Importer',
	'Maintenance' => 'Maintenance',
	'AdminCenter' => 'Administration Center',
	'UploadQuiz' => 'Upload Quiz',
	'Results' => 'Results',
	'Disputes' => 'Disputes',
);

// Blurb text for titles
$txt['SMFQuizAdmin_Title_Blurbs'] = array(
	'Settings' => 'Here you will find the general settings for the quiz that allow you to customise it how you like',
	'Quizes' => 'Create, modify and delete quizzes. This is where you manage all of the quizzes installed',
	'QuizLeagues' => 'You may create a number of quiz leagues on your site. This is where you manage your created quiz leagues',
	'Categories' => 'Each quiz can be placed in categories, this is where you manage those categories',
	'Questions' => 'These are the quiz questions, all of them. If you want to manage the questions for a specific quiz you might be better off viewing them from the Quiz section',
	'QuizImporter' => 'There are hundreds of pre-made quizzes available for you to import from the SMF Modding site, you may import these directly into your site here. <b>Please be part of the community and share any quizzes you have made</b>',
	'Maintenance' => 'The quiz mod may need a little tender loving care every now and again. You can perform some of the maintenance functions to give love to your mod here',
	'Results' => 'You can view every quiz result ever submitted from here',
	'Disputes' => 'Some of your members may dispute answers to questions. You can view these from here',
);


// Language text located in the results page
$txt['SMFQuizAdmin_QuizResults_Page'] = array(
	'DeleteQuizResult' => 'Delete Quiz Result',
);

// Language text located in the admin center page
$txt['SMFQuizAdmin_AdminCenter_Page'] = array(
	'LiveFromSMFModding' => 'Live from SMF Modding...',
	'VersionInformation' => 'Version Information',
	'ModVersion' => 'Mod Version',
	'CurrentSMFQuizVersion' => 'Current SMF Quiz Version',
	'TotalQuizes' => 'Total Quizzes',
	'QuizesNotEnabled' => 'Quizzes not Enabled',
	'QuizesWaitingReview' => 'Quizzes waiting Review',
	'TotalResults' => 'Total Results',
	'OutstandingDisputes' => 'Outstanding Disputes',
	'Statistics' => 'Statistics',
);

// Language text located in the disputes page
$txt['SMFQuizAdmin_QuizDisputes_Page'] = array(
	'DeleteQuizDispute' => 'Delete Quiz Dispute',
	'RespondToDispute' => 'Respond to Dispute',
);

// Language text located in the quizzes page
$txt['SMFQuizAdmin_Quizes_Page'] = array(
	'PackageName' => 'Package Name',
	'PackageDescription' => 'Package Description',
	'PackageAuthor' => 'Package Author',
	'PackageSiteAddress' => 'Package Site Address',
	'PackageQuiz' => 'Package Quiz',
	'NewQuiz' => 'New Quiz',
	'DeleteQuiz' => 'Delete Quiz',
	'QuizUploadedSuccessfully' => 'Quiz uploaded successfully',
	'QuizImportedSuccessfully' => 'Quiz imported successfully',
	'QuizUploadError' => 'An error occurred on the SMF Modding site while trying to import this uploaded quiz. Please contact the administrator of http://www.smfmodding.com',
	'QuizUploadExists' => 'Quiz already exists on SMF Modding site',
	'UploadQuiz' => 'Upload this Quiz to SMFModding',
	'UploadEnabled' => 'Enabled (click to disable)',
	'UploadDisabled' => 'Disabled (click to enable)',
	'WaitingReview' => 'Waiting review',
	'PreviewQuiz' => 'Preview this Quiz',
);

// Language text located in the new quiz page
$txt['SMFQuizAdmin_NewQuiz_Page'] = array(
	'NewQuiz' => 'New Quiz',
	'SaveQuiz' => 'Save Quiz',
	'SaveQuizAndAddQuestions' => 'Save Quiz and Add Questions',
	'Done' => 'Done',
	'BestNotToSelect' => 'best not to select this until after all questions/answers are added',
);

// Language text located in the edit quiz page
$txt['SMFQuizAdmin_EditQuiz_Page'] = array(
	'EditQuiz' => 'Edit Quiz',
	'UpdateQuiz' => 'Update Quiz',
	'QuizQuestions' => 'Quiz Questions',
	'UpdateQuizAndAddQuestions' => 'Update Quiz and Add Questions',
	'Done' => 'Done',
);

// Language text located in the edit quiz league page
$txt['SMFQuizAdmin_EditQuizLeague_Page'] = array(
	'EditQuizLeague' => 'Edit Quiz League',
	'UpdateQuizLeague' => 'Update Quiz League',
	'Done' => 'Done',
);

// Language text located in the quiz leagues page
$txt['SMFQuizAdmin_QuizLeagues_Page'] = array(
	'NewQuizLeague' => 'New Quiz League',
	'DeleteQuizLeague' => 'Delete Quiz League',
	'NoQuizLeagues' => 'There are no Quiz Leagues defined',
	'LeagueEnabled' => 'League is Enabled',
	'LeagueDisabled' => 'League is Disabled',
	'LeagueCompleted' => 'League has Completed',
);

// Language text located in the new quiz league page
$txt['SMFQuizAdmin_NewQuizLeague_Page'] = array(
	'NewQuizLeague' => 'New Quiz League',
	'SaveQuizLeague' => 'Save Quiz League',
	'Done' => 'Done',
);

// Language text located in the categories page
$txt['SMFQuizAdmin_Categories_Page'] = array(
	'NewCategory' => 'New Category',
	'DeleteCategory' => 'Delete Category',
	'ParentCategory' => 'Parent Category',
);


// Language text located in the edit category page
$txt['SMFQuizAdmin_EditCategory_Page'] = array(
	'EditCategory' => 'Edit Category',
	'UpdateCategory' => 'Update Category',
	'Done' => 'Done',
);

// Language text located in the new category page
$txt['SMFQuizAdmin_NewCategory_Page'] = array(
	'NewCategory' => 'New Category',
	'SaveCategory' => 'Save Category',
	'Done' => 'Done',
);

// Language text located in the questions page
$txt['SMFQuizAdmin_Questions_Page'] = array(
	'NewQuestion' => 'New Question',
	'DeleteQuestion' => 'Delete Question',
	'NoQuestions' => 'There are no questions defined',
);

// Language text located in the edit question page
$txt['SMFQuizAdmin_EditQuestion_Page'] = array(
	'EditQuestion' => 'Edit Question',
	'UpdateQuestion' => 'Update Question',
	'UpdateQuestionAndAddMore' => 'Update Question and Add More',
	'Done' => 'Done',
);

// Language text located in the new question page
$txt['SMFQuizAdmin_NewQuestion_Page'] = array(
	'NewQuestion' => 'New Question',
	'SaveQuestion' => 'Save Question',
	'SaveAndAddMore' => 'Save and Add More',
	'Done' => 'Done',
);

// Language text located in the quiz importer page
$txt['SMFQuizAdmin_QuizImporter_Page'] = array(
	'AvailableQuizesToImport' => 'Available Quizzes to Import',
	'available' => 'available from filtered results',
	'Legend' => 'Legend',
	'Unavailable' => 'Unavailable',
	'Installed' => 'Installed',
	'NotInstalled' => 'Not Installed',
	'Top10QuizImports' => 'Top 10 Quiz Imports',
	'Last10QuizImports' => 'Last 10 Quiz Imports'
);

// Language text located in the maintenance page
$txt['SMFQuizAdmin_Maintenance_Page'] = array(
	'Maintenance' => 'Maintenance',
	'ResetAllQuizData' => 'Are you sure you wish to reset ALL quiz data?',
	'OrphanedQuestions' => 'Orphaned Questions',
	'QuestionId' => 'Question Id',
	'QuizId' => 'Quiz Id',
	'NoOrphansFound' => 'No Orphans Found',
	'OrphanedAnswers' => 'Orphaned Answers',
	'AnswerId' => 'Answer Id',
	'OrphanedQuizResults' => 'Orphaned Quiz Results',
	'QuizResultId' => 'Quiz Result Id',
	'UserId' => 'User Id',
	'OrphanedCategories' => 'Orphaned Categories',
	'ParentId' => 'Parent Id',
	'CategoryId' => 'Category Id',
	'ResetQuizResults' => 'Reset Quiz Results',
	'OrphanedData' => 'Orphaned Data',
	'FindOrphanedQuestions' => 'Find Orphaned Questions',
	'FindOrphanedAnswers' => 'Find Orphaned Answers',
	'FindOrphanedQuizResults' => 'Find Orphaned Quiz Results',
	'FindOrphanedCategories' => 'Find Orphaned Categories',
	'CompleteQuizSessions' => 'Complete Quiz Sessions',
	'CleanInformationBoard' => 'Clean Information Board',
);

// Language text located in the quiz export page
$txt['SMFQuizAdmin_QuizExport_Page'] = array(
	'NoNameEntered' => 'NoNameEntered',
	'NoDescriptionEntered' => 'No Description Entered',
	'NoAuthorEntered' => 'No Author Entered',
	'NoSiteEntererd' => 'No Site Entered',
	
);

// Language text located in the settings page
$txt['SMFQuizAdmim_Settings_Page'] = array(
	'GeneralSettings' => 'General Settings',
	'QuizAutoClean' => 'Auto Clean',
	'WelcomeMessage' => 'Welcome Message',
	'ListPageSizes' => 'List Page Sizes',
	'InfoBoardItemsToDisplay' => 'InfoBoard Items to Display',
	'QuizCompletionSettings' => 'Quiz Completion Settings',
	'Score0to19' => 'Score 0-19%',
	'Score20to39' => 'Score 20-39%',
	'Score40to59' => 'Score 40-59%',
	'Score60to79' => 'Score 60-79%',
	'Score80to99' => 'Score 80-99%',
	'Score100' => 'Score 100%',
	'ImportQuizesAsUser' => 'Import Quizzes as User ID',
	'QuizMessagingSettings' => 'Quiz Messaging Settings',
	'SendPMOnBrokenTopScore' => 'Send PM on Broken Top Score',
	'SendPMOnLeagueRoundUpdate' => 'Send PM on Quiz League Round Update',
	'PMBrokenTopScoreMsg' => 'Broken Top Score Message',
	'PMLeagueRoundUpdateMsg' => 'League Round Update Message',
	'SessionTimeLimit' => 'Session Replay Time (minutes)',
	'ShowUserRating' => 'Show User Ratings',
	'PreviewPMSent' => 'The Preview PM has been sent to your message box, please go there to review it',
);

$txt['quiz_mod_summary'] = 'Quiz Mod Summary';
$txt['quiz_mod_statistics'] = 'Quiz Mod Statistics';
$txt['quiz_mod_successful_import'] = 'Congratulations! You have successfully imported the following quizzes:';
$txt['quiz_mod_unsuccessful_import'] = 'Attention! The following quizzes have not been imported:';
$txt['quiz_mod_clean_quiz'] = 'Remove quiz';
$txt['quiz_mod_more_quizzes'] = 'Add quiz';

$txt['quiz_mod_quiz_already_exists'] = 'a quiz with the same title already exists';
$txt['quiz_mod_error_reading_file'] = 'error reading the quiz file. Please verify it is valid XML';
$txt['quiz_mod_unknown_quiz'] = 'The entire quiz package {FILENAME}';
$txt['quiz_mod_failure_reason'] = 'reason';

$txt['quiz_mod_preview_disabled'] = 'Sorry at the moment the preview is disabled, it will be back at a later stage of development';
?>