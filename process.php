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

function removeMeta( $rm ) {
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

if( $_POST['submit'] ) {
	switch( $_POST['form_action'] ) {
	
		// EDT - Treatment
		case 'newEDT':
			if( !checkFilled($_POST) || 
				!verifyDate($_POST['dateperf']) || 
				!checkNumber($_POST['cost']) ||
				!checkNumber($_POST['duration']) ||
				!checkNumber($_POST['pid_num']) ) 
				break;
			
			$pid = ($_POST['pid_num'] ? $_POST['pid_num'] : $_POST['pid_name']);
			$data = removeMeta($_POST);
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
			if( !checkFilled($_POST) ||
				!checkNumber($_POST['pid_num']) )
				break;
			$pid = ($_POST['pid_num'] ? $_POST['pid_num'] : $_POST['pid_name']);
			$data = removeMeta($_POST);
			$t = new Treatment;
			if( $t->getEDTRecords( $data, (bool)$_POST['option'] ) )
				header('Location: index.php?treatment&success=t');
			else
				$errors[] = "Failed to get EDT records.";
			break;
			
		// Admin
		case 'addPatient':
			if( !checkFilled($_POST) ||
				!verifyDate($_POST['dob']) )
				break;
			$data = removeMeta($_POST);
			$a = new Admin;
			if( $a->addPatient($data) )
				header('Location: index.php?admin&success=a');
			else
				$errors[] = "Failed to add a new patient.";
			break;
		case 'updatePatient':
		
		case 'checkIn':
		
		case 'checkOut':
		
		// Billing
		
		
		default: echo "No action specified";
	}
	
	// If we did not successfully perform the action, print out errors
	include('header.php');
	foreach( $errors as $e ) 
		echo "<p class='error'>".$e."</p>";
	include ('footer.php');
}
?>