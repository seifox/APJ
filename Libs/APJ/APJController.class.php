<?php
/**
* APJ's parent Controller
* Controlador padre de APJ
* Versión: 1.7.180718
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
  private $_self = NULL;
  /**
  * Form Object<br>
  * Objeto Form (formulario)
  * @var stdClass
  */
  public $Form;
  
  /**
  * Ajax default timeout<br>
  * Timeout por defecto de Ajax
  * @var int
  */
  protected $TimeOut = 10000;
  
  /**
  * User Id in session<br>
  * Id del usuario en la sesión
  * @var int
  */
  protected $userId = NULL;
  
  /**
  * Array of form fields types, used in setFormValues<br>
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
  * Defines whether APJCall passes the parameters as an array or as independent arguments<br> 
  * Define si APJCall pasa los parámetros como un arreglo o como argumentos independientes
  * @var boolean
  */
  protected $useParametersAsArray = true;
  
  // Common methods Trait
  use APJCommon;  
  
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
  * Controls de session<br>
  * Control de sesión
  */
  protected function sessionControl() {
    if (!APJSession::active(APPNAME)) {
      $this->redirect(LOGIN,true);
    }
    APJSession::start(APPNAME,SESSION_LIMIT);
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
  * Extracts into array the (optional) adicional parameters from APJSubmit<br>
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
  * Create a Form objet from submitted form<br>
  * Crea un objeto Form con los campos del formulario enviado
  */
  public function getForm() {
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
  * Assigns the array values to the Form object<br>
  * Asigna los valores de un arreglo al objeto Form
  * @param (array) Associative array
  */
  protected function arrayToForm($array) {
    $this->createForm();
    foreach ($array as $name=>$value) {
      $this->Form->$name = $array[$name];
    }
  }
  
  /**
  * Clear Form Object<br>
  * Limpia el objeto Form
  */
  protected function clearForm() {
    unset($this->Form);
  }
  
  /**
  * Create the Form object<br>
  * Crea el objeto Form
  */
  public function createForm() {
    if (!isset($this->Form)) {
      $this->Form = new stdClass;
    }
  }
  
  /**
  * Sets the html form values from Form object or array<br>
  * Asigna los valores del formulario html desde objeto Form o Array
  * @param (mixed) Array or object (optional)
  */
  protected function setFormValues($data='',$form=NULL) {
    $this->createForm();
    if (empty($data) and !empty($this->Form)) {
      $data=$this->Form;
    }
    foreach ($data as $field=>$value) {
      $this->Form->$field = $value;
      $selector=$this->selector($field,$form);
      if ($this->fieldTypes[$field]=="checkbox" or $this->fieldTypes[$field]=="radio") {
        if ($value) {
          $this->jQ($selector)->prop('checked', true);
        } else {
          $this->jQ($selector)->prop('checked', false);
        }
      } else {
        $this->jQ($selector)->val($value);
      }
    }
  }
  
  /**
  * Sets form special input types like checkbox or radio<br>
  * Define los campos del formulario de tipo especial como checkbox o radio
  * @param string input field id/name
  * @param string type (checkbox or radio)
  */
  protected function setFieldType($field,$type='checkbox') {
    if ($type=="checkbox" or $type=="radio") {
      $this->fieldTypes[$field]=$type;
    }
  }
  
  /**
  * Returns a jQ selector by ID or Form :input name
  * Retorna un selector de jQ por ID o por Formulario :input
  * @param string input ID or name
  * @param string form ID (optional)
  */
  protected function selector($field,$form=NULL) {
    if ($form) {
      $sel="#{$form} :input[name={$field}]";
    } else {
      $sel="#{$field}";
    }
    return $sel;
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
        if (!$this->useParametersAsArray and is_array($data)) {
          return call_user_func_array(array($this,$func),$data);
        } else {
          return $this->{$func}($data);
        }
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
    if (is_array($array) and $valueIndex and $textIndex) {
      foreach ($array as $row) {
        $sel="";
        if ($row[$valueIndex]==$selected) {
          $sel="selected";
        }
        $options.='<option value="'.$row[$valueIndex].'" '.$sel.'>'.$row[$textIndex].'</option>';
      }
    }
    return $options;
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
  * Creates a jQuery object selector<br>
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
  * Displays a showdown list with result elements<br>
  * Despliega el resultado de una busqueda en una lista desplegable
  * @param (string) Element to locate the list under
  * @param (string) Container element to display/toggle
  */
  protected function jShowDown($input,$container,$html=NULL) {
    if ($html) {
      $this->jQ("#{$container}")->html($html);
    }
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
  
}
