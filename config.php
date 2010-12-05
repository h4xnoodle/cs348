<?php

/*	================================================

	Author:	Rebecca Putinski
	UWID:	20271463
	Class:	CS348 Section 3
	
	This file:
		Configure the credentials for database access
		and some other options.
		
	================================================
*/

// Database connection
// Assuming: IBM DB2 Express-C on local machine, listening to port 50000. 
// Adjust if necessary.
define("DATABASE_HOST", "localhost");
define("DATABASE_DBNAME","CS348");
define("DATABASE_USER", "bex");
define("DATABASE_PASS", "orca");
define("DATABASE_PORT", 50000);

// Run tests
define("RUN_TESTS",true);

// Turn off PHP errors for production
error_reporting(0);

?>