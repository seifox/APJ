<?php
/**
* APJPDO Class for MySQL PDO managment<br>
* Clase para la gestión de PDO de MySQL
* Versión: 1.17.0614
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
		    $this->_Pdo = new PDO($dsn, $this->_Settings["user"], $this->_Settings["password"],array(PDO::ATTR_PERSISTENT => true));
        $this->_Pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,'SET NAMES '.$this->_Settings['charset']);
		    $this->_Pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    $this->_Pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
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
          $param['value']=($param['value']==="")?NULL:$param['value'];
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
  * Clears data binding
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
        } catch(Exception $e) {
          $this->error=true;
          $this->errormsg = $e->getMessage();
        }
        $this->numrows = count($rows);
        return $rows;
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
        return $this->_Qry->fetch($fetchMode);
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
      return $this->_Qry->fetch($fetchmode);      
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
        return $this->affected = $this->_Qry->rowCount();
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
        return $this->affected = $this->_Qry->rowCount();
      } else {
        return $this->numrows = $this->_Qry->rowCount();
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
    return $this->_Pdo->lastInsertId();
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
      $this->_Pdo->commit();
      $this->trans=false;
    }
  }  
  
  /**
  * Rolls back a transaction<br>
  * Deshace la transacción
  */
  public function rollBack() {
    if ($this->trans) {
      $this->_Pdo->rollBack();
      $this->trans=false;
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
        $newValue = ($newValue !== "") ? $newValue : NULL;
        break;
      case "float":
      case "double":
      case "real":
        $newValue = ($newValue !== "") ? floatval($newValue) : NULL;
        break;
      case "date":
        $dateTime=new DateTime($newValue);
        $newValue = ($newValue !== "") ? $dateTime->format('Y-m-d') : NULL;
        break;
      case "timestamp":
      case "datetime":
        $dateTime=new DateTime($newValue);
        $newValue = ($newValue !== "") ? $dateTime->format('Y-m-d H:i:s') : NULL;
        break;
      case "time":
        $dateTime=new DateTime($newValue);
        $newValue = ($newValue !== "") ? $dateTime->format('H:i:s') : NULL;
        break;
      case "bit":
      case "tinyint":
      case "smallint":
      case "mediumint":
      case "int":
      case "year":
        $newValue = ($newValue !== "") ? intval($newValue) : NULL;
        break;
      defautl:
        $newValue = ($newValue !== "") ? $newValue : NULL;
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
  * Error logging
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
  * Returns the database server information
  * Devuelve la información del servidor de base de datos
  * @return (string) Database server info
  */
  public function server_info() {
    return $this->_Pdo->getAttribute(PDO::ATTR_SERVER_INFO);
  }
  
}
