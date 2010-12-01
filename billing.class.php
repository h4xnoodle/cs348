<?php

// Billing class
// Allows creation, update, etc of billing accounts of patients

//require_once('../database.class.php');

class Billing {

	private $dbh;
	
	function __construct() {
		try {
			$this->dbh = new Database();
		} catch( Exception $e ) {
			die("Could not perform Admin action. Message: <br><br>".$e->getMessage());
		}
	}
	
	// Helpers
	private function billingAccountExist( $pid ) {}
	
	// Data entry
	public function createBillingAccount( $pid, $stuff ) {}
	
	public function updateBillingAccount( $pid, $stuff ) {}
	
}

?>