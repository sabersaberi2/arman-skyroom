<?php
class observer {
    public static function course_module_deleted($event) {
		global $DB, $CFG, $USER, $PAGE;
		echo "<pre>";
		print_r($event);
		echo "</pre>";
		echo "<pre>";
		print_r($PAGE);
		echo "</pre>";
		echo "<pre>";
		print_r($USER);
		echo "</pre>";
		//\core\event\course_module_deleted 
		//die();
		echo $event->other[modulename];
		if($event->other[modulename] == "modulename"){
			echo "<br />**********shod************<br />";
			// $some_data['action'] ='deleteRoom';
			// $params = array(
				// 'room_id' => $room,
			// );
			// $some_data['params'] =json_encode($params);
			// $r=observer::akyroom_call($some_data);
		}

    }
	
}