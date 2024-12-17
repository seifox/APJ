<?php
require_once("init.php");
class Paises extends APJController
{
  private $modeloPaises;
  private $gen;
  
  public function __construct($page) {
    $this->sessionControl();
    $this->gen = new APJHtmlGen();
    $this->instanciaModelos();
    parent::__construct($page);
  }
  
  private function instanciaModelos() {
    $this->modeloPaises = new Model_Paises();
  }
  
  public function buscaPais($pais) {
    if ($this->modeloPaises->find(array('codigo'=>$pais))) {
      $this->modelToForm($this->modeloPaises);
      $this->setFormValues();
      $menos=$this->gen->create("button")->type("button")->clas("smallButton")->onclick("jConfirm('Esta seguro de eliminar este País?','Confirme',APJCall,['eliminar','{$this->Form->codigo}']);return false;")->text("-")->end();
      $this->jQ("#menos")->html($menos);
      $this->jQ("form")->scrollTop(0);
      $this->jQ("#pais")->focus();
    } else {
      $this->jQ("#menos")->empty();
    }
  }
  
  public function guardar() {
    $this->formObjectToModel($this->modeloPaises);
    if ($this->modeloPaises->guardar()) {
      $this->jQ("#reset")->click();
    } else {
      $this->muestraErrores();
    }
  }

  public function eliminar($id) {
    if ($this->modeloPaises->eliminar($id)) {
      $this->jInfo("Pais eliminado.");
      $this->jQ("#reset")->click();
    } else {
      $this->muestraErrores();
    }
  }
  
  private function muestraErrores() {
    if ($this->modeloPaises->errors) {
      $this->showWarnings($this->modeloPaises->errors,"Validación de datos",$this->modeloPaises->alias);
      return true;
    }
    if ($this->modeloPaises->errormsg) {
      $this->jError($this->modeloPaises->errormsg,"Error");
      return true;
    }
    $this->jError("Error en la base de datos, revise el LOG para más detalles.");
  }
  
  public function grillaPaises() {
    $rows=$this->modeloPaises->all('pais');
    foreach ($rows as $row) {
      $out.=$this->gen->create("tr")->clas("modo1")->onclick("APJCall('buscaPais','{$row['codigo']}')")->
      add("td")->text($row['codigo'])->close()->add("td")->text($row['pais'])->end();
    }
    return $out;
  }
}
$app = new Paises('paises.html');