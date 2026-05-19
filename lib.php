<?php 
defined('MOODLE_INTERNAL') || die();

function user_has_completed_course($userid, $courseid) {
    global $DB,$PAGE,$USER;
    if ($PAGE->context->contextlevel == CONTEXT_COURSE) {
        $courseid = $PAGE->context->instanceid;
        $course = get_course($courseid);
        // Get the user's enrollment record for the course.
        $enrol = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual'], '*',);
        if($enrol){
            $enrolment = $DB->get_record('user_enrolments', ['userid' => $USER->id, 'enrolid' => $enrol->id]);
            // Check if the enrollment has ended.
            if ($enrolment && $enrolment->timeend && $enrolment->timeend < time()) {
                // Check if the user has completed the course.
                $completion = new \completion_info($course);
                if ($completion->is_course_complete($USER->id)) {
                    // Redirect to the custom "Course Completed" page.
                    return true;
                    // $redirecturl = new \moodle_url('/local/coursecompleted/course_completed.php', ['id' => $courseid]);
                    // redirect($redirecturl);
                }
            }

        }
    }
    return false;
}

function local_coursecompleted_before_http_headers() {
    global $COURSE,$USER;
    if (user_has_completed_course($USER->id, $COURSE->id)) {
        redirect(new moodle_url('/local/coursecompleted/course_completed.php', ['id' => $COURSE->id]));
    }
}