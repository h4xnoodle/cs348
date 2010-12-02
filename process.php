<?php

// Process all the forms on index.php
include('admin.class.php');
include('treatment.class.php');
include('billing.class.php');

$errors = array();

function verfiyDate( $date ) {
	global $errors;
	$date = split($date,'-');
	if( !(strlen($date[0])==4 && strlen($date[1])==2 && strlen($date[2])==2) ) {
		$errors[] = "The date provided is invalid. Use YYYY-MM-DD format.";
		return false;
	}
	return true;
}

function checkFilled( $form ) {
	$count = 0;
	global $errors;
	foreach( $form as $k=>$v ) {
		if( $v == '' )
			$count++;
	}
	if( $count ) $errors[] = "There were ".$count." fields left blank. Please fill them out.";
	return (!(bool)$count);
}

if( $_POST['submit'] ) {

	switch( $_POST['form_action'] ) {
	
		// EDT - Treatment
		case 'newEDT':
			
			if( !checkFilled($_POST) || !verifyDate($_POST['dateperf']) ) break;

			$pid = ($_POST['pid_num'] != '' ? $_POST['pid_num'] : $_POST['pid_name']);
			
			
			break;
		case 'getEDT':

			
			break;
		// Admin
		case 'newPatient':
		
		case 'updatePatient':
		
		case 'checkIn':
		
		case 'checkOut':
		
		// Billing
		
		
		default: break;
	}
	
	// Output Errors
	print_r($errors);
}
?>