<?php

// Treatment class
// Creation of EDT records

require_once('database.class.php');

class Treatment {

	private $dbh;
	
	function __construct() {
		try {
			$this->dbh = new Database();
		} catch( Exception $e ) {
			die("Could not perform Admin action. Message: <br><br>".$e->getMessage());
		}
	}
	
	private function isPatient( $pid ) {
		$q = "SELECT pid FROM Patients WHERE pid = ".$pid;
		$result = $this->dbh->query($q);
		if( $result ) return true;
		return false;
	}
	
	// Data entry
	// $data: pid,dateperf,activitytype,enames,description,duration,outcome,cost
	// Check: Open file for patient (pid valid)
	public function newEDT( $data ) {
		if( !$this->isPatient( $data['pid'] ) ) return false;
		return ($this->dbh->insert('EDTRecords',$data));
	}
	
	// Query
	public function getEDTRecords( $pid, $option=false ) {
		if( !$this->isPatient( $pid ) ) return false;
		$q = "SELECT dateperf,activitytype,enames,description,duration,outcome " .
				"FROM EDTRecords WHERE pid = '".$pid."'";
		
		// Print out entries for current visit only
		// 'biggest' checkin and later.
		if( $option ) {
			$q .= " AND ...";
		}
		$result = $this->dbh->query($q);
		if( $result ) return $result;
		return false;
	}
	
	// Display
	public function displayEDTRecords( $data ) {
		if( !$data ) {	
			echo "Nothing to display.";
			return;
		}
		echo "<table>";
		echo "<tr><th>Date Performed</th><th>Activity Type</th><th>Names of Physicians</th><th>Description</th><th>Duration</th><th>Outcome</th>";
		foreach( $data as $record ) {
			echo "<tr>";
			foreach( $record as $k=>$field ) {
				if( is_numeric($k) )
					echo "<td>".$field."</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
}
?>