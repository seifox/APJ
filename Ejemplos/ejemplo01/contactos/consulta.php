<?php
require_once("init.php");
class Consulta extends APJController
{
  private $modeloContactos;
  private $gen;
  
  public function __construct($page) {
    $this->TimeOut = 20000;
    $this->sessionControl();
    $this->instanciaModelos();
    $this->gen = new APJHtmlGen();
    parent::__construct($page);
  }
  
  private function instanciaModelos() {
    $this->modeloContactos = new Model_Contactos();
  }

  public function consulta() {
    // Verifica si hay parametros adicionales en el APJSubmit
    if ($this->Form->parameters) {
      $page=($this->Form->parameters[0])?$this->Form->parameters[0]:1;
      $criterio=$this->Form->busqueda;
      $paged=$this->Form->conPaginacion;
    } else {
      $criterio="";
      $page=1;
      $paged=true;
    }
    if ($paged) {
      $rows=$this->modeloContactos->buscarCaulquierPaginado($criterio,$page,15);
      $this->jQ("#paging")->html($this->paginacion($page));
    } else {
      $rows=$this->modeloContactos->buscarCaulquier($criterio);
      $this->jQ("#paging")->empty();
    }
    $cuerpo=" ";
    if (!$rows) {
        $this->jWarning("No hay resultados de la busqueda");
        return false;
    }
    $cuerpo=$this->preparaTabla($rows);
    // Modifica las filas de la consulta por jquery. Solo a peticion desde la vista con APJSubmit
    $this->jQ("#cuerpo")->html($cuerpo);
    // Retorna la filas de la consulta al controlador para que genere la vista, llamdado desde la vista con APJ:{consulta(1)}
    return $cuerpo;
  }

  private function preparaTabla($rows) {
    $out="";
    foreach ($rows as $row) {
      // Usando Clase añadida (Helper) para calcular la edad
      $edad=Helper_FuncionesFecha::edad($row['fecha_nacimiento']);
      $rowf=$this->modeloContactos->setFormat($row);
      $out.=$this->gen->create("tr")->clas("modo1")->
      add("td")->text($row['pais'])->close()->
      add("td")->text($row['dni'])->close()->
      add("td")->text($row['nombres'])->close()->
      add("td")->text($row['apellido_paterno'])->close()->
      add("td")->text($row['apellido_materno'])->close()->
      add("td")->text($row['ciudad'])->close()->
      add("td")->text($row['direccion'])->close()->
      add("td")->text($row['fono'])->close()->
      add("td")->text($row['email'])->close()->
      add("td")->text($rowf['fecha_nacimiento'])->close()->
      add("td")->text($edad)->end();
    }
    return $out;
  }
  
  public function paginacion($page) {
    $this->gen->create("table")->clas("tabla")->add("tr")->clas("modo2");
    if ($page>1) {
      $this->gen->add("td")->
      add("img")->clas("manito")->attr("src","images/first.gif")->attr("alt","Primero")->attr("witdh","18")->
      attr("heiht","13")->title("Primera página")->onclick("APJSubmit('form','consulta',[1])")->close()->close()->
      add("td")->add("img")->clas("manito")->attr("src","images/previous.gif")->attr("alt","Anterior")->attr("witdh","18")->
      attr("heiht","13")->title("Página anterior")->onclick("APJSubmit('form','consulta',[{$this->modeloContactos->previousPage}])")->close()->close();
    } else {
      $this->gen->add("td")->text("&nbsp;")->close()->add("td")->text("&nbsp")->close();
    }
    $this->gen->add("td")->text($page.'/'.$this->modeloContactos->lastPage)->close();
    if ($page<$this->modeloContactos->lastPage) {
      $this->gen->add("td")->
      add("img")->clas("manito")->attr("src","images/next.gif")->attr("alt","Siguiente")->attr("witdh","18")->
      attr("heiht","13")->title("Siguiente página")->onclick("APJSubmit('form','consulta',[{$this->modeloContactos->nextPage}])")->close()->close()->
      add("td")->add("img")->clas("manito")->attr("src","images/last.gif")->attr("alt","Anterior")->attr("witdh","18")->
      attr("heiht","13")->title("Última página")->
      onclick("APJSubmit('form','consulta',[{$this->modeloContactos->lastPage}])");
    } else {
      $this->gen->add("td")->text("&nbsp;")->close()->add("td")->text("&nbsp")->close();
    }
    return $this->gen->end();
  }
}
$app = new Consulta('consulta.html');