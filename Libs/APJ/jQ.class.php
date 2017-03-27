<?php
/**
 jQ
 Static class that respons to jQuery from PHP</br>
 Clase Estática que responde acciones jQuery desde PHP
 Version: 1.16.0806
*/
class jQ
{
  /**
  * Static class instance container</br>
  * Contenedor estatico de la instancia de la clase
  * @var jQ
  */
  public static $jQ;
  
  /**
  * Array with query/actions responses</br>
  * Array de respuestas de querys/acciones 
  * @var mixed
  */
  public $response = array(
    'action' => array(),
    'query' => array()
  );

  /**
  * Initializes the static container with a new instance of the class (Signleton pattern)</br>
  * Inicializa el contenedor estático con una nueva instacia de la clase (patrón Singleton)
  */
  public static function init() 
  {
    if (empty(jQ::$jQ)) {
      jQ::$jQ = new jQ();
    }
    return true;
  }

  /*
  
  $key: clave del valor
  $value: valor
  $callBack: funcion callback (opcional)
  */
  /**
  * Adds data to te response</br>
  * Agrega un dato a la respuesta
  * @param $key (mixed) Value Key
  * @param $value (mixed) Key Value
  * @param (optional) $callBack (string) callback function
  * @return jQ (object) the jQ object
  */
  public static function setData($key, $value, $callBack = null) 
  {
    jQ::init();
    $jQAction = new jQAction();
    $jQAction->set('key', $key);
    $jQAction->set('value', $value);
    if ($callBack) {
      $jQAction->set("callback", $callBack);
    }
    jQ::setAction(__FUNCTION__, $jQAction);
    return jQ::$jQ;
  }

  /**
  * Adds a script to response</br>
  * Agrega un script a la respuesta
  * @param mixed $script
  * @return jQ (object) jQ object
  */
  public static function Script($script) 
  {
    jQ::init();
    $jQAction = new jQAction();        
    $jQAction->set("scr", $script);
    jQ::setAction(__FUNCTION__, $jQAction);
    return jQ::$jQ;
  }

  /**
  * Puts the response in JSON</br>
  * Genera la respuesta en JSON
  */
  public static function getResponse() 
  {
    jQ::init();
    echo json_encode(jQ::$jQ->response);
    exit ();
  }

  /**
  * Instance a new jQ selector</br>
  * Instancia un nuevo selector
  * @param $selector (string) jQuery style selector
  * @return jQSelector (object) instance of the selector
  */
  public static function setQuery($selector) 
  {
    jQ::init();
    return new jQSelector($selector);
  }

  /**
  * Adds a selector by reference to the queue</br>
  * Agrega un selector por referencia a la cola
  * @param jQSelector $jQSelector
  */
  public static function setSelector(jQSelector &$jQSelector) 
  {
    jQ::init();
    array_push(jQ::$jQ->response['query'], $jQSelector);
  }

  /**
  * Adds a action by reference to the action queue</br>
  * Agrega una acción por referencia a la cola de acciones
  * @param $name (string) Method name
  * @param jQAction (object) action object
  */
  public static function setAction($name, jQAction &$jQAction) 
  {
    jQ::init();
    jQ::$jQ->response['action'][$name][] = $jQAction;
  }

  /**
  * Information alert window</br>
  * Ventana de alerta de información
  * @param $msg (string) Message
  * @param $title (string) Title
  * @param $callBack (string) Callback method/function
  * @param $params (array) Array of callback parameters
  * @return jQ (object)
  */
  public static function jInfo($msg, $title = null, $callBack = null, $params = null) 
  {
    jQ::init();
    $jQAction = new jQAction();        
    $jQAction->set("msg", $msg);
    if ($title) {
      $jQAction->set("title", $title);
    }
    if ($callBack) {
      $jQAction->set("callback", $callBack);
    }
    if ($params) {
      $jQAction->set("params",  $params);
    }
    jQ::setAction(__FUNCTION__, $jQAction);
    return jQ::$jQ;
  }

  /**
  * Warning alert window</br>
  * Ventana de alerta de advertencia
  * @param $msg (string) Message
  * @param $title (string) Title
  * @param $callBack (string) Callback method/function
  * @param $params (array) Array of callback parameters
  * @return jQ (object)
  */
  public static function jWarning($msg, $title = null, $callBack = null, $params = null) 
  {
    jQ::init();
    $jQAction = new jQAction();        
    $jQAction->set("msg", $msg);
    if ($title) {
      $jQAction->set("title", $title);
    }
    if ($callBack) {
      $jQAction->set("callback", $callBack);
    }
    if ($params) {
      $jQAction->set("params",  $params);
    }
    jQ::setAction(__FUNCTION__, $jQAction);
    return jQ::$jQ;
  }

  /**
  * Error alert window</br>
  * Ventana de alerta de error
  * @param $msg (string) Message
  * @param $title (string) Title
  * @param (optional) $callBack (string) Callback method/function
  * @param (optional) $params (array) Array of callback parameters
  * @return jQ (object)
  */
  public static function jError($msg, $title = null, $callBack = null, $params = null) 
  {
    jQ::init();
    $jQAction = new jQAction();        
    $jQAction->set("msg", $msg);
    if ($title) {
      $jQAction->set("title", $title);
    }
    if ($callBack) {
      $jQAction->set("callback", $callBack);
    }
    if ($params) {
      $jQAction->set("params", $params);
    }
    jQ::setAction(__FUNCTION__, $jQAction);
    return jQ::$jQ;
  }

  /**
  * Confirmation window</br>
  * Ventana de confirmación
  * @param $msg (string) Message
  * @param $title (string) Title
  * @param (optional) $callBack (string) Callback method/function
  * @param (optional) $params (array) Array of callback parameters
  * @return jQ (object)
  */
  public static function jConfirm($msg, $title = null, $callBack = null, $params = null) 
  {
    jQ::init();
    $jQAction = new jQAction();        
    $jQAction->set("msg", $msg);
    if ($title) {
      $jQAction->set("title", $title);
    }
    if ($callBack) {
      $jQAction->set("callback", $callBack);
    }
    if ($params) {
      $jQAction->set("params",  $params);
    }
    jQ::setAction(__FUNCTION__, $jQAction);
    return jQ::$jQ;
  }

  /**
  * Data prompt window</br>
  * Ventana de petición de datos
  * @param $msg (string) Message
  * @param $title (string) Title
  * @param (optional) $callBack (string) Callback method/function
  * @return jQ (object)
  */
  public static function jPrompt($msg, $title = null, $callBack = null) 
  {
    jQ::init();
    $jQAction = new jQAction();        
    $jQAction->set("msg", $msg);
    if ($title) {
      $jQAction->set("title", $title);
    }
    if ($callBack) {
      $jQAction->set("callback", $callBack);
    }
    jQ::setAction(__FUNCTION__, $jQAction);
    return jQ::$jQ;
  }

  /**
  * Process information window</br>
  * Ventana de información de proceso
  * @param $msg (string) Message
  * @param $title (string) Title
  * @param (optional) $style (string) 'Blink' for blinking message
  * @return jQ (object)
  */
  public static function jProcess($msg, $title = null, $style = 'blink') 
  {
    jQ::init();
    $jQAction = new jQAction();        
    $jQAction->set("msg", $msg);
    if ($title) {
      $jQAction->set("title", $title);
    }
    if ($style) {
      $jQAction->set("style", $style);
    }
    jQ::setAction(__FUNCTION__, $jQAction);
    return jQ::$jQ;
  }

  /**
  * Closes alert/informations windows</br>
  * Cierra ventanas de alert/información
  * @return jQ (object)
  */
  public static function jClose() 
  {
    jQ::setAction(__FUNCTION__);
    return jQ::$jQ;
  }
}
