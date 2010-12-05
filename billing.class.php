<?php

/*	================================================

	Author:	Rebecca Putinski
	UWID:	20271463
	Class:	CS348 Section 3
	
	This file:
		API for billing actions. 
		Billing accounts for patients, billing
		specialists manage accounts.
		
	================================================
*/

require_once('database.class.php');
include('admin.class.php');
include('treatment.class.php');

class Billing {

	private $dbh;
	
	function __construct() {
		try {
			$this->dbh = new Database();
		} catch( Exception $e ) {
			die("Could not perform Billing action. Message: <br><br>".$e->getMessage());
		}
	}
	
	private function billingAccountExists( $pid ) {
		$q = "SELECT balance FROM BillingAccounts WHERE pid = ".$pid;
		$result = $this->dbh->query($q);
		return ($result ? $result[0]['balance'] : false); 
	}

	public function getPatients( $option = false ) {
		$results = array();
		$q = "SELECT pid,pname FROM Patients";
		if( $option ) {
			$q .= " WHERE pid IN(SELECT pid FROM BillingAccounts)";
		} else {
			$q .= " WHERE pid NOT IN(SELECT pid FROM BillingAccounts)";
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
	public function addAccount( $data ) {
		if( $this->billingAccountExists( $data['pid'] ) ) return false;
		return ($this->dbh->insert('BillingAccounts',$data));
	}
	
	public function updateAccount( $data ) {
		if( !$this->billingAccountExists($data['pid']) ) return false;
		$where = array('field'=>'pid','value'=>$data['pid']);
		unset($data['pid']);
		return ($this->dbh->update('BillingAccounts',$data,$where));
	}

	public function receivePayment( $data ) {
		$balance = $this->billingAccountExists($data['pid']);
		if( !$balance ) return false;
		$where = array('field'=>'pid','value'=>$data['pid']);
		$data['balance'] = $balance + $data['amnt'];
		unset($data['pid'],$data['amnt']);
		return ($this->dbh->update('BillingAccounts',$data,$where));
	}
	
	public function processEDT( $record ) {
		// Add EDT record's cost to balance on billing account
		$q = "SELECT P.pid,E.cost FROM EDTRecords E, PatientExaminations P WHERE P.edtid = ".$record;
		$temp = $this->dbh->query($q);
		if( !$temp ) return false;
		$where = array('field'=>'edtid','value'=>$record);
		if( !$this->receivePayment(array('pid'=>$temp[0]['pid'],'amnt'=>(-$temp[0]['cost']))) )
			return false;
		// Should probably use a transaction here, as balance could pass, processed could fail. 
		// DB2 doesn't really use transactions
		// Mark EDT record as processed
		$data['processed'] = 1;
		$where = array('field'=>'edtid','value'=>$record);
		if( !$this->dbh->update('PatientExaminations',$data,$where) ) return false;
		return true;
	}
	
	// Cascades through all other tables and deletes all patient information
	public function closeAccount( $pid ) {
		return ($this->dbh->delete('Patients',array('field'=>'pid','value'=>$pid)));
	}
	
	// Data query
	public function getBill( $pid ) {
		$q = "SELECT B.pid, P.dob, P.address, P.contact, P.pname, B.balance, B.insname, B.insacct, B.insaddress
			 FROM BillingAccounts B JOIN Patients P ON P.pid = B.pid WHERE P.pid = ".$pid;
		$result = $this->dbh->query($q);
		if( $result ) {
			$result = $result[0];
		}
		return $result;
	}
	
	// Display
	public function displayBill( $pid ) {
		$bill = $this->getBill($pid);
		$a = new Admin;
		$allPatients = $a->getAllPatients();
		echo "<h2>Bill for ".($pid ? $allPatients[$pid] : "N/A")."</h2>";
		if( !$bill ) {	
			echo "<p class='notice'>Nothing to display.</p>";
			return;
		}
		echo "<p><b>ID / Name</b>: ".$bill['pid']." / ".$bill['pname']."</p>";
		echo "<p><b>D.O.B.</b>: ".$bill['dob']."</p>";
		echo "<p><b>Address:</b>: ".$bill['address']."</p>";
		$contact = array(0=>'',1=>'');
		$contact = explode(',',$bill['contact']);
		echo "<p><b>Contact Info</b>: Phone: ".(isset($contact[0]) ? $contact[0] : "")." Email: ".(isset($contact[1]) ? $contact[1] : "")."</p>";
		if( $bill['insname'] ) echo "<p><b>Insurance Provider</b>: ".$bill['insname']."</p>";
		if( $bill['insacct'] ) echo "<p><b>Insurance Account#</b>: ".$bill['insacct']."</p>";
		if( $bill['insaddress'] ) echo "<p><b>Insurance Provider Address</b>: ".$bill['insaddress']."</p>";
		echo "<p><b>Balance</b>: $<b class='balance ".($bill['balance'] < 0 ? "positive" : "negative")."'>".$bill['balance']."</b></p>";
	}
	
	public function displayUnprocessedEDTs( $edts ) {
		$a = new Admin;
		$activities = $a->getActivityTypes();
		$patients = $a->getAllPatients();
		echo "<h2>Process EDT Records for ".($edts ? $edts[0]['pname'] : "N/A")."</h2>";
		echo "<p>Click the 'Yes' beside each entry to process individually or click the button below to process all shown.</p>";
		if( !$edts ) {	
			echo "<p class='notice'>Nothing to display.</p>";
			return;
		}
		echo "<table><tr>";
		echo "<th>Process?</th><th>Name</th><th>Date Performed</th><th>Action</th><th>Physicians</th><th>Cost</th>";
		foreach($edts as $e) {
			unset($e['pid']);
			echo "<tr>";
			foreach( $e as $k=>$d ) {
				if( $k == 'activitytype' ) {
					echo "<td>".$activities[$d]."</td>";
				} elseif ( $k == 'duration' || $k == 'description' || $k == 'outcome' ) {
					continue; 
				} elseif ( $k == 'edtid' ) {
					echo "<td><a href='process.php?processEDT=".$d."'>Yes</a></td>";
				} else {
					echo "<td>".$d."</td>";
				}
			}
			echo "</tr>";
		}
		echo "</table>";
		$form = array(
				'form_action'=>'processAllEDTs',
				'submit_text'=>'Process All Records',
				'_hidden1'=>array('name'=>'pid','value'=>array_search($edts[0]['pname'],$patients))
				);
		buildForm($form);
	}
}
?>