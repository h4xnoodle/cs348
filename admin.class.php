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
	
	private function UDTInsert( $table, $data ) {
		$values = array(); $keys = array();
		$q = "INSERT INTO ".$table."(";
		foreach( $data as $k=>$v ) {
			switch( $k ) {
				case 'contact_email':
					$keys[] = 'contact';
					$values[] = " ..email('".$v."'),";
					break;
				case 'contact_phone':
					$values[] = "ContactInfo_t() ..phone('".$v."')";
					break;
				case 'emerg_name':
					$keys[] = 'emergcontact';
					$values[] = "EmergContact_t() ..ename('".$v."')";
					break;
				case 'emerg_phone':
					$values[] = " ..ephone('".$v."'),";
					break;
				default:
					$keys[] = $k;
					$values[] = "'".$v."',";
			}
		}
		reset($keys); reset($values);

		$q .= current($keys);
		while( $k = next($keys) )
			$q .= ",".$k;
		$q .= ") VALUES(";
		$q .= current($values);
		while( $v = next($values) )
			$q .= $v;
		$q = substr($q,0,-1);
		$q .= ")";
		return $this->dbh->directInsert($q);
	}
	
	// Data entry
	// $args: pname,dob,address,contact_phone,contact_email,emerg_phone,emerg_name
	public function addPatient( $args ) {
		if( $this->isPatient($args['pname']) ) return false;
		return $this->UDTInsert('Patients',$args);
	}
	
	public function updatePatient( $args ) {
		if( !$this->isPatient($args['pname']) ) return false;
		return $this->UDTUpdate('Patients',$args);
	}
	
	public function checkInPatient( $args ) {}
	
	public function checkOutPatient( $args ) {}
	
	// Query functions
	public function getCurrentPatients() {
		return $this->getAllPatients( $options='current' );
	}
	
	public function getAllPatients( $options=false ) {
		$results = array();
		$q = "SELECT pid,pname FROM Patients";
		
		if( $options == 'current' ) {
		
			$q .= " WHERE pid IN(" .
				"SELECT pid FROM CheckInOuts WHERE outdate IS NULL) ";
		
		// A date for getAdmittedPatients
		} elseif ( $options ) {
			
			$q .= " WHERE pid IN(" .
					"SELECT pid FROM CheckInOuts WHERE outdate = '".$date."' AND indate = '".$date."'";
		}
		$q .= " ORDER BY pname DESC";
		$result = $this->dbh->query($q);
		if( $result ) {
			foreach( $result as $r )
				$results[$r['pid']] = $r['pname'];
		}
		return $results;
	}
	
	public function getActivityTypes() {
		return array('E'=>"Examination",'D'=>"Diagnosis",'T'=>"Treatment");
	}
	
	// YYYY-MM-DD
	public function getPatientsOnDate( $date ) {
		return $this->getAllPatients( $date );
	}
	
	public function getAllPhysicians() {
		$results = array();
		$q = "SELECT eid,ename FROM Employees WHERE eid IN(
				SELECT eid FROM EmployeeJobs WHERE jid IN(
					SELECT jid FROM Jobs WHERE jtype = 'C')))";
		$result = $this->dbh->query($q);
		if( $result ) {
			foreach( $result as $r ) {
				$results[$r['eid']] = $r['ename'];
			}
		}
		return $results;
	}
	
	public function getPatientVisits( $pid ) {}
	
	public function getPhysiciansPatients( $eid ) {}
	
	// Display functions
	public function displayAdmittedPatients( $data ) {}
	
	public function displayPatientVisits( $data ) {}
	
	public function displayPhysiciansPatients( $data ) {}
	
}
?>