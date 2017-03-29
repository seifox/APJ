<?php
/*
Table structure of [paises]
+--------+---------+------+----------+------+-----+---------+
| Field  |  Type   | Size | Decimals | Null | Key | Default |
+--------+---------+------+----------+------+-----+---------+
| codigo | char    |    2 |          | NO   | PRI |         |
| pais   | varchar |   50 |          | NO   |     |         |
+--------+---------+------+----------+------+-----+---------+
*/
class Models_Paises extends APJModel
{
  public function __construct() {
    parent::__construct();
    $this->setTable('paises');
  }
  
  public function guardar() {
    if ($this->basicValidation()) {
      return false;
    }
    $this->codigo=strtoupper($this->codigo);
    $this->pais=strtoupper($this->pais);
    return $this->replace();
  }
  
}