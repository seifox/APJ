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
class Model_Paises extends APJModel
{
  public function __construct() {
    parent::__construct();
    $this->setTable('paises');
    //$this->modeloPaises->showModel(true);
    $this->toUpper = array('codigo','pais');
  }
  
  public function guardar() {
    if ($this->basicValidation()) {
      return false;
    }
    $this->replace();
    return !$this->error;
  }
  
  public function buscarPais($param) {
    return $this->select("codigo='{$param}' OR pais LIKE '{$param}%'",'pais');
  }
  
  public function eliminar($id) {
    return $this->delete(array('codigo'=>$id));
  }  
  
}