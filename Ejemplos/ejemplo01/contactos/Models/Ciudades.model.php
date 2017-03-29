<?php
/*
Table structure of [ciudades]
+-------------+---------+------+----------+------+-----+---------+
|    Field    |  Type   | Size | Decimals | Null | Key | Default |
+-------------+---------+------+----------+------+-----+---------+
| id          | int     |   11 |          | NO   | PRI |         |
| ciudad      | varchar |   50 |          | NO   | MUL |         |
| codigo_pais | char    |    2 |          | NO   | MUL |         |
+-------------+---------+------+----------+------+-----+---------+
*/
class Models_Ciudades extends APJModel
{
  public function __construct() {
    parent::__construct();
    // Define la tabla del modelo
    $this->setTable('ciudades');
    // Trunca los espacios en blanco de los campos
    $this->trim=true;
  }
  
  public function guardar() {
    if ($this->basicValidation()) {
      return false;
    }
    $this->ciudad=strtoupper($this->ciudad);
    $this->codigo_pais=strtoupper($this->codigo_pais);
    if ($this->id) {
      return $this->update();      
    }
    return $this->insert();
  }
  
}