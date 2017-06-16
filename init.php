<?php
//Constantes de Sistema y Auto carga
define("DEVELOPMENT",true); // Proyecto en desarrollo = true, Producción = false
define("APPNAME", "Nombre de la aplicación"); // Nombre de la aplicación **DEBE MODIFICARSE**
define("ROOT",__DIR__); // Carpeta de raíz de la aplicación
define("APP", ROOT); // Carpeta de Aplicación
define("LIBS",  "Libs"); // Carpeta del Librerias
define("CONTROLLERS", APP); // Carpeta de Controladores
define("MODELS",  "Models"); // Carpeta de los Modelos
define("MODELS_PREFIX",  "Model_"); // Prefijo con el que se definen las Clases Modelo
define("MODELS_FILE_EXTENSION",  ".model.php"); //Extensión de los archivos de Modelos
define("VIEWS",  "Views"); // Carpeta de Vistas
define("HELPERS",  "Helpers"); // Carpeta de Clases adicionales (Helpers)
define("HELPERS_PREFIX",  "Helper_"); // Prefijo de una clase Helper
define("HELPERS_FILE_EXTENSION",  ".helper.php"); // Extensión de los archivos de Helpers
define("VENDORS",  "Vendor"); // Carpeta de Proveedores de Librerias
define("VENDORS_FILE_EXTENSION",  ".class.php"); // Extensión de los archivos de Proveedores de Librerias
define("APJ", LIBS.DIRECTORY_SEPARATOR."APJ"); // Carpeta de APJ
define("IMAGES",  APP.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR); // Carpeta de imagenes
if (DEVELOPMENT) {
  error_reporting(E_ERROR | E_WARNING | E_PARSE);
  $domain="localhost";
  $rootUrl="/MiAplicacion"; //URL raíz de la aplicación en desarrollo **DEBE MODIFICARSE**
  $es="ES";
} else {
  error_reporting(0);
  $domain="miservidor.com"; // **DEBE MODIFICARSE** al poner en producción
  $rootUrl=ROOT; //URL raíz de la aplicación en producción **DEBE MODIFICARSE**
  $es="es_ES";
}
define("ROOTURL", $rootUrl); // URL raíz de la aplicación
define("DOMAIN", $domain); // Dominio de la aplicación
define("LOGIN", "login.php"); // Controlador de Login **DEBE MODIFICARSE**
define("LOGIN_ATTEMPTS", 3); // Intentos de inicio de sesión para el bloqueo
define("SESSION_LIMIT", 1800); // Segundo que duran las sesiones
// Array de Formatos predefinidos a mostrar segun tipo de dato 
define ("FORMATS", serialize (array ("int"=>array(0,',','.'), "decimal"=>array(2,',','.'), "date"=>'d-m-Y','datetime'=>'d-m-Y H:i:s','time'=>'H:i','timestamp'=>'d-m-Y H:i:s','booleanTrue'=>'Si','booleanFalse'=>'No')));
//Zona Horaria (Solo necesaria si no esta definida en php.ini o si es diferente alque desea)
define("TIMEZONE", 'America/Santiago');
date_default_timezone_set(TIMEZONE);
define("LOCALTIMESYMBOL",$es); //Simbolo para fecha local
// Auto cargador de clases
require_once APJ.DIRECTORY_SEPARATOR."APJAutoload.php";
$vendorAutoload=VENDORS.DIRECTORY_SEPARATOR.'autoload.php';
if (file_exists($vendorAutoload)) {
  require $vendorAutoload;
}