<!-- Author: Rebecca Putinski 20271463 for CS348 -->

<?php 
include('common.php');
include('treatment.class.php');
include('admin.class.php');
include('billing.class.php');
include('header.php');

switch( key($_GET) ) { 
	case 'admin': 
		$items = array(
				'addPatient'=>'Add',
				'updatePatient'=>'Update',
				'getPatientInfo'=>'Patient Info',
				'checkInOut'=>'Check In/Out'
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
					'dob'=>"Date of Birth (YYYY-MM-DD)",
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
		<?php 
			$a = new Admin;
			$allPatients = $a->getAllPatients();
			if( !$allPatients ) echo "<p class='notice'>There are no patient files! Please create one.</p>";
			$form = array(
					'form_action'=>'updatePatient',
					'submit_text'=>'Update Patient',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$allPatients),
					'dob'=>"Date of Birth (YYYY-MM-DD)",
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
			$date = date('Y-m-d');
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
					'indate'=>array('label'=>'Date (Default: Today)','value'=>$date)
					);
			buildForm( $form );
		?>
		<p>Check out:</p>
		<?php 
			$allPatients = array();
			$date = date('Y-m-d');
			$a = new Admin;
			$allPatients = $a->getCurrentPatients();
			if( !$allPatients ) echo "<p class='notice'>There are currently no patients to check out.</p>";
			if( !$allPhysicians ) echo "<p class='notice'>There are no physicians working at the hospital!</p>";
			$form = array(
					'form_action'=>'checkOut',
					'submit_text'=>'Check Out Patient',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$allPatients),
					'_dropdown2'=>array('label'=>'Physician','name'=>'eidout',$allPhysicians),
					'outdate'=>array('label'=>'Date (Default: Today)','value'=>$date)
					);
			buildForm( $form );
		?>
		
		<a name="getPatientInfo"></a>
		<h2>Get Patient Information</h2>
		<?php
			$a = new Admin;
			$allPatients = $a->getAllPatients();
			if( !$allPatients )
				echo "<p class='notice'>There are no patients at the hospital!</p>";
			$form = array(
					'form_action'=>'getPatientInfo',
					'submit_text'=>'Get Information',
					'_dropdown1'=>array('label'=>"Patient Name",'name'=>"pname",$allPatients),
					);
			buildForm( $form );
		?>
		<a name='getPatientsOnDate'></a>
		<h2>Get Patients admitted/released on a Specific Date</h2>
		<?php
			$date = date('Y-m-d');
			$form = array(
					'form_action'=>'getPatientsOnDate',
					'submit_text'=>'Get Patients',
					'date'=>array('label'=>'Date (YYYY-MM-DD)','value'=>$date),
					);
			buildForm( $form );
		?>
		<a name='getPatientsByPhysician'></a>
		<h2>Get Patients Admitted/Released By Physcian</h2>
		<?php
			$a = new Admin;
			$allPhysicians = $a->getAllPhysicians();
			if( !$allPhysicians ) {
				echo "<p class='notice'>No Physicians work at the hospital!</p>";
			}
			$form = array(
					'form_action'=>'getPatientsByPhys',
					'submit_text'=>'Get Patients',
					'_dropdown1'=>array('label'=>"Physician",'name'=>'ename',$allPhysicians),
					);
			buildForm( $form );
		?>
	
	<?php break; case 'treatment': 
		$items = array(
				'newEDT'=>"New EDT Record",
				'getEDT'=>"Get EDT Records"
				);
		buildSubMenu( $items );
		?>
		<a name="newEDT"></a>
		<h2>Create an EDT Record for a Patient</h2>
		<p>Select a patient from the dropdown box or enter their Patient ID number. The ID number (when non-zero) takes precedence if both fields are filled.</p>
		<?php
			$a = new Admin;
			$currentPatients = $a->getCurrentPatients();
			if( !$currentPatients ) echo "<p class='notice'>There are currently no patients visiting the hospital!</p>";
			$activitytypes = $a->getActivityTypes();
			$form = array(
				'form_action'=>'newEDT',
				'submit_text'=>'Create',
				'_dropdown1'=>array('label'=>'Patient','name'=>'pid_name', $currentPatients),
				'pid_num'=>array('label'=>'Patient ID Num','value'=>'0'),
				'dateperf'=>'Date Performed (YYYY-MM-DD)',
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
		<p>Supply a Patient name or ID and retrieve EDT records. Decide whether for all visits or just current/active visit.</p>
		<?php
			$a = new Admin;
			$allPatients = $a->getAllPatients();
			$form = array(
				'form_action'=>'getEDT',
				'submit_text'=>'Retrieve',
				'_dropdown1'=>array('label'=>'Patient','name'=>'pid_name',$allPatients),
				'pid_num'=>array('label'=>'Patient ID','value'=>0),
				'_dropdown2'=>array('label'=>'Option','name'=>'option',array('0'=>'All visits','1'=>'Current Visit Only'))
				);
			buildForm( $form );
		?>
	
	<?php break; case 'billing': 
		$items = array(
				'addAccount'=>'Add Account',
				'receivePayment'=>'Receive Payment',
				'closeAccount'=>'Close account'
				);
		buildSubMenu($items); ?>
		<a name='addAccount'></a>
		<h2><?php echo $items[0]; ?></h2>
		<?php
			$b = new Billing;
			$noAcc = $b->getPatients();
			$form = array(
					'form_action'=>'addAccount',
					'submit_text'=>'Create Billing Account',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$noAcc);
					'ins'=>'Insurance Provider'
					);
			buildForm($form);
		?>

		<a name='receivePayment'></a>
		<h2><?php echo $items[1]; ?></h2>
		<?php 
			$b = new Billing;
			$patients = $b->getPatients(true);
			$form = array(
					'form_action'=>'receivePayment',
					'submit_text'=>'Update Bill',
					'_dropdown1'=>array('label'=>'Patient','name'=>'pid',$patients),
					'amnt'=>array('label'=>'Amount','value'=>0)
					);
			buildForm($form);
		?>
	
	<?php break; case 'tests' : 
		include('tests.php');
		?>
	
	<?php break; default: 
		$items = array(
				'readme'=>"Read Me",
				'assumptions'=>"Assumptions",
				'tests'=>"Run Tests"
				);
		buildSubMenu( $items );
	
	?>
		<h2>Read Me</h2>
		<p>To use the functions of the hospital management system, choose an option from the above menu. Then, navigate to an appropriate sub-action.</p>
		<p>You can edit the database connection credentials in config.php.</p>
		<h2>Assumptions</h2>
		<p>DB: cs348, Username: cs348, password cs348.</p>
		<h2>Perform Tests</h2>
		<p><a href="index.php?tests">HERE</a></p>

<?php 
} 
include('footer.php');
?>
