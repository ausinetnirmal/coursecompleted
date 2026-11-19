<?php
defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\output\before_http_headers::class,
        'callback' => [\local_coursecompleted\hook_callbacks::class, 'before_http_headers'],
    ],
];