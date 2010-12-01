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

function buildForm( $args ) {
	$form_action = array_shift($args);
	$submit_text = array_shift($args);
	
	echo "<form method='post' action='process.php'>";
	echo "<input type='hidden' name='action' value='".$form_action."'>";

	foreach( $args as $t=>$p ) {
	
		// lol im so awesome
		$type = substr($t,0,strlen($t)-1);
	
		// Dropdown box
		if( $type == "_dropdown" ) {
		
			$label = array_shift($p);
			$name = array_shift($p);
			$options = array_shift($p);
			echo "<p><label for='".$name."'>".$label."</label><select id='".$name."' name='".$name."'>";
			foreach( $options as $k=>$v )
				echo "<option value='".$k."'>".$v."</option>";
			echo "</select>";
		
		// Textarea
		} elseif( $type == "_textbox" ) {
		
			echo "<p><label for='".$p['name']."'>".$p['label']."</label>";
			echo "<textarea id='".$p['name']."' name='".$p['name']."'></textarea>";
		
		// Normal text fields
		} else {
		
			echo "<p><label for='".$t."'>".$p."</label><input type='text' id='".$t."' name='".$t."'></p>";
			
		}
	}
	
	echo "<p style='text-align:right;margin:0;'><input type='submit' name='submit' value='".$submit_text."'></p>";
	echo "</form>";
}