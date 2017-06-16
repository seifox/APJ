<?php
/**
* Class Autoloader
* Autocargador de clases
* Versión: 1.17.0614
* Author: Ricardo Seiffert
*/
function APJAutoload($classname) {
  switch (true) {
    // APJ Classes
    case stripos($classname,"APJ")===0 or stripos($classname,"jQ")===0:
      $prefilename=APJ.DIRECTORY_SEPARATOR.$classname;
      $ext=".class.php";
      break;
    // Models Classes
    case stripos($classname,MODELS_PREFIX)===0:
      $prefilename=replaceNameClass($classname,MODELS_PREFIX,MODELS);
      $ext=MODELS_FILE_EXTENSION;
      break;
    // Helpers Classes
    case stripos($classname,HELPERS_PREFIX)===0:
      $prefilename=replaceNameClass($classname,HELPERS_PREFIX,HELPERS);
      $ext=HELPERS_FILE_EXTENSION;
      break;
    // Controller Classes
    default:
      $ext=".php";
      $prefilename=APP.DIRECTORY_SEPARATOR.$classname;
  }
  $filename = $prefilename.$ext;
  $filename = verifyAndCorrect($prefilename,$ext);
  if (is_readable($filename)) {
    require_once $filename;
  } else {
    $bt=end(debug_backtrace());
    throw new Exception("El archivo {$filename} no está disponible desde ".$bt['file']." - Linea:".$bt['line']);
  }
}
function verifyAndCorrect($file,$ext) {
  $filename=$file.$ext;
  if (is_readable($filename)) {
    return $filename;
  }
  $filename=strtolower($filename);
  if (is_readable($filename)) {
    return $filename;
  }
  $filename=$file.$ext;
  $files=rglob(VENDORS);
  if (in_array($filename,$files)) {
    $filename = $files[array_search($filename,$files)];
    if (is_readable($filename)) {
      return $filename;
    }
  }
  $filename=strtolower($file.$ext);
  if (in_array($filename,$files)) {
    $filename = $files[array_search($filename,$files)];
    if (is_readable($filename)) {
      return $filename;
    }
  }
  return $file.$ext;
}

function replaceNameClass($classname,$prefix,$folder) {
  if (stripos($classname,$prefix)===0) {
    return str_replace($prefix, $folder.DIRECTORY_SEPARATOR, $classname);
  }
  return $classname;
}

function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags); 
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}

spl_autoload_register('APJAutoload', true, true);
