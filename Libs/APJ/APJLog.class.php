<?php 
/**
* Logs PDO errors<br>
* Registros de errores de PDO
* Versión: 1.1.170530
* Author: Ricardo Seiffert
*/
class APJLog 
{
  /**
  * APJPDO Log file path folder<br>
  * Ruta de la carpeta del Log de APJPDO
  * @var string
  */
  private $path = NULL;
  
  /**
  * Constructor, defines the APJPDO log file path<br>
  * Constructor, define la ruta del archivo Log de APJPDO
  */
  public function __construct() {
    $this->setLogPath(APJ.DIRECTORY_SEPARATOR.'APJPDO_logs'.DIRECTORY_SEPARATOR);
  }

  /**
  * Creates Log file<br>
  * Createa el archivo Log
  * @param string Log message
  */
  public function write($msg) {
    $date = new DateTime();
    $log = $this->path . $date->format('Y-m-d').".txt";

    if(is_dir($this->path)) {
      if(!file_exists($log)) {
        $fileHandler = fopen($log, 'a+') or die("Fatal Error!");
        $logcontent = "Time: " . $date->format('H:i:s')."\r\n" . $msg ."\r\n";
        fwrite($fileHandler, $logcontent);
        fclose($fileHandler);
      } else {
        $this->_append($log,$date, $msg);
      }
    } elseif(mkdir($this->path,0777) === true) {
      $this->write($msg);  
    }
  }

  public function setLogPath($path) {
    $this->path = $path;
  }
  
  private function _append($log,$date,$msg) {
    $logcontent = "Time: " . $date->format('H:i:s')."\r\n" . $msg ."\r\n\r\n";
    $logcontent = $logcontent . file_get_contents($log);
    file_put_contents($log, $logcontent);
  }
}
