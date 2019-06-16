<?php

// List of observers.  
$observers = [
    [
        'eventname'   => '\core\event\course_module_deleted',
        'callback'    => 'observer::course_module_deleted',
		'includefile' => '/mod/newmodule/observer.php',
    ],
];