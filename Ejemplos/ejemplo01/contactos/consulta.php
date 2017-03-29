<?php
require_once("init.php");
class Consulta extends APJController
{
  private $timeOut = 10000;
  private $modeloContactos;
  
  public function __construct($page) {
    $this->sessionControl();
    $this->instanciaModelos();
    parent::__construct($page);
  }
  
  private function instanciaModelos() {
    $this->modeloContactos = new Models_Contactos();
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
    $out=" ";
    foreach ($rows as $row) {
      // Usando Clase añadida (Helper) para calcular la edad
      $edad=Classes_FuncionesFecha::edad($row['fecha_nacimiento']);
      $out.=<<<FILA
        <tr class="modo1">
          <td>{$row['pais']}</td>
          <td>{$row['dni']}</td>
          <td>{$row['nombres']}</td>
          <td>{$row['apellido_paterno']}</td>
          <td>{$row['apellido_materno']}</td>
          <td>{$row['ciudad']}</td>
          <td>{$row['direccion']}</td>
          <td>{$row['fono']}</td>
          <td>{$row['email']}</td>
          <td>{$row['fecha_nacimiento']}</td>
          <td>{$edad}</td>
        </tr>
FILA;
    }
    return $out;
  }
  
  public function paginacion($params) {
    $page=(is_array($params))?$params[0]:$params;
    $out='<table class="tabla">
      <tr class="modo2">';
    if ($page>1) {
      $out.=<<<PRIMERO
        <td>
          <img src="images/first.gif" class="manito" alt="Primero" width="18" height="13" title="Primera página" onclick="APJSubmit('form','consulta',[1])">
        </td>
        <td>
          <img src="images/previous.gif" class="manito" alt="anterior" width="14" height="13" title="Página anterior" onclick="APJSubmit('form','consulta',[{$this->modeloContactos->previousPage}])">
        </td>
PRIMERO;
    }        
    $out.='<td>'.$page.'/'.$this->modeloContactos->lastPage.'</td>';
    if ($page<$this->modeloContactos->lastPage) {
      $out.=<<<SIGUIENTE
        <td>
          <img src="images/next.gif" class="manito" alt="siguiernte" width="14" height="13" title="Siguiente página" onclick="APJSubmit('form','consulta',[{$this->modeloContactos->nextPage}])">
        </td>
        <td>
          <img src="images/last.gif" class="manito" alt="ultimo" width="18" height="13" title="Última página" onclick="APJSubmit('form','consulta',[{$this->modeloContactos->lastPage}])">
        </td>
SIGUIENTE;
    }
    $out.='</tr></table>';
    return $out;
  }
}
$app = new Consulta('consulta.html');