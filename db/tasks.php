<?php

defined('MOODLE_INTERNAL') || die();

# Runs every 5 mins

$tasks = array(
    array(
        'classname' => 'tool_completionreset\task\completion_reset',
        'blocking' => 0,
        'minute' => '*/5',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
);