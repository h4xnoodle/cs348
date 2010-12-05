<?php

/*	================================================

	Author:	Rebecca Putinski
	UWID:	20271463
	Class:	CS348 Section 3
	
	This file:
		Performs tests on the API functions. 
		
	================================================
*/

include('config.php');

if( !RUN_TESTS ) {
	echo "<p class='notice'>Tests are turned off.</p>";
	return;
}

$results = array();

// Data for test purposes
function populateBilling() {
	$data = array(
		'pname'=>'BTEST1',
		'dob'=>'01/19/1989',
		'address'=>'123 Fake St.',
		'contact_phone'=>'6131337667',
		'contact_email'=>'rjputins@uwaterloo.ca',
		'emerg_name'=>'Sister',
		'emerg_phone'=>'1337133756'
		);
	$a = new Admin;
	$t = new Treatment;
	$a->addPatient($data);
	$emps = $a->getAllPhysicians();
	$patients = $a->getAllPatients();
	$pid = array_search('BTEST1',$patients);
	
	$data = array('pid'=>$pid,'indate'=>date('m/d/Y'),'eidin'=>key($emps));
	$a->checkInPatient($data);
	
	$edt = array(
		'pid'=>$pid,
		'dateperf'=>'02/14/2010',
		'activitytype'=>'D',
		'enames'=>current($emps),
		'description'=>"Chocolate overdose",
		'duration'=>'1',
		'outcome'=>'Yum',
		'cost'=>33
		);
	$t->newEDT($edt);
}

// Clean up
function cleanAdmin() {
	$d = new Database;
	$d->delete('Patients',array('field'=>'pname','value'=>'TEST1'),1);
	$d->delete('Patients',array('field'=>'pname','value'=>'TEST2'),1);
}

// Connect to the database test
function runConnect() {
	global $results;
	$results['connectToDB'] = (bool) new Database;
}	

// New EDT record, Get EDT records
// At least one patient, with PID = 1
function runTreatment() {
	global $results;
	
	$t = new Treatment;
	$a = new Admin;
	$patients = $a->getAllPatients();
	$emps = $a->getAllPhysicians();
	
	// New
	$pid = key($patients);
	$eidin = key($emps);
	$moo = array(
		'pid'=>$pid,
		'dateperf'=>'11/30/2010',
		'activitytype'=>'E',
		'enames'=>current($emps),
		'description'=>"Today I did things",
		'duration'=>'1',
		'outcome'=>'Yay?',
		'cost'=>100
		);
	// Pre-req: Checked In.
	$data = array('pid'=>$pid,'indate'=>date('m/d/Y'),'eidin'=>$eidin);
	$results['admin_checkInPatient'] = ($a->checkInPatient($data));
	
	// New EDT Record
	$results['treatment_newEDT'] = ($t->newEDT($moo));
	
	// Get EDT Records
	$results['treatment_getEDTs'] = ($t->getEDTRecords( $pid ));
	
	$db = new Database;
	$db->delete('EDTRecords',array('field'=>'pid','value'=>$pid),1);
	
	return (array_search(false,$results));
}

function runBilling() {
	global $results;
	$b = new Billing;
	$t = new Treatment;
	$a = new Admin; // For getting list of patients
	$patients = $a->getAllPatients();
	$pid = array_search('BTEST1',$patients);
	
	// Create billing account
	// Prereq: Patient exists (uses PTEST1)
	$new = array(
				'pid'=>$pid,
				'insname'=>'test',
				'insacct'=>'1337'
				);
	$results['billing_newAcct'] = ($b->addAccount($new));
	
	// Update Billing account
	// Prereq: Billing account exists
	$new = array(
			'pid'=>$pid,
			'insname'=>'MooCows Inc.'
			);
	$results['billing_updateAcct'] = ($b->updateAccount($new));
	
	// Get Bill
	// Prereq: Billing account exists.
	$results['billing_getBill'] = ($b->getBill($pid));
	
	//Process EDT record
	//Prereq: Unprocessed EDT record exists for patient, billing account exists.
	$edts = $t->getUnprocessedEDTs($pid);
	$edt = $edts[0]['edtid'];
	$results['billing_processEDT'] = ($b->processEDT($edt));
	
	//Receive a payment
	//Prereq: Billing account exists.
	$results['billing_receivePayment'] = ($b->receivePayment(array('pid'=>$pid,'amnt'=>10)));
	
	// Close Account
	// Prereq: Account exists. 
	// 		- For testing purposes, balance should be negative as to avoid two-stage process (manually tested).
	$results['billing_closeAccount'] = ($b->closeAccount($pid));
	
}

function runAdmin() {
	global $results;
	$a = new Admin;
	
	// New patient
	$data = array(
			'pname'=>'TEST1',
			'dob'=>'01/01/1989',
			'address'=>'123 Fake St.',
			'contact_phone'=>'5197817372',
			'contact_email'=>'h4xnoodle@gmail.com',
			'emerg_name'=>'Tori',
			'emerg_phone'=>'6138367372'
			);
	$data2 = array(
			'pname'=>'TEST2',
			'dob'=>'01/01/1989',
			'address'=>'123 Fake St.',
			'contact_phone'=>'5197817372',
			'contact_email'=>'h4xnoodle@gmail.com',
			'emerg_name'=>'Tori',
			'emerg_phone'=>'6138367372'
			);
	$results['admin_newPatient'] = ($a->addPatient( $data ));
	$a->addPatient( $data2 );
	
	// Try to add a patient that already exists
	$attempt = array('pname'=>'TEST1');
	$results['admin_newPatientNot'] = (!$a->addPatient($attempt));
	
	$patients = $a->getAllPatients();
	$emps = $a->getAllPhysicians();
	$pid = array_search('TEST1',$patients);
	
	// Update patient
	// Prereq: Patient exists
	$newData = array(
			'pid'=>$pid,
			'address'=>'123 Updated St.',
			'contact_phone'=>'5197817372',
			'contact_email'=>'h4xnoodle@gmail.com'
			);
	$where = array('field'=>'pid','value'=>$pid);
	$results['admin_updatePatient'] = ($a->updatePatient($newData));
	
	// Check in patient
	// Prereq: Checked in patient
	$a->checkInPatient(array('pid'=>array_search('TEST2',$patients),'indate'=>date('m/d/Y'),'eidin'=>key($emps)));
	$results['admin_datePatients'] = ($a->getPatientsOnDate(date('m/d/Y')));
	
	// Get patient vists
	// Prereq: At least 1 EDT record for patient
	$t = new Treatment;
	$edt = array(
		'pid'=>$pid,
		'dateperf'=>'11/30/2010',
		'activitytype'=>'E',
		'enames'=>current($emps),
		'description'=>"Today I did things",
		'duration'=>'1',
		'outcome'=>'Yay?',
		'cost'=>100
		);
	$results['admin_patientVisits'] = ($t->newEDT($edt) && $a->getPatientVisits($pid));
	
	// Get patients based on physician
	// Prereq: Checked in patient
	$results['admin_physicianReleasedAdmitted'] = ($a->getPhysiciansPatients(key($emps)));
	
	// Check out a patient
	// Prereq: Checked in patient
	$results['admin_checkOutPatient'] = ($a->checkOutPatient(array('pid'=>array_search('TEST2',$patients),'outdate'=>date('m/d/Y'),'eidout'=>key($emps))));
}

// Run!
runConnect();
runAdmin();
runTreatment();

populateBilling(); // Adds a patient with unprocess EDT records
runBilling();

cleanAdmin();

// RESULTS

// A lot failed - likely the DB isn't setup
$failed = count(array_keys($results,false));
if( $failed > 10 ) {
	echo "<p class='notice'>A lot of tests failed, have the tables been created? (Those errors above are a result from attempting to run the tests.)</p>";
	return;
} elseif( $failed <= 8 && $failed > 3 ) {
	echo "<p class='notice'>Some tests that rely on an employee being present have failed. Have you inserted an employee? Click the link above to do so :)</p>";
}

echo "<table>";
echo "<tr><th>Name</th><th>Result</th></tr>";
foreach( $results as $n=>$r ) {
	echo "<tr><td>".$n."</td>";
	echo ( $r ? "<td style='background:green;color:white;'>Pass</td>" : "<td style='background:red;color:white;'>Fail</td>" );
}
echo "</table>";

?>