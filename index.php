<!-- Author: Rebecca Putinski 20271463 for CS348 -->

<?php
	function buildMenu() {
		$items = array(
					'admin'=>'Admin',
					'treatment'=>'Treatment',
					'billing'=>'Billing',
					''=>'Home');
		foreach( $items as $k=>$v ) {
			echo $_SERVER['QUERY_STRING'] == $k ? "<li class='current'>" : "<li>";
			echo "<a href='index.php?".$k."'>".$v."</a></li>\n";
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Hospital Management System</title>
	<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>

<div id="wrap">
	<h1>Hospital Management System</h1>
	<div id="menu">
		<ul>
<?php buildMenu(); ?>
		</ul>
	</div>
	<hr>
	<div id="body">

<?php switch($_SERVER['QUERY_STRING']) { 

	case 'admin': ?>
		<p><ul class="submenu">
			<li><a href="#addPatient">Add a Patient</a></li>
			<li><a href="#updatePatient">Update a Patient</a></li>
			<li><a href="#get">Get Patient Information</a></li>
		</ul></p>
		<hr style='clear:both;'>
		<h2>Administration Actions</h2>
		<p>Here an administrator can manage patients, and check patients in and out of the hospital.</p>
		
		<a name="addPatient"></a>
		<h2>Add a Patient</h2>
		<p>Stuff!</p>
		
		<a name="updatePatient"></a>
		<h2>Update a Patient</h2>
		<p>More stuff!</p>
		
		<a name="get"></a>
		<h2>Get Patient Information</h2>
		<p>Lala</p>
	
	<?php break; case 'treatment': ?>
		<p><ul class="submenu">
			<li><a href="#newEDT">Create EDT</a></li>
			<li><a href="#getEDT">Get EDT Records</a></li>
		</ul></p>
		<hr style='clear:both;'>
		
		<a name="newEDT"></a>
		<h2>Create an EDT Record for a Patient</h2>
		
		<a name="getEDT"></a>
		<h2>Retrieve EDT Records</h2>
	
	<?php break; case 'billing': ?>
	
	
	<?php break; default: ?>
		<h2>Read Me</h2>
		<p>To use the functions of the hospital management system, choose an option from the above menu. Then, navigate to an appropriate sub-action.</p>
		<p></p>

<?php } ?>
	</div>
	<div id="footer">CS348 Project P2 - Rebecca Putinski 20271463</div>
</div>
</body>
</html>