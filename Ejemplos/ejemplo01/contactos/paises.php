<?php
require_once("init.php");
class Paises extends APJController
{
  private $modeloPaises;
  
  public function __construct($page) {
    $this->sessionControl();
    $this->instanciaModelos();
    parent::__construct($page);
  }
  
  private function instanciaModelos() {
    $this->modeloPaises = new Models_Paises();
    //$this->modeloPaises->showModel(true);
  }
  
  public function buscaPais($params) {
    if ($this->modeloPaises->find(array('codigo'=>$params[0]))) {
      $this->modelToForm($this->modeloPaises);
      $this->setFormValues();
      $menos="<button class=\"smallButton\" type=\"button\" onclick=\"jConfirm('Esta seguro de eliminar este País?','Confirme',APJCall,['eliminar','{$this->Form->codigo}']);return false;\" title=\"Eliminar\">-</button>";
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

  public function eliminar($params) {
    if ($this->modeloPaises->delete(array('codigo'=>$params[0]))) {
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
    $out=" ";
    foreach ($rows as $row) {
      $out.='<tr class="modo1" onclick="APJCall(\'buscaPais\',\''.$row['codigo'].'\')">';
      $out.='<td>'.$row['codigo'].'</td><td>'.$row['pais'].'</td></tr>';
    }
    return $out;
  }
}
$app = new Paises('paises.html');