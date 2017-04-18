<?php 
/**
* Logs PDO errors<br>
* Registros de errores de PDO
* Versión: 1.17.0418
* Author: Ricardo Seiffert
*/
class APJLog 
{
  /**
  * APJPDO Log Filename<br>
  * Archivo del Log de APJPDO
  * @var string
  */
  private $path = 'APJPDO_logs';
  
  /**
  * Constructor, defines the APJPDO log file path<br>
  * Constructor, define la ruta del archivo Log de APJPDO
  */
  public function __construct() {
    $this->path = APJ.DIRECTORY_SEPARATOR.$this->path.DIRECTORY_SEPARATOR;	
  }

  /**
  * Creates Log file<br>
  * Createa el archivo Log
  * @param $msg (string) message
  */
  public function write($msg) {
    $date = new DateTime();
    $log = $this->path . $date->format('Y-m-d').".txt";

    if(is_dir($this->path)) {
      if(!file_exists($log)) {
        $fileHandler = fopen($log, 'a+') or die("Error Fatal!");
        $logcontent = "Hora : " . $date->format('H:i:s')."\r\n" . $msg ."\r\n";
        fwrite($fileHandler, $logcontent);
        fclose($fileHandler);
      } else {
        $this->_append($log,$date, $msg);
      }
    } else {
      if(mkdir($this->path,0777) === true) {
        $this->write($msg);  
      }	
    }
  }
  
  private function _append($log,$date,$msg) {
    $logcontent = "Hora : " . $date->format('H:i:s')."\r\n" . $msg ."\r\n\r\n";
    $logcontent = $logcontent . file_get_contents($log);
    file_put_contents($log, $logcontent);
  }
}
