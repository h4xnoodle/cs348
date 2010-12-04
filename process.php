<?php

// Process all the forms on index.php
// Call the appropriate class methods for each module

include('admin.class.php');
include('treatment.class.php');
include('billing.class.php');
include('common.php');
include('header.php');

if( array_key_exists('submit',$_POST) ) {
	$action = $_POST['form_action'];
	$data = filterData($_POST);
	switch( $action ) {
	
		// EDT - Treatment Module 1
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
			
		// Admin module
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

		// Billing module
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
		
		default: printError("Nothing was done...");
	}
} elseif( $_GET['processEDT'] ) {
	$b = new Billing;
	$t = new Treatment;
	if( substr($_GET['processEDT'],0,3) == 'all' ) {
		
	} else {
		if( $b->processEDT($_GET['processEDT']) )
			header('Location: index.php?billing&success=b');
		else
			printError("Failed to process EDT record.");
	}
} else {
	echo "<p>This page is purely for processing form actions.</p>";
}
include('footer.php');
?>