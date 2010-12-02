<?php

// Process all the forms on index.php
include('admin.class.php');
include('treatment.class.php');
include('billing.class.php');
include('common.php');

$errors = array();

function verifyDate( $date ) {
	global $errors;
	$date = explode('-',$date);
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

function filterData( $rm ) {
	while( list($k,$v) = each($rm) ) {
		if( $k != 'form_action' && $k != 'submit' )
			$result[$k] = $v;
	}
	return $result;
}

function checkNumber( $field ) {
	global $errors;
	if( !is_numeric( $field ) ) {
		$errors[] = "Please ensure you input numbers in the appropriate fields (cost, patient ID, duration, etc)";
		return false;
	}
	return true;
}

// Big assumption: $_POST only contains my form data, with 'form_action' and 'submit' as the 'meta' for the forms.
// If this doesn't hold, it is likely this entire thing will break. FIX? extract function
if( $_POST['submit'] ) {
	$action = $_POST['form_action'];
	$data = filterData($_POST);
	switch( $action ) {
		// EDT - Treatment
		case 'newEDT':
			if( !checkFilled($data) || !verifyDate($data['dateperf']) || 
				!checkNumber($data['cost']) || !checkNumber($data['duration']) ||
				!checkNumber($data['pid_num']) ) 
				break;
			
			$pid = ($data['pid_num'] ? $data['pid_num'] : $data['pid_name']);
			array_shift($data); // pid_num
			array_shift($data); // pid_name
			$data['pid'] = $pid;
			$t = new Treatment;
			if( $t->newEDT($data) )
				header('Location: index.php?treatment&success=t');
			else
				$errors[] = "Failed to insert new EDT Record.";
			break;
		case 'getEDT':
			if( !checkFilled($data) || !checkNumber($data['pid_num']) )
				break;
			$pid = ($data['pid_num'] ? $data['pid_num'] : $data['pid_name']);
			$t = new Treatment;
			if( $t->getEDTRecords( $data, (bool)$data['option'] ) )
				header('Location: index.php?treatment&success=t');
			else
				header('Location: index.php?treatment&success=tn');
			break;
			
		// Admin
		case 'addPatient':
			if( !checkFilled($data) || !verifyDate($data['dob']) )
				break;
			$a = new Admin;
			if( $a->addPatient($data) )
				header('Location: index.php?admin&success=a');
			else
				$errors[] = "Failed to add a new patient.";
			break;
		case 'updatePatient':
			if( !checkFilled($data) || !verifyDate($data['dob']) )
				break;
			$a = new Admin;
			if( $a->updatePatient($data) )
				header('Location: index.php?admin&success=a');
			else
				$errors[] = "Failed to update patient.";
			break;
		case 'checkIn':
			if( !checkFilled($data) || !verifyDate($data['indate']) ||
				!checkNumber($data['pid']) || !checkNumber($data['eidin']) )
				break;
			$a = new Admin;
			if( $a->checkInPatient( $data ) )
				header('Location: index.php?admin&success=a');
			else
				$errors[] = "Failed to check in patient.";
			break;
		case 'getPatientAdmits':
			if( !checkFilled($data) ) break;
			$a = new Admin;
			$results = $a->getPatientAdmits($data);
			
			break;
		case 'getPatientsByPhys':
		
			break;
		// DO MORE STUFF HERE ~~~~~~~~~~~~~~~~~~~~
		case 'checkOut':
			if( !checkFilled($data) || !verifyDate($data['outdate']) ||
				!checkNumber($data['eidout']) || !checkNumber($data['pid']) )
				break;
			$a = new Admin;
			if( $a->checkOutPatient( $data ) )
				header('Location: index.php?admin&success=a');
			else
				$errors[] = "Failed to check out patient.";
			break;
			
			
		
		// Billing
		
		
		default: $errors[] = "Nothing was done...";
	}
	
	// If we did not successfully perform the action, print out errors
	include('header.php');
	foreach( $errors as $e ) 
		echo "<p class='error'>".$e."</p>";
	include ('footer.php');
}
?>