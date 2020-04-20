<?php
/**
* Common methods Trait<br>
* Rasgo de métodos comunes
* Versión: 1.0.200408
* Author: Ricardo Seiffert
*/
trait APJCommon
{

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
  * Validate dates by format<br>
  * Valida fechas segun fromato
  * @param $date (mixed)
  * @param $format (string)
  * @param $strict (boolean) strict validation
  * @return boolean true = invalid, false=valid
  */
  public function verifyDate($date, $format, $strict=true) {
    $dto = new DateTime();
    if ($format=='timestamp') {
      $dateTime=$dto->setTimestamp($date);
    } else {
      $dateTime=$dto->createFromFormat($format, $date);
      if ($dateTime and $strict) {
        $dateComp=$dateTime->format($format);
        if ($dateTime==false or $date!=$dateComp) {
          return true;
        }
        $errors = DateTime::getLastErrors();
        if (!empty($errors['warning_count'])) {
          return false;
        }
      }
    }
    return ($dateTime == false);
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
        $part = str_replace(',', '', $part);
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
  * Returns a object from an array<br>
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

  /**
  * Returns an associative array of an object
  * Devuelve un arreglo asociativo de un objeto
  * @param object $object
  * @return array
  */
  protected function objectToarray($object) {
    return (array) $object;
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