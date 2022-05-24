<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     coursereport_myh5preport
 * @copyright   2021 Ivan Gula<ivan.gula.wien@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
require_once("../../config.php");
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once('lib.php');
require_once("./classes/selecth5pform.php");	//Formular zur Auswahl der H5P aktivität

$courseid = optional_param('id', '0',PARAM_INT);
$activityid = optional_param('h5pid', '0',PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);


function normalize($string) {
    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', ' '=>'_',
    );
   
    return strtr($string, $table);
}

function gig_user_profilfelder($headerOrIndex='h'){
	$returnHeader = array("Userame","city","country","Geschlecht","Alter","Herkunftsland","Wohnsituation","Asylberechtigt","Seit Jahr in Österreich");
	$returnIndex = array("username","city","country","profile_field_Geschlecht","profile_field_Alter","profile_field_Herkunftsland","profile_field_Wohnsituation","profile_field_Asylberechtigt","profile_field_seitjahr");
	
	if($headerOrIndex=='h'){
		return $returnHeader;
	}
	if($headerOrIndex=='i'){
		return $returnIndex;
	}
}


function get_colum($firstAttemptId, $headerOrIndex ='h', $gig_user_profilfelder=true){
	global $DB, $CFG;
	if($gig_user_profilfelder){
		$returnHeader = gig_user_profilfelder('h');
		$returnIndex = gig_user_profilfelder('i');
		$returnIndex[] = "timecreated";
		$returnIndex[] = "attempt";
		$returnHeader[] = "Durchgeführt am";
		$returnHeader[] = "Versuch Nr.";
	}
	$h5pactivity_attempt_results = $DB->get_records('h5pactivity_attempts_results', array('attemptid'=>$firstAttemptId),'','id, description');
	$i = 0;
	foreach($h5pactivity_attempt_results as $questions){
		if($questions->description!=""){
			$i++;
			$returnIndex[] = "q".$i;
			$returnHeader[] = $questions->description;
			//var_dump($questions->description);
		}
	}
	if($headerOrIndex=='h'){
		return $returnHeader;
	}
	if($headerOrIndex=='i'){
		return $returnIndex;
	}
}



if($courseid == 0){
	$context = context_system::instance();
	$url = new moodle_url('/report/myh5preport/index.php');

}else{
	$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
	$context = context_course::instance($course->id);
	$url = new moodle_url('/report/myh5preport/index.php',array('id'=>$course->id));
}


require_login();

$PAGE->set_context($context);
$PAGE->set_url($url);

require_capability('report/myh5preport:read', $context);

$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'report_myh5preport'));
$PAGE->set_heading(get_string('pluginname', 'report_myh5preport'));

if($courseid == 0){
	$h5pactivity = $DB->get_records('h5pactivity', null,'','id,name');
}else{
	// alle H5Ps im Kurs
	$h5pactivity = $DB->get_records('h5pactivity', array('course'=>$course->id),'','id,name');
	
}
// Selectoptionen für Formular
foreach($h5pactivity as $one_activity){
	$h5pimcourse[$one_activity->id] = $one_activity->name;
}
//var_dump($h5pimcourse);

// auswahlformular mit H5Ps aus dem Kurs
$selecth5pform = new selecth5pform(null, array('arrayOfOptions'=>$h5pimcourse, 'courseid'=>$courseid));

if($activityid>0){
	$toform = array('h5pid'=>$activityid);
	$selecth5pform->set_data($toform);
}
if ($fromform = $selecth5pform->get_data()) {
    // This branch is where you process validated data.
    // Do stuff ...
	//var_dump($fromform);
    // Typically you finish up by redirecting to somewhere where the user
    // can see what they did.
    $nexturl = new moodle_url('/report/myh5preport/index.php',array('id'=>$fromform->courseid,'h5pid'=>$fromform->h5pid ));
	redirect($nexturl);
}

if($activityid>0 && $courseid>0){

	$myh5preporttable = new flexible_table('my_h5p_report_table');




	/*
	$select = "h5pactivityid in(";
	foreach($h5pactivity as $aid => $value){
		$select.=$aid.",";
	}
	$select = substr($select, 0, -1);
	$select .= ")";

	$h5pactivity_attempts = $DB->get_records_select('h5pactivity_attempts',$select);
	*/
	//$tablecolumns = array('name','activity' ,'attempt', 'question1', 'answer1');
	//$tableheaders = array('name','activity' ,'attempt', 'question1', 'answer1');

	//$tablecolumns = array_keys(get_object_vars($h5pactivity[array_key_first($h5pactivity)]));
	//$tableheaders = array_keys(get_object_vars($h5pactivity[array_key_first($h5pactivity)]));
	$myTableData = Array();
	$one_activity = $h5pactivity[$activityid];
	$h5pactivity_attempts = $DB->get_records('h5pactivity_attempts', array('h5pactivityid'=>$one_activity->id),'','id, userid, timecreated, attempt');
	
	$firstAttemptId = get_object_vars($h5pactivity_attempts[array_key_first($h5pactivity_attempts)])['id'];
	
	$tablecolumns = get_colum($firstAttemptId, 'i');
	$tableheaders = get_colum($firstAttemptId, 'h');
	
	
	
	
	//$tablecolumns = array_keys(get_object_vars($h5pactivity_attempts[array_key_first($h5pactivity_attempts)]));
	//$tableheaders = array_keys(get_object_vars($h5pactivity_attempts[array_key_first($h5pactivity_attempts)]));
	
	//$tablecolumns = array('acitivyID','activity' , 'userName', 'time');
	//$tableheaders = array('acitivyID2','activity2' , 'userName2', 'time2');

	$myh5preporttable->define_columns($tablecolumns);
	$myh5preporttable->define_headers($tableheaders);
	$myh5preporttable->sortable(false);
	$baseurl = new moodle_url('/report/myh5preport/index.php',array('id'=>$courseid,'h5pid'=>$activityid ));
	$myh5preporttable->define_baseurl($baseurl);
	//var_dump($one_activity->name);
	$tablename = normalize($one_activity->name);
	$myh5preporttable->is_downloading($download, $tablename, $tablename);
	$myh5preporttable->setup();






	if (!$myh5preporttable->is_downloading()) {
		echo $OUTPUT->header();
		echo $OUTPUT->heading('H5P Reports');
		$selecth5pform->display();
	}

	
	foreach($h5pactivity_attempts as $a_index => $a_data){
		$check = true;
		$my_short_array = Array();
		$my_short_array2 = Array();
		$my_short_array3 = Array();
		//foreach ($one_activity as $one_activity_index => $one_activity_data) {
		//	$my_short_array[] = $one_activity_data;
		//}
		foreach ($a_data as $a_index_row =>$a_data_row ){
			//var_dump($a_index_row);
			if($a_index_row=="userid"){
				if(is_activ_user($a_data_row)){
					$user = $DB->get_record('user',array('id'=>$a_data_row));
					profile_load_data($user);
					//var_dump($user);
					
					
					if($gig = true){
						$myDataFields = gig_user_profilfelder('i');
						$userArray = get_object_vars($user);
						foreach ($myDataFields as $fieldnames){
							$my_short_array[] = $userArray[$fieldnames];
						}
					}else{
						$my_short_array[] = $user->firstname.' '.$user->lastname;
					}
					
				}else{
					$check = false;
					break;
				}
			}elseif($a_index_row=="timecreated"){
				$my_short_array2[] = date( "d-m-Y H:i:s", $a_data_row);
			}elseif($a_index_row=="attempt"){
				$my_short_array2[] =  $a_data_row;
			}else{
				$h5pactivity_attempt_results = $DB->get_records('h5pactivity_attempts_results', array('attemptid'=>$a_data_row),'','id, description, response, additionals');
				foreach($h5pactivity_attempt_results as $q_index => $q_data){
					//$my_short_array[] = $q_data->description;
					if($q_data->response!=""){
						$myJSON = $q_data->additionals;	
						//var_dump(get_object_vars(get_object_vars(json_decode($myJSON))["choices"][$q_data->response]->description)["en-US"]);
						//var_dump("/////////////////////////////////////////////////////////////////////////////");
						$my_short_array3[] = get_object_vars(get_object_vars(json_decode($myJSON))["choices"][$q_data->response]->description)["en-US"];
					}
				}
			}
		}
		//var_dump($my_short_array);
		//$myh5preporttable->add_data($my_short_array);
		if($check){
		//var_dump($my_short_array3);	
			$myTableData[] = array_merge($my_short_array,$my_short_array2,$my_short_array3);
		}
	}
		
	


	foreach($myTableData as $table_row_index =>$table_row_data ){
		$myh5preporttable->add_data($table_row_data);
	}
	$myh5preporttable->finish_output();


	if (!$myh5preporttable->is_downloading()) {
		//var_dump($h5pactivity_attempts);
		echo $OUTPUT->footer();
	}
}else{
	echo $OUTPUT->header();
	echo $OUTPUT->heading('H5P Reports');
	$selecth5pform->display();
	echo $OUTPUT->footer();
}
