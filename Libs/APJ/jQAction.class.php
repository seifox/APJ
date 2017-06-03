<?php
/**
* Class that sets parameters for jQAction object<br>
* Clase que define los parámetros del objeto jQAction
* Version: 1.17.0602
* Author: Ricardo Seiffert
*/
class jQAction
{
  /**
  * Sets parameters and value to jQAction object
  * Define los parametros y valores del objeto jQAction
  * @param (string) Action parameter name
  * @param (mixed) Action parameter value
  * @return (object) jQAction
  */
  public function set($param, $value) {
    $this->$param = $value;
    return $this;
  }
}
