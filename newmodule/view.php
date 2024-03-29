<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of newmodule
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_newmodule
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace newmodule with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
global $PAGE, $USER;
$u_id = $USER->id;

$c_id = $PAGE->course;
$query = "SELECT DISTINCT u.id AS userid,u.firstname,u.lastname, c.id AS courseid, DATE_FORMAT(FROM_UNIXTIME(ue.timecreated),'%m/%d/%Y') AS timecreated FROM mdl_user u JOIN mdl_user_enrolments ue ON ue.userid = u.id JOIN mdl_enrol e ON e.id = ue.enrolid JOIN mdl_role_assignments ra ON ra.userid = u.id JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel =50 JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id JOIN mdl_role r ON r.id = ra.roleid AND r.shortname =  'editingteacher' WHERE courseid =185";
//$var = $DB->get_record_sql($query);
//echo "**********<br />";
//print_r($u_id);
//echo "**********<br />";
//print_r($c_id);
$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... newmodule instance ID - it should be named as the first character of the module.
if ($id) {
    $cm         = get_coursemodule_from_id('newmodule', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $newmodule  = $DB->get_record('newmodule', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $newmodule  = $DB->get_record('newmodule', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $newmodule->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('newmodule', $newmodule->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_newmodule\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $newmodule);
$event->trigger();

// Print the page header.



$PAGE->set_url('/mod/newmodule/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($newmodule->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('newmodule-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

$cuid=$USER->id;
// Conditions to show the intro can change to look for own settings or whatever.
if ($newmodule->intro) {
    echo $OUTPUT->box(format_module_intro('newmodule', $newmodule, $cm->id), 'generalbox mod_introbox', 'newmoduleintro');
}
$result = $DB->get_record('newmodule',array('id'=>$cm->instance));
if( is_object($result)){
    $url="joinroom.php?id=".$_GET['id'];
echo '<div '.$OUTPUT->heading($result->name).'</div>';
    echo '<br><b>'.$result->descr.'</b>';
    echo '<br><b>جهت ورود به کلاس بر روی لینک زیر کلیک کنید</b><br> <br>'.
        '<a href="'.$url.'" target="_blank" class="btn btn-warning span3 " >ورود به کلاس</a><br><br>'
    ;




}
else{
    echo $OUTPUT->heading('درخواست غیر معتبر');

}
// Replace the following lines with you own code.

//  $mform->addElement('html', '<div class="qheader">');



// Finish the page.
echo $OUTPUT->footer();
