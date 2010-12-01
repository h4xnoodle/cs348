<?php

// Admin class
// Patient administration

//require_once('../database.class.php');

class Admin {
	
	private $dbh;
	
	function __construct() {
		try {
			$this->dbh = new Database();
		} catch( Exception $e ) {
			die("Could not perform Admin action. Message: <br><br>".$e->getMessage());
		}
	}
	
	// Data entry
	public function addPatient( $args ) {}
	
	public function updatePatient( $args ) {}
	
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