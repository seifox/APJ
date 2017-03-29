<?php
/*
Table structure of [usuarios]
+---------------------+----------+------+----------+------+-----+---------+----------------+
|        Field        |   Type   | Size | Decimals | Null | Key | Default |     Extra      |
+---------------------+----------+------+----------+------+-----+---------+----------------+
| id                  | int      |   11 |          | NO   | PRI |         | auto_increment |
| usuario             | varchar  |   50 |          | NO   | UNI |         |                |
| contrasena          | varchar  |  100 |          | NO   |     |         |                |
| nickname            | varchar  |   30 |          | YES  |     |         |                |
| nombre              | varchar  |  100 |          | NO   |     |         |                |
| ultima_contrasena   | varchar  |  100 |          | NO   |     |         |                |
| nivel               | tinyint  |    1 |          | NO   |     |       0 |                |
| bloqueado           | tinyint  |    1 |          | NO   |     |       0 |                |
| motivo_bloqueo      | varchar  |  255 |          | YES  |     |         |                |
| intentos            | tinyint  |    1 |          | NO   |     |       0 |                |
| ultimo_intento      | datetime |   19 |          | YES  |     |         |                |
| ultimo_acceso       | datetime |   19 |          | YES  |     |         |                |
| ultimo_cambio       | date     |   10 |          | NO   |     |         |                |
| duracion_contrasena | smallint |    3 |          | NO   |     |     120 |                |
| vence               | date     |   10 |          | NO   |     |         |                |
| cookie              | varchar  |  255 |          | YES  |     |         |                |
| creacion            | date     |   10 |          | NO   |     |         |                |
| modificacion        | datetime |   19 |          | NO   |     |         |                |
+---------------------+----------+------+----------+------+-----+---------+----------------+
*/

class Models_Usuarios extends APJModel
{
  public function __construct() {
    parent::__construct();
    $this->setTable('usuarios');
  }
  
  public function aumentaIntento($id) {
    $hoy=date('Y-m-d H:i:s');
    $this->execute("UPDATE {$this->table} SET intentos=intentos+1, ultimo_intento='{$hoy}' WHERE id={$id}");
    $this->find($id);
  }
  
  public function bloqueo($id) {
    $params=array('bloqueado'=>1,'motivo_bloqueo'=>'Más de 3 intentos de acceso','id'=>$id);
    return $this->execute("UPDATE {$this->table} SET bloqueado=:bloqueado, motivo_bloqueo=:motivo_bloqueo WHERE id=:id",$params);
  }
  
  public function guardaCookie($id,$cookie) {
    $params=array('cookie'=>$cookie);
    return $this->update($id,$params);
  }
  
  public function eliminaCookie($id) {
    if ($id) {
      $params=array('cookie'=>NULL);
      return $this->update($id,$params);
    }
  }
  
  public function actualizaAcceso($id) {
    $upd=array('intentos'=>0,'ultimo_acceso'=>date('Y-m-d H:i:s'));
    $this->update($id,$upd);
  }
  
  public function grilla() {
    $sql="SELECT 
      usuarios.id,
      usuarios.usuario,
      usuarios.nickname,
      usuarios.nombre,
      IF(usuarios.bloqueado, 'Si', 'No') AS bloqueado,
      usuarios.motivo_bloqueo,
      usuarios.intentos,
      usuarios.ultimo_intento,
      usuarios.ultimo_acceso,
      usuarios.ultimo_cambio,
      usuarios.duracion_contrasena,
      usuarios.vence,
      usuarios.creacion,
      usuarios.modificacion
    FROM
      usuarios
    ORDER BY
      usuarios.usuario";
    return $this->rows($sql);
  }
  
  // Encripta la contraseña
  public function hashmd5($pass) {
    $hash=crypt($pass,md5($pass));
    $md5hash=md5($hash);
    return $md5hash;
  }

}