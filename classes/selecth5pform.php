<?php

defined('MOODLE_INTERNAL') || die();

require_once("../../config.php");
require_once("$CFG->libdir/formslib.php");



class selecth5pform extends moodleform {

    function definition() {
        global $CFG;
       
        $mform = $this->_form; // Don't forget the underscore! 

		$mform->addElement('select', 'h5pid', 'H5P Activity', $this->_customdata['arrayOfOptions']);// Add elements to your form
		 $mform->setType('courseid', PARAM_INT);
		$mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
		$buttonarray=array();
		$buttonarray[] = $mform->createElement('submit', 'submitbutton', 'Auswählen');
		$mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
		
    }                           // Close the function
}                               // Close the class