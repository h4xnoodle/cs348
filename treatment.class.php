<?php

// Treatment class
// Creation of EDT records

require_once('database.class.php');
include('admin.class.php');
include('common.php');

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
		$q = "SELECT pname FROM Patients WHERE pid = ".$pid;
		$result = $this->dbh->query($q);
		if( $result ) return true;
		return false;
	}
	
	public function newEDT( $data ) {
		if( !$this->isPatient( $data['pid'] ) || !checkFilled($data) || 
			!verifyDate($data['dateperf']) || !checkNumber($data['cost']) || 
			!checkNumber($data['duration']) || !checkNumber($data['pid']) ) 
			return false;
		
		// Verify the physicians mentioned are valid
		$a = new Admin;
		$physicians = $a->getAllPhysicians();
		$enames = explode(',',$data['enames']);
		foreach( $enames as  $e) {
			if( !array_search(trim($e), $physicians) ) {
				printError("A physician specified in the Physician Names field does not exist.");
				return false;
			}
		}
		$success = true;
		$pid = $data['pid'];
		unset($data['pid']);
		if( !$this->dbh->insert('EDTRecords',$data) ) { 
			$success = false;
		} else {
			$result = $this->dbh->query('SELECT MAX(edtid) AS last FROM EDTRecords');
			print_r($result);
			$newEDT = $result[0]['last']; // Could potentially be bad, if the ordering of insertions between users interferes
		}
		if( !$this->dbh->insert('PatientExaminations',array('pid'=>$pid,'edtid'=>$newEDT)) )
			$success = false;
		return $success;
	}
	
	// Query
	public function getEDTRecords( $pid, $option=false ) {
		$result = false;
		if( !$this->isPatient( $pid ) || !checkNumber($pid) )
			return $result;
			
		$q = "SELECT P.edtid, A.pname,E.dateperf,E.activitytype,E.enames,E.description,E.duration,E.outcome, E.cost
			 FROM EDTRecords E,Patients A,PatientExaminations P WHERE A.pid = P.pid AND P.edtid = E.edtid AND P.pid = '".$pid."'";
		
		// Print out entries for current visit only if true
		if( $option == 'current' ) {
			$q .= " AND P.pid IN(SELECT pid FROM CheckInOuts WHERE outdate IS NULL)";
		} elseif ( $option == 'unprocessed' ) {
			$q .= " AND P.processed = 0";
		}
		$result = $this->dbh->query($q);
		return $result;
	}
	
	public function getUnprocessedEDTs( $pid ) {
		return $this->getEDTRecords($pid,'unprocessed');
	}
	
	// Display
	private function special($key, $value) {
		switch( $key ) {
			case 'activitytype':
				$a = new Admin;
				$activities = $a->getActivityTypes(); 
				return $activities[$value];
			case 'duration':
				return $value." hrs";
			case 'dateperf':
				return date('m/d/Y',strtotime($value));
			default: return $value;
		}
	}
	
	public function displayEDTRecords( $data ) {
		echo "<h2>EDT Records for ".$data[0]['pname']."</h2>";
		for($i=0;$i < count($data);$i++) unset($data[$i]['pname']);
		if( !$data ) {	
			echo "<p class='notice'>Nothing to display.</p>";
			return;
		}
		$moo = 0;
		echo "<table>";
		echo "<tr><th>Date</th><th>Activity</th><th>Physicians</th><th>Description</th><th>Duration</th><th>Outcome</th>";
		foreach( $data as $record ) {
			unset($record['cost'],$record['edtid']); // Don't display cost but we wanted cost for other uses from getEDTRecords().
			echo "<tr".($moo % 2 ? " class='odd'" : "").">";
			foreach( $record as $k=>$field ) {
				echo "<td>".$this->special($k,$field)."</td>";
			}
			echo "</tr>";
			$moo++;
		}
		echo "</table>";
	}
}
?>