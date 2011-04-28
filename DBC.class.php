<?php
/**
 * DBConnection class file
 *
 * @author Cai JingMing <caijingming@aspirehld.com>
 * @copyright Copyright &copy; 139.com
 */

class DBC {
  
    public $db_conf;

	public $username;
	
	public $password;

    public $db_profiling = false;
	public $db_debug = false;

    private $_pdo;
    private $_active = false;
	
  
    public function __construct(array $config, $debug = false, $profile = false) {
	  if(empty($config)) throw Kao::Exception("DBConnection construct need one argument but NULL given");
	  
	  
	  $this->db_conf = "mysql:dbname={$config['db']};host={$config['host']};port={$config['port']}";
	  $this->username = $config['username'];
	  $this->password = $config['password'];

	  $this->db_debug = $debug;
	  $this->db_profiling = $profile;
	  
					  
    }

    public function __sleep() {
       $this->close();
       return array_keys(get_object_vars($this));
    }

    public static function getDrivers() {
       return PDO::getAvailableDrivers();
    }

    public function getAlive() {
       return $this->_active;
    }

    public function setAlive($value) {
	  if($value != $this->_active) {
		if($value) $this->open();
		else $this->close();
	  }
	}

	protected function createPdoInstance($attributes) {
	  $pdoClass='PDO';
	  return new $pdoClass($this->db_conf,$this->username,
									$this->password,$attributes);
	}

	protected function open() {
	  if($this->_pdo === null) {
		if(empty($this->db_conf)) throw Kao::Exception("DBConnection db_conf is NULL");
		try {
		  $attributes = array(
			PDO::ATTR_EMULATE_PREPARES   => true,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
							  );

		  $this->_pdo = $this->createPdoInstance($attributes);
		  $this->_active=true;
		}
		catch(PDOException $e) {
		  if($this->db_debug)
			Kao::debug("DBConnection failed to open",array('error' => $e->getMessage(),'code' => $e->getCode(), 'info' => $e->errorInfo()));
		  else
			throw Kao::Exception("DBConnection failed to open,error: ".$e->getMessage());
		}
	  }
	}

    protected function close() {
	  $this->_pdo=null;
	  $this->_active=false;
	}

	public function exec($sql) {
	  $this->setAlive(true);
	  if($this->db_profiling)
		Kao::profile("begin",get_class($this),$sql);
	  try {
		$n = $this->_pdo->exec($sql);

		if($this->db_profiling)
		  Kao::profile("end",get_class($this),$sql);
		
		return $n;
	  }
	  catch (Exception $e) {
		throw Kao::Exception("DBConnection failed to exec sql,error: ".$e->getMessage());
	  }
	}

	public function query($sql) {
	  $this->setAlive(true);
	  if($this->db_profiling)
		Kao::profile("begin",get_class($this),$sql);
	  try {
		$statement = $this->_pdo->prepare($sql);
		$statement->execute();
		
		$r = $statement->fetchAll();
		$statement->closeCursor();
		if($this->db_profiling)
		  Kao::profile("end",get_class($this),$sql);
		
		return $r;
	  }
	  catch (Exception $e) {
		throw Kao::Exception("DBConnection failed to query sql,error: ".$e->getMessage());
	  }
	}
	  

	public function lastInsertId() {
	  $this->setAlive(true);
	  return $this->_pdo->lastInsertId();
	}
}