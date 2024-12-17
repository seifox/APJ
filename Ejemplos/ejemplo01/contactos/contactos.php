<?php
require_once("init.php");
class Contactos extends APJController
{
  private $modeloPaises;
  private $modeloCiudades;
  private $modeloContactos;
  private $gen;
  
  public function __construct($page) {
    $this->sessionControl();
    $this->instanciaModelos();
    $this->gen = new APJHtmlGen();
    parent::__construct($page);
  }
  
  private function instanciaModelos() {
    $this->modeloPaises = new Model_Paises();
    $this->modeloCiudades = new Model_Ciudades();
    $this->modeloContactos = new Model_Contactos();
    //$this->modeloContactos->showModel(false);
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

  public function buscarPais($pais=NULL) {
    if ($pais) {
      $rows=$this->modeloPaises->buscarPais($pais);
    } else {
      $this->jWarning('Debe especificar que quiere buscar');
      return false;
    }
    if ($rows) {
      $columnas=array('codigo','pais');
      $out=$this->creaTablaResultados($rows,$columnas,'seleccionaPais');
      $this->jShowDown('paisTxt','listadodiv',$out);
    } else {
      $this->jInfo('No se han encontrado coincidencias');
    }
    
  }
  
  public function seleccionaPais($param) {
    $this->jQ("#listadodiv")->hide();
    $pais=$param[0];
    $this->jQ("#pais")->val($pais);
    $this->modeloPaises->find(array('codigo'=>$pais));
    $this->jQ("#paisTxt")->val($this->modeloPaises->pais);
    $this->cambiaCiudades($pais);
  }
  
  public function cambiaCiudades($pais) {
    $ciudades=$this->gen->create("select")->name("ciudad")->id("ciudad")->add("option")->value("")->attr("selected","selected")->text("-Seleccione la Ciudad-")->close()->
    literal($this->optCiudades($pais))->end();
    $this->jQ("#CiudadTd")->html($ciudades);
    $this->refrescaGrilla($pais);
  }
  
  public function editaContacto($id) {
    if ($this->modeloContactos->find($id)) {
      $this->modelToForm($this->modeloContactos);
      $this->seleccionaPais(array($this->Form->pais));
      $this->fieldTypes['activo']="checkbox";
      $this->setFormValues($this->Form);
      $this->calculaEdad(array($this->Form->fecha_nacimiento));
      $eliminar=$this->gen->create("button")->type("button")->onclick("jConfirm('Esta seguro de eliminar este Contacto?','Confirme',APJCall,['eliminar',{$this->Form->id}]);return false;")->title("Eliminar")->text("ELIMINAR")->end();
      $this->jQ("#eliminar")->html($eliminar);
      $this->jQ("#form")->scrollTop(0);
      $this->jQ("#dni")->focus();
    }
  }
  
  public function buscarDNI($pais,$dni) {
    if ($pais and $dni) {
      if ($this->modeloContactos->find(array('pais'=>$pais,'dni'=>$dni))) {
        $this->editaContacto($this->modeloContactos->id);
      }
    }
  }
  
  public function buscarNombres($nombre=NULL) {
    if ($nombre) {
      $rows=$this->modeloContactos->buscarNombre($nombre);
    } else {
      $this->jWarning('Debe especificar que quiere buscar');
      return false;
    }
    if ($rows) {
      $columnas=array('id','nombre');
      $out=$this->creaTablaResultados($rows,$columnas,'seleccionaContacto');
      $this->jShowDown('nombres','listadodiv',$out);
    } else {
      $this->jInfo('No se han encontrado coincidencias');
    }
  }
  
  public function seleccionaContacto($id) {
    $this->jQ("#listadodiv")->hide();
    $this->editaContacto($id);
  }
  
  public function calculaEdad($fecha) {
    if (!empty($fecha)) {
      if ($edad=Helper_FuncionesFecha::edad($fecha)) {
        $this->jQ("#edad")->text(" Edad:{$edad} años");
      } else {
        $this->jQ("#edad")->text(" Edad:0");
      }
    }
  }
  
  public function guardar() {
    $this->formObjectToModel($this->modeloContactos);
    if ($this->modeloContactos->guardar($this->Form)) {
      $this->refrescaGrilla($this->modeloContactos->pais);
      $this->jQ("#reset")->click();
    } else {
      $this->muestraErrores();
    }
  }

  public function eliminar($id) {
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
    $this->gen->start();
    if ($rows=$this->modeloContactos->consulta($pais)) {
      foreach ($rows as $row) {
        // setFormat() devuelve la fila formateda según los tipos de datos del modelo
        $rowf=$this->modeloContactos->setFormat($row);
        $this->gen->add("tr")->clas("modo1")->onclick("APJCall('editaContacto',{$row['id']})");
        // Se crea cada columna de acuerdo a los campos del modelo
        foreach ($this->modeloContactos->fields as $campo) {
          $this->gen->add("td")->text($rowf[$campo])->close();
        }
        $this->gen->close();
      }
    }
    return $this->gen->end();
  }
  
  // Crea el encabezado de la grilla dependiendo del alias/comentario definido en el modelo   
  public function creaEncabezado() {
    $this->gen->create("tr");
    foreach ($this->modeloContactos->alias as $desc) {
      $this->gen->add("th")->text($desc)->close();
    }
    return $this->gen->end();
  }
  
  private function creaTablaResultados($rows,$columnas,$metodo) {
    $this->gen->create("table")->clas("tabla");
    foreach($rows as $row) {
      $this->gen->add("tr")->clas("modo1")->onclick("APJCall('{$metodo}','{$row[$columnas[0]]}')");
      foreach ($columnas as $k=>$col) {
        if ($k>0) {
          $this->gen->add("td")->text($row[$col])->close();
        }
      }
      $this->gen->close();
    }
    return $this->gen->end();
  }
  
  
}
$app = new Contactos('contactos.html');