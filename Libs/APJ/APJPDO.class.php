<?php
/*
  APJPDO Class for MySQL PDO managment
  Versión: 1.17.0322
  Author: Ricardo Seiffert
*/
class APJPDO 
{
	private $_pdo;
	private $_mQry;
	private $_settings;
	private $_connected = false;
	private $_log;
	private $_parameters;
  private $_ACTION = array("insert","replace","update","delete","truncate");
  private $_SELECTION = array("select","show","describe");
  public $trans=false; 
  public $result = false;
  public $error = false; 
  public $errormsg = NULL; 
  public $errornum = 0; 
  public $affected = 0;
  public $numrows = 0;
  
  /**
  * Constructor
  * @param (optional) $dsn 
  * @param (optional) $user
  * @param (optional) $password
  */
	public function __construct($dsn=NULL,$user=NULL,$password=NULL)	{ 			
		$this->_log = new APJLog();	
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
		    $this->_settings = parse_ini_file($inifile);
        if ($this->_settings) {
		      $dsn = 'mysql:dbname='.$this->_settings["dbname"].';host='.$this->_settings["host"].';charset='.$this->_settings['charset'];
        } else {
          die($this->_errorLog("El archivo de DSN {$inifile} no se pudo leer."));
        }
      } else {
        die($this->_errorLog("El archivo de DSN {$inifile} no está disponible. "));
      }
    } else {
      $this->_settings["user"]=$user;
      $this->_settings["password"]=$password;
    }
		try {
		  $this->_pdo = new PDO($dsn, $this->_settings["user"], $this->_settings["password"],array(PDO::ATTR_PERSISTENT => true));
      $this->_pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,'SET NAMES '.$this->_settings['charset']);
		  $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		  
		  $this->_connected = true;
		} catch (PDOException $e) {
			die($this->_errorLog($e->getMessage()));
		}
	}
  
  /**
  * Disconnect from database</br>
  * Desconecta de la base de datos
  */
	public function disconnect()	{
	 	$this->_pdo = NULL;
	}
		
	private function _prepare($query,$parameters = "") {
	  if(!$this->_connected) { 
      $this->Connect();
    }
	  try {
			$this->_mQry = $this->_pdo->prepare($query);
			$this->bindArray($parameters);
			if(!empty($this->_parameters)) {
				foreach($this->_parameters as $param) {
          $param['value']=($param['value']==="")?NULL:$param['value'];
					$this->_mQry->bindParam($param['param'],$param['value']);
				}
			}
			$this->result = $this->_mQry->execute();		
      $this->error = false;
      $this->errornum = 0;
      $this->errormsg = NULL;
		}	catch(PDOException $e) {
			$this->_errorLog($e->getMessage(), $query);
      $this->error = true;
      $this->errornum = $this->_pdo->errorCode();
      $this->errormsg = $e->getMessage();
		}
		$this->clearBinding();
	}
	
  /**
  * Binds data to parameters</br>
  * Enlaza datos a los parametros
  * @param $param (string) parameter name
  * @param $value (mixed) parameter value
  * @param $type (string) data type
  * @param $trim (boolean) data must be trimmed
  */
	public function bind($param, $value, $type='none', $trim=false)	{	
    if ($type!='none') $value=$this->getValue($value,$type,$trim);
	  $this->_parameters[]['param'] = ":".$param;
    end($this->_parameters);
    $key=key($this->_parameters);
    $this->_parameters[$key]['value']=$value;
    return $value;
	}
  
  /**
  * Bind a array of data to parameters</br>
  * Enlaza un array de datos a parámetros
  * @param $paramarray (array)
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
    $this->_parameters=array();  
  }
  
  /**
  * Returns all rows from query into array</br>
  * Retorna todas las filas en un array
  * @param $query (string)
  * @param (optional) $params (array) binding parameters
  * @param (optional) $fetchmode (constant) can PDO::FETCH_ASSOC, PDO::FETCH_BOTH, PDO::FETCH_CLASS, PDO::FETCH_NUM
  * @return (mixed) array or false
  */
  public function rows($query,$params = NULL, $fetchmode = PDO::FETCH_ASSOC)  {
    $rows=array();
    $query = trim($query);
    if (in_array($this->_fisrt($query),$this->_SELECTION)) {
      $this->_prepare($query,$params);
      if ($this->error==false) {
        $rows = $this->_mQry->fetchAll($fetchmode);
        $this->numrows = count($rows);
        return $rows;
      }
    } 
    return false;
  }

  /**
  * Returns a row from query into array</br>
  * Retorna todas las filas en un array
  * @param $query (string)
  * @param $params (array) binding parameters
  * @param (optional) $fetchmode (constant) can PDO::FETCH_ASSOC, PDO::FETCH_BOTH, PDO::FETCH_CLASS, PDO::FETCH_NUM
  * @return (mixed) array or false
  */
  public function row($query,$params = NULL,$fetchmode = PDO::FETCH_ASSOC) {        
    $query = trim($query);
    if (in_array($this->_fisrt($query),$this->_SELECTION)) {
      $this->_prepare($query,$params);
      if ($this->error==false) {
        return $this->_mQry->fetch($fetchmode);      
      }
    }
    return false;      
  }

  /**
  * Returns next row from query into array</br>
  * Retorna las siguiente fila de una consulta en un array
  * @param (optional) $fetchmode (constant) can PDO::FETCH_ASSOC, PDO::FETCH_BOTH, PDO::FETCH_CLASS, PDO::FETCH_NUM
  * @return (mixed) array or false
  */
  public function nextRow($fetchmode = PDO::FETCH_ASSOC) {        
    if ($this->error==false) {
      return $this->_mQry->fetch($fetchmode);      
    }
    return false;      
  }

  /**
  * Execute a query binding parameters</br>
  * Ejecuta una consulta enlazando parámetros
  * @param $query (string)
  * @param (optional) $params (array) binding parameters
  * @return (mixed) number of affected rows or false
  */
	public function execute($query,$params = NULL)	{
    $query = trim($query);
		if (in_array($this->_fisrt($query),$this->_ACTION)) {
      $this->_prepare($query,$params);
      if ($this->error==false) {
        return $this->affected = $this->_mQry->rowCount();
      }
		}
    return false;
	}

  /**
  * Execute a query without binding parameters</br>
  * Ejecuta una consulta sin enlazar parámetros
  * @param string $query
  * @return number of rows or affected
  */
  public function query($query) {
    $query = trim($query);
    $this->_mQry = $this->_pdo->query($query);
    if ($this->error==false) {
      if (in_array($this->_fisrt($query),$this->_ACTION)) {
        return $this->affected = $this->_mQry->rowCount();
      } else {
        return $this->numrows = $this->_mQry->rowCount();
      }
    }
    return false;
  }
  
  /**
  * Return last inserted primary key value</br>
  * Retorna la última clave promaria insertada
  * @return (mixed) last id
  */
  public function lasttId() {
    return $this->_pdo->lastInsertId();
  }  

  /**
  * Begins Transaction</br>
  * Comienza el control de transacción
  */
  public function beginTrans() {
    try {
      $this->_pdo->beginTransaction();
      $this->trans=true;
    } catch(PDOException $e) {
      $this->_errorLog($e->getMessage());
      $this->error = true;
      $this->errornum = $this->_pdo->errorCode();
      $this->errormsg = $this->_pdo->errorInfo();
    }
  }  
  
  /**
  * Commits a transaction
  * Confirma la transacción
  */
  public function commitTrans() {
    if ($this->trans) {
      $this->_pdo->commit();
      $this->trans=false;
    }
  }  
  
  /**
  * Rolls back a transaction</br>
  * Deshace la transacción
  */
  public function rollBack() {
    if ($this->trans) {
      $this->_pdo->rollBack();
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
        $this->_pdo->commit();
      } catch (PDOException $e) {
        $this->_pdo->rollBack();
      }
      $this->trans=false;
    }
  }
  
  /**
  * Converts a value according to given data type</br>
  * Convierte un valor según el tipo de dato dado
  * @param $value (mixed) 
  * @param $type (string) data type
  * @param (optional) $trim (boolean) trims the value
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
      /*case "timestamp":
        $dateTime=new DateTime($newValue);
        $newValue = ($newValue !== "") ? $dateTime->getTimestamp() : NULL;
        break;*/
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

  private function _fisrt($query) {
    $query=str_replace("\r\n", ' ', $query);
    $array = explode(" ", $query);
    return strtolower($array[0]);
  }
  
	private function _errorLog($message , $sql = "")	{
		$exception = 'Excepción no manejada<br>';
		$exception .= $message;
		$exception .= "<br>Puede encontrar el error en el Log";
		if(!empty($sql)) {
			$message .= "\r\nSQL : ".$sql;
		}
		$this->_log->write($message);
		return $exception;
	}			
  
  /**
  * Returns the database server information
  * Retorna la información del servidor de base de datos
  */
  public function server_info() {
    return $this->_pdo->getAttribute(PDO::ATTR_SERVER_INFO);
  }
  
}
