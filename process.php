<?php

/*	================================================

	Author:	Rebecca Putinski
	UWID:	20271463
	Class:	CS348 Section 3
	
	This file:
		Process the forms from the index.php. Calls
		to the APIs.
		
	================================================
*/

include('admin.class.php');
include('treatment.class.php');
include('billing.class.php');
include('common.php');
include('header.php');

// Decide which form we're processing
if( array_key_exists('submit',$_POST) ) {
	$action = $_POST['form_action'];
	$data = filterData($_POST);
	switch( $action ) {

		// Admin Module 1
		case 'addPatient':
			$a = new Admin;
			if( $a->addPatient($data) )
				header('Location: index.php?admin&success=a');
			else
				printError("Failed to add a new patient.");
			break;
		case 'updatePatient':
			$a = new Admin;
			if( $a->updatePatient($data) )
				header('Location: index.php?admin&success=a');
			else
				printError("Failed to update patient.");
			break;
		case 'checkIn':
			$a = new Admin;
			if( $a->checkInPatient( $data ) )
				header('Location: index.php?admin&success=a');
			else
				printError("Failed to check in patient.");
			break;
		case 'checkOut':
			$a = new Admin;
			if( $a->checkOutPatient( $data ) )
				header('Location: index.php?admin&success=a');
			else
				printError("Failed to check out patient.");
			break;	
		case 'getPatientsOnDate':
			$a = new Admin;
			$a->displayPatientsOnDate($data['date']);
			break;
		case 'getPatientVisits':
			$a = new Admin;
			$a->displayPatientVisits($data['pid']);
			break;
		case 'getPatientsByPhysician':
			$a = new Admin;
			$a->displayPhysiciansPatients($data['ename']);
			break;
			
		// ====================================================		
		// Treatment Module 2
		case 'newEDT':
			$t = new Treatment;		
			if( $t->newEDT($data) )
				header('Location: index.php?treatment&success=t');
			else
				printError("Failed to insert new EDT Record.");
			break;
		case 'getEDT':
			$t = new Treatment;
			$results = $t->getEDTRecords($data['pid'], (bool)$data['option']);
			if( $results )
				$t->displayEDTRecords($results);
			else
				header('Location: index.php?treatment&success=tn');
			break;
		
		// ====================================================
		// Billing Module 3
		case 'addAccount':
			$b = new Billing;
			if( $b->addAccount($data) )
				header('Location: index.php?billing&success=b');
			else
				printError("Failed to add billing account.");
			break;
		case 'updateAccount':
			$b = new Billing;
			if( $b->updateAccount($data) )
				header('Location: index.php?billing&success=b');
			else
				printError("Failed to update billing account.");
			break;
		case 'receivePayment':
			$b = new Billing;
			if( $b->receivePayment($data) )
				header('Location: index.php?billing&success=b');
			else
				printError("Failed to receive payment.");
			break;
		case 'processEDTDisplay':
			$t = new Treatment;
			$b = new Billing;
			$edts = $t->getUnprocessedEDTs($data['pid']);
			$b->displayUnprocessedEDTs($edts);
			break;
		case 'processAllEDTs':
			$success = true;
			$b = new Billing;
			$t = new Treatment;
			$all = $t->getUnprocessedEDTs($data['pid']);
			foreach( $all as $e ) {
				if( !$b->processEDT($e['edtid']) ) {
					$success = false;
					break;
				}
			}
			if( $success ) header('Location: index.php?billing&success=b');
			else printError("Failed to process all EDT records.");
			break;
		case 'closeAccount':
			$b = new Billing;
			$bill = $b->getBill($data['pid']);
			if( $bill['balance'] >= 0 ) {
				$b->displayBill($data['pid']);
				echo "<p class='confirmDelete'>Are you sure you wish to delete all information associated with this account?</p>";
				$form = array(
						'form_action'=>'closeAccountConfirm',
						'submit_text'=>'Confirm Deletion of All Information',
						'_hidden1'=>array('name'=>'pid','value'=>$data['pid']),
						'_dropdown1'=>array('label'=>'Response','name'=>'confirm',array(0=>'No',1=>'Yes'))
						);
				buildForm($form);
				break;
			}
			$data['confirm'] = 1;
			// Fall through otherwise
		case 'closeAccountConfirm':
			$b = new Billing;
			if( !$data['confirm'] ) {
				header('Location: index.php?billing&success=bn');
			} else {
				if( $b->closeAccount($data['pid']) ) 
					header('Location: index.php?billing&success=b');
				else
					printError("Failed to delete patient.");
			}
			break;
		case 'getBill':
			$b = new Billing;
			$b->displayBill($data['pid']);
			break;
		
		// Do nothing
		default: printError("Nothing was done...");
	}

// ==================================================

// Special cases
// Process an individual EDT
} elseif( array_key_exists('processEDT',$_GET) && $_GET['processEDT'] ) {
	$b = new Billing;
	$t = new Treatment;
	if( $b->processEDT($_GET['processEDT']) )
		header('Location: index.php?billing&success=b');
	else
		printError("Failed to process EDT record.");

// For first run of this HMS (index.php)
} elseif( $_SERVER['QUERY_STRING'] == 'insertEmployees' ) {
	$db = new Database;
	if( $db->query("SELECT sin FROM Employees") ) {
		echo "<p class='notice'>This employee already exists.</p>";
	} else {
		$employee = array('ename'=>'Emp1','sin'=>1337,'address'=>'1 Hello Wrld.');
		$job = array('jname'=>'OB','jtype'=>'C');
		if( $db->insert('Employees',$employee) )
			echo "<p class='success'>Employee inserted</p>";
		else
			echo "<p class='error'>Could not insert employee</p>";
		
		if( $db->insert('Jobs',$job) )
			echo "<p class='success'>Job inserted</p>";
		else
			echo "<p class='error'>Could not insert job</p>";
			
		$myEid = $db->query("SELECT eid FROM Employees WHERE sin='1337'");
		$myEid = $myEid[0]['eid'];
		$jid = $db->query("SELECT jid FROM Jobs WHERE jname='OB'");
		$jid = $jid[0]['jid'];
		
		if( $db->insert('EmployeeJobs',array('eid'=>$myEid,'jid'=>$jid)) )
			echo "<p class='success'>Employee Job relationship realised.</p>";
		else
			echo "<p class='error'>Could not complete actions.</p>";
	}
	
// If you access process.php without any of the above defined
} else {
	echo "<p>This page is purely for processing form actions.</p>";
}
include('footer.php');
?>