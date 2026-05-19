<?php 
$observers = [
    [
        'eventname' => '\core\event\course_viewed',
        'callback' => 'local_coursecompleted\observer::course_viewed',
    ],
    [
        'eventname' => 'core\event\course_module_completion_updated',
        'callback' => 'local_coursecompleted\observer::course_module_completion_updated',
    ],
];