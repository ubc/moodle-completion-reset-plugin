<?php

/**
* @package   tool_completion_reset
* @copyright 2019, Pan Luo <pan.luo@ubc.ca>
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2019073005;
$plugin->requires = 2018120300; # moodle 3.6
$plugin->cron = 0;
$plugin->component = 'tool_completionreset';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = 'v0.1';

$plugin->dependencies = [
];