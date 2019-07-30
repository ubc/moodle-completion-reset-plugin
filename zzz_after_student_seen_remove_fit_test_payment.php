<?php

require_once('C:\Web_Application_Configs\moodle_shibboleth_link\config.php');

$conn = sqlsrv_connect($databaselocation, $connectionInfo);
if($conn == false){
     echo "Failed to connect...";
}

//This is a custom page written by Jessica Tranter on January 26 2017
//It is specifically for the scheduler in the fit test course
//Its purpose is to make it so that when a student has been marked as seen for the scheduler appointment, it will take away the grade for their fit test payment, so that they will have to pay to have another fit test

$itemid = 176; //This is the ID of the fit_test_payment grade item
$courseid = 27; //The ID of the Moodle Fit Test Course

//First, load the data of all the scheduler slots in this course (course 27 is the respirator course) including the grade item of the respirator fit test (grade item ID 176)
$query = "SELECT dboscheduler_appointment.id AS scheduler_appointment_ID, dboscheduler_appointment.slotid, dboscheduler_appointment.studentid, dboscheduler_appointment.attended, dboscheduler_appointment.grade, dbograde_items.itemname, dbograde_grades.id AS grade_ID, dbograde_grades.finalgrade, dbograde_items.id AS grade_item_ID, dboscheduler.course
FROM dboscheduler_appointment 
LEFT JOIN dbograde_grades ON dboscheduler_appointment.studentid = dbograde_grades.userid 
LEFT JOIN dbograde_items ON dbograde_grades.itemid = dbograde_items.id 
LEFT JOIN dboscheduler_slots ON dboscheduler_appointment.slotid = dboscheduler_slots.id
LEFT JOIN dboscheduler ON dboscheduler_slots.schedulerid = dboscheduler.id
WHERE dbograde_items.id = " . $itemid . " AND course = " . $courseid . " AND attended = 1 AND grade IS NULL AND finalgrade = 100";

//The where statement filters the query to only show scheduler slots in course 27, where the person has attended their slot, where the grade is null (which means that this has not triggered the payment info to be deleted), and a finalgrade of 100 (which means they have a payment on record)

$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
$stmt = sqlsrv_query($conn, $query, $params, $options);

if( $stmt === false ) { //Checks for errors, the commented out piece (sqlsrv_errors) will give you detailed errors
	 die(print("An error has occurred. Please contact the system administrator, error 4065.")); //die( print_r( sqlsrv_errors(), true));
}

$num=sqlsrv_num_rows($stmt);

If ($num > 0){
	//Load the required Moodle files so that we can update the grades properly
	require_once 'C:/inetpub/RMSWebsiteData/hse.ubc.ca/moodle/config.php';
	require_once $CFG->dirroot.'/grade/lib.php';
	require_once $CFG->dirroot.'/grade/report/lib.php';
	
	//Keep track of the IDS that need to be updated for the two tables
	$dboscheduler_appointment_where_string = "";
	$dbograde_grades_where_string = "";
	
	//Go through all of the results
	while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
		$userid = $row['studentid'];
		
		//echo ($userid . "<br>");

		$grade_item = grade_item::fetch(array('id'=>$itemid, 'courseid'=>$courseid));
		
		//Give this user a grade of 0 for their fit test payment, which means they have to pay again to register for another scheduler session
		//$grade_item->update_final_grade($data->userid, $data->finalgrade, 'editgrade', $data->feedback, $data->feedbackformat);
		$grade_item->update_final_grade($userid, 0, 'editgrade', '', 0);
		
		If ($dboscheduler_appointment_where_string != ""){
			$dboscheduler_appointment_where_string .= " OR ";
		}
		$dboscheduler_appointment_where_string .= "id = " . $row['scheduler_appointment_ID'];
		
		/*
		If ($dbograde_grades_where_string != ""){
			$dbograde_grades_where_string .= " OR ";
		}
		$dbograde_grades_where_string .= "id = " . $row['grade_ID'];
		*/
	}
	
	$dboscheduler_appointment_where_string = "WHERE " . $dboscheduler_appointment_where_string;
	//$dbograde_grades_where_string = "WHERE " . $dbograde_grades_where_string;
	
	//echo ("dboscheduler_appointment_where_string " . $dboscheduler_appointment_where_string . "<br><br>");
	//echo ("dbograde_grades_where_string " . $dbograde_grades_where_string . "<br><br>");
	
	//Now update these tables
	$query = "UPDATE dboscheduler_appointment SET grade = 100 " . $dboscheduler_appointment_where_string;
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	$stmt = sqlsrv_query($conn, $query, $params, $options);

	if( $stmt === false ) { //Checks for errors, the commented out piece (sqlsrv_errors) will give you detailed errors
		 die(print("An error has occurred. Please contact the system administrator, error 4065a.")); //die( print_r( sqlsrv_errors(), true));
	}
	
}
		
		


