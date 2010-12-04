<!DOCTYPE html>
<html>
<head>
	<title>Hospital Management System  - <?php echo $_SERVER['QUERY_STRING']; ?></title>
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