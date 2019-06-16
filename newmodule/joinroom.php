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


/*
echo "newmodule : ".$newmodule->course;
echo "<br />";
echo "PAGE : ".$PAGE->course->id;
echo "<br />";
echo "USER : ".$USER->id;
echo "<br />";
*/
$new_course_id = $newmodule->course;
$new_user_id = $USER->id;
$query = "SELECT DISTINCT u.id AS userid,u.firstname,u.lastname, c.id AS courseid, DATE_FORMAT(FROM_UNIXTIME(ue.timecreated),'%m/%d/%Y') AS timecreated FROM mdl_user u JOIN mdl_user_enrolments ue ON ue.userid = u.id JOIN mdl_enrol e ON e.id = ue.enrolid JOIN mdl_role_assignments ra ON ra.userid = u.id JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel =50 JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id JOIN mdl_role r ON r.id = ra.roleid AND r.shortname =  'editingteacher' AND u.id = $new_user_id WHERE  courseid =$new_course_id";
$res = $result = $DB->get_records_sql($query);
print_r($res);
$access = 1;
if($res != null && $res != ""){
	$access = 3;
}

//echo "access = $access";
//die();




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
global $USER, $DB;
$cuid=$USER->id;
// Conditions to show the intro can change to look for own settings or whatever.
if ($newmodule->intro) {
    echo $OUTPUT->box(format_module_intro('newmodule', $newmodule, $cm->id), 'generalbox mod_introbox', 'newmoduleintro');
}
$result = $DB->get_record('newmodule',array('id'=>$cm->instance));
if( is_object($result)){


    echo $OUTPUT->heading($result->title);
    echo '<br><b>'.$result->descr.'</b>';
    $room = $result->sky_room_id;
    $user_map = $DB->get_record('omira_sky_usermap',array('m_id'=>$USER->id));
    $user_class = $DB->get_record('omira_users_room',array('user_id'=>$USER->id,'room_id'=>$result->sky_room_id));
    if(is_object($user_map)&& is_object($user_class)){
        $some_data['action'] ='getLoginUrl';
        $params = array(
            'room_id' => $result->sky_room_id,
            'user_id'=>$user_map->s_id,
            'ttl'=>300
        );
		$q ="SELECT s_id FROM `mdl_omira_sky_usermap` WHERE m_id = ".$USER->id;
		$res = $result = $DB->get_records_sql($q);
		//print_r($res);
		$user_id = 0;
		foreach($res as $key => $value){
			$user_id = $key;
		}
        $some_data['params'] =json_encode($params);
        $response=akyroom_call($some_data);
        if($response['ok']==true ){
			
			$some_data['action'] ='updateRoomUser';
			$params = array(
				'room_id' => $room,
				'user_id' =>$user_id,
				'access' => $access,
			);
			$some_data['params'] =json_encode($params);
			$r=akyroom_call($some_data);
			/*
			echo "room_id = $room<br />";
			echo "user_id = $user_id<br />";
			echo "access = $access<br />";
			echo "<pre>";
			echo "**************<br />";
			print_r($r);
			echo "1";
			echo "<pre>";
			*/
			//die();
            redirect($response['result'], 'ورود به کلاس ...', 0);
        }


    }
    else if(is_object($user_map)&& !is_object($user_class)){
        $newmodule=array();
        $newmodule['user_id']=$USER->id;
        $newmodule['room_id']=$room;
        $DB->insert_record('omira_users_room', $newmodule);
        $some_data['action'] ='addRoomUsers';
        $params = array(
            'room_id' => $room,
            'users' => array(
                array('user_id' =>$user_map->s_id)
            ),
        );
        $some_data['params'] =json_encode($params);
        $response=akyroom_call($some_data);
        $some_data['action'] ='getLoginUrl';
        $params = array(
            'room_id' => $room,
            'user_id'=>$user_map->s_id,
            'ttl'=>300
        );
		
		$q ="SELECT s_id FROM `mdl_omira_sky_usermap` WHERE m_id = ".$USER->id;
		$res = $result = $DB->get_records_sql($q);
		//print_r($res);
		$user_id = 0;
		foreach($res as $key => $value){
			$user_id = $key;
		}
        $some_data['params'] =json_encode($params);
        $response=akyroom_call($some_data);
        if($response['ok']==true ){
			
			$some_data['action'] ='updateRoomUser';
			$params = array(
				'room_id' => $room,
				'user_id' =>$user_id,
				'access' => $access,
			);
			$some_data['params'] =json_encode($params);
			$response=akyroom_call($some_data);
			/*
			echo "<pre>";
			echo "**************<br />";
			print_r($response);
			echo "2";
			echo "<pre>";
			*/
            redirect($response['result'], 'ورود به کلاس ...', 0);

        }
    }
    else{
		
        $user_id=0;
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
            $newmodule=array();
            $newmodule['m_id']=$cuid;
            $newmodule['s_id']=$response['result'];
            $DB->insert_record('omira_sky_usermap', $newmodule);
            $user_id= $response['result'];
        }else{
			 $some_data['action'] ='getUsers';
        $some_data['params'] =json_encode(array(
            'username'=> $c_username
            
        ));

        $response2=akyroom_call($some_data);
		$result=$response2['result'];
		$user_id=0;

		foreach($result as $r){
		if($r['username']== $c_username)
			$user_id=$r['id'];
		}
		
		}
        $newmodules=array();
        $newmodules['user_id']=$cuid;
        $newmodules['room_id']=$room;
        $DB->insert_record('omira_users_room', $newmodules);
        $some_data['action'] ='addRoomUsers';
        $params = array(
            'room_id' => $room,
            'users' => array(
                array('user_id' =>$user_id)
            ),
        );
        $some_data['params'] =json_encode($params);
        $response=akyroom_call($some_data);
        $some_data['action'] ='getLoginUrl';
        $params = array(
            'room_id' => $room,
            'user_id'=>$user_id,
            'ttl'=>300
        );
		$q ="SELECT s_id FROM `mdl_omira_sky_usermap` WHERE m_id = ".$USER->id;
		$res = $result = $DB->get_records_sql($q);
		//print_r($res);
		$user_id = 0;
		foreach($res as $key => $value){
			$user_id = $key;
		}
        $some_data['params'] =json_encode($params);
        $response=akyroom_call($some_data);
        if($response['ok']==true ){
			$some_data['action'] ='updateRoomUser';
			$params = array(
				'room_id' => $room,
				'user_id' =>$user_id,
				'access' => $access,
			);
			$some_data['params'] =json_encode($params);
			$r=akyroom_call($some_data);
			/*echo "<pre>";
			echo "**************<br />";
			print_r($response);
			echo "3";
			echo "<pre>";*/
            redirect($response['result'], 'ورود به کلاس ...', 0);

        }
    }
}
else{
    echo $OUTPUT->heading('درخواست غیر معتبر');

}
// Replace the following lines with you own code.

//  $mform->addElement('html', '<div class="qheader">');



// Finish the page.
echo $OUTPUT->footer();
