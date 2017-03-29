<?php
require_once("init.php");
class Contactos extends APJController
{
  private $timeOut = 10000;
  private $modeloPaises;
  private $modeloCiudades;
  private $modeloContactos;
  
  public function __construct($page) {
    $this->sessionControl();
    $this->instanciaModelos();
    parent::__construct($page);
  }
  
  private function instanciaModelos() {
    $this->modeloPaises = new Models_Paises();
    $this->modeloCiudades = new Models_Ciudades();
    $this->modeloContactos = new Models_Contactos();
    //$this->modeloContactos->showModel(true);
  }
  
  public function optPaises() {
    $paises = $this->modeloPaises->all('pais');
    return $this->options($paises,'codigo','pais');
  }
  
  public function optCiudades($pais) {
    if (empty($pais)) {
      $pais='CL';
    }
    $ciudades = $this->modeloCiudades->select("codigo_pais = '{$pais}'",'ciudad');
    return $this->options($ciudades,'id','ciudad');
  }

  public function cambiaCiudades($params) {
    $pais=$params[0];
    $ciudades='<select name="ciudad" id="ciudad"><option value="" selected>-Seleccione la Ciudad-</option>';
    $ciudades.=$this->optCiudades($pais);
    $ciudades.="</select>";
    $this->jQ("#CiudadTd")->html($ciudades);
    $this->refrescaGrilla($pais);
  }
  
  public function editaContacto($params) {
    $id=$params[0];
    if ($this->modeloContactos->find($id)) {
      $this->modelToForm($this->modeloContactos);
      $this->fieldTypes['activo']="checkbox";
      $this->setFormValues($this->Form);
      $this->calculaEdad(array($this->Form->fecha_nacimiento));
      $eliminar="<button type=\"button\" onclick=\"jConfirm('Esta seguro de eliminar este Contacto?','Confirme',APJCall,['eliminar','{$this->Form->id}']);return false;\" title=\"Eliminar\">ELIMINAR</button>";
      $this->jQ("#eliminar")->html($eliminar);
      $this->jQ("#form")->scrollTop(0);
      $this->jQ("#dni")->focus();
    }
  }
  
  public function calculaEdad($params) {
    if (!empty($params)) {
      if ($edad=Classes_FuncionesFecha::edad($params[0])) {
        $this->jQ("#edad")->text(" Edad:{$edad} años");
      } else {
        $this->jQ("#edad")->text(" Edad:0");
      }
    }
  }
  
  public function guardar() {
    $this->formObjectToModel($this->modeloContactos);
    if ($this->modeloContactos->guardar($this->Form)===false) {
      $this->muestraErrores();
    } else {
      $this->refrescaGrilla($this->modeloContactos->pais);
      $this->jQ("#reset")->click();
    }
  }

  public function eliminar($params) {
    $id=$params[0];
    if ($this->modeloContactos->delete($id)) {
      $this->jInfo("Contacto eliminado.");
      $this->refrescaGrilla($this->modeloContactos->pais);
      $this->jQ("#reset")->click();
    } else {
      $this->muestraErrores();
    }
  }
  
  private function muestraErrores() {
    if ($this->modeloContactos->errors) {
      $this->showWarnings($this->modeloContactos->errors,"Validación de datos",$this->modeloContactos->alias);
      return true;
    }
    if ($this->modeloContactos->errormsg) {
      $this->jError($this->modeloContactos->errormsg,"Error");
      return true;
    }
    $this->jError("Error en la base de datos, revise el LOG para más detalles.");
  }
  
  public function refrescaGrilla($pais) {
    $grilla=" ".$this->grillaContactos($pais);
    $this->jQ("#grillaContactos")->html($grilla);
  }
  
  public function grillaContactos($pais) {
    if (empty($pais)) {
      $pais='CL';
    }
    $out=" ";
    if ($rows=$this->modeloContactos->consulta($pais)) {
      foreach ($rows as $row) {
        // setFormat() devuelve la fila formateda según los tipos de datos del modelo
        $rowf=$this->modeloContactos->setFormat($row);
        $out.='<tr class="modo1" onclick="APJCall(\'editaContacto\','.$row['id'].')">';
        // Se crea cada columna de acuerdo a los campos del modelo
        foreach ($this->modeloContactos->fields as $campo) {
          $out.="<td>".$rowf[$campo]."</td>";
        }
        $out.="</tr>";
      }
    }
    return $out;
  }
  
  // Crea el encabezado de la grilla dependiendo del alias/comentario definido en el modelo   
  public function creaEncabezado() {
    $out='<tr>';
    foreach ($this->modeloContactos->alias as $desc) {
      $out.='<th>'.$desc.'</th>';
    }
    $out.='</tr>';
    return $out;
  }
}
$app = new Contactos('contactos.html');