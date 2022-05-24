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
 * The class for the table. Table view of Users be deleted in 24h.
 *
 * @package     report_myh5preport
 * @copyright   2019 Ivan Gula <ivan.gula.wien@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
namespace report_myh5preport;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/classes/user.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
 
use table_sql;
use html_writer;
use confirm_action;
use moodle_url;
use pix_icon;
use action_link;
use core_user;

class myh5preporttable extends table_sql {
	
	/**
     * Sets up the table.
     *
     * 
     * @throws coding_exception
     */
    public function __construct() {
        global $PAGE, $CFG;

        parent::__construct('myh5preporttable');
        $this->define_baseurl($PAGE->url->out(false));

        // Define columns in the table.
        $this->define_table_columns();

        // Define configs.
        $this->define_table_configs();

        // TODO die folgenden Variablen!
		$from = '{user}';
		$fields = ['id','firstname','lastname','username', 'auth', 'timecreated'];
		
		if($CFG->deluser_aftertime_filter=='email_manual'){
				$filter = "'email', 'manual'";
			}else{
				$filter = "'".$CFG->deluser_aftertime_filter."'";
			}
			
		$select = 'auth in('.$filter.') and deleted = 0 and suspended = 0 and timecreated < unix_timestamp(DATE_SUB(DATE_SUB(curdate(),INTERVAL 1 DAY),INTERVAL '.$CFG->deluser_aftertime_count.' '.$CFG->deluser_aftertime_time.'))';
		
		
        $this->set_sql(implode(', ', $fields), $from, $select);
    }

}