<?php

// Build the menus with arrays
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

function buildSubMenu( $items ) {
	if( $items ) {
		echo "<p><ul class='submenu'>";
		foreach( $items as $k=>$v )
			echo "<li><a href='#".$k."'>".$v."</a></li>";
		echo "</ul></p><hr style='clear:both;'>";
	}
	successMsg();
}

// Operation success messages
function successMsg() {
	if( array_key_exists('success',$_GET) ) {
		switch( $_GET['success'] ) {
			case 'tn':
			case 't':
				echo "<p class='success'>Treatment"; break;
			case 'a':
				echo "<p class='success'>Administration"; break;
			case 'b':
			case 'bn':
				echo "<p class='success'>Billing"; break;
			default:;
		}
		echo " operation completed successfully!";
		if( $_GET['success'] == 'tn' ) echo " But there were no records.";
		if( $_GET['success'] == 'bn' ) echo " The account was not deleted.";
		echo "</p>";
	}
}

// Build the forms from arrays
function buildForm( $args ) {
	$form_action = array_shift($args);
	$submit_text = array_shift($args);
	
	echo "<form method='post' name='".$form_action."' action='process.php'>\n";
	echo "<input type='hidden' name='form_action' value='".$form_action."'>\n";

	foreach( $args as $t=>$p ) {
	
		// Since we can't have duplicate keys, _<type><int> is how dropdowns/textareas are labeled. Here, we get the <type> part
		$type = substr($t,1,-1);
	
		// Dropdown box
		if( $type == "dropdown" ) {
		
			$label = array_shift($p);
			$name = array_shift($p);
			$options = array_shift($p);
			
			echo "<p><label for='".$name."'>".$label."</label><select id='".$name."' name='".$name."'>\n";
			if( !$options )	echo "\t<option value=''></option>\n";
			foreach( $options as $k=>$v ) {
				echo "\t<option value='".$k."'>".$v."</option>\n";
			}
			echo "</select></p>\n";
		
		// Textarea
		} elseif( $type == "textbox" ) {
		
			echo "<p><label for='".$p['name']."'>".$p['label']."</label>";
			echo "<textarea id='".$p['name']."' name='".$p['name']."'></textarea></p>\n";
		} elseif( $type == 'hidden' ) {
			
			echo "<input type='hidden' name='".$p['name']."' value='".$p['value']."'>\n";
			
		// Normal text fields
		} else {
			if( is_array($p) )
				echo "<p><label for='".$t."'>".$p['label']."</label><input type='text' id='".$t."' value='".$p['value']."' name='".$t."'></p>\n";
			else
				echo "<p><label for='".$t."'>".$p."</label><input type='text' id='".$t."' name='".$t."'></p>\n";
		}
	}
	
	echo "<p style='text-align:right;margin:0;'><input type='submit' name='submit' value='".$submit_text."'></p>\n";
	echo "</form>";
	echo "<p class='toplink'><a href='#'>Top</a></p>";
}

// Print error message
function printError( $msg ) {
	echo "<p class='error'>".$msg."</p>";
}

// Form validation functions
// Verify the date is correctly input
function verifyDate( $date ) {
	function inrange($low,$high,$value) {
		return ($value >= $low && $value <= $high);
	}
	$date = explode('/',$date);
	if( ! ((strlen($date[0])==2 && inrange(1,31,$date[0])) && 
			(strlen($date[1])==2 && inrange(1,12,$date[1])) &&
			(strlen($date[2])==4) && inrange(1900,2020,$date[2])) ) {
		printError("The date provided is invalid. Use MM/DD/YYYY format.");
		return false;
	}
	return true;
}
// Check that all fields are filled (this is not used for 'update' actions)
function checkFilled( $form ) {
	$count = 0;
	foreach( $form as $k=>$v ) {
		if( $v == '' )
			$count++;
	}
	if( $count ) printError("There were ".$count." fields left blank. Please fill them out.");
	return (!(bool)$count);
}
// Filter out the form meta data (action to take on submit, and value of submit.
function filterData( $rm ) {
	$result = array();
	while( list($k,$v) = each($rm) ) {
		if( $k != 'form_action' && $k != 'submit' )
			$result[$k] = $v;
	}
	return $result;
}
// Verify a field is a number when it should be
function checkNumber( $field ) {
	if( !is_numeric( $field ) ) {
		printError("Please ensure you input numbers in the appropriate fields (cost, patient ID, duration, etc)");
		return false;
	}
	return true;
}