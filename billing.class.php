<?php

// Billing class
// Allows creation, update, etc of billing accounts of patients

require_once('database.class.php');

class Billing {

	private $dbh;
	
	function __construct() {
		try {
			$this->dbh = new Database();
		} catch( Exception $e ) {
			die("Could not perform Billing action. Message: <br><br>".$e->getMessage());
		}
	}
	
	// Helpers
	private function billingAccountExist( $pid ) {
		$q = "SELECT bid FROM BillingAccounts WHERE pid = ".$pid;
		return ($this->dbh->query($q) ? true : false); 
	}
	
	public function getPatients( $option ) {
		$q .= "SELECT pid,pname FROM Patients";
		if( $option ) {
			$q .= " WHERE pid IN(SELECT pid FROM BillingAccounts)";
		} 
		$q .= " ORDER BY pname DESC";
		$result = $this->dbh->query($q);
		if( $result ) {
			foreach( $result as $r )
				$results[$r['pid']] = $r['pname'];
		}
		return $results;
	}

	// Data entry
	public function createBillingAccount( $data ) {}
	
	public function updateBillingAccount( $data ) {}

	public function receivePayment( $data ) {
		if( !billingAccountExit($data['pid']) ) return false;
		
	
	}
	
}

?>

CREATE TABLE BillingAccounts (
	pid INT NOT NULL UNIQUE,
	balance DECIMAL(10,2) DEFAULT 0,
	
	FOREIGN KEY(pid) REFERENCES Patients,
	PRIMARY KEY(pid),
	CONSTRAINT nonNeg
		CHECK(balance > 0)
);
	
