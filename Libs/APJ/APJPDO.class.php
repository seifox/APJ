<?php
/**
* APJPDO Class for PDO managment<br>
* Clase para la gestión de PDO
* Version: 1.9.200513
* Author: Ricardo Seiffert
*/
class APJPDO 
{
	private $_Pdo;
	private $_Qry;      
	private $_Settings;
	private $_Connected = false;
	private $_Log;
	private $_Parameters;
  private $_ACTION = array("insert","replace","update","delete","truncate");
  private $_SELECTION = array("select","show","describe");
  public $trans = false; 
  public $result = false;
  public $error = false; 
  public $errormsg = NULL; 
  public $errornum = 0; 
  public $affected = 0;
  public $numrows = 0;
  
  /**
  * Constructor
  * @param string $dsn (optional)
  * @param string $user (optional)
  * @param strinf $password (optional)
  */
	public function __construct($dsn=NULL,$user=NULL,$password=NULL)	{ 			
		$this->_Log = new APJLog();	
		$this->_connect($dsn,$user,$password);
		$this->clearBinding();
	}
  
  public function __destruct() {
    $this->disconnect();
  }
	
	private function _connect($dsn,$user,$password) {
    if ($dsn==NULL) {
      $inifile=APJ.DIRECTORY_SEPARATOR."APJPDO.ini.php";
      if (is_readable($inifile)) {
		    $this->_Settings = parse_ini_file($inifile);
        if ($this->_Settings) {
		      $dsn = 'mysql:dbname='.$this->_Settings["dbname"].';host='.$this->_Settings["host"].';charset='.$this->_Settings['charset'];
        } else {
          die($this->_errorLog("El archivo de DSN {$inifile} no se pudo leer."));
        }
      } else {
        die($this->_errorLog("El archivo de DSN {$inifile} no está disponible. "));
      }
    } else {
      $this->_Settings["user"]=$user;
      $this->_Settings["password"]=$password;
    }
		try {
      if (class_exists('PDO')) {
		    $this->_Pdo = APJPDOConnection::instance($dsn, $this->_Settings);
		    $this->_Connected = true;
      } else {
        die($this->_errorLog("The PDO object is not available."));
      }
		} catch (PDOException $e) {
			die($this->_errorLog($e->getMessage()));
		}
	}
  
  /**
  * Disconnect from database<br>
  * Desconecta de la base de datos
  */
	public function disconnect()	{
    $this->_Qry = NULL;
	 	$this->_Pdo = NULL;
	}
	
  /**
  * Prepares the query and data binding<br>
  * Prepara la consulta y enlace de datos
  * @param (string) Query string
  * @param (array) Parameters array
  */
	private function _prepare($query,$parameters = "") {
	  if(!$this->_Connected) { 
      $this->Connect();
    }
	  try {
			$this->_Qry = $this->_Pdo->prepare($query);
			$this->bindArray($parameters);
			if(!empty($this->_Parameters)) {
				foreach($this->_Parameters as $param) {
          $param['value']=(strlen($param['value'])==0)?NULL:$param['value'];
					$this->_Qry->bindParam($param['param'],$param['value']);
				}
			}
			$this->result = $this->_Qry->execute();		
      $this->error = false;
      $this->errornum = 0;
      $this->errormsg = NULL;
		}	catch(PDOException $e) {
			$this->_errorLog($e->getMessage(), $query);
      $this->error = true;
      $this->errornum = $this->_Pdo->errorCode();
      $this->errormsg = $e->getMessage();
		}
		$this->clearBinding();
	}
	
  /**
  * Binds data to parameters<br>
  * Enlaza datos a los parametros
  * @param (string) Query parameter name
  * @param (mixed) Query parameter value
  * @param (string) Value data type (default 'none)
  * @param (boolean) Data value must be trimmed (default false)
  * @return (mixed) Binded new value
  */
	public function bind($param, $value, $type='none', $trim=false)	{	
    if ($type!='none') $value=$this->getValue($value,$type,$trim);
	  $this->_Parameters[]['param'] = ":".$param;
    end($this->_Parameters);
    $key=key($this->_Parameters);
    $this->_Parameters[$key]['value']=$value;
    return $value;
	}
  
  /**
  * Bind an array of data to parameters of a query<br>
  * Enlaza un array de datos a parámetros de una consulta
  * @param (array) Array of parameters to be binded
  */
	public function bindArray($paramarray) {
		if(is_array($paramarray)) {
      $this->clearBinding();
			$columns = array_keys($paramarray);
			foreach($columns as $i => &$column)	{
				$this->bind($column, $paramarray[$column]);
			}
		}
	}
  
  /**
  * Clears data binding<br>
  * Limpia los enlaces de datos
  */
  public function clearBinding() {
    $this->_Parameters=array();  
  }
  
  /**
  * Returns all rows from query into array<br>
  * Devuelve todas las filas en un array
  * @param (string) Query string
  * @param (array) Binding parameters (optional)
  * @param (PDO constant) Fetchmode, can PDO::FETCH_ASSOC (default), PDO::FETCH_BOTH, PDO::FETCH_CLASS, PDO::FETCH_NUM
  * @return (mixed) array or false
  */
  public function rows($query, $params = NULL, $fetchmode = PDO::FETCH_ASSOC)  {
    $rows=array();
    $query = trim($query);
    if (in_array($this->_fisrt($query),$this->_SELECTION)) {
      $this->_prepare($query,$params);
      if ($this->error==false) {
        try {
          $rows = $this->_Qry->fetchAll($fetchmode);
          $this->numrows = count($rows);
          return $rows;
        } catch(PDOException $e) {
          $this->_errorLog($e->getMessage());
          $this->error = true;
          $this->errornum = $this->_Pdo->errorCode();
          $this->errormsg = $this->_Pdo->errorInfo();
          $this->numrows=0;
        }
      }
    } 
    return false;
  }

  /**
  * Returns a row from query into array<br>
  * Devuelve todas las filas en un array
  * @param (string) Query string
  * @param (array) Binding parameters
  * @param (PDO constant) Fetch mode, PDO::FETCH_ASSOC (default), PDO::FETCH_BOTH, PDO::FETCH_CLASS, PDO::FETCH_NUM
  * @return (mixed) Array or false
  */
  public function row($query,$params = NULL,$fetchMode = PDO::FETCH_ASSOC) {        
    $query = trim($query);
    if (in_array($this->_fisrt($query),$this->_SELECTION)) {
      $this->_prepare($query,$params);
      if ($this->error==false) {
        try {
          return $this->_Qry->fetch($fetchMode);
        } catch(PDOException $e) {
          $this->_errorLog($e->getMessage());
          $this->error = true;
          $this->errornum = $this->_Pdo->errorCode();
          $this->errormsg = $this->_Pdo->errorInfo();
        }
      }
    }
    return false;      
  }

  /**
  * Returns next row from query into array<br>
  * Devuelve las siguiente fila de una consulta en un array
  * @param (PDO constant) Fetch mode, PDO::FETCH_ASSOC (default), PDO::FETCH_BOTH, PDO::FETCH_CLASS, PDO::FETCH_NUM
  * @return (mixed) Array or false
  */
  public function nextRow($fetchmode = PDO::FETCH_ASSOC) {        
    if ($this->error==false) {
      try {
        return $this->_Qry->fetch($fetchmode);      
      } catch(PDOException $e) {
        $this->_errorLog($e->getMessage());
        $this->error = true;
        $this->errornum = $this->_Pdo->errorCode();
        $this->errormsg = $this->_Pdo->errorInfo();
      }
    }
    return false;      
  }

  /**
  * Execute a query binding parameters<br>
  * Ejecuta una consulta enlazando parámetros
  * @param (string) Query string
  * @param (array) Binding parameters (optional)
  * @return (mixed) Number of affected rows or false if not an ACTION query
  */
	public function execute($query,$params = NULL)	{
    $query = trim($query);
		if (in_array($this->_fisrt($query),$this->_ACTION)) {
      $this->_prepare($query,$params);
      if ($this->error==false) {
        try {
          return $this->affected = $this->_Qry->rowCount();
        } catch(PDOException $e) {
          $this->_errorLog($e->getMessage());
          $this->error = true;
          $this->errornum = $this->_Pdo->errorCode();
          $this->errormsg = $this->_Pdo->errorInfo();
        }
      }
		}
    return false;
	}

  /**
  * Execute a query without binding parameters<br>
  * Ejecuta una consulta sin enlazar parámetros
  * @param (string) Query string
  * @return (mixed) number of result rows or affected rows or false if error
  */
  public function query($query) {
    $query = trim($query);
    $this->_Qry = $this->_Pdo->query($query);
    if ($this->error==false) {
      if (in_array($this->_fisrt($query),$this->_ACTION)) {
        try {
          return $this->affected = $this->_Qry->rowCount();
        } catch(PDOException $e) {
          $this->_errorLog($e->getMessage());
          $this->error = true;
          $this->errornum = $this->_Pdo->errorCode();
          $this->errormsg = $this->_Pdo->errorInfo();
        }
      } else {
        try {
          return $this->numrows = $this->_Qry->rowCount();
        } catch(PDOException $e) {
          $this->_errorLog($e->getMessage());
          $this->error = true;
          $this->errornum = $this->_Pdo->errorCode();
          $this->errormsg = $this->_Pdo->errorInfo();
        }
      }
    }
    return false;
  }
  
  /**
  * Return last inserted primary key value<br>
  * Devuelve la última clave promaria insertada
  * @return (mixed) Last id
  */
  public function lastId() {
    try {
      return $this->_Pdo->lastInsertId();
    } catch(PDOException $e) {
      $this->_errorLog($e->getMessage());
      $this->error = true;
      $this->errornum = $this->_Pdo->errorCode();
      $this->errormsg = $this->_Pdo->errorInfo();
    }
    return false;      
  }  

  /**
  * Begins Transaction<br>
  * Comienza el control de transacción
  */
  public function beginTrans() {
    try {
      $this->_Pdo->beginTransaction();
      $this->trans=true;
    } catch(PDOException $e) {
      $this->_errorLog($e->getMessage());
      $this->error = true;
      $this->errornum = $this->_Pdo->errorCode();
      $this->errormsg = $this->_Pdo->errorInfo();
    }
  }  
  
  /**
  * Commits a transaction<br>
  * Confirma la transacción
  */
  public function commitTrans() {
    if ($this->trans) {
      try {
        $this->_Pdo->commit();
        $this->trans=false;
      } catch(PDOException $e) {
        $this->_errorLog($e->getMessage());
        $this->error = true;
        $this->errornum = $this->_Pdo->errorCode();
        $this->errormsg = $this->_Pdo->errorInfo();
      }
    }
  }  
  
  /**
  * Rolls back a transaction<br>
  * Deshace la transacción
  */
  public function rollBack() {
    if ($this->trans) {
      try {
        $this->_Pdo->rollBack();
        $this->trans=false;
      } catch(PDOException $e) {
        $this->_errorLog($e->getMessage());
        $this->error = true;
        $this->errornum = $this->_Pdo->errorCode();
        $this->errormsg = $this->_Pdo->errorInfo();
      }
    }
  }  
  
  /**
  * Ends transaction<br>
  * Termina la trasacción
  */
  public function endTrans() {
    if ($this->trans) {
      try {
        $this->_Pdo->commit();
      } catch (PDOException $e) {
        $this->_Pdo->rollBack();
      }
      $this->trans=false;
    }
  }
  
  /**
  * Converts a value according to given data type<br>
  * Convierte un valor según el tipo de dato dado
  * @param (mixed) Value
  * @param (string) data type
  * @param (boolean) Trims the value (optional)
  * @return (mixed) New converted value
  */
  public function getValue($value,$type='none',$trim=false) {
    $newValue=($trim)?trim($value):$value;
    $type=strtolower($type);
    switch ($type) {
      case "text":
      case "varchar":
      case "char":
      case "blob":
      case "binary":
      case "varbinary":
      case "numeric":
      case "decimal":
      case "enum":
      case "set":
        $newValue = (strlen($newValue)>0) ? $newValue : NULL;
        break;
      case "float":
      case "double":
      case "real":
        $newValue = (strlen($newValue)>0) ? floatval($newValue) : NULL;
        break;
      case "date":
        $dateTime=new DateTime($newValue);
        $newValue = (strlen($newValue)>0) ? $dateTime->format('Y-m-d') : NULL;
        break;
      case "timestamp":
      case "datetime":
        $dateTime=new DateTime($newValue);
        $newValue = (strlen($newValue)>0) ? $dateTime->format('Y-m-d H:i:s') : NULL;
        break;
      case "time":
        $dateTime=new DateTime($newValue);
        $newValue = (strlen($newValue)>0) ? $dateTime->format('H:i:s') : NULL;
        break;
      case "bit":
      case "tinyint":
      case "smallint":
      case "mediumint":
      case "int":
      case "year":
        $newValue = (strlen($newValue)>0) ? intval($newValue) : NULL;
        break;
      defautl:
        $newValue = (strlen($newValue)>0) ? $newValue : NULL;
    }
    return $newValue;
  }
  
  /**
  * Returns the first query instruction<br>
  * Devuelve la primera instrucción de la consulta
  * @param (string) Query string
  * @return (string) First Query instruction
  */
  private function _fisrt($query) {
    $query=str_replace("\r\n", ' ', $query);
    $array = explode(" ", $query);
    return strtolower($array[0]);
  }
  
  /**
  * Error logging<br>
  * Registro de Errores
  * @param (string) Message
  * @param (string) Query string (optional)
  * @return (string) Exception message
  */
	private function _errorLog($message , $sql = "")	{
		$exception = 'Excepción no manejada<br>';
		$exception .= $message;
		$exception .= "<br>Puede encontrar el error en el Log";
		if(!empty($sql)) {
			$message .= "\r\nSQL : ".$sql;
		}
		$this->_Log->write($message);
		return $exception;
	}			
  
  /**
  * Returns the database server information<br>
  * Devuelve la información del servidor de base de datos
  * @return (string) Database server info
  */
  public function server_info() {
    return $this->_Pdo->getAttribute(PDO::ATTR_SERVER_INFO);
  }

  /**
  * Returns the database server version<br>
  * Devuelve la version del servidor de base de datos
  * @return (string) Database server version
  */
  public function server_version() {
    $row=$this->row('SELECT version()');
    return $row['version()'];
  }
  
  /**
  * Returns the database name<br>
  * Retorna el nombre de la base de datos
  * @return (string) Database name
  */
  public function getDataBaseName() {
    return $this->_Settings['dbname'];
  }  
  
}

/**
* APJPDOConnection is a singleton implementation for returning a PDO instance<br>
* APJPDOConnection es una implementación sigleton para devolver una instancia de PDO
* Usage: $db = APJPDOConnection::instance('dsn', 'username', 'password');
* If you assign diferent arguments, it will return a new connection.
* Review: 170627
*/
class APJPDOConnection
{
  private static $_instance = NULL;
  private static $_dsn = NULL;
  private static $_settings = array();
  
  public function __construct() {}
  
  private function __destruct(){}
  
  public function __clone() {
    return false;
  }
  
  public function __wakeup() {
    return false;
  }  
  
  /**
  * Returns a instance of the database connection<br>
  * Devuelve una instancia de la conexión de la base de datos.
  * @param mixed $dsn
  * @param mixed $settings
  * @return PDO
  */
  public static function instance($dsn,$settings) {
    if (self::sameConnection($dsn,$settings)) {
      return self::$_instance;
    } else {
      if (empty(self::$_instance)) {
        self::$_instance = self::getConnection($dsn,$settings);
        self::$_dsn = $dsn;
        self::$_settings = $settings;
        return self::$_instance;
      } else {
        return self::getConnection($dsn,$settings);
      }
    }
  }
  
  private static function getConnection($dsn, $settings) {
    $conn = NULL;
    try {
      $conn = new PDO($dsn,$settings["user"], $settings["password"],array(PDO::ATTR_PERSISTENT => PERSISTENT_CONNECTION));
      if (isset($settings['charset'])) {
        $conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,'SET NAMES '.$settings['charset']);
      }
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      return $conn;
    }  catch (PDOException $e) {
      throw $e;
      die($e);
    } catch(Exception $e) {
      throw $e;
      die($e);
    }
  }
  
  private static function sameConnection($dsn,$settings) {
    if (isset(self::$_instance) and self::$_dsn==$dsn and self::$_settings==$settings) {
      return true;
    }
    return false;
  }
  
}
