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
 * Library of interface functions and constants for module newmodule
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the newmodule specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_newmodule
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Example constant, you probably want to remove this :-)
 */
define('NEWMODULE_ULTIMATE_ANSWER', 42);

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function newmodule_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the newmodule into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $newmodule Submitted data from the form in mod_form.php
 * @param mod_newmodule_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted newmodule record
 */
function newmodule_add_instance(stdClass $newmodule, mod_newmodule_mod_form $mform = null) {
    global $DB;
    global $USER;

    $newmodule->timecreated = time();
	
    $some_data['action'] ='createRoom';
	$t = $newmodule->timelimit != null && $newmodule->timelimit != '' && $newmodule->timelimit != 0 ? intval($newmodule->timelimit) : null;
	$m = $newmodule->max_users != null && $newmodule->max_users != '' && $newmodule->max_users != 0 ? intval($newmodule->max_users) : null;
    $some_data['params'] =json_encode(array(
        'name'=> $newmodule->title,
        'title'=> $newmodule->name,
        'guest_login'=> $newmodule->guest_login,
        'op_login_first'=> $newmodule->op_login_first,
		'max_users'=> $m ,
		'time_limit'=> $t * 3600
    ));

    $newmodule->descr = format_text($newmodule->descr['text']);

    $response=akyroom_call($some_data);

    if($response['ok']==true ){
        $room_id=$response['result'];
        $newmodule->sky_room_id=$room_id;
        $newmodule->id = $DB->insert_record('newmodule', $newmodule);
        insert_user_in_skyroom($room_id);
        //newmodule_grade_item_update($newmodule);
        return $newmodule->id;
    }
    else{

    }


//print_r($result);
//die();
    // You may have to add extra stuff in here.


}
function count_alternative($v){
	if($v === null || $v == null)
		return false;
	if($v === "" || $v == "")
		return false;
	if($v === array() || $v == array())
		return false;
	return true;
	}
function akyroom_call($some_data){
    global $DB;
    global $USER;
    $curl = curl_init();
    $result = $DB->get_record('omira_setting',array('id'=>1));
	
    if(count_alternative($result)>0){
        curl_setopt($curl, CURLOPT_POST, 1);
        // Set the url path we want to call
        curl_setopt($curl, CURLOPT_URL,$result->api_url );
        // Make it so the data coming back is put into a string
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Insert the data
        curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
        // You can also bunch the above commands into an array if you choose using: curl_setopt_array
        // Send the request
        $response = curl_exec($curl);
        $errNo = curl_errno($curl);
        if ($errNo !== 0) {
            throw new NetworkException(curl_error($curl), $errNo);
        }

        // check HTTP status code
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($http_code !== 200) {
            throw new HttpException('HTTP Error', $http_code);
        }

        // decode JSON response
        $response = json_decode($response, true);
        if ($response === null) {
            throw new JsonException('Invalid Response', json_last_error());
            $response['ok']=false;
            $response['message']='خطا دوباره سعی کنید';
        }
        return $response;
    }
    // We POST the data

}

 function insert_user_in_skyroom($room_id){
     global $DB;
     global $USER;
	 global $PAGE;
     $user_id=0;
     $result = $DB->get_record('omira_sky_usermap',array('m_id'=>$USER->id));
     if( is_object($result)){
         $user_id =$result->s_id;

     }
     else{
         $c_uid=$USER->id;
         $c_username=$USER->username;
         $c_fname=$USER->firstname;
         $c_lname=$USER->lastname;
         $c_nickname=$USER->firstname.' '.$USER->lastname;
        // $c_password= sha1(time());
         $c_password= "123098765456";
         $c_status= 1;
         $c_is_public= true;
         $some_data['action'] ='createUser';
         $some_data['params'] =json_encode(array(
             'username'=> $c_username,
             'password'=> $c_password,
             'nickname'=> $c_nickname,
             'fname'=> $c_fname,
             'lname'=> $c_lname,
             'status'=> $c_status,
             'is_public'=> $c_is_public
         ));

         $response=akyroom_call($some_data);
         if($response['ok']){
            $newmodule['m_id']=$USER->id;
            $newmodule['s_id']=$response['result'];
             $DB->insert_record('omira_sky_usermap', $newmodule);

            $user_id=  $newmodule['s_id'];
        }

     }

     $newmodule['user_id']=$USER->id;
     $newmodule['room_id']=$room_id;
     $DB->insert_record('omira_users_room', $newmodule);
     $some_data['action'] ='addRoomUsers';
	 /*
	 echo "newmodule : ".$newmodule->course;
	 echo "<br />";
	 echo "PAGE : ".$PAGE->course->id;
	 echo "<br />";
	 echo "USER : ".$USER->id;
	 echo "<br />";
	 */
	 $new_course_id = $PAGE->course->id;
	 $new_user_id = $USER->id;
	 $query = "SELECT DISTINCT u.id AS userid,u.firstname,u.lastname, c.id AS courseid, DATE_FORMAT(FROM_UNIXTIME(ue.timecreated),'%m/%d/%Y') AS timecreated FROM mdl_user u JOIN mdl_user_enrolments ue ON ue.userid = u.id JOIN mdl_enrol e ON e.id = ue.enrolid JOIN mdl_role_assignments ra ON ra.userid = u.id JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel =50 JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id JOIN mdl_role r ON r.id = ra.roleid AND r.shortname =  'editingteacher' AND u.id = $new_user_id WHERE  courseid =$new_course_id";
	 $res = $result = $DB->get_records_sql($query);
	 //print_r($res);
	 $access = 1;
	 if($res != null && $res != ""){
		 $access = 3;
	 }
     $params = array(
         'room_id' => $room_id,
         'users' => array(
             array('user_id' =>$user_id ,'access' => $access)
         ),
     );
     $some_data['params'] =json_encode($params);
     $response=akyroom_call($some_data);


}

/**
 * Updates an instance of the newmodule in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $newmodule An object from the form in mod_form.php
 * @param mod_newmodule_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function newmodule_update_instance(stdClass $newmodule, mod_newmodule_mod_form $mform = null) {
    global $DB;

    $newmodule->timemodified = time();
    $newmodule->id = $newmodule->instance;

    // You may have to add extra stuff in here.

    $result = $DB->update_record('newmodule', $newmodule);

    newmodule_grade_item_update($newmodule);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every newmodule event in the site is checked, else
 * only newmodule events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function newmodule_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$newmodules = $DB->get_records('newmodule')) {
            return true;
        }
    } else {
        if (!$newmodules = $DB->get_records('newmodule', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($newmodules as $newmodule) {
        // Create a function such as the one below to deal with updating calendar events.
        // newmodule_update_events($newmodule);
    }

    return true;
}

/**
 * Removes an instance of the newmodule from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function newmodule_delete_instance($id) {
    global $DB;

    if (! $newmodule = $DB->get_record('newmodule', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('newmodule', array('id' => $newmodule->id));

    newmodule_grade_item_delete($newmodule);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $newmodule The newmodule instance record
 * @return stdClass|null
 */
function newmodule_user_outline($course, $user, $mod, $newmodule) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $newmodule the module instance record
 */
function newmodule_user_complete($course, $user, $mod, $newmodule) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in newmodule activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function newmodule_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link newmodule_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function newmodule_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@link newmodule_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function newmodule_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function newmodule_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function newmodule_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of newmodule?
 *
 * This function returns if a scale is being used by one newmodule
 * if it has support for grading and scales.
 *
 * @param int $newmoduleid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given newmodule instance
 */
function newmodule_scale_used($newmoduleid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('newmodule', array('id' => $newmoduleid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of newmodule.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any newmodule instance
 */
function newmodule_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('newmodule', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given newmodule instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $newmodule instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function newmodule_grade_item_update(stdClass $newmodule, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($newmodule->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($newmodule->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $newmodule->grade;
        $item['grademin']  = 0;
    } else if ($newmodule->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$newmodule->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/newmodule', $newmodule->course, 'mod', 'newmodule',
            $newmodule->id, 0, null, $item);
}

/**
 * Delete grade item for given newmodule instance
 *
 * @param stdClass $newmodule instance object
 * @return grade_item
 */
function newmodule_grade_item_delete($newmodule) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/newmodule', $newmodule->course, 'mod', 'newmodule',
            $newmodule->id, 0, null, array('deleted' => 1));
}

/**
 * Update newmodule grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $newmodule instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function newmodule_update_grades(stdClass $newmodule, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/newmodule', $newmodule->course, 'mod', 'newmodule', $newmodule->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function newmodule_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for newmodule file areas
 *
 * @package mod_newmodule
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function newmodule_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the newmodule file areas
 *
 * @package mod_newmodule
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the newmodule's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function newmodule_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding newmodule nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the newmodule module instance
 * @param stdClass $course current course record
 * @param stdClass $module current newmodule instance record
 * @param cm_info $cm course module information
 */
function newmodule_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Extends the settings navigation with the newmodule settings
 *
 * This function is called when the context for the page is a newmodule module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $newmodulenode newmodule administration node
 */
function newmodule_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $newmodulenode=null) {
    // TODO Delete this function and its docblock, or implement it.
}
