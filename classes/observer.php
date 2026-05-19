<?php 
namespace local_coursecompleted;

defined('MOODLE_INTERNAL') || die();

class observer {
    public static function course_viewed(\core\event\course_viewed $event) {
        global $CFG,$DB, $COURSE, $USER,$PAGE;
        require_once($CFG->dirroot.'/enrol/locallib.php');
        $courseid = $event->courseid;
        $course = get_course($courseid);
        $context = \context_course::instance($course->id, MUST_EXIST);
        $is_enrolled = is_enrolled($context, $USER->id, '', true);
        if($is_enrolled){
            $ecm = new \course_enrolment_manager($PAGE,$course);
            $user_enrollment = $ecm->get_user_enrolments($USER->id);
            if($user_enrollment){
                $first = reset($user_enrollment);
                $current_time = time();
                if($first->timeend>$current_time){
                    $date1 = new \DateTime("now");
                    $date2 = new \DateTime(date('Y-m-d H:i:s',$first->timeend));
                    $interval = $date1->diff($date2);
                    $diff_in_days =  $interval->days; 
                    $string =  '<span class="blink-soft">'.$diff_in_days.'</span> days.';
                    if($diff_in_days<=30 && $diff_in_days>7){
                        $color_class = 'below_30_days';//'#e4cac8'; // #ffe2c4
                        \core\notification::warning("Your course expires in $string");
                    } else if($diff_in_days<=7) {
                        // $color ='#e4cac8'; // #ffbfbf
                        $color_class = 'below_7_days';
                        \core\notification::error("Your course expires in $string");
                    } else {
                        // $color ='#e4cac8';
                        $color_class = 'above_30_days';
                    }
                } 
            }
        }
    }

    // public static function course_module_completion_updated(\core\event\course_module_completion_updated $event){
    //     global $DB, $CFG;
    //         require_once($CFG->dirroot.'/lib/badgeslib.php');

    //         $eventdata = $event->get_record_snapshot('course_modules_completion', $event->objectid);
    //         $userid = $event->relateduserid;
    //         $mod = $event->contextinstanceid;

    //         $cm = get_coursemodule_from_id(null, $event->objectid, 0, false, MUST_EXIST);
    //         $sectionnum = $cm->section;
    //         $courseid = $cm->course;
    //         $modinfo = get_fast_modinfo($courseid);
    //         $sectioninfo = $modinfo->get_section_info($sectionnum);

    //         if ($eventdata->completionstate == COMPLETION_COMPLETE
    //             || $eventdata->completionstate == COMPLETION_COMPLETE_PASS
    //             || $eventdata->completionstate == COMPLETION_COMPLETE_FAIL) {
                
    //                 if($eventdata->completionstate == COMPLETION_COMPLETE_FAIL){

    //                 }  else {
    //                     $obj = new \stdClass;
    //                     $obj->userid = $userid;
    //                     $obj->mod = $mod;
    //                     $obj->marks = $sectioninfo->unithours;
    //                     $DB->insert_record('');
    //                 }
    //         }
    // }
}