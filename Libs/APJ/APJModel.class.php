<?php
/*
* APJ Base Model that extends APJPDO class<br>
* Modelo de base que extiende la clase APJPDO
* Version: 1.9.230828
* Author: Ricardo Seiffert
*/
class APJModel extends APJPDO
{
  /**
  * Table name<br>
  * Nombre de la Tabla
  * @var string
  */
  public $table = NULL;
  /**
  * Primary key<br>
  * Clave primaria
  * @var string
  */
  public $pk = array();
  /**
  * Primary key full name<br>
  * Ruta completa de la clave primaria 
  * @var mixed
  */
  public $fullPk = array();
  /**
  * Columns values<br>
  * Valores de columnas
  * @var array
  */
  public $variables = array();
  /**
  * Model structure<br>
  * Estructura del modelo
  * @var array
  */
  public $structure = array();
  /**
  * Model columns<br>
  * columnas del modelo
  * @var array
  */
  public $fields = array();
  /**
  * Defines if columns must by trimmed<br>
  * Define si se recortan los valores de las columnas
  * @var boolean
  */
  public $trim = false;
  /**
  * Errors array<br>
  * Array de errores 
  * @var array
  */
  public $errors = array();
  /**
  * Columns Alias/Comment<br>
  * Alias/Comentario de las columnas
  * @var array
  */
  public $alias = array();
  /**
  * Values in associative array for update, insert and others<br>
  * Array asociativo de valores para update, insert y otros
  * @var array
  */
  private $values = NULL;
  /**
  * Conditions for update, delete and find (associative array or literal condition)<br>
  * Condicione para update, delete y find
  * @var mixed array or string
  */
  private $where = NULL;

  /**
  * Array with join instructions for query methods<br>
  * Arreglo con instrucciones join para los métodos de consultas
  * @var array
  */
  private $joins = array();
  
  /**
  * Limit of rows for query methods<br>
  * Limite de filas para los métodos de consultas
  * @var int
  */
  private $limit = NULL;
  
  /**
  * Query order<br>
  * Orden de la consulta
  * @var string
  */
  private $order = NULL;
  
  /**
  * Define charset to use<br>
  * Define el juego de caractéres a usar
  * @var string
  */
  private $charset = 'utf-8';

  /**
  * Array of columns to be stored in lowercase<br>
  * Array asociativo de columnas que deben guardarse en minúsculas
  * @var array
  */
  public $toLower = array();

  /**
  * Array of columns to be stored in uppercase<br>
  * Array asociativo de columnas que deben guardarse en mayúsculas
  * @var array
  */
  public $toUpper = array();
  
  /**
  * Paging properties<br>
  * Propiedades de paginación
  * @var int
  */
  public $lastPage = 0;
  public $currentPage = 0;
  public $previousPage = 0;
  public $nextPage = 0;
  
  private $quotes = array("'",'"');
  
  // Common methods Trait
  use APJCommon;
  
  /**
  * Constructor (connects to database)<br>
  * Constructor (conecta con la base de datos)
  */
  public function __construct($dsn=NULL,$user=NULL,$password=NULL,$charset=NULL) {
    parent::__construct($dsn,$user,$password,$charset);    
    $this->_clearError();
  }
  
  /**
  * Defines the table name and reads model<br>
  * Define el nombre de la tabla y lee el modelo
  * @param (string) Table name
  */
  public function setTable($table) {
    $this->_clearError();
    $this->table=$table;
    $this->_defineModel();
  }
  
  /**
  * Set the columns Alias<br>
  * Define los Alias de las columnas
  * @param (array) Array of alias names ('name'=>'Alias'...)
  */
  public function setAlias($names) {
    $i=0;
    foreach ($this->fields as $fld) {
      $this->alias[$fld]=$names[$i];
      $i++;
    }  
  }
  
  /**
  * Extracts and define the model structure<br>
  * Extrae y define la estructura del modelo
  */
  private function _defineModel() {
    $sql="SHOW FULL COLUMNS FROM `{$this->table}`";
    if ($struc=$this->rows($sql)) {
      foreach ($struc as $str) {
        $type=$this->_type($str['Type']);
        $size=$this->_size($type,$str['Type']);
        $this->structure[$str['Field']]['Type']=$type;
        $this->structure[$str['Field']]['Size']=$size[0];
        $this->structure[$str['Field']]['Decimals']=$size[1];
        $this->structure[$str['Field']]['Null']=$str['Null'];
        $this->structure[$str['Field']]['Key']=$str['Key'];
        $this->structure[$str['Field']]['Default']=$str['Default'];
        $this->structure[$str['Field']]['Extra']=$str['Extra'];
        $this->structure[$str['Field']]['Comment']=$str['Comment'];
        $this->alias[$str['Field']]=$str['Comment'];
        if ($str['Key']=="PRI") {
          $this->pk[]=$str['Field'];
          $this->fullPk[]="`{$this->table}`.".$str['Field'];
        }
      }
      $this->fields = array_keys($this->structure);
    }
  }

  /**
  * Extracts a foreign table structure<br>
  * Extrae la estructura de una tabla foranea
  */
  private function _foreignModel($table) {
    $sql="SHOW FULL COLUMNS FROM ".$table;
    if ($structure=$this->rows($sql)) {
      return $structure;
    }
    return false;
  }

  /**
  * Show table structure<br>
  * Muestra la estructura de la tabla
  */
  public function showStructure() {
    $out='<textarea rows="30" cols="120">';
    $out.='$this->table = "'.$this->table.'";'.PHP_EOL;
    $out.='$this->structure = array(';
    foreach ($this->structure as $fld=>$infos) {
      $out.="'{$fld}'=>array(";
      foreach ($infos as $info=>$value) {
        if ($value==NULL or is_numeric(trim($value))) {
          $sep='';
          if ($value==NULL) {
            $value='NULL';
          }
        } else {
          $sep="'";
        }
        $out.="'{$info}'=>".$sep.$value.$sep.',';
      }
      $out=substr($out,0,-1);
      $out.="),";
    }
    $out=substr($out,0,-1);
    $out.=');'.PHP_EOL;
    if ($this->alias) {
      $out.='$this->alias = array(';
      foreach ($this->alias as $fld=>$comment) {
        $out.="'{$fld}'=>'{$comment}',";
      }
      $out=substr($out,0,-1);
      $out.=');'.PHP_EOL;
    }
    if ($this->pk) {
      $out.='$this->pk = array(';
      foreach ($this->pk as $pk) {
        $out.="'{$pk}',";
      }
      $out=substr($out,0,-1);
      $out.=');'.PHP_EOL;
    }
    if ($this->fullPk) {
      $out.='$this->fullPk = array(';
      foreach ($this->fullPk as $pk) {
        $out.="'{$pk}',";
      }
      $out=substr($out,0,-1);
      $out.=');'.PHP_EOL;
    }
    $out.='$this->fields = array_keys($this->structure);';
    $out.='</textarea>';
    echo $out;
    exit();
  }
  
  /**
  * Show table model in a textarea (for documentation)<br>
  * Muestra el modelo de la tabla en un textarea (para documentación)
  * @param (boolean) short description (default false)
  */
  public function showModel($short=false) {
    $this->_clearError();
    $arr=array();
    $tab=array();
    $tr='';
    $sizes=array();
    $arr[0]['Field']=' Field ';
    foreach ($this->structure as $fld=>$str) {
      foreach ($str as $key=>$value) {
        if ($short and ($key=="Comment" or $key=="Extra")) {
          break; 
        } else {
          $arr[0][$key]=" {$key} ";
        }
      }
      break;
    }
    $inx=1;
    foreach ($this->structure as $fld=>$str) {
      $arr[$inx]['Field']=" {$fld} ";
      foreach ($str as $key=>$value) {
        if ($short and ($key=="Comment" or $key=="Extra")) {
          break; 
        } else {
          $arr[$inx][$key]=" {$value} ";
        }
      }
      $inx++;
    }
    foreach ($arr as $ar) {
      foreach ($ar as $k=>$a) {
        $size=strlen($a);
        if ($sizes[$k]<$size) {
          $sizes[$k]=$size;
        }
      }
    }
    foreach ($sizes as $size) {
      $tr.='+'.str_repeat('-',$size);
    }
    $tr.='+';
    foreach ($arr as $inx=>$fld) {
      foreach ($fld as $key=>$value) {
        if ($inx==0) {
          $align=STR_PAD_BOTH;
        } elseif (is_numeric(trim($value))) {
          $align=STR_PAD_LEFT;
        } else {
          $align=STR_PAD_RIGHT;
        }
        $tab[$inx].='|'.str_pad($value,$sizes[$key]," ",$align);
      }
      $tab[$inx].='|';
    }
    $out='<textarea rows="30" cols="120">';
    $out.='/*'.PHP_EOL;
    $out.='Table structure of ['.$this->table.']'.PHP_EOL;
    $out.=$tr.PHP_EOL;
    foreach ($tab as $k=>$row) {
      $out.=$row.PHP_EOL;
      if ($k==0) {
        $out.=$tr.PHP_EOL;
      }
    }
    $out.=$tr.PHP_EOL;
    $out.='*/'.PHP_EOL;
    $out.='</textarea>';
    echo $out;
    exit();
  }
  
  /**
  * Set model column properties and value, if exist in strcture (overloading)<br>
  * Crea la columna del modelo con su valor, si existe en la estructura (sobrecarga) 
  * @param (string) column name
  * @param (mixed) column value
  */
  public function __set($name,$value){
    if (isset($this->structure[$name]['Type'])) {
      $value = $this->_upperLower($name,$value);
      $this->variables[$name] = $this->getValue($value,$this->structure[$name]['Type'],$this->trim);
      return;
    }
    $trace = debug_backtrace();
    trigger_error(
    'Propiedad indefinida mediante __set(): ' . $name .
    ' en ' . $trace[0]['file'] .
    ' en la línea ' . $trace[0]['line'],
    E_USER_NOTICE);
  }

  /**
  * Converts the values of the specified columns to lowercase or uppercase<br>
  * Convierte a minúsculas o mayúsculas los valores de las columnas especificadas
  * @param (string) Column name
  * @param (mixed) Column value
  * @return (mixed) New value
  */
  private function _upperLower($name,$value) {
    if (in_array($name,$this->toUpper)) {
      return mb_strtoupper($value,$this->charset);
    }
    if (in_array($name,$this->toLower)) {
      return mb_strtolower($value,$this->charset);
    }
    return $value;
  }
  
  /**
  * Get model columns values (overloading)<br>
  * Obitene los valores de las columnas del modelo (sobrecarga)
  * @param (string) Name
  * @return (mixed) Value
  */
  public function __get($name) {  
    if(is_array($this->variables)) {
      if(array_key_exists($name,$this->variables)) {
        return $this->variables[$name];
      }
    }
    $trace = debug_backtrace();
    trigger_error(
    'Propiedad indefinida mediante __get(): ' . $name .
    ' en ' . $trace[0]['file'] .
    ' en la línea ' . $trace[0]['line'],
    E_USER_NOTICE);
    return null;
  }
  
  /**
  * Basic columns validation by model and set errors propertie<br>
  * Validación básica según modelo y rellena la propiedad errors
  * @return (boolean) true = has errors, false = no errors
  */
  public function basicValidation() {
    $this->_clearError();
    if (is_array($this->structure)) {
      foreach ($this->fields as $fld) {
        $var=$this->variables[$fld];
        $type = $this->structure[$fld]['Type'];
        $size = $this->structure[$fld]['Size'];
        $decimals = $this->structure[$fld]['Decimals'];
        $null = $this->structure[$fld]['Null'];
        $default = $this->structure[$fld]['Default'];
        $extra = $this->structure[$fld]['Extra'];
        $comment = $this->structure[$fld]['Comment']; 
        if (in_array($fld,$this->pk)==FALSE and $null === 'NO' and strlen($var)==0 and strlen($default)>0) {
          $var=$default;
          $this->variables[$fld]=$default;
        }
        switch (true) {
          case (in_array($fld,$this->pk) and $extra != 'auto_increment' and (is_null($var) or strlen($var)==0)):
          case (in_array($fld,$this->pk)==FALSE and $null === 'NO' and strlen($var)==0 and strlen($default)==0):
            $this->errors[$fld] = "no puede estar vacío";
            break;
          case ($size and iconv_strlen($var)>$size):
            $this->errors[$fld] = "excede el tamaño definido ({$size}).";
            break;
        }
        if (in_array($fld,$this->pk)==FALSE and strlen($var)>0) {
          switch ($type) {
            case 'int':
            case 'bigint':
            case 'tinyint':
            case 'smallint':
            case 'boolean':
              if(!is_numeric($var))  {
                $this->errors[$fld] = "no es un número entero.";
              }
              break;
            case 'date':
              if ($this->verifyDate($var, 'Y-m-d', true)) {
                $this->errors[$fld] = "no es una fecha válida";
              }
              break;
            case 'datetime':
              if ($this->verifyDate($var,'Y-m-d H:i:s', false)) {
                $this->errors[$fld] = "no es una fecha/hora válida";
              }
              break;
            case 'timestamp':
              if ($this->verifyDate($var,'timestamp', true)) {
                $this->errors[$fld] = "no es una fecha/hora válida";
              }
              break;
            case 'time':
              if ($this->verifyDate($var, 'H:i:s', false)) {
                $this->errors[$fld] = "no es una hora válida";
              }
              break;
            case 'year':
              if ($this->verifyDate($var, 'Y', true)) {
                $this->errors[$fld] = "no es un año válido";
              }
              break;
          }
        }
      }
    }
    if (count($this->errors)>0) {
      $this->error=true;
    }
    return (count($this->errors)>0);
  }
  
  /**
  * Assigns object/array values to model matching columns<br>
  * Asigna los valores de un objeto/arreglo a las columnas del modelo
  * @param mixed $object
  */
  protected function objectToModel($object) {
    if (is_array($object) or is_object($object)) {
      foreach ($object as $name => $value) {
        if (in_array($name, $this->fields)) {
          $this->$name = $value;
        }
      }
    }
  }
  
  /**
  * Formats columns values according to data type defined in init.php file<br>
  * Formatea los valores de las columnas según los tipos de datos definidos en el archivo init.php
  * @param (array) Row array
  * @return (array) Formatted row array
  */
  public function setFormat($row) {
    $rowf=array();
    if (is_array($row) and isset($row)) {
      foreach ($row as $fld=>$value) {
        $this->$fld = $value;
        $type=$this->structure[$fld]['Type'];
        $rowf[$fld] = $this->format($value,$type);
      }
    }
    return $rowf;
  }
  
  /**
  * Update table<br>
  * Actualiza tabla
  * @param (mixed) where condition as array o string (optional if primary key is defined)
  * @param (array) columns values in associative array (optional if values are defined)
  * @return (mixed) number of affected rows or false if any error
  */
  public function update($where='', $values='') {
    $this->_clearError();
    $fieldsvals = '';
    if ($this->_values($values)) {
      foreach($this->values as $column=>$val) {
        $fieldsvals .= $column . " = :". $column . ",";
      }
      $fieldsvals = substr($fieldsvals , 0, -1);
      if ($this->_condition($where,true)) {
        $sql = "UPDATE " . $this->table .  " SET " . $fieldsvals . $this->_queryWhere();
        return $this->execute($sql,$this->values);
      }
    }
    return false;
  }
  
  /**
  * Insert into table<br>
  * Insertar en la tabla
  * @param (array) Associative array of values (optional if values are set)
  * @param (boolean) Ignore duplicate rows (default false)
  * @return (mixed) number of affected rows or false if any error
  */
  public function insert($ignore=false,$values=NULL) { 
    $this->_clearError();
    if($this->_values($values)) {
      $fields='';
      $fieldsvals='';
      foreach($this->values as $column=>$val) {
        $fields .= "{$column},";
        $fieldsvals.=",:{$column}";
      }
      $fields = substr($fields , 0, -1);
      $fieldsvals = substr($fieldsvals , 1);
      $ignsql=($ignore)?" IGNORE ":" ";
      $sql = "INSERT{$ignsql}INTO `{$this->table}` ({$fields}) VALUES ({$fieldsvals})";
      return $this->execute($sql,$this->values);
    } else {
      return false;
    }
  }
  
  /**
  * Replace into table<br>
  * Reemplaza en la tabla
  * @return (mixed) number of affected rows or false if any error
  */
  public function replace($values='') { 
    $this->_clearError();
    if($this->_values($values,true)) {
      $fields='';
      $fieldsvals='';
      foreach($this->values as $column=>$val) {
        $fields .= "{$column},";
        $fieldsvals.=",:{$column}";
      }
      $fields = substr($fields , 0, -1);
      $fieldsvals = substr($fieldsvals , 1);
      $sql = "REPLACE INTO `{$this->table}` ({$fields}) VALUES ({$fieldsvals})";
      return $this->execute($sql,$this->values);
    } else {
      return false;
    }
  }

  /**
  * Deletes a row<br>
  * Elimina una fila
  * @param (mixed) array/int or WHERE string condition (optional if primary key value is set)
  * @return (mixed) number of affected rows or false if any error
  */
  public function delete($where='') {
    $this->_clearError();
    if ($this->_condition($where,true)) {
      $sql = "DELETE FROM `{$this->table}` {$this->_queryWhere()}";
    } else {
      return false;
    }
    return $this->execute($sql,$this->values);
  }

  /**
  * Truncate a table<br>
  * Trunca una tabla
  * @param (string) $table
  * @return (mixed)
  */
  public function truncate($table=NULL) {
    $this->_clearError();
    $table=($table)?$table:$this->table;
    return $this->query("TRUNCATE TABLE `{$table}`");
  }  
  
  /**
  * Drop a table<br>
  * Elimina una tabla
  * @param (boolean) is a temporary table (optional)
  * @param (string) name of the table, if not specified it will be the current table (optional) 
  * @return (mixed)
  */
  public function drop($temporary=false,$table=NULL) {
    $this->_clearError();
    $table=($table)?$table:$this->table;
    $temp=($temporary)?'TEMPORARY':'';
    return $this->query("DROP {$temp} TABLE IF EXISTS `{$table}`");
  }
  
  /**
  * Find a row and assigns values<br>
  * Encuentra una fila y asigna los valores
  * @param (mixed) array or string where condition (optional if primary key value is set)
  * @return (array) Associative row array, false if error
  */
  public function find($where='') {
    $this->_clearError();
    if ($this->_condition($where,true)) {
      $sql=$this->_querySelect().$this->_queryWhere().$this->_queryOrder().$this->_queryLimit();
      return $this->variables = $this->row($sql,$this->values);
    }
    return false;
  }
  
  /**
  * Returns all rows that meet the condition in LIKE<br>
  * Devuelve todas las fila que cumplen la condición LIKE
  * @param (string) Column
  * @param (string) Search condition
  * @return (array) Result array
  */
  public function like($field, $search) {
    $this->_clearError();
    if (in_array($field,$this->fields) and $search) {
      return $this->rows($this->_querySelect()." WHERE {$field} LIKE '{$search}'".$this->_queryLimit());
    }
  }
  
  /**
  * Returns all table rows by given order<br>
  * Devuelve todas las filas de la tabla según orden dado
  * @param (string) Comma separated order columns (optional)
  * @return (mixed) Associative rows array or false if any error
  */
  public function all($order=''){
    $this->_clearError();
    $order=($order)?" ORDER BY {$order}":$this->_queryOrder();
    return $this->rows($this->_querySelect().$order.$this->_queryLimit());
  }
  
  /**
  * Returns all rows for given condition<br>
  * Devuelve todas las filas para la condición dada
  * @param (mixed) Array or string where condition (optional if values are set)
  * @param (string) Comma separated order columns (optional)
  * @return (mixed) Associative rows array or false if any error
  */
  public function select($condition='', $order=''){
    $this->_clearError();
    $order=($order)?" ORDER BY {$order}":$this->_queryOrder();
    if ($this->_condition($condition,false)) {
      return $this->rows($this->_querySelect().$this->_queryWhere().$order.$this->_queryLimit(),$this->values);
    }
    return false;
  }
  
  private function _queryJoin() {
    $joins="";
    if (count($this->joins)>0) {
      foreach ($this->joins as $join) {
        $joins.=$join." ";
      }
    }
    return $joins;
  }
  
  private function _querySelect() {
    return "SELECT {$this->_queryColumns()} FROM `{$this->table}`".$this->_queryJoin();
  }
  
  private function _queryColumns() {
    $columns="*";
    if (count($this->joins)>0) {
      $columns="";
      foreach ($this->fields as $field) {
        $columns.="`{$this->table}`.{$field}, ";
      }
      $fieldArray=$this->fields;
      $cumul=array();
      $as=array();
      foreach ($this->joins as $table=>$join) {
        if ($fields=$this->_foreignModel($table)) {
          foreach ($fields as $field) {
            $col=$field['Field'];
            $columns.="`{$table}`.{$col}";
            if (in_array($col,$fieldArray)) {
              $cumul[$col]++;
              $as=$col.$cumul[$col];
              $columns.=" AS {$as}";
            }
            $columns.=", "  ;
            $fieldArray[]=$col;
          }
        }
      }
      $columns=substr($columns,0,-2);
    }
    return $columns;
  }
  
  private function _queryWhere() {
    $where="";
    if ($this->where) {
      $where=" WHERE ".$this->where;
    }
    return $where;
  }
  
  private function _queryOrder() {
    $order="";
    if ($this->order) {
      $order=" ORDER BY ".$this->order;
    }
    return $order;
  }
  
  /**
  * Sets a foreign table Join for query methods<br>
  * Define un Join con tablas foráneas en los métodos de consultas
  * @param (string) Foreign table name
  * @param (string) Local column 
  * @param (string) Column comparator
  * @param (string) Foreign Key column to compare with
  * @param (string) Type of join (INNER, LEFT, RIGHT) Default: INNER
  * @return (mixed)
  */
  public function join($table,$column,$comparator,$foreignKey,$type="INNER") {
    $types=array('INNER','LEFT','RIGHT','LEFT OUTER','RIGHT OUTER');
    $type=strtoupper($type);
    if ($table and in_array($type,$types)) {
      $join=" {$type} JOIN `{$table}` ON (";
      if ($column) {
        $column=(strpos($column,'.')>0)?$column:"`{$this->table}`.{$column}";
        $join.=$column;
        if ($comparator) {
          $join.=" {$comparator} ";
          if ($foreignKey) {
            $column=(strpos($foreignKey,'.')>0)?$foreignKey:"`{$table}`.{$foreignKey}";
            $join.="{$column})";
            $this->joins[$table]=$join;
            return true;
          }
        }
      }
    }
    $this->error = true;
    $this->errormsg = "Error defining the join";
    return false;
  }  
  
  /**
  * Sets query limits<br>
  * Define los limites de consultas
  */
  private function _queryLimit() {
    if ($this->limit > 0) {
      return " LIMIT {$this->limit}";
    }
    return "";
  }
  
  /**
  * Sets query limit for query methods<br>
  * Define el limie para los métodos de consulta
  * 
  * @param mixed $limit
  */
  public function limit($limit) {
    $this->limit = $limit;
  }  
   
  /**
  * Clear columns values<br>
  * Limpia los valores de las columnas;
  */
  public function clearValues() {
    $this->variables=array();
  }
  
  /**
  * Clear foreign joins<br>
  * Limpia los joins foraneos
  */
  public function clearJoins() {
    $this->joins = array();  
  }
  
  /**
  * Returns the minimum value of a column<br>
  * Devuelve el valor mínimo de una columna
  * @param (string) Column name
  * @param (mixed) Array or string where condition (optional if values are set)
  * @return (mixed) Min column value
  */
  public function min($field,$condition=NULL)  {
    $this->_clearError();
    $params="";
    if ($this->_condition($condition,false)) {
      $params=$this->values;
    }
    if ($row = $this->row("SELECT MIN({$field}) FROM `{$this->table}` {$this->_queryWhere()}",$params,PDO::FETCH_NUM)) {
      return $row[0];
    }
    return false;
  }
  
  /**
  * Returns the maximum value of a field<br>
  * Devuelve el valor máximo de un campo
  * @param (string) Column name
  * @param (mixed) Array or string where condition (optional if values are set)
  * @return (mixed) Max column value
  */
  public function max($field,$condition=NULL)  {
    $this->_clearError();
    $params="";
    if ($this->_condition($condition,false)) {
      $params=$this->values;
    }
    if ($row = $this->row("SELECT MAX({$field}) FROM `{$this->table}` {$this->_queryWhere()}",$params,PDO::FETCH_NUM)) {
      return $row[0];
    }
    return false;
  }
  
  /**
  * Returns the average value of a field<br>
  * Devuelve el valor promedio de un campo
  * @param (string) Column name
  * @param (mixed) Array or string where condition (optional if values are set)
  * @return (mixed) Average column value
  */
  public function avg($field,$condition=NULL)  {
    $this->_clearError();
    $params="";
    if ($this->_condition($condition,false)) {
      $params=$this->values;
    }
    if ($row = $this->row("SELECT AVG({$field}) FROM `{$this->table}` {$this->_queryWhere()}",$params,PDO::FETCH_NUM)) {
      return $row[0];
    }
    return false;
  }
  
  /**
  * Returns the sum of a field<br>
  * Devuelve la suma de un campo
  * @param (string) Column name
  * @param (mixed) Array or string where condition (optional if values are set)
  * @return (mixed) Sum of the column values
  */
  public function sum($field,$condition=NULL)  {
    $this->_clearError();
    $params="";
    if ($this->_condition($condition,false)) {
      $params=$this->values;
    }
    if ($row = $this->row("SELECT SUM({$field}) FROM `{$this->table}` {$this->_queryWhere()}",$params,PDO::FETCH_NUM)) {
      return $row[0];
    }
    return false;
  }
  
  /**
  * Returns the count of a field<br>
  * Devuelve la cuenta de un campo
  * @param (string) Column name
  * @param (mixed) Array or string where condition (optional if values are set)
  * @return (int) Count of column values
  */
  public function count($field,$condition=NULL)  {
    $this->_clearError();
    $params="";
    if ($this->_condition($condition,false)) {
      $params=$this->values;
    }
    if ($row = $this->row("SELECT COUNT({$field}) FROM `{$this->table}` {$this->_queryWhere()}",$params,PDO::FETCH_NUM)) {
      return $row[0];
    }
    return false;
  }  

  /**
  * Returns an array of paged results<br>
  * Devuelve un array con resultado paginados
  * @param (string) SQL query
  * @param (int) Limit of returned rows (default 20)
  * @param (int) Page to return (default 1)
  * @return (mixed) Paged result array or false if any error
  */
  public function paging($query, $limit=20,$page=1) {
    if (empty($query)) {
      $query="SELECT {$this->_queryColumns()} FROM `{$this->table}`".$this->_queryJoin().$this->_queryWhere().$this->_queryOrder(); 
    }
    if ($all=$this->query($query)) {
      $this->lastPage = ceil($all / $limit);
      $this->currentPage = $page;
      $this->previousPage = ($page>1)?$page-1:1;
      $this->nextPage = ($page<$this->lastPage)?$page+1:$this->lastPage;
      $offset=($page-1)*$limit;
      $pagedQuery=$query." LIMIT {$limit} OFFSET {$offset}";
      return $this->rows($pagedQuery);
    }
    return false;
  }
  
  /**
  * Assign values to values array if in field structure<br>
  * Asigna valores al array values con columnas coincidentes de la estructura
  * @param (array) Array of values (optional)
  * @param (boolean) include id
  * @return (boolean) True if values are assigned and false if values are missing
  */
  private function _values($values,$incId=false) {
    $this->values=array();
    if (empty($values) and $this->variables) {
      foreach ($this->variables as $column=>$val) {
        if (in_array($column, $this->fields)) {
          if ($this->structure[$column]['Extra']!="auto_increment" or $incId) {
            $this->values[$column]=$val;
          }
        }
      }
      return true;
    } elseif (is_array($values)) {
      foreach ($values as $column=>$val) {
        if (in_array($column, $this->fields)) {
          if ($this->structure[$column]['Extra']!="auto_increment" or $incId) {
            $this->values[$column]=$val;
          }
        }
      }
      return true;
    }
    $this->error=true;
    $this->errormsg="The values are missing";
    return false;
  }
  
  /**
  * Sets query order<br>
  * Establece el ordern de la consulta;
  * @param string Order
  */
  public function order($order) {
    $this->order = $order;
  }  
  
  /**
  * Sets query condition<br>
  * Establece la condición de la consulta;
  * @param mixed array or string condition
  * @return (boolean)
  */
  public function where($condition) {
    return $this->_condition($condition,true);
  }  
  
  /**
  * Creates the WHERE condition<br>
  * Crea la condición WHERE
  * @param (mixed) Condicion
  * @return (boolean) True if condition could be created
  */
  private function _condition($condition,$mandatory=true) {
    $where='';
    if(empty($condition) and !empty($this->where) and $mandatory) {
      return true;
    } elseif(empty($condition) and !$mandatory) {      
      $this->where = NULL;
      return true;
    } elseif(empty($condition) and $this->variables) {
      foreach ($this->pk as $inx=>$fld) {
        $fldinx=$fld.$inx;
        $where.= "{$this->fullPk[$inx]} = :{$fldinx} AND ";
        $this->values[$fldinx]=$this->variables[$fld];
      }
      $this->where=(strlen($where)>5)?substr($where,0,-5):$where;
      return true;
    } elseif (is_array($condition)) {
      $count=0;
      foreach ($condition as $fld=>$val) {
        $fldinx=$fld.$count;
        $where.="{$fld}=:{$fldinx} AND ";
        $this->values[$fldinx]=$val;
        $count++;
      }
      $this->where=(strlen($where)>5)?substr($where,0,-5):$where;
      return true;
    } elseif (strlen($condition)>0 and is_numeric($condition)) {
      $this->where = "{$this->fullPk[0]}={$condition}";      
      return true;
    } elseif (strlen($condition)>0) {
      $this->where = $condition;
      return true;
    }
    $this->where = NULL;
    $this->error=true;
    $this->errormsg="There are no defined conditions";
    return false;
  }
  
  /**
  * Extracts the column type from structure<br>
  * Extrae el tipo de columna de la estructura
  * @param (string) Structure colunmn type
  * @return (string) Colunmn type
  */
  private function _type($type) {
    if (($len=strpos($type,"(",0))>0) {
      return substr($type,0,$len);
    }
    return $type;
  }

  /**
  * Extracts column size from structure<br>
  * Extrae el tamaño de la columna de la estructura
  * @param (string) Structure type
  * @param (string) Simple type
  * @return (array) Size (integers,decimals)
  */
  private function _size($btype,$type) {
    $type=str_replace($this->quotes, "", $type);
    if ($size=$this->getStringBetween($type,'(',')',1)) {
      if($btype=='enum' and strpos($size,",")) {
        $enumValues=explode(',',$size);
        $size=1;
        foreach ($enumValues as $value) {
          $esize=iconv_strlen($value);
          $size=($esize>$size)?$esize:$size;
        }
        $result=array($size,NULL);
      } elseif (strpos($size,",")>0) {
        $result=explode(",",$size);
      } else {
        $result=array($size,NULL);
      }
    } else {
      switch ($btype) {
        case "date":
          $result=array(10,NULL);
          break;
        case "datetime":
          $result=array(19,NULL);
          break;
        case "time":
          $result=array(8,NULL);
          break;
        default:
          $result=array(NULL,NULL);
      }
    }
    return $result;
  }

  /**
  * Clear all error properties<br>
  * Limpia todas las propiedades de errores
  */
  private function _clearError() {
    $this->error = false;
    $this->errormsg = NULL;
    $this->errors = array();
    $this->values = NULL;
    $this->where = NULL;
  }  

  /**
  * Set the Charset<br>
  * Define el charset
  * @param mixed $charset
  */
  public function setCharset($charset="utf-8") {
    $this->charset = $charset;
  }
  
}
