<?php 
defined('MOODLE_INTERNAL') || die();




/**
 * Check whether a user's manual enrolment has expired
 * and the user has completed the course.
 *
 * @param int $userid User ID.
 * @param int $courseid Course ID.
 * @return bool
 */
function user_has_completed_course($userid, $courseid) {
    global $DB;

    $course = get_course($courseid);

    // Find the manual enrolment instance.
    $enrol = $DB->get_record('enrol', [
        'courseid' => $course->id,
        'enrol' => 'manual',
        'status' => ENROL_INSTANCE_ENABLED,
    ]);

    if (!$enrol) {
        return false;
    }

    // Find this user's enrolment.
    $enrolment = $DB->get_record('user_enrolments', [
        'userid' => $userid,
        'enrolid' => $enrol->id,
        'status' => ENROL_USER_ACTIVE,
    ]);

    if (!$enrolment) {
        return false;
    }

    // Check whether the enrolment has expired.
    if (empty($enrolment->timeend) || $enrolment->timeend >= time()) {
        return false;
    }

    // Check course completion.
    $completion = new completion_info($course);

    return $completion->is_course_complete($userid);
}


/*function local_coursecompleted_before_http_headers() {
    global $COURSE,$USER;
    if (user_has_completed_course($USER->id, $COURSE->id)) {
        redirect(new moodle_url('/local/coursecompleted/course_completed.php', ['id' => $COURSE->id]));
    }
}*/


function local_coursecompleted_before_http_headers() {
    global $USER, $COURSE, $CFG;

    // Prevent this callback from initiating a recursive redirect.
    static $redirecting = false;

    if ($redirecting) {
        return;
    }

    // Only redirect from the course view page.
    $currentscript = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
    $courseviewscript = realpath($CFG->dirroot . '/course/view.php');

    if (!$currentscript || $currentscript !== $courseviewscript) {
        return;
    }

    if (!isloggedin() || isguestuser()) {
        return;
    }

    if (empty($COURSE->id) || $COURSE->id == SITEID) {
        return;
    }

    if (user_has_completed_course($USER->id, $COURSE->id)) {
        $redirecting = true;

        redirect(new moodle_url(
            '/local/coursecompleted/course_completed.php',
            ['id' => $COURSE->id]
        ));
    }
}

