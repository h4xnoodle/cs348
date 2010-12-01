<?php

// Admin class
// Patient administration

require_once('database.class.php');

class Admin {
	
	private $dbh;
	
	function __construct() {
		try {
			$this->dbh = new Database();
		} catch( Exception $e ) {
			die("Could not perform Admin action. Message: <br><br>".$e->getMessage());
		}
	}
	
	private function isPatient( $pname ) {
		$q = "SELECT pid FROM Patients WHERE pname = '".$pname."'";
		$result = $this->dbh->query($q);
		if( $result ) return true;
		return false;
	}
	
	// Data entry
	public function addPatient( $args ) {
		if( isPatient($args['pname']) ) return false;
		
	
	}
	
	public function updatePatient( $args ) {
		if( !isPatient($args['pname']) ) return false;
		
	}
	
	public function checkInPatient( $args ) {}
	
	public function checkOutPatient( $args ) {}
	
	// Query functions
	public function getAdmittedPatients( $date ) {}
	
	public function getPatientVisits( $pid ) {}
	
	public function getPhysiciansPatients( $eid ) {}
	
	// Display functions
	public function displayAdmittedPatients( $data ) {}
	
	public function displayPatientVisits( $data ) {}
	
	public function displayPhysiciansPatients( $data ) {}
	
}
?>