<?php

// TESTS
$results = array();
$toRun = 'all';

// Data for test purposes
function populateTreatment() {}
function populateBilling() {}
function populateAdmin() {
	
	// Persistent Test case for a_patientNot
	$data = array(
		'pname'=>'PTEST1',
		'dob'=>'1989-01-19',
		'address'=>'123 Fake St.',
		'contact_phone'=>'6131337667',
		'contact_email'=>'rjputins@uwaterloo.ca',
		'emerg_name'=>'Sister',
		'emerg_phone'=>'1337133756'
		);
	$a = new Admin;
	$a->addPatient($data);
}

// New EDT record, Get EDT records
// At least one patient, with PID = 1
function runTreatment() {
	global $results;
	populateTreatment();
	
	$t = new Treatment;
	// New
	$moo = array(
		'pid'=>1,
		'dateperf'=>'2010-11-30',
		'activitytype'=>'E',
		'enames'=>'Me',
		'description'=>"Today I did things",
		'duration'=>'1',
		'outcome'=>'Yay?',
		'cost'=>100
		);
	$results['t_newEDT'] = ($t->newEDT($moo) ? true : false);
	
	// Get
	$lala = $t->getEDTRecords( 1 );
	$results['t_getEDTs'] = ( $lala ? true : false );
	
	$db = new Database;
	$db->delete('EDTRecords',array('field'=>'pid','value'=>1),100);
}

function runBilling() {
	global $results;
	$results['b_newAcct'] = false;
	$results['b_updateAcct'] = false;
	$results['b_getRecords'] = false;
	$results['b_getRecords2'] = false;
	$results['b_recordPayment'] = false;
	$results['b_eraseRecords'] = false;
	$results['b_eraseRecords2'] = false;
	$results['b_getRecords'] = false;
}

function runAdmin() {
	global $results;
	$a = new Admin;
	populateAdmin();
	// New patient
	$data = array(
			'pname'=>'TEST1',
			'dob'=>'1989-01-01',
			'address'=>'123 Fake St.',
			'contact_phone'=>'5197817372',
			'contact_email'=>'h4xnoodle@gmail.com',
			'emerg_name'=>'Tori',
			'emerg_phone'=>'6138367372'
			);
	$results['a_newPatient'] = ($a->addPatient( $data ) ? true : false);
	
	$attempt = array('pname'=>'PTEST1');
	$results['a_newPatientNot'] = (!$a->addPatient($attempt) ? true : false);
	
	$results['a_updatePatient'] = false;
	$results['a_registerVisit'] = false;
	$results['a_checkOutPatient'] = false;
	$results['a_datePatients'] = false;
	$results['a_patientVisits'] = false;
	$results['a_physicianReleasedAdmitted'] = false;
	
	$db = new Database;
	$db->delete('Patients',array('field'=>'pname','value'=>'TEST1'),1);
}

//runTreatment();
//runBilling();
//runAdmin();

// Output
echo "<table>";
echo "<tr><th>Name</th><th>Result</th></tr>";
foreach( $results as $n=>$r ) {
	echo "<tr><td>".$n."</td>";
	echo ( $r ? "<td style='background:green;color:white;'>Pass</td>" : "<td style='background:red;color:white;'>Fail</td>" );
}
echo "</table>";

?>