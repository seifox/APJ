<?php
/**
* Class Autoloader
* Autocargador de clases
* Versión: 1.17.0602
* Author: Ricardo Seiffert
*/
function APJAutoload($classname) {
  if (strpos($classname,"_")>0) {
    $prefilename = str_replace('_', DIRECTORY_SEPARATOR, $classname);
  }
  $prext="";
  switch (true) {
    // APJ Classes
    case stripos($classname,"APJ")===0 or stripos($classname,"jQ")===0:
      $prefilename=APJ.DIRECTORY_SEPARATOR.$classname;
      $prext=".class";
      break;
    // Extra and Helpers Classes
    case stripos($classname,CLASSES)===0:
      $prext=".class";
      break;
    // Models Classes
    case stripos($classname,MODELS)===0:
      $prext=".model";
      break;
    // Controllers Classes
    default:
      $prext="";
      $prefilename=CONTROLLERS.DIRECTORY_SEPARATOR.strtolower($classname);
  }
  $filename = $prefilename.$prext.'.php';
  if (is_readable($filename)) {
    require_once $filename;
  } else {
    $bt=end(debug_backtrace());
    throw new Exception("El archivo {$filename} no está disponible desde ".$bt['file']." - Linea:".$bt['line']);
  }
}

if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
  spl_autoload_register('APJAutoload', true, true);
} else {
  function __autoload($classname) {
    APJAutoload($classname);
  }
}
