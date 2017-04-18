<?php
/**
* APJ's parent Controller
* Controlador padre de APJ
* Versión: 1.17.0418
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
  * @param $page (string) Rendered view
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
  * @param $page (string) View name
  * @param $return (boolean) Returns the rendered view
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
  * Extrae los parametros adicionales (opcionales) que envía APJSubmit en un array
  * @param $param (string) parameters submitted from APJSubmit
  * @return array of parameters
  */
  protected function getParameters($params) {
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
  protected function getForm() {
    $this->Form = new stdClass;
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
  * @param $model (string) Model name
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
  * @param $model (string) Model name
  */
  protected function formObjectToModel($model) {
    foreach ($this->Form as $name => $value) {
      if (in_array($name, $model->fields)) {
        $model->$name = $value;
      }
    }
  }
  
  /**
  * Assigns Model values to Form object<br>
  * Asigna valores del Modelo al objeto Form
  * @param $model (string) Model name
  */
  protected function modelToForm($model) {
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
  * Sets the form values from Form object or array
  * Asigna los valores del formulario desde objeto Form o Array
  * @param (mixed) (optional) array o object
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
  * @param $url (string) file url
  * @return file content (string)
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
  * @param $url (string) file url
  * @return file content (string)
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
        if ($replace) {
          $html=str_replace($allNeedle,$replace,$html);
          $len=strlen($replace);
        }
      }
      $startPos+=$len;
    }
    return $html;
  }
  
  /**
  * Return formatted current Date and Time<br>
  * Retorna la Fecha y Hora actual con formato
  * @param $format (string) datetime format
  * @return formatted DateTime (string)
  */
  protected function currentDateTime($format='Y-m-d H:i:s') {
    return date($format);
  }

  /**
  * Formats a value dependign on data type
  * Formatea un valor dependiendo del tipo de dato
  * @param string $type data type
  * @param mixed $value the value
  */
  protected function format($type,$value) {
    $type=strtolower($type);
    $fmt = unserialize(FORMATS);
    switch ($type) {
      case "float":
      case "real":
      case "double":
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
      case "int":
      case "bigint": 
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
  * @param $dateTime (DateTime)
  * @param $format (string) format
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
  * @param $Array (array)
  * @param $String (string) where to search
  * @param $Delim (string) delimiter, default space
  */
  protected function arrayInString($Array,$String,$Delim=' ') {
    $StringAsArray = explode( $Delim , $String );
    return ( count( array_intersect( $Array , $StringAsArray ) )>0 );
  }
  
  /**
  * Generates the option of a select from given array<br>
  * Genera los option de un select según array dado
  * @param $array (array) Array of elements
  * @param $valueIndex (string) Value index name
  * @param $textIndex (string) Item index name
  * @param $selected (string) Selected item value
  * @return string with html options
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
  * @param $string (string) entire string
  * @param $start (string) staring search string
  * @param $end (string) ending search string
  * @param $pos (int) starting position (optional)
  * @return string with result
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
  * @return (string) filename
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
  * @param array $data
  * @param int $limit
  * @param int $page
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
  * @return (int) timeout value
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
  * @param $selector (string)
  * @return jQSelector (object)
  */
  protected function jQ($selector) {
    return jQ::setQuery($selector);
  }
  
  /**
  * Creates a jQuery script<br>
  * Crea un script de jQuery
  * @param $script (string) Script text
  */
  protected function jScript($script) {
    jQ::Script($script);
  }
  
  /**
  * Displays a search result to a showdown list
  * Despliega el resultado de una busqueda en una lista desplegable
  * @param string element to locate
  * @param string element to toggle
  */
  protected function jShowDown($input,$container) {
    jQ::Script("jShowDown('{$input}','{$container}')");  
  }
  
  /**
  * Displays a information alert<br>
  * Despliega una alerta de información
  * @param $message (string)
  * @param (optional) title (string)
  * @param (optional) callback (string) Callback function/method
  * @param (optional) params (array) Callback parameters
  */
  protected function jInfo($message,$title='',$callback='',$params='') {
    return jQ::jInfo($message,$title,$callback,$params);
  }
  
  /**
  * Displays a warning alert<br>
  * Despliega una alerta de advertencia
  * @param $message (string)
  * @param (optional) $title (string)
  * @param (optional) $callback (string) Callback function/method
  * @param (optional) $params (array) Callback parameters
  */
  protected function jWarning($message,$title='',$callback='',$params='') {
    return jQ::jWarning($message,$title,$callback,$params);
  }
  
  /**
  * Displays a error alert<br>
  * Despliega una alerta de error
  * @param $message (string)
  * @param (optional) $title (string)
  * @param (optional) $callback (string) Callback function/method
  * @param (optional) $params (array) Callback parameters
  */
  protected function jError($message,$title='',$callback='',$params='') {
    return jQ::jError($message,$title,$callback,$params);
  }
  
  /**
  * Displays a Confirmation<br>
  * Despliega una confirmación
  * @param $message (string)
  * @param (optional) $title (string)
  * @param $callback (string) Callback function/method
  * @param (optional) $params (array) Callback parameters
  */
  protected function jConfirm($message,$title='',$callback='',$params='') {
    return jQ::jConfirm($message,$title,$callback,$params);
  }
  
  /**
  * Displays a value prompt<br>
  * Despliega una captura de valor
  * @param $message (string)
  * @param (optional) $title (string)
  * @param (optional) $callback (string) Callback function/method
  * @param (optional) $params (array) Callback parameters
  */
  protected function jPrompt($message,$title='',$callback='',$params='') {
    return jQ::jPrompt($message,$title,$callback,$params);
  }
  
  /**
  * Displays a processing alert<br>
  * Despliega una alerta de procesamiento
  * @param $message (string)
  * @param (optional) $title (string)
  * @param (optional) $style (can by 'blink')
  */
  protected function jProcess($message,$title='',$style='') {
    return jQ::jProcess($message,$title,$style);
  }
  
  /**
  * Closes any alert window<br>
  * Cierra cualquier ventana de alerta
  */
  protected function jClose() {
    return jQ::jClose();
  }

  /**
  * Displays an array of Errors, Warnings or Information<br>
  * Despliega un array de Errores, Advertencias o Información
  * @param $messages (array) Arrays with messages
  * @param $title (string) Title of message
  * @param $type (string) can by 'Error', 'Warning' or 'Info'
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
  * @param $errors (array) Errors array
  * @param $title (string) jError Title
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
  * @param $warnings (array) Warnigs array
  * @param $title (string) jWarning Title
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
  * @param array
  * @return stdClass
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
  * Redirects to other controller
  * Redirige a otro controlador
  * @param $url (string) controller url
  * @param $parent (boolean) from parent location
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
  * Formats value according to type defined in init.php file<br>
  * Formatea el valor según el tipos de datos definido en el archivo init.php
  * @param value (mixed) the value
  * @param type (mixed) the data type
  * @return (mixed) formatted value
  */
  protected function setFormat($value,$type) {
    $fmt = unserialize(FORMATS);
    switch ($type) {
      case "float":
      case "real":
      case "double":
      case "decimal":
      case "numeric":
        if (is_numeric($value)) {
          $fvalue = number_format($value,$fmt['decimal'][0],$fmt['decimal'][1],$fmt['int'][2]);
        } else {
          $fvalue = $value;
        }
        break;
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
      case "int":
      case "bigint": 
        if (is_numeric($value)) {
          $fvalue = number_format($value,$fmt['int'][0],$fmt['int'][1],$fmt['int'][2]);
        } else {
          $fvalue = $value;
        }
        break;
      default:
        $fvalue = $value;
    }
    return $fvalue;
  }
 
  protected function timeStamp() {
    return time();
  }
}
