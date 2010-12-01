<?php

// TESTS
include('treatment.class.php');
include('admin.class.php');
include('billing.class.php');

$results = array();
$toRun = 'all';

// Data for test purposes
function populateTreatment() {}
function populateBilling() {}
function populateAdmin() {}

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
	$results['a_newPatient'] = false;
	$results['a_updatePatient'] = false;
	$results['a_registerVisit'] = false;
	$results['a_checkOutPatient'] = false;
	$results['a_datePatients'] = false;
	$results['a_patientVisits'] = false;
	$results['a_physicianReleasedAdmitted'] = false;
}

switch( $toRun ) {
	case 'all' :
		runTreatment();
		runBilling();
		runAdmin();
		break;
	case 'treatment' :
		runTreatment();
		break;
	case 'patient' :
		runAdmin();
		break;
	case 'billing' :
		runBilling();
		break;
	default:;
}

// Output
echo "<h1>Tests</h1><p>Running teeesstttsss</p>";
echo "<table>";
echo "<tr><th>Name</th><th>Result</th></tr>";
foreach( $results as $n=>$r ) {
	echo "<tr><td>".$n."</td>";
	echo ( $r ? "<td style='background:green;color:white;'>Pass</td>" : "<td style='background:red;color:white;'>Fail</td>" );
}
echo "</table>";

?>