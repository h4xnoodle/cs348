<?php 

/*	================================================

	Author:	Rebecca Putinski
	UWID:	20271463
	Class:	CS348 Section 3
	
	This file:
		The main index page. Displays most of the
		user-facing output.
		
	================================================
*/

include('common.php');			// Forms and other common functions
include('treatment.class.php');	// Treatment operations
include('admin.class.php');		// Admin operations
include('billing.class.php');	// Billing operations
include('header.php');			// Header formatting/output

// Decide which page to be on
switch( key($_GET) ) { 

	// Administrative actions
	case 'admin': 
		$items = array(
				'addPatient'=>'Add',
				'updatePatient'=>'Update',
				'checkInOut'=>'Check In/Out',
				'getPatientInfo'=>'Patient Info'
				);
		buildSubMenu( $items );
		?>
		<h2>Administration Actions</h2>
		<p>Here an administrator can manage patients, and check patients in and out of the hospital.</p>
		<a name="addPatient"></a>
		<h2>Add a Patient</h2>
		<?php 
			$form = array(
					'form_action'=>'addPatient',
					'submit_text'=>'Register Patient',
					'pname'=>"Patient Name",
					'dob'=>"Date of Birth (MM/DD/YYYY)",
					'address'=>"Address",
					'contact_phone'=>"Contact Phone Number",
					'contact_email'=>"Contact Email",
					'emerg_name'=>"Emergency Contact Name",
					'emerg_phone'=>"Emergency Contact Phone"
					);
			buildForm( $form );
		?>
		<a name="updatePatient"></a>
		<h2>Update a Patient</h2>
		<p>Fields left blank will remain the same.</p>
		<?php 
			$a = new Admin;
			$allPatients = $a->getAllPatients();
			if( !$allPatients ) echo "<p class='notice'>There are no patient files! Please create one.</p>";
			$form = array(
					'form_action'=>'updatePatient',
					'submit_text'=>'Update Patient',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$allPatients),
					'dob'=>"Date of Birth (MM/DD/YYYY)",
					'address'=>"Address",
					'contact_phone'=>"Contact Phone Number",
					'contact_email'=>"Contact Email",
					'emerg_name'=>"Emergency Contact Name",
					'emerg_phone'=>"Emergency Contact Phone"
					);
			buildForm( $form );
		?>	
		<a name="checkInOut"></a>
		<h2>Patient Check In/Out</h2>
		<p>Check in:</p>
		<?php 
			$allPhysicians = $allPatients = array();
			$date = date('m/d/Y');
			$a = new Admin;
			$allPatients = $a->getAllPatients();
			if( !$allPatients ) echo "<p class='notice'>There are no patient files! Please create one.</p>";
			$allPhysicians = $a->getAllPhysicians();
			if( !$allPhysicians ) echo "<p class='notice'>There are no physicians working at the hospital!</p>";
			$form = array(
					'form_action'=>'checkIn',
					'submit_text'=>'Check In Patient',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$allPatients),
					'_dropdown2'=>array('label'=>'Physician','name'=>'eidin',$allPhysicians),
					'indate'=>array('label'=>'Date (MM/DD/YYYY)','value'=>$date)
					);
			buildForm( $form );
		?>
		<p>Check out:</p>
		<?php 
			$allPatients = array();
			$date = date('m/d/Y');
			$a = new Admin;
			$allPatients = $a->getCurrentPatients();
			if( !$allPatients ) echo "<p class='notice'>There are currently no patients to check out.</p>";
			if( !$allPhysicians ) echo "<p class='notice'>There are no physicians working at the hospital!</p>";
			$form = array(
					'form_action'=>'checkOut',
					'submit_text'=>'Check Out Patient',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$allPatients),
					'_dropdown2'=>array('label'=>'Physician','name'=>'eidout',$allPhysicians),
					'outdate'=>array('label'=>'Date (MM/DD/YYYY)','value'=>$date)
					);
			buildForm( $form );
		?>
		
		<a name="getPatientInfo"></a>
		<h2>Get Patient Visits</h2>
		<p>Display all visits from a patient.</p>
		<?php
			$a = new Admin;
			$allPatients = $a->getAllPatients();
			if( !$allPatients )
				echo "<p class='notice'>There are no patients at the hospital!</p>";
			$form = array(
					'form_action'=>'getPatientVisits',
					'submit_text'=>'Get Information',
					'_dropdown1'=>array('label'=>"Patient Name",'name'=>"pid",$allPatients),
					);
			buildForm( $form );
		?>
		
		<h2>Get Patients admitted/released on a Specific Date</h2>
		<p>Display patients released/admitted on a specific date.</p>
		<?php
			$date = date('m/d/Y');
			$form = array(
					'form_action'=>'getPatientsOnDate',
					'submit_text'=>'Get Patients',
					'date'=>array('label'=>'Date (MM/DD/YYYY)','value'=>$date),
					);
			buildForm( $form );
		?>
		<h2>Get Patients Admitted/Released By Physcian</h2>
		<p>Display patients admitted/released by a specific physician.</p>
		<?php
			$a = new Admin;
			$allPhysicians = $a->getAllPhysicians();
			if( !$allPhysicians ) {
				echo "<p class='notice'>No Physicians work at the hospital!</p>";
			}
			$form = array(
					'form_action'=>'getPatientsByPhysician',
					'submit_text'=>'Get Patients',
					'_dropdown1'=>array('label'=>"Physician",'name'=>'ename',$allPhysicians),
					);
			buildForm( $form );
		?>
	
		<?php 
		break; 
		
		// Treatment actions
		case 'treatment': 
		$items = array(
				'newEDT'=>"New EDT Record",
				'getEDT'=>"Get EDT Records"
				);
		buildSubMenu( $items );
		?>
		<a name="newEDT"></a>
		<h2>Create an EDT Record for a Patient</h2>
		<p>Select a patient from the dropdown box and fill in the other fields. All fields must be filled.</p>
		<?php
			$a = new Admin;
			$currentPatients = $a->getCurrentPatients();
			if( !$currentPatients ) echo "<p class='notice'>There are currently no patients visiting the hospital!</p>";
			$activitytypes = $a->getActivityTypes();
			$form = array(
				'form_action'=>'newEDT',
				'submit_text'=>'Create',
				'_dropdown1'=>array('label'=>'Patient','name'=>'pid', $currentPatients),
				'dateperf'=>array('label'=>'Date (MM/DD/YYYY)','value'=>date('m/d/Y')),
				'_dropdown2'=>array('label'=>'Activity Type','name'=>'activitytype',$activitytypes),
				'enames'=>'Physician Names (sep. by comma)',
				'_textbox1'=>array('label'=>'Description','name'=>'description'),
				'_textbox2'=>array('label'=>'Outcome','name'=>'outcome'),
				'duration'=>'Duration (eg 1.5 = 1:30hrs)',
				'cost'=>'Cost $'
				);
			buildForm( $form );
		?>
		
		<a name="getEDT"></a>
		<h2>Retrieve EDT Records</h2>
		<p>Choose a patient and retrieve EDT records. Decide whether for all visits or just current/active visit.</p>
		<?php
			$a = new Admin;
			$allPatients = $a->getAllPatients();
			$form = array(
				'form_action'=>'getEDT',
				'submit_text'=>'Retrieve',
				'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$allPatients),
				'_dropdown2'=>array('label'=>'Option','name'=>'option',array('0'=>'All visits','current'=>'Current Visit Only'))
				);
			buildForm( $form );
		?>
	
		<?php 
		break; 
		
		// Billing actions
		case 'billing': 
		$items = array(
			'addAccount'=>'Add',
			'updateAccount'=>'Update',
			'processEDTs'=>'Process EDTs',
			'receivePayment'=>'Receive Payment',
			'closeAccount'=>'Close account',
			'getBill'=>'Get Bill'
			);
		buildSubMenu($items); ?>
		<a name='addAccount'></a>
		<h2><?php echo $items['addAccount']; ?></h2>
		<p>Data such as date of birth, contact information, and address are implicitly added from the patients' existing file.</p>
		<?php
			$b = new Billing;
			$noAcc = $b->getPatients( false ); // Patients without accounts
			if( !$noAcc ) echo "<p class='notice'>All patients have billing accounts.</p>";
			$form = array(
					'form_action'=>'addAccount',
					'submit_text'=>'Create Billing Account',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$noAcc),
					'insname'=>'Insurance Provider',
					'insacct'=>'Insurance Account #',
					'insaddress'=>'Insurance Provider Addr.'
					);
			buildForm($form);
		?>
		<a name='updateAccount'></a>
		<h2><?php echo $items['updateAccount']; ?></h2>
		<p>Fields left blank will remain the same.</p>
		<?php
			$b = new Billing;
			$acc = $b->getPatients( true ); // Patients with accounts only
			if( !$acc ) echo "<p class='notice'>There are no patients with billing accounts.</p>";
			$form = array(
					'form_action'=>'updateAccount',
					'submit_text'=>'Update Billing Account',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$acc),
					'insname'=>'Insurance Provider',
					'insacct'=>'Insurance Account #',
					'insaddress'=>'Insurance Provider Addr.'
					);
			buildForm($form);
		?>
	
		<a name='processEDTs'></a>
		<h2>Process EDTs</h2>
		<p>Select a patient to being processing EDT records for. You will then have the option to process all records or process them individually.</p>
		<?php
			$a = new Admin;
			$allPatients = $a->getAllPatients();
			$form = array(
					'form_action'=>'processEDTDisplay',
					'submit_text'=>'Begin Processing',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$allPatients),
					);
			buildForm($form);
		?>
		
		<a name='closeAccount'></a>
		<h2>Close an Account</h2>
		<p>You will need to confirm for patients with positive balances. This action will remove all records for this patient in all modules.</p>
		<?php 
			$b = new Billing;
			$acc = $b->getPatients( true ); // Patients with accounts only
			if( !$acc ) echo "<p class='notice'>There are no patients with billing accounts.</p>";
			$form = array(
					'form_action'=>'closeAccount',
					'submit_text'=>'Close Billing Account',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$acc)
					);
			buildForm($form);
		?>
		
		<a name='receivePayment'></a>
		<h2><?php echo $items['receivePayment']; ?></h2>
		<p>The payment received will be added to the negative balance on the patient's account.</p>
		<?php 
			$b = new Billing;
			$patients = $b->getPatients(true);
			if( !$patients ) echo "<p class='notice'>There are no patients with billing accounts.</p>";
			$form = array(
					'form_action'=>'receivePayment',
					'submit_text'=>'Update Bill',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$patients),
					'amnt'=>array('label'=>'Amount $','value'=>0)
					);
			buildForm($form);
		?>
		
		<a name='getBill'></a>
		<h2><?php echo $items['getBill']; ?></h2>
		<p>View the current bill for the patient.</p>
		<?php 
			$b = new Billing;
			$patients = $b->getPatients(true);
			if( !$patients ) echo "<p class='notice'>There are no patients with billing accounts.</p>";
			$form = array(
					'form_action'=>'getBill',
					'submit_text'=>'Get Bill',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$patients)
					);
			buildForm($form);
		?>		
		
	
		<?php 
		break; 
		
		// Default index page
		default: 
			$items = array(
					'readme'=>"Read Me",
					'tests'=>"Run Tests"
					);
			buildSubMenu( $items );
		?>
		<h2>Hello!</h2>
		<p>Welcome to the hospital management system project!</p>
		<h2>Read Me</h2>
		<p>You can create the tables required with the createTables.sql file manually in DB2 Express C. This is also the updated Db2 DDL file for this part of the project.</p>
		<p>To use the functions of the hospital management system, choose an option from the above menu. Then, navigate to an appropriate sub-action.</p>
		<p>To insert some physicians into the database to use some functionality, click <a href='process.php?insertEmployees'>here</a>.</p>
		<p>You can edit the database connection credentials in config.php.</p>
		
		<a name='tests'></a>
		<h2>Perform Tests</h2>
		<p>These test the basic functionality of the system's functions.</p>
		<?php include('tests.php'); ?>

<?php 
} 
include('footer.php');
?>