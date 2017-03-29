<?php
// Constantes de Sistema y Auto carga

// Constante que define si la aplicación esta en producción (false) o en desarrollo (true)
define("DEVELOPMENT",true);

// Directorio raíz de la Aplicación
define("ROOT",__DIR__);

// Directorio de Aplicación
define("APP", ROOT);

// Nombre de la aplicación **DEBE MODIFICARSE**
define("APPNAME", "Nombre de la aplicación");

// Directorio de Librerias
define("LIBS",  "Libs");

// Directorio de Controladores
define("CONTROLLERS", APP);

// Directorio de Modelos
define("MODELS",  "Models");

// Directorio de Vistas
define("VIEWS",  "Views");

// Directorio de Clases adicionales (Helpers)
define("CLASSES",  "Classes");

// Directorio de APJ
define("APJ",  LIBS.DIRECTORY_SEPARATOR."APJ");

// Directorio de imagenes
define("IMAGES",  APP.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR);

// URL raíz de la aplicación **DEBE MODIFICARSE**
define("ROOTURL", "/MiAplicacion");

// Dominio **DEBE MODIFICARSE**
if (DEVELOPMENT) {
  $domain="localhost";
} else {
  $domain="miservidor.com";  
}
define("DOMAIN", $domain);

// Controlador de Login **DEBE MODIFICARSE**
define("LOGIN", "login.php");

// Lugar de almacenamineto de session y cookie **DEBE MODIFICARSE**
define("SESSIONPATH", APP.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR);

// Array de Formatos predefinidos a mostrar segun tipo de dato 
define ("FORMATS", serialize (array ("int"=>array(0,',','.'), "decimal"=>array(2,',','.'), "date"=>'d-m-Y','datetime'=>'d-m-Y H:i:s','time'=>'H:i','timestamp'=>'d-m-Y H:i:s','booleanTrue'=>'Si','booleanFalse'=>'No')));

//Zona Horaria (Solo necesaria si no esta definida en php.ini)
//define("TIMEZONE", 'America/Santiago');
//date_default_timezone_set(TIMEZONE);

// Habilida/Deshabilita el reporte de errores
if (DEVELOPMENT) {
  error_reporting(E_ERROR | E_WARNING | E_PARSE);
} else {
  error_reporting(0);
}

// Auto cargador de clases
require_once APJ.DIRECTORY_SEPARATOR."APJAutoload.php";