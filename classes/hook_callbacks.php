<?php
namespace local_coursecompleted;

defined('MOODLE_INTERNAL') || die();

class hook_callbacks {

    public static function before_http_headers(\core\hook\output\before_http_headers $hook): void {
        global $PAGE, $USER;

        if (during_initial_install() || CLI_SCRIPT || AJAX_SCRIPT || !isloggedin() || isguestuser()) {
            return;
        }
        // Only the course home page (course-view-topics, course-view-weeks, ...).
        if (strpos((string)$PAGE->pagetype, 'course-view-') !== 0) {
            return;
        }
        $course = $PAGE->course;
        if (empty($course->id) || $course->id == SITEID) {
            return;
        }
        $context = \context_course::instance($course->id);

        // Never redirect admins, trainers or anyone who manages the course.
        if (is_siteadmin()
                || has_capability('moodle/course:update', $context)
                || has_capability('moodle/grade:viewall', $context)) {
            return;
        }

        if (self::has_completed_and_enrolment_ended($USER->id, $course)) {
            redirect(new \moodle_url('/local/coursecompleted/course_completed.php', ['id' => $course->id]));
        }
    }

    /**
     * True if the user's manual enrolment(s) in the course have all ended and the course is complete.
     */
    public static function has_completed_and_enrolment_ended(int $userid, \stdClass $course): bool {
        global $CFG, $DB;
        require_once($CFG->libdir . '/completionlib.php');

        $sql = "SELECT ue.timeend
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE ue.userid = :userid AND e.courseid = :courseid AND e.enrol = 'manual'";
        $timeends = $DB->get_fieldset_sql($sql, ['userid' => $userid, 'courseid' => $course->id]);
        if (!$timeends) {
            return false;
        }
        $now = time();
        foreach ($timeends as $timeend) {
            if (empty($timeend) || $timeend >= $now) {
                return false; // Still has an active enrolment.
            }
        }

        $completion = new \completion_info($course);
        return $completion->is_enabled() && $completion->is_course_complete($userid);
    }
}