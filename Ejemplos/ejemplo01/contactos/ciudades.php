<?php
require_once("init.php");
class Ciudades extends APJController
{
  // Los modelos
  private $modeloPaises;
  private $modeloCiudades;
  
  // Construcor (primer metodo ejecutado)
  public function __construct($page) {
    // Propiedad utilizada por la vista, que define el tiempo de espera en milisegundos
    $this->TimeOut = 10000;
    // Controla la sesión del usuario
    $this->sessionControl();
    // Instancia los modelos
    $this->instanciaModelos();
    // Muestra la vista desde el contructor de APJController si es que es necesario
    parent::__construct($page);
  }
  
  private function instanciaModelos() {
    $this->modeloPaises = new Models_Paises();
    $this->modeloCiudades = new Models_Ciudades();
    // showModel(true) permite documentar el modelo, mostrando la estrcutura básica de la tabla
    // showStructure() crea el código necesario para crear la estructura fija en el modelo
    //$this->modeloCiudades->showModel(true); true=documentación corta, false=completa
    //$this->modeloCiudades->showStructure();
  }
  
  // Llamado desde la vista con APJ:{optPaises()}
  // Crea las opciones para el select de Paises y lo retorna a la vista
  public function optPaises() {
    $paises = $this->modeloPaises->all('pais');
    //options es un metodo que automatiza el proceso de crear las opciones de un select
    return $this->options($paises,'codigo','pais','CL');
  }
  
  // Llamado desde la vista con APJCall, por ello pasa los parametros como un array
  public function buscaCiudad($params) {
    $id=$params[0];
    // Busca en el modelo Ciudades por numero de Id
    if ($this->modeloCiudades->find($id)) {
      // Pasa los datos del modelo al objeto Form
      $this->modelToForm($this->modeloCiudades);
      // Rellena el formulario de la vista con los valores del objeto Form
      $this->setFormValues();
      $menos="<button class=\"smallButton\" type=\"button\" onclick=\"jConfirm('Esta seguro de eliminar esta Ciudad?','Confirme',APJCall,['eliminar','{$this->Form->id}']);return false;\" title=\"Eliminar\">-</button>";
      // Asigna el botón eliminar al <div> #menos
      $this->jQ("#menos")->html($menos);
      // Hace que el formulario suba al tope y centre el foco en el campo Ciudad
      $this->jQ("form")->scrollTop(0);
      $this->jQ("#ciudad")->focus();
    } else {
      // Quita el botón eliminar
      $this->jQ("#menos")->empty();
    }
  }
  
  // Llamado por medio de APJSubmit. El objeto Form ya contiene los valores del formulario
  public function guardar() {
    // Pasa los datos de Form al modelo
    $this->formObjectToModel($this->modeloCiudades);
    // Llama al metodo guardar del modelo Ciudades, si hay errores que los muestre
    if ($this->modeloCiudades->guardar($this->Form)) {
      $this->cambiaGrilla($this->Form->codigo_pais);
      $this->jQ("#reset")->click();
    } else {
      $this->muestraErrores();
    }
  }

  // Lllamado desde la vista don APJCall para eliminar la ciudad
  public function eliminar($params) {
    // Si son varios parametros se puede usar list($id,$nombre,$otroParametro)=$params;
    $id=$params[0];
    // Llama al meoto delete del modelo Ciudades para eliminar.
    if ($this->modeloCiudades->delete($id)) {
      $this->jInfo("Ciudad eliminada.");
      $this->cambiaGrilla('CL');
      $this->jQ("#reset")->click();
    } else {
      $this->muestraErrores();
    }
  }
  
  // Muestra los errores generados por el modelo Ciudades
  private function muestraErrores() {
    if ($this->modeloCiudades->errors) {
      // showWarnings, showErrors facilita desplegar advertencias desde un array de mensajes, como "errors" que contiene todos los mensajes arrojados desde basicValidation() en el modelo
      $this->showWarnings($this->modeloCiudades->errors,"Validación de datos",$this->modeloCiudades->alias);
      return true;
    }
    // erromsg contiene el mensaje del último error ocurrido en el modelo
    if ($this->modeloCiudades->errormsg) {
      // jError muestra un mensaje con formato de error
      $this->jError($this->modeloCiudades->errormsg,"Error");
      return true;
    }
    $this->jError("Error en la base de datos, revise el LOG para más detalles.");
  }
  
  public function cambiaGrilla($params) {
    $grilla=" ".$this->grillaCiudades($params);
    $this->jQ("#grillaCiudades")->html($grilla);
  }
  
  // Llamado desde la vista y este controlador para generar la grilla de Ciudades
  public function grillaCiudades($params) {
    //$params puede o no ser un array
    if (is_array($params)) {
      $pais=$params[0];  
    } else {
      $pais=$params;
    }
    // select($condiciones,$orden) devuelve un array de filas con los criterios dados
    $rows=$this->modeloCiudades->select(array('codigo_pais'=>$pais),'ciudad');
    $out=" ";
    foreach ($rows as $row) {
      $out.='<tr class="modo1" onclick="APJCall(\'buscaCiudad\','.$row['id'].')">';
      $out.='<td>'.$row['codigo_pais'].'</td><td>'.$row['ciudad'].'</td></tr>';
    }
    return $out;
  }
}
// Verifica si debe instanciar el controlador
if (!$_POST['noInstance']) {
  // Instancia el controlador y le pasa por parámetro la vista que debe abrir
  $app = new Ciudades('ciudades.html');
}