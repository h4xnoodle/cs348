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
		$results = array();
		$result = $this->handle->query($query);
		if( $result ) {
			$result = $result->fetchAll();
			// Strip numeric indices and change to lower case
			foreach( $result as $r ) {
				$temp = array();
				foreach( $r as $k=>$v ) {
					if( !is_numeric($k) ) {
						$temp[strtolower($k)] = $v;
					}
				}
				$results[] = $temp;
			}
		}
		return $results;
	}
	
	// Really only for UDTInsert/Update in Admin
	public function directInsert( $query ) {
		return !($this->handle->exec( $query ) === false);
	}
	
	// Build an insert statement
	public function insert( $table,$data,$where=false ) {
		$q = "INSERT INTO ".$table."(";
		$q .= key($data);
		next($data);
		while( list($k,$v) = each($data) ) {
			if($v != '')
				$q .= ",".$k;
		}
		$q .= ") VALUES(?";
		reset($data);
		next($data);
		while( list($k,$v) = each($data)) {
			if($v != '') $q .= ",?";
		}
		$q .= ")";
		reset($data);
		$prepared[] = current($data);
		next($data);
		while( list($k,$v) = each($data) ) {
			if($v != '')
				$prepared[] = $v;
		}
		
		if( $where ) {
			if ( !array_key_exists('op',$where) ) $where['op'] = "=";
			$q .= " WHERE ".$where['field']." ".$where['op']." \"".$where['value']."\"";
		}
		echo "<pre>".$q."</pre>";
		$stmt = $this->handle->prepare($q);
		echo $q; print_r($prepared); 
		return !($stmt->execute($prepared) === false);
	}
	
	// Build update statement
	public function update( $table,$data,$where=false,$co=false ) {
		if( !$where ) return false; // Must have 'where' clause
		if( !array_key_exists('op',$where) ) $where['op'] = '=';
		$q = "UPDATE ".$table." SET ";
		foreach( $data as $k=>$v ) 
			$q .= $k."='".$v."',";
		$q = substr($q,0,-1);
		$q .= " WHERE ".$where['field']." ".$where['op']." '".$where['value']."'";
		if( $co ) { // Dirty hax for check out
			$q .= " AND cid = (SELECT max(cid) FROM CheckInOuts WHERE pid = ".$where['value'].")";
		}
		return !($this->handle->exec( $q ) === false );
	}
	
	// Delete
	public function delete( $table,$where=false,$limit=1 ) {
		$q = "DELETE FROM ".$table;
		if( $where ) {
			if ( !array_key_exists('op',$where) ) $where['op'] = "=";
			$q .= " WHERE ".$where['field']." ".$where['op']." '".$where['value']."'";
		}
		$q .= " LIMIT ".$limit;
		return ($this->handle->exec($q) !== false);
	}
}
?>