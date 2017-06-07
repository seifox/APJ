<?php
/**
* APJ's parent Controller
* Controlador padre de APJ
* Versión: 1.17.0604
* Author: Ricardo Seiffert
*/
class APJController
{
  /**
  * Can redner<br>
  * Puede renderizar
  * @var bool
  */
  protected $canRender = true;
  /**
  * Controller filename<br>
  * Archivo del Controlador
  * @var string
  */
  protected $_self = NULL;
  /**
  * Form Object<br>
  * Objeto Form (formulario)
  * @var stdClass
  */
  public $Form;
  
  /**
  * Ajax default timeout
  * Timeout por defecto de Ajax
  * @var int
  */
  protected $TimeOut = 10000;
  
  /**
  * User Id in session
  * Id del usuario en la sesión
  * @var int
  */
  protected $userId = NULL;
  
  /**
  * Array of form fields types, used in setFormValues
  * Arreglo de tipo de campos del formulario, usado en seFormValues
  * @var array
  */
  protected $fieldTypes = array();

  /**
  * Array paging properties<br>
  * Propiedades de paginación de arreglos
  * @var int
  */
  protected $lastPage = 0;
  protected $currentPage = 0;
  protected $previousPage = 0;
  protected $nextPage = 0;
  
  /**
  * Constructor
  * @param (string) Rendered view
  */
  public function __construct($page='') {
    $method = isset($_POST['action'])?$_POST['action']:null;
    $data = isset($_POST['data'])?$_POST['data']:$_POST;
    $this->_unsetAction();
    if (method_exists($this,$method)) {
      $data=$this->_isJson($data);
      $this->getForm();
      $this->{$method}($data);
      $this->getResponse();
    } elseif ($method) {
      $this->jError("El metodo {$method} no existe!");
      $this->getResponse();
    } elseif ($page) {
      $this->render($page,false);
    }
  }
  
  /**
  * Renders the view</br>
  * Renderiza la vista
  * @param (string) View name
  * @param (boolean) true=Returns the rendered view, false=displays the view (optional)
  */
  protected function render($page,$return=false) {
    if ($this->canRender) {
      $url=VIEWS.DIRECTORY_SEPARATOR.$page;
      $html=$this->_getContent($url);
      if ($html) {
        $replace='<head><base href="'.ROOTURL.'/">';
        $html = str_replace('<head>', $replace, $html);
        if ($return) {
          return $html;
        } else {
          echo $html;
        }
      } else {
        $html="Can't open view ".$url;
        if ($return) {
          return $html;
        } else {
          echo $html;
        }
      }
    }
  }

  /**
  * Controls de session
  * Control de sesión
  */
  protected function sessionControl() {
    if (!APJSession::active(APPNAME)) {
      $this->redirect(LOGIN,true);
    }
    APJSession::start(APPNAME,3600);
    if (!isset($_SESSION['id']) or !isset($_SESSION['IPaddress']) or !isset($_SESSION['userAgent'])) {
      $this->redirect(LOGIN,true);
    }
    $app=md5($this->getController());
    if (in_array($app,$_SESSION['app'])==FALSE) {
      $this->redirect(LOGIN,true);
    }
    $this->userId = $_SESSION['id'];
  }
  
  /**
  * Extracts into array the (optional) adicional parameters from APJSubmit
  * Extrae los parametros (opcionales) adicionales que envía APJSubmit en un array
  * @param (string) Parameters submitted from APJSubmit
  * @return (array) Parameters array
  */
  private function getParameters($params) {
    $string = trim($params,'[');
    $string = trim($string,']');
    $string = str_replace('"', "", $string);
    $string = str_replace("'", "", $string);
    $result = explode(',',$string);
    return $result;
  }
  
  /**
  * Create a Form objet from submitted form
  * Crea un objeto Form con los campos del formulario enviado
  */
  private function getForm() {
    $this->createForm();
    foreach ($_REQUEST as $name => $value) {
      if ($name != 'action' and $name != 'parameters') {
        $this->Form->$name = $value;
      } elseif ($name === 'parameters') {
        $this->Form->parameters = $this->getParameters($value);
      }
    }
  }
  
  /**
  * Assign matching form fields to the Model<br>
  * Asigna los campos coincidentes del formulario al Modelo
  * @param (string) Model name
  */
  protected function formToModel($model) {
    if (isset($_REQUEST['action'])) {
      foreach ($_REQUEST as $name => $value) {
        if ($name != 'action' and in_array($name, $model->fields)) {
          $model->$name = $value;
        }
      }
    }
  }
  
  /**
  * Assign matching Form object fields to the Model<br>
  * Asigna los campos coincidentes del objeto Form al Modelo
  * @param (string) Model name
  */
  protected function formObjectToModel($model) {
    $this->createForm();
    foreach ($this->Form as $name => $value) {
      if (in_array($name, $model->fields)) {
        $model->$name = $value;
      }
    }
  }
  
  /**
  * Assigns Model values to Form object<br>
  * Asigna valores del Modelo al objeto Form
  * @param (string) Model name
  */
  protected function modelToForm($model) {
    $this->createForm();
    $fields=$model->fields;
    foreach ($fields as $name) {
      $this->Form->$name = $model->$name;
    }
  }
  
  /**
  * Clear Form Object
  * Limpia el objeto Form
  */
  protected function clearForm() {
    unset($this->Form);
  }
  
  /**
  * If not exist, create tehe Form object
  * Si no exite, crea el objeto Form
  */
  private function createForm() {
    if (!isset($this->Form)) {
      $this->Form = new stdClass;
    }
  }
  
  /**
  * Sets the html form values from Form object or array
  * Asigna los valores del formulario html desde objeto Form o Array
  * @param (mixed) Array or object (optional)
  */
  protected function setFormValues($data='') {
    if (empty($data)) {
      $data=$this->Form;
    }
    foreach ($data as $field=>$value) {
      $this->Form->$field = $value;
      if ($this->fieldTypes[$field]=="checkbox" or $this->fieldTypes[$field]=="radio") {
        if ($value) {
          $this->jQ("#{$field}")->prop('checked', true);
        } else {
          $this->jQ("#{$field}")->prop('checked', false);
        }
      } else {
        $this->jQ("#{$field}")->val($value);
      }
    }
  }
  
  private function _isJson($json) {
    if (is_string($json) and (is_object(json_decode($json)) or is_array(json_decode($json)))) {
     return json_decode($json);
    } else {
     return $json;
    }
  }
  
  private function _APJCall($params) {
    if (is_array($params)) {
      $func=$params[0];
      if (count($params)>1) {
        $data=array_slice($params,1);
      }
      if (method_exists($this,$func)) {
        return $this->{$func}($data);  
      }
    }
  }

  private function _getContent($page) {
    $html=$this->getLocalContent($page);
    if ($html) {
      $html=$this->_ApjReplace($html);
    }
    return $html;
  }

  /**
  * Gets local file content<br>
  * Obtiene el contenido de un archivo local
  * @param (string) File url
  * @return (string) File content
  */
  protected function getLocalContent($url) {
    if (file_exists($url) and is_readable($url)) {
      try {
        $file = $this->getContent($url);
      } catch (Exception $e) {
        $file = NULL;
      }
    } else {
      $file=NULL;
    }
    return $file;
  }
  
  /**
  * Gets a file content<br>
  * Obtiene el contenido de un archivo
  * @param (string) File url
  * @return (string) File content
  */
  protected function getContent($url) {
    if (ini_get('allow_url_fopen')!=1) {
      ini_set('allow_url_fopen',1);
    }
    try {
      $file = file_get_contents($url);
    } catch (Exception $e) {
      $file = NULL;
    }
    return $file;
  }
  
  private function _unsetAction() {
    unset($_POST['action']);
    unset($_POST['data']);
  }
  
  private function _ApjReplace($html) {
    $sNeedle = "APJ:{";
    $eNeedle = "}";
    $startPos = 0;
    while (($startPos = strpos($html, $sNeedle, $startPos))!== false) {
      $len=strlen($sNeedle);
      $allFunc=$this->getStringBetween($html,$sNeedle,$eNeedle,$startPos);
      if ($allFunc) {
        $allNeedle=$sNeedle.$allFunc.$eNeedle;
        $func=substr($allFunc,0,strpos($allFunc,'('));
        $params=$this->getStringBetween($allFunc,'(',')',0);
        if ($params) {
          $paramArray=explode(',',$params);
          if (count($paramArray)) {
            array_unshift($paramArray,$func);
          } else {
            $paramArray=array($func,$params);
          }
        } else {
          $paramArray=array($func);
        }
        $replace=$this->_APJCall($paramArray);
        $html=str_replace($allNeedle,$replace,$html);
        $len=strlen($replace);
      }
      $startPos+=$len;
    }
    return $html;
  }
  
  /**
  * Return formatted current Date and Time<br>
  * Retorna la Fecha y Hora actual con formato
  * @param (string) Datetime format
  * @return (string) formatted Datetime
  */
  protected function currentDateTime($format='Y-m-d H:i:s') {
    return date($format);
  }

  /**
  * Formats a value dependign on data type<br>
  * Formatea un valor dependiendo del tipo de dato
  * @param (mixed) The value
  * @param (string) Data type
  */
  protected function format($value,$type) {
    $type=strtolower($type);
    $fmt = unserialize(FORMATS);
    switch ($type) {
      case "float":
      case "real":
      case "double":
      case "double precision":
      case "fixed":
      case "dec":
      case "decimal":
        if (is_numeric($value)) {
          $fvalue = number_format($value,$fmt['decimal'][0],$fmt['decimal'][1],$fmt['int'][2]);
          break;
        }
      case "date":
        $dateTime=new DateTime($value);
        $fvalue = ($value=='0000-00-00' or $value==NULL)?NULL:$dateTime->format($fmt['date']);
        break;
      case "datetime":
        $dateTime=new DateTime($value);
        $fvalue = ($value=='0000-00-00 00:00:00' or $value==NULL)?NULL:$dateTime->format($fmt['datetime']);
        break;
      case "time":
        $dateTime=new DateTime($value);
        $fvalue = ($value=='00:00:00' or $value==NULL)?NULL:$dateTime->format($fmt['time']);
        break;
      case "timestamp":
        $dateTime=new DateTime($value);
        $fvalue = ($value==0 or $value==NULL)?NULL:$dateTime->format($fmt['timestamp']);
        break;
      case "smallint":
      case "mediumint":
      case "integer":
      case "int":
      case "bigint": 
      case "bit":
        if (is_numeric($value)) {
          $fvalue = number_format($value,$fmt['int'][0],$fmt['int'][1],$fmt['int'][2]);
          break;
        }
      case "boolean":
      case "bool":
      case "tinyint":
        if (is_numeric($value)) {
          if ($value) {
            $fvalue=$fmt['booleanTrue'];
          } else {
            $fvalue=$fmt['booleanFalse'];
          }
          break;
        }
      default:
        $fvalue = $value;
    }
    return $fvalue;    
  }
  
  /**
  * Converts Date and Time by format<br>
  * Convierte Fecha y Hora según formato
  * @param (string) Datetime
  * @param (string) Format
  * @return (string) Formated datetime
  */
  protected function convertDateTime($dateTime, $format='Y-m-d H:i:s') {
    $arraymonth=array();
    $months=array();
    $delimiters=array(' ','-','/');
    $months=array(array('ninguno','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'),array('ninguno','ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'),array('none','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'));
    $separator=" ";
    foreach ($months as $month) {
      foreach ($delimiters as $delimiter) {
        if ($this->arrayInString($month,strtolower($dateTime),$delimiter)) {
          $arraymonth=$month;
          $separator=$delimiter;
          break;
        }
      }
    }
    if (count($arraymonth)) {
      $fa=explode($separator,strtolower($dateTime));
      $dd='';
      $mm='';
      $yy='';
      foreach ($fa as $part) {
        if (is_numeric($part)) {
          if (strlen($part)<=2) {
            $dd=$part;
          }
          if (strlen($part)==4) {
            $yy=$part;
          }
        } elseif (in_array($part,$arraymonth)!==false) {
          $mm=array_search($part,$arraymonth,false);
        }
      }
      if ($dd and $mm and $yy) {
        $dateTime=$mm.'/'.$dd.'/'.$yy;
      }
    }
    try {
      $dateTime=new DateTime($dateTime);  
    } catch (Exception $e) {
      return "Error";
    }
    return $dateTime->format($format);
  }
  
  /**
  * Searches array elements in a string<br>
  * Busca elementos de un array en un string
  * @param (array) Array elements to be searched
  * @param (string) Where to search
  * @param (string) Delimiter (default ' ')
  * @return (boolean) True or False
  */
  protected function arrayInString($array,$string,$delim=' ') {
    $stringAsArray = explode( $delim , $string );
    return ( count( array_intersect( $array , $stringAsArray ) )>0 );
  }
  
  /**
  * Generates the <option> of a <select> from given array<br>
  * Genera los <option> de un <select> según array dado
  * @param (array) Array of elements
  * @param (string) Value index name
  * @param (string) Item index name
  * @param (string) Default selected item value (optional)
  * @return (string) <options>
  */
  protected function options($array, $valueIndex, $textIndex, $selected='') {
    $options='';
    foreach ($array as $row) {
      $sel="";
      if ($row[$valueIndex]==$selected) {
        $sel="selected";
      }
      $options.='<option value="'.$row[$valueIndex].'" '.$sel.'>'.$row[$textIndex].'</option>';
    }
    return $options;
  }
  
  
  /**
  * Returns a substring delimited by 2 strings<br>
  * Retorna un substring delimitado por 2 strings
  * @param (string) Entire string
  * @param (string) Staring search string
  * @param (string) Ending search string
  * @param (int) Starting position (default 0)
  * @return (string) Result
  */
  protected function getStringBetween($string, $start, $end, $pos=0) {
    $string = " ".$string;
    $ini = strpos($string,$start,$pos);
    if ($ini == 0) {
      return "";
    }
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
  }
  
  /**
  * Return current filename to _self (used with url = "APJ:{self()}")<br>
  * Retorna el nombre del archivo actual a _self (para uso en url = "APJ:{self()}")
  * @return (string) Controller filename
  */
  protected function self() {
    $obj = new ReflectionClass($this);
    return $this->_self = basename($obj->getFileName());    
  }
  
  /**
  * Return current controller name<br>
  * Retorna el nombre del controlador actual
  * @return (string) Controller name
  */
  protected function getController() {
    $file=$this->self();
    return substr($file,0,-4);
  }

  /**
  * Returns an array with paged results from a data array<br>
  * Retorna un arreglo con resultado paginados de un arreglo de datos
  * @param (array) Data array
  * @param (int) Elements per page
  * @param (int) Page number
  * @return (array) Result array
  */
  protected function paging($data, $limit=20,$page=1) {
    if (is_array($data)) {
      $this->lastPage = ceil(count($data) / $limit);
      $this->currentPage = $page;
      $this->previousPage = ($page>1)?$page-1:1;
      $this->nextPage = ($page<$this->lastPage)?$page+1:$this->lastPage;
      $pagedArray=array_chunk($data,$limit,true);
      $currentPage=$pagedArray[$page-1];
      return $currentPage;
    }
    return false;
  }
  
  
  /**
  * Return the timeout parameter for the view<br>
  * Retorna el parametro timeout para la vista
  * @return (int) Timeout value
  */
  protected function timeout() {
    return $this->TimeOut;
  }
  
  /**
  * Get response for Ajax<br>
  * Obtiene respuesta para Ajax
  */
  protected function getResponse() {
    jQ::getResponse();
  }
  
  /**
  * Creates a jQuery object selector
  * Crea un objeto selector jQuery
  * @param (string) Selector
  * @return (object) jQSelector
  */
  protected function jQ($selector) {
    return jQ::setQuery($selector);
  }
  
  /**
  * Creates a jQuery/javascript script<br>
  * Crea un script de jQuery/javascript
  * @param (string) Script
  */
  protected function jScript($script) {
    jQ::Script($script);
  }
  
  /**
  * Displays a showdown list with result elements
  * Despliega el resultado de una busqueda en una lista desplegable
  * @param (string) Element to locate the list under
  * @param (string) Container element to display/toggle
  */
  protected function jShowDown($input,$container) {
    jQ::Script("jShowDown('{$input}','{$container}')");  
  }
  
  /**
  * Displays a information alert<br>
  * Despliega una alerta de información
  * @param (string) Message
  * @param (string) Title (optional)
  * @param (string) Callback function/method (optional)
  * @param (string) Javasript array format of callback parameters (optional)
  * @return (object) Jinfo
  */
  protected function jInfo($message,$title='',$callback='',$params='') {
    return jQ::jInfo($message,$title,$callback,$params);
  }
  
  /**
  * Displays a warning alert<br>
  * Despliega una alerta de advertencia
  * @param (string) Message
  * @param (string) Title (optional)
  * @param (string) Callback function/method (optional)
  * @param (string) Javasript array format of callback parameters (optional)
  * @return (object) JWarning
  */
  protected function jWarning($message,$title='',$callback='',$params='') {
    return jQ::jWarning($message,$title,$callback,$params);
  }
  
  /**
  * Displays a error alert<br>
  * Despliega una alerta de error
  * @param (string) Message
  * @param (string) Title (optional)
  * @param (string) Callback function/method (optional)
  * @param (string) Javasript array format of callback parameters (optional)
  * @return (object) JError
  */
  protected function jError($message,$title='',$callback='',$params='') {
    return jQ::jError($message,$title,$callback,$params);
  }
  
  /**
  * Displays a Confirmation<br>
  * Despliega una confirmación
  * @param (string) Message
  * @param (string) Title (optional)
  * @param (string) Callback function/method (optional)
  * @param (string) Javasript array format of callback parameters (optional)
  * @return (object) JConfirm
  */
  protected function jConfirm($message,$title='',$callback='',$params='') {
    return jQ::jConfirm($message,$title,$callback,$params);
  }
  
  /**
  * Displays a value prompt<br>
  * Despliega una captura de valor
  * @param (string) Message
  * @param (string) Title (optional)
  * @param (string) Callback function/method (optional)
  * @param (string) Javasript array format of callback parameters (optional)
  * @return (object) Jprompt
  */
  protected function jPrompt($message,$title='',$callback='',$params='') {
    return jQ::jPrompt($message,$title,$callback,$params);
  }
  
  /**
  * Displays a processing alert<br>
  * Despliega una alerta de procesamiento
  * @param (string) Message
  * @param (string) Title (optional)
  * @param (string) Style, can be 'blink' (default none)
  * @return (object) Jprocess
  */
  protected function jProcess($message,$title='',$style='') {
    return jQ::jProcess($message,$title,$style);
  }
  
  /**
  * Closes any alert window<br>
  * Cierra cualquier ventana de alerta
  * @return (object) JClose
  */
  protected function jClose() {
    return jQ::jClose();
  }

  /**
  * Displays an array of Errors, Warnings or Information<br>
  * Despliega un array de Errores, Advertencias o Información
  * @param (array) Arrays with messages
  * @param (string) Title of message
  * @param (string) Can be 'Error', 'Warning' or 'Info'
  */
  protected function showMessages($messages,$title,$type) {
    $func=array("Error"=>"jError","Info"=>"jInfo","Warning"=>"jWarning");
    $msg="";
    foreach ($messages as $message) {
      $msg.=$message.'<br>';
    }
    $this->$func[$type]($msg,$title);
  }

  /**
  * Displays an array of errors in a Error alert (jError)<br>
  * Despliega un array de errores en una alerta de Error (jError)
  * @param (array) Errors messages
  * @param (string) Title
  * @param (array) Fields alias (optional)
  */
  protected function showErrors($errors,$title,$alias) {
    $msg="";
    foreach ($errors as $fld=>$err) {
      $fld=$this->_getAlias($fld,$alias);
      $msg.=$fld.": ".$err.'<br>';
    }
    if ($msg) {
      $this->jError($msg,$title);
    }
  }

  /**
  * Displays array of warnings in a Warning alert (jWarning)<br>
  * Despliega un array de advertencias de un modelo en una alerta de Advertencia (jWarning)
  * @param (array) Warning messages
  * @param (string) Title
  * @param (array) Fields alias (optional)
  */
  protected function showWarnings($warnings,$title,$alias) {
    $msg="";
    foreach ($warnings as $fld=>$wrn) {
      $fld=$this->_getAlias($fld,$alias);
      $msg.=$fld.": ".$wrn.'<br>';
    }
    if ($msg) {
      $this->jWarning($msg,$title);
    }
  }
  
  /**
  * Returns a object from an array
  * Devuelve un objeto a partir de una matriz
  * @param (array) Elements to be converted
  * @return (object) stdClass object
  */
  protected function arrayToObject($array) {
    $object = new stdClass();
    if (is_array($array)) {
      foreach ($array as $key=>$value) {
        $object->$key = $value;
      }
    }
    return $object;
  }
  
  private function _getAlias($fld,$alias) {
    return ($alias[$fld])?$alias[$fld]:$fld;
  }
  
  /**
  * Redirects to other controller<br>
  * Redirige a otro controlador
  * @param (string) controller url
  * @param (boolean) From parent location (default false)
  */
  protected function redirect($url,$parent=false) {
    if (isset($_REQUEST['action'])) {
      if ($parent) {
        $this->jScript('parent.location = "'.$url.'"');
      } else {
        $this->jScript('window.location = "'.$url.'"');
      }
      $this->getResponse();
    } else {
      if ($parent) {
        echo "<script languaje=javascript> parent.location = '{$url}';</script>";
      } else {
        header("Location: {$url}");
      }
      die();
    }
  }
  
  /**
  * Returns current unix date<br>
  * Retorna la fecha unix actual
  * @return (timestamp) Unix date format
  */
  protected function timeStamp() {
    return time();
  }
}
