<?php
/*
SMF QUIZ
Hooks by vbgamer45 https://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function smfquiz_actions(&$actionArray)
{
  global $sourcedir, $modSettings;

    $actionArray += array('SMFQuiz' => array('SMFQuiz.php', 'SMFQuiz'));
    $actionArray += array('SMFQuizAnswers' => array('SMFQuizAnswers.php', 'UpdateSession'));
    $actionArray += array('SMFQuizStart' => array('SMFQuizStart.php', 'loadQuiz'));
    $actionArray += array('SMFQuizEnd' => array('SMFQuizEnd.php', 'endQuiz'));
    $actionArray += array('SMFQuizQuestions' => array('SMFQuizQuestions.php', 'quizQuestions'));
    $actionArray += array('SMFQuizDispute' => array('SMFQuizDispute.php', 'quizDispute'));
    $actionArray += array('SMFQuizAjax' => array('SMFQuizAjax.php', 'quizImageUpload'));
    $actionArray += array('SMFQuizExport' => array('SMFQuizExport.php', 'quizExport'));
}

// Permissions
function smfquiz_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;
	
   $permissionList['membergroup'] += array(
       'quiz_view' => array(false, 'quiz', 'quiz'),
       'quiz_play' => array(false, 'quiz', 'quiz'),
       'quiz_submit' => array(false, 'quiz', 'quiz'),
       'quiz_admin' => array(false, 'quiz', 'quiz'),
    );

    $context['non_guest_permissions'][] = 'quiz_play';
    $context['non_guest_permissions'][] = 'quiz_submit';
    $context['non_guest_permissions'][] = 'quiz_admin';

}

function smfquiz_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;


    smfquiz_array_insert($admin_areas, 'layout',
	        array(
	            'quiz' => array(
        			'title' => 'Quiz',
        			'permission' => array('quiz_admin'),
        			'areas' => array(
                        'quiz' => array(
                            'label' => 'Quiz',
                            'file' => 'SMFQuizAdmin.php',
                            'function' => 'SMFQuizAdmin',
                            'icon' => 'quiz.png',
                            'permission' => array('quiz_admin'),
                            'subsections' => array(
                                // @TODO localization
                                'adminCenter' => array('Admin Center'),
                                'settings' => array('Settings'),
                                'quizes' => array('Quizzes'),
                                'quizleagues' => array('Quiz Leagues'),
                                'categories' => array('Categories'),
                                'questions' => array('Questions'),
                                'results' => array('Results'),
                                'disputes' => array('Disputes'),
                                'quizimporter' => array('Quiz Importer'),
                                'maintenance' => array('Maintenance'),
                            ),
                        ),

                        ),
                        
        		),
                
	        )
        );
		
        


}

function smfquiz_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $context, $modSettings, $scripturl;


	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options
	
	if (!isset($txt['SMFQuiz']))
        $txt['SMFQuiz'] = 'Quiz';
		
	#Where the button will be shown on the menu
	$button_insert = 'mlist';
	
	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist

    smfquiz_array_insert($menu_buttons, $button_insert,
		     array(
                    'SMFQuiz' => array(
                    				'title' => $txt['SMFQuiz'],
                    				'href' => $scripturl . '?action=SMFQuiz',
                    				'icon' => 'quiz.png',
                                    'show' => allowedTo('quiz_view') && !empty($modSettings['SMFQuiz_enabled']),
            		                'sub_buttons' => array(),
				    
			    )	
		    )
	    ,$button_pos);
        
 


}

function smfquiz_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);
	
	// Key not found -> insert as last
	if ($position === false)
	{
		$input = array_merge($input, $insert);
		return;
	}
	
	if ($where === 'after')
		$position += 1;

	// Insert as first
	if ($position === 0)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(
			array_slice($input, 0, $position),
			$insert,
			array_slice($input, $position)
		);
}


?>