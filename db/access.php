<?php

$capabilities = array(
    'report/myh5preport:read' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
			/*'guest'          => CAP_PREVENT,
			'student'        => CAP_PROHIBIT,
			'teacher'        => CAP_ALLOW,
			'editingteacher' => CAP_ALLOW,
			'coursecreator'  => CAP_ALLOW,
			'manager'        => CAP_ALLOW*/
		)
    )
 );