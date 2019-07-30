<?php
defined('MOODLE_INTERNAL') || die;

if ( $hassiteconfig ){

    // Create the new settings page
    // - in a local plugin this is not defined as standard, so normal $settings->methods will throw an error as
    // $settings will be NULL
    $settings = new admin_settingpage( 'tool_completionreset', 'Scheduled Test Completion Reset' );

    // Add a setting field to the settings for this page
    $settings->add( new admin_setting_configtext(

    // This is the reference you will use to your configuration
        'tool_completionreset/course_id',

        // This is the friendly title for the config, which will be displayed
        'Course ID',

        // This is helper text for this config field
        'The ID of the course that the test is in',

        // This is the default value
        0,

        // This is the type of Parameter this config is
        PARAM_INT

    ));

    $settings->add(new admin_setting_configtext(
        'tool_completionreset/item_id',
        'Item ID',
        'This is the ID of the fit_test_payment grade item',
        0,
        PARAM_INT
    ));

    $ADMIN->add('tools', $settings);
}
