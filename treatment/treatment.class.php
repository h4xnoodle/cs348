<?php

// Treatment class
// Creation of EDT records

require('../database.class.php');

class Treatment {

	private $dbh;
	
	function __construct() {
		try {
			$this->dbh = new Database();
		} catch( Exception $e ) {
			die("Could not perform Admin action. Message: <br><br>".$e->getMessage());
		}
	}
	
	// Data entry
	public function newEDT() {}
	
	// Query
	public function getEDTRecords( $pid, $option ) {}
	
	// Display
	public function displayEDTRecords( $data ) {}
	
}
?>