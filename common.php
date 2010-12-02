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

function buildSubMenu( $items ) {
	if( $items ) {
		echo "<p><ul class='submenu'>";
		foreach( $items as $k=>$v )
			echo "<li><a href='#".$k."'>".$v."</a></li>";
		echo "</ul></p><hr style='clear:both;'>";
	}
	successMsg();
}

function successMsg() {
	if( array_key_exists('success',$_GET) ) {
		switch( $_GET['success'] ) {
			case 't':
				echo "<p class='success'>Treatment"; break;
			case 'a':
				echo "<p class='success'>Administration"; break;
			case 'b':
				echo "<p class='success'>Billing"; break;
			default:;
		}
		echo " operation completed successfully!</p>";
	}
}

function buildForm( $args ) {
	$form_action = array_shift($args);
	$submit_text = array_shift($args);
	
	echo "<form method='post' action='process.php'>\n";
	echo "<input type='hidden' name='form_action' value='".$form_action."'>\n";

	foreach( $args as $t=>$p ) {
	
		// lol im so awesome
		$type = substr($t,0,-1);
	
		// Dropdown box
		if( $type == "_dropdown" ) {
		
			$label = array_shift($p);
			$name = array_shift($p);
			$options = array_shift($p);
			
			echo "<p><label for='".$name."'>".$label."</label><select id='".$name."' name='".$name."'>\n";
			foreach( $options as $k=>$v ) {
				echo "\t<option value='".$k."'>".$v."</option>\n";
			}
			echo "</select></p>\n";
		
		// Textarea
		} elseif( $type == "_textbox" ) {
		
			echo "<p><label for='".$p['name']."'>".$p['label']."</label>";
			echo "<textarea id='".$p['name']."' name='".$p['name']."'></textarea></p>\n";
		
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
}