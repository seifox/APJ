<?php
/*
** creado con $this->modeloCiudades->showModel(true); desde el controlador antes de mostrar la vista **
Table structure of [ciudades]
+-------------+---------+------+----------+------+-----+---------+
|    Field    |  Type   | Size | Decimals | Null | Key | Default |
+-------------+---------+------+----------+------+-----+---------+
| id          | int     |   11 |          | NO   | PRI |         |
| ciudad      | varchar |   50 |          | NO   | MUL |         |
| codigo_pais | char    |    2 |          | NO   | MUL |         |
+-------------+---------+------+----------+------+-----+---------+
*/
class Model_Ciudades extends APJModel
{
  public function __construct() {
    parent::__construct();
    
    // Define la estructura del modelo en forma dinámica
    //$this->setTable('ciudades');
    
    // Define la estructura del modelo en forma fija
    $this->defineEstructura();
    
    // Habilita el truncado automático de los espacios en blanco, de los valores de los campos, antes de guardar
    $this->trim=true;
    
    // Permite definir que valores de columnas van en mayusculas (toUpper) o minusculas (toLower)
    $this->toUpper = array('ciudad','codigo_pais');
  }
  
  /**
  * Ejemplo de definición de estructura fija. Se utiliza normalmente cuando ya no cambiará o
  * para mejorar el rendimiento, ya que no lee la estructura cada vez que se utiliza,
  * pero tendrá que actualizarla si ha cambiado la estructura de la tabla.
  * El código aqui definido lo genera el metodo showStructure(), llamado desde el controlador 
  * $this->modeloCiudades->showStructure();
  */
  private function defineEstructura() {
    $this->table = "ciudades";
    $this->structure = array('id'=>array('Type'=>'int','Size'=>11,'Decimals'=>NULL,'Null'=>'NO','Key'=>'PRI','Default'=>NULL,'Extra'=>'auto_increment','Comment'=>'Id'),'ciudad'=>array('Type'=>'varchar','Size'=>50,'Decimals'=>NULL,'Null'=>'NO','Key'=>'MUL','Default'=>NULL,'Extra'=>NULL,'Comment'=>'Ciudad'),'codigo_pais'=>array('Type'=>'char','Size'=>2,'Decimals'=>NULL,'Null'=>'NO','Key'=>'MUL','Default'=>NULL,'Extra'=>NULL,'Comment'=>'Código País'));
    $this->alias = array('id'=>'Id','ciudad'=>'Ciudad','codigo_pais'=>'Código País');
    $this->pk = array('id');
    $this->fields = array_keys($this->structure);
  }
  
  public function guardar() {
    // Validación báscia en base a la estrctura, si es verdadero, no pasa la validación
    if ($this->basicValidation()) {
      return false;
    }
    // Si hay id, está editando
    if ($this->id) {
      // Actualiza
      $this->update();      
    } else {
      //Si no, es nuevo e inserta
      $this->insert();
    }
    return !$this->error;
  }
  
}