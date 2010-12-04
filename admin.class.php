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
	
	private function isPatient( $p ) {
		$q = "SELECT pid FROM Patients WHERE ";
		if( is_numeric( $p ) )
			$q .= "pid = ".$p;
		else
			$q .= "pname = '".$p."'";
		return ($this->dbh->query($q));
	}

	private function isCheckedIn( $p ) {
		$q = "SELECT cid FROM CheckInOuts WHERE pid = ".$p." AND outdate IS NULL";
		return ($this->dbh->query($q) ? true : false);
	}
	
	private function UDTUpdate( $table, $data, $where=false ) {
		$values = array(); $keys = array();
		$UDTMagic = array(false,false);
		$check = false; // Only execute update if at least one value is to be changed
		$q = "UPDATE ".$table." SET ";
		foreach( $data as $k=>$v ) {
			if( $v == "" ) continue;	// Values could be blank
			$check = true; 
			switch( $k ) {
				case 'contact_email':
					if( !$UDTMagic[0] ) {
						$keys[] = 'contact';
						$values[] = "ContactInfo_t() ..email('".$v."')";
						$UDTMagic[0] = true;	// We've seen contact now
					} else {	
						$values[] = " ..email('".$v."')";
					}
					break;
				case 'contact_phone':
					if( !$UDTMagic[0] ) {
						$keys[] = 'contact';
						$values[] = "ContactInfo_t() ..phone('".$v."')";
						$UDTMagic[0] = true;	// We've seen contact now
					} else {	
						$values[] = " ..phone('".$v."')";
					}
					break;
				case 'emerg_name':
					if( !$UDTMagic[1] ) {
						$keys[] = 'emergcontact';
						$values[] = "EmergContact_t() ..ename('".$v."')";
						$UDTMagic[1] = true; // We've seen emergcontact now
					} else {
						$values[] = " ..ename('".$v."')";
					}
					break;
				case 'emerg_phone':
					if( !$UDTMagic[1] ) {
						$keys[] = 'EmergContact_t..ephone';
						$values[] = "EmergContact_t..ephone('".$v."')";
						$UDTMagic[1] = true;
					} else {
						$values[] = " ..ephone('".$v."')";
					}
					break;
				default:
					$keys[] = $k;
					$values[] = "'".$v."'";
			}
		}
		reset($keys); reset($values);

		do {
			$q .= current($keys)."=".current($values).",";
		} while( next($keys) && next($values) );
		$q = substr($q,0,-1); // Remove trailing comma
		if( $where ) {
			if( !array_key_exists('op',$where) ) $where['op'] = '=';
			$q .= " WHERE ".$where['field']." ".$where['op']." '".$where['value']."'";
		}
echo $q;exit;
		return (!$check || $this->dbh->directInsert($q));
	}
	
	private function UDTInsert( $table, $data ) {
		$values = array(); $keys = array();
		$data['dob'] = date('Y-m-d',strtotime($data['dob']));
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
	
	public function addPatient( $data ) {
		if( $this->isPatient($data['pname']) || !checkFilled($data) || 
			!verifyDate($data['dob'])) return false;
		return $this->UDTInsert('Patients',$data);
	}
	
	public function updatePatient( $data ) {
		if( !$this->isPatient($data['pid']) || ($data['dob'] && !verifyDate($data['dob'])) ) 
			return false;
		$where = array('field'=>'pid','value'=>$data['pid']);
		unset($data['pid']); // remove pid
		return $this->UDTUpdate('Patients',$data,$where);
	}
	
	public function checkInPatient( $data ) {
		if( !checkFilled($data) || !verifyDate($data['indate']) ||
			!checkNumber($data['pid']) || !checkNumber($data['eidin']) ||
			!$this->isPatient($data['pid']) )
			return false;
		if( $this->isCheckedIn($data['pid']) ) return true;
		return ($this->dbh->insert('CheckInOuts',$data));
	}
	
	public function checkOutPatient( $data ) {
		if( !$this->isPatient( $data['pid'] ) || !checkFilled($data) || 
			!verifyDate($data['outdate']) || !checkNumber($data['eidout']) || 
			!checkNumber($data['pid']) || !$this->isCheckedIn($data['pid'])) 
			return false;
		$where = array('field'=>'pid','value'=>$data['pid']);
		
		// Add up costs of EDT records for that visit
		$total = 0;
		$t = new Treatment;
		$edts = $t->getEDTRecords($data['pid'],true);
		foreach( $edts as $e ) {
			$total = $total + $e['cost'];
		}
		$data['totalbill'] = $total;
		unset($data['pid']); // remove pid from elements
		return ($this->dbh->update('CheckInOuts',$data,$where,true));
	}
	
	// Query functions
	public function getAllPatients( $options=false ) {
		$results = array();
		if( $options && $options != 'current' )
			$q = "SELECT P.pid, P.pname, C.indate, C.outdate, C.eidin, C.eidout FROM Patients P, CheckInOuts C";
		else 
			$q = "SELECT pid,pname FROM Patients";
		
		if( $options == 'current' ) {
		
			$q .= " WHERE pid IN(" .
				"SELECT pid FROM CheckInOuts WHERE outdate IS NULL) ";
		
		} elseif ( $options ) {
			
			$q .= " WHERE P.pid IN(" .
					"SELECT pid FROM CheckInOuts WHERE outdate = '".$options."' OR indate = '".$options."')";
		}
		$q .= " ORDER BY pname DESC";
		$result = $this->dbh->query($q);
		if( $result ) {
			if( !$options || $options == 'current' ) {
				foreach( $result as $r )
					$results[$r['pid']] = $r['pname'];
			} else {
				$results = $result;
			}
		}
		return $results;
	}
	
	public function getCurrentPatients() {
		return $this->getAllPatients( $options='current' );
	}
	
	public function getAllPhysicians() {
		$results = array();
		$q = "SELECT eid,ename FROM Employees WHERE eid IN(
				SELECT eid FROM EmployeeJobs WHERE jid IN(
					SELECT jid FROM Jobs WHERE jtype = 'C'))";
		$result = $this->dbh->query($q);
		if( $result ) {
			foreach( $result as $r ) {
				$results[$r['eid']] = $r['ename'];
			}
		}
		return $results;
	}
	
	public function getActivityTypes() {
		return array('E'=>"Examination",'D'=>"Diagnosis",'T'=>"Treatment");
	}
	
	public function getPatientsOnDate( $date ) {
		return ($this->getAllPatients($date));
	}
	
	public function getPatientVisits( $pid ) {
		$q = "SELECT P.pid,P.pname,C.indate,C.outdate,C.totalbill FROM Patients P, CheckInOuts C
				WHERE P.pid = ".$pid;
		return ($this->dbh->query($q));
	}
	
	public function getPhysiciansPatients( $eid ) {
		$q = "SELECT P.pid, P.pname, C.indate, C.outdate FROM Patients P 
			  JOIN CheckInOuts C ON P.pid = C.pid
			  WHERE C.eidin = ".$eid." OR
				C.eidout = ".$eid;
		return ($this->dbh->query($q));
	}
	
	// Display functions
	private function display( $title, $headers, $data ) {
		echo "<h2>".$title."</h2>";
		if( !$data ) {	
			echo "<p class='notice'>Nothing to display.</p>";
			return;
		}
		echo "<table>";
		echo "<tr>";
		foreach($headers as $h)
			echo "<th>".$h."</th>";
		echo "</tr>";
		$moo = 0;
		foreach( $data as $record ) {
			echo "<tr".($moo % 2 ? "class='odd'" : "").">";
			foreach( $record as $k=>$field ) {
				echo "<td>".$field."</td>";
			}
			echo "</tr>";
			$moo++;
		}
		echo "</table>";
	
	}
	
	public function displayPatientsOnDate( $date ) {
		$allPhysicians = $this->getAllPhysicians();
		$data = $this->getPatientsOnDate($date);
		$title = "Patients Admitted/Released on ".$date;
		$headers = array('Patient ID','Name','Physician','Action');
		$adjusted = array();
		foreach( $data as $record ) {
			$record['physician'] = ($record['indate'] ? $allPhysicians[$record['eidin']] : $allPhysicians[$record['eidin']]);
			$record['action'] = ($record['indate'] ? "Admitted" : "Released");
			unset($record['indate'],$record['outdate'],$record['eidin'],$record['eidout']);
			$adjusted[] = $record;
		}
		$this->display($title,$headers,$adjusted);
	}
	
	public function displayPatientVisits( $pid ) {
		$data = $this->getPatientVisits($pid);
		$title = "Patient Visits for ".($data ? $data[0]['pname'] : "N/A");
		$headers = array('ID','Name','Admittance Date','Release Date','Billing Total');
		$adjusted = array();
		foreach( $data as $record ) {
			$record['indate'] = date('m/d/Y',strtotime($record['indate']));
			if($record['outdate'] != '') 
				$record['outdate'] = date('m/d/Y',strtotime($record['outdate']));
			else
				$record['outdate'] = "N/A";
			$record['totalbill'] = "$".$record['totalbill'];
			$adjusted[] = $record;
		}
		$this->display($title,$headers,$adjusted);
	}
	
	public function displayPhysiciansPatients( $eid ) {
		$allPhysicians = $this->getAllPhysicians();
		$data = $this->getPhysiciansPatients($eid);
		$title = "Patients Admitted/Released by ".$allPhysicians[$eid];
		$headers = array('Patient ID','Name','Date','Action');
		$adjusted = array();
		foreach( $data as $record ) {
			$record['date'] = ($record['indate'] ? date('m/d/Y',strtotime($record['indate'])) : date('m/d/Y',strtotime($record['outdate'])));
			$record['action'] = ($record['indate'] ? "Admitted" : "Released");
			unset($record['indate'],$record['outdate']);
			$adjusted[] = $record;
		}
		$this->display($title,$headers,$adjusted);
	}
	
}
?>