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
 * Plugin administration pages are defined here.
 *
 * @package     report_myh5preport
 * @category    admin
 * @copyright   2021 Ivan Gula<ivan.gula.wien@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function report_myh5preport_extend_navigation_course($navigation, $course, $context) {
  $navigation->add(
    get_string('pluginname', 'report_myh5preport'),
    new moodle_url('/report/myh5preport/index.php', ['id' => $course->id]),
    navigation_node::TYPE_SETTING,
    get_string('pluginname', 'report_myh5preport'),
    'myh5preport',
    new pix_icon('icon', '', 'report_myh5preport'));
}

function is_activ_user($user){
	global $DB;
	if(is_array($user)){
		if ($user['deleted'] == 0){
			return true;
		}else{
			return false;
		}
	}elseif(is_object($user)){
		if ($user->deleted == 0){
			return true;
		}else{
			return false;
		}
	}else{
		$user = $DB->get_record('user',array('id'=>$user));
		return is_activ_user($user);
	}
}

function is_user_in_curse($user, $course){
	
}

function is_student_in_curse($user, $course){
	
}