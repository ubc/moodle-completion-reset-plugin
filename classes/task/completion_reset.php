<?php

namespace tool_completionreset\task;

require_once($CFG->libdir . '/gradelib.php');

/**
 * completion_reset scheduled task.
 */
class completion_reset extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('completionreset', 'tool_completionreset');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        $courseid = get_config('tool_completionreset', 'course_id');
        $itemid = get_config('tool_completionreset', 'item_id');

        if (empty($courseid) || empty($itemid)) {
            # plugin not setup
            return;
        }

        $query = 'SELECT sa.id AS scheduler_appointment_ID, sa.slotid, sa.studentid, sa.attended, sa.grade, 
                    gi.itemname, gg.id AS grade_ID, gg.finalgrade, gi.id AS grade_item_ID, s.course
            FROM {scheduler_appointment} sa
            LEFT JOIN {grade_grades} gg ON sa.studentid = gg.userid 
            LEFT JOIN {grade_items} gi ON gg.itemid = gi.id 
            LEFT JOIN {scheduler_slots} ss ON sa.slotid = ss.id
            LEFT JOIN {scheduler} s ON ss.schedulerid = s.id
            WHERE gi.id = ' . $itemid . ' AND course = ' . $courseid . ' AND attended = 1 AND grade IS NULL AND finalgrade = 100';

        $items = $DB->get_recordset_sql($query);

        //Keep track of the IDS that need to be updated
        $appointment_ids = array();

        foreach ($items as $item) {
            $userid = $item->studentid;

//            var_dump($item);

            $grade_item = \grade_item::fetch(array('id'=>$itemid, 'courseid'=>$courseid));

            //Give this user a grade of 0 for their fit test payment, which means they have to pay again to register for another scheduler session
            //$grade_item->update_final_grade($data->userid, $data->finalgrade, 'editgrade', $data->feedback, $data->feedbackformat);
            $grade_item->update_final_grade($userid, 0, 'editgrade');

            $appointment_ids[] = $item->scheduler_appointment_id;
        }

        $items->close();

        if (empty($appointment_ids)) {
            return;
        }

        //Now update those IDs
        $query = "UPDATE {scheduler_appointment} SET grade = 100  WHERE id IN (" . implode(',', $appointment_ids) . ')';
//        var_dump($query);
        $result = $DB->execute($query);

        if( $result === false ) { //Checks for errors, the commented out piece (sqlsrv_errors) will give you detailed errors
            throw new \coding_exception('An error has occurred. Please contact the system administrator, error 4065a.'); //die( print_r( sqlsrv_errors(), true));
        }
    }
}


