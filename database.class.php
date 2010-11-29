<?php
require("config.php");

// Access to the Database (API)
class Database {

	private $handle;
	
	function __construct() {
		try {
			$this->handle = new PDO("ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=".DATABASE_DBNAME . 
				";HOSTNAME=".DATABASE_HOST.";PORT=".DATABASE_PORT.";PROTOCOL=TCPIP;", 
				DATABASE_USER, DATABASE_PASS);
		} catch( PDOException $e ) {
			die("Could not open database connection. Verify your credentials are correct in config.php. Message:<br><br>".$e->getMessage());
		}
	}
	
	// Returns all results in array
	public function query($query) {
		$result = $this->handle->query($query);
		return $result->fetchAll();
	}
	
	// Build an insert statement
	public function insert( $table,$data,$where=false ) {
		$q = "INSERT INTO ".$table."(";
		for( $i = 0; $i < count($data)-1; $i++ ) {
			$q .= key($data).",";
			next($data);
		}
		$q .= key($data).") VALUES(";
		reset($data);
		for( $i = 0; $i < count($data)-1; $i++ ) {
			$q .= "\"".current($data)."\",";
			next($data);
		}
		$q .= "\"".current($data)."\")";
		if( $where ) {
			if ( $where['op'] == "" ) $where['op'] = "=";
			$q .= " WHERE ".$where['field']." ".$where['op']." ".$where['value'];
		}
		echo $q;
	}
	
	// Build update statement
	public function update( $table,$data,$where=false ) {}
	
	// Delete
	public function delete( $table,$where=false,$limit=1 ) {}
	
	
}


// test
$db = new Database;
//print_r($db->query("SELECT * FROM test"));
$db->insert("test",array('name'=>'moocow','address'=>'123 false st.','test'=>'lolol'),false);

?>