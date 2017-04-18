<?php
/**
* Class that sets parameters for jQAction object<br>
* Clase que define los parámetros del objeto jQAction
* Version: 1.17.0418
* Author: Ricardo Seiffert
*/
class jQAction
{
  /**
  * Sets parameters and value to jQAction object
  * Define los parametros y valores del objeto jQAction
  * @param $param (string) Parameter name
  * @param $value (mixed) Parameter value
  * @return jQAction (object)
  */
  public function set($param, $value) 
  {
    $this->$param = $value;
    return $this;
  }
}
