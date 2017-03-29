<?php
/*
Table structure of [contactos]
+------------------+---------+------+----------+------+-----+---------+
|      Field       |  Type   | Size | Decimals | Null | Key | Default |
+------------------+---------+------+----------+------+-----+---------+
| id               | int     |   11 |          | NO   | PRI |         |
| pais             | varchar |    2 |          | NO   | MUL |         |
| dni              | varchar |   25 |          | NO   |     |         |
| nombres          | varchar |   50 |          | NO   |     |         |
| apellido_paterno | varchar |   40 |          | NO   |     |         |
| apellido_materno | varchar |   40 |          | YES  |     |         |
| ciudad           | int     |   11 |          | NO   | MUL |         |
| direccion        | varchar |  100 |          | YES  |     |         |
| fono             | varchar |   25 |          | YES  |     |         |
| email            | varchar |  255 |          | YES  |     |         |
| fecha_nacimiento | date    |   10 |          | YES  |     |         |
| activo           | tinyint |    1 |          | NO   |     |       0 |
+------------------+---------+------+----------+------+-----+---------+
*/
class Models_Contactos extends APJModel
{
  public function __construct() {
    parent::__construct();
    // Define la tabla del modelo (lee la estructura en forma dinámica)
    $this->setTable('contactos');
    // Trunca los espacios en blanco de los campos
    $this->trim=true;
  }
  
  public function guardar() {
    // Si no pasa la validación básica, retornará verdadero
    if ($this->basicValidation()) {
      return false;
    }
    if ($this->id) {
      // Actualiza y retorna las filas afectadas o FALSE si hay algún problema
      return $this->update();      
    }
    // Insert  y retorna las filas afectadas o FALSE si hay algún problema
    return $this->insert();
  }
  
  public function consulta($pais) {
    $sql="SELECT 
      contactos.id,
      paises.pais,
      contactos.dni,
      contactos.nombres,
      contactos.apellido_paterno,
      contactos.apellido_materno,
      ciudades.ciudad,
      contactos.direccion,
      contactos.fono,
      contactos.email,
      contactos.fecha_nacimiento,
      contactos.activo
    FROM
      paises
      INNER JOIN contactos ON (paises.codigo = contactos.pais)
      INNER JOIN ciudades ON (contactos.ciudad = ciudades.id)
    WHERE contactos.pais = :pais
    ORDER BY
      contactos.apellido_paterno,
      contactos.nombres
    LIMIT 1000";
    $params=array('pais'=>$pais);
    // Retorna todas las filas de la consulta con asignacion de parametro :pais (binding)
    return $this->rows($sql,$params);
  }

  // Consulta con filtro literal aplicado a cualquier columna
  public function buscarCaulquier($filtro) {
    $condicion=$this->condicion($filtro);
    $sql="SELECT * FROM (
    SELECT contactos.id,
           paises.pais,
           contactos.dni,
           contactos.nombres,
           contactos.apellido_paterno,
           contactos.apellido_materno,
           ciudades.ciudad,
           contactos.direccion,
           contactos.fono,
           contactos.email,
           contactos.fecha_nacimiento,
           contactos.activo
    FROM paises
         INNER JOIN contactos ON (paises.codigo = contactos.pais)
         INNER JOIN ciudades ON (contactos.ciudad = ciudades.id)
    WHERE contactos.activo = 1) AS consulta
    {$condicion}
    ORDER BY
      apellido_paterno,
      nombres
    LIMIT 1000";
    // Retorna todas las filas de la consulta
    return $this->rows($sql);
  }
  
  // Consulta paginada
  public function buscarCaulquierPaginado($filtro,$page,$limit=20) {
    $condicion=$this->condicion($filtro);
    $sql="SELECT * FROM (
    SELECT contactos.id,
           paises.pais,
           contactos.dni,
           contactos.nombres,
           contactos.apellido_paterno,
           contactos.apellido_materno,
           ciudades.ciudad,
           contactos.direccion,
           contactos.fono,
           contactos.email,
           contactos.fecha_nacimiento,
           contactos.activo
    FROM paises
         INNER JOIN contactos ON (paises.codigo = contactos.pais)
         INNER JOIN ciudades ON (contactos.ciudad = ciudades.id)
    WHERE contactos.activo = 1) AS consulta
    {$condicion}
    ORDER BY
      apellido_paterno,
      nombres";
    // Retorna la pagina especificada de la consulta
    return $this->paging($sql,$limit,$page);
  }  
  
  // Prepara las condiciones de la consulta
  private function condicion($filtro) {
    if ($filtro) {
      $condicion="WHERE ";
      foreach ($this->fields as $fld) {
        $condicion.=$fld." LIKE '%".$filtro."%' OR ";
      }  
      $condicion=substr($condicion,0,-4);
    }
    return $condicion;    
  }
}