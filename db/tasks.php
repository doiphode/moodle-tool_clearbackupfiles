<?php 

defined('MOODLE_INTERNAL') || die();
$tasks = array(
    array(
        'classname' => '\tool_clearbackupfiles\task\cron_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
);
