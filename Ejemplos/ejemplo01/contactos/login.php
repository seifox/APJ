<?php
require_once("init.php");
class Login extends APJController
{
  // Los modelos
  private $modeloUser = NULL;
  private $modeloAcceso = NULL;
  private $modeloLog = NULL;
  
  const INTENTOS = 3;
  
  public function __construct($page) {
    $this->TimeOut = 10000;
    $this->instanciaModelos();
    if (!isset($_POST['action'])) {
      $this->verificaCookies();
    }
    parent::__construct($page);
  }
  
  private function instanciaModelos() {
    $this->modeloUser = new Models_Usuarios();
    //$this->modeloAcceso = new Models_Accesos();
    //$this->modeloLog = new Models_Log();
  }

  private function verificaCookies() {
    if (!isset($_GET['end']) and isset($_COOKIE["idContactos"]) and isset($_COOKIE["secretContactos"])) {
      $id=$_COOKIE["idContactos"];
      $secret=$_COOKIE["secretContactos"];
      if ($secret === md5($id.$_SERVER['HTTP_USER_AGENT'])) {
        $this->modeloUser->find($id);
        $cookieGuardada=$this->modeloUser->cookie;
        if ($cookieGuardada===$secret) {
          if ($this->verificaCuenta()) {
            if ($this->cargaSession($id)) {
              $this->accede($id);
              return true;
            } else {
              $this->jError("Ud. no tiene acceso a ningún módulo");
              return false;
            }
          }
        } else {
          $this->eliminaCookie($id);
        }
      }
    } else {
      $this->eliminaCookie($_COOKIE["idContactos"]);
    }
  }
  
  private function toMenu() {
    $this->redirect(ROOTURL);
    exit();
  }
  
  private function eliminaCookie($id) {
    $this->modeloUser->eliminaCookie($id);
    unset($_COOKIE["idContactos"]);
    unset($_COOKIE["secretContactos"]);
  }
  public function toCambiaContrasena($id) {
    if (isset($_COOKIE["idContactos"])) {
      $this->eliminaCookie($_COOKIE["idContactos"]);
    }
    $this->redirect('contrasena.php?id='.$id[0]);
  }
  
  private function cargaSession($id) {
    APJSession::start(APPNAME,3600);
    if (session_status()==PHP_SESSION_ACTIVE) {
      $_SESSION['id']=$id;
      return $this->defineAccesos($id);
    }
  }
  
  private function showRecupera() {
    $out=<<<out
     <button type="button" name="olvido" id="olvido" onclick="APJSubmit('loginForm','recupera')">Recuperar contraseña</button>
out;
    $this->jQ("#recuperadiv")->html($out);
  }
  
  public function Control() {
    if ($this->Form->usuario and $this->Form->contrasena) {
      $usuario=$this->Form->usuario;
      $contrasena=$this->modeloUser->hashmd5($this->Form->contrasena);
      $this->modeloUser->find(array('usuario'=>$usuario));
      // Verifica si no está bloqueada o ha expirado
      if ($usuario == $this->modeloUser->usuario and $contrasena === $this->modeloUser->contrasena) {
        if ($this->verificaCuenta()) {
          if ($this->Form->guardar_clave) {
            $this->setCookies($this->modeloUser->id);
          } else {
            $this->eliminaCookie($this->modeloUser->id);
          }
          if ($this->cargaSession($this->modeloUser->id)) {
            $this->accede($this->modeloUser->id);
            return true;
          } else {
            $this->jError("Ud. no tienen acceso a ningún módulo");
            return false;
          }
        }
      } else {
        if ($this->modeloUser->id) {
          $this->modeloUser->aumentaIntento($this->modeloUser->id);
        }
        if ($this->modeloUser->intentos > self::INTENTOS) {
          //$this->modeloUser->bloqueo($this->modeloUser->id);
          //$this->modeloLog->log($this->getController(),'bloqueado','Más de '.self::INTENTOS.' intentos de acceso');
          //$this->jError('Su cuenta se encuentra bloqueada.','Error de acceso');
          return false;
        }
        //$this->modeloLog->log($this->getController(),'denegado','Contraseña incorrecta');
        $this->jWarning('Los datos introducidos son incorrectos.','Advertencia de acceso');  
      }
      return false;
    } else {
      $this->jError('Debe ingresar el usuario y contraseña','Error de acceso');
    }
  }
  
  private function accede($id) {
    //$this->modeloLog->log($this->getController(),'acceso','correcto');
    $this->modeloUser->actualizaAcceso($id);
    $this->toMenu();
    return true;
  }
  
  private function verificaCuenta() {
    if ($this->modeloUser->id) {
      if ($this->modeloUser->bloqueado==1) {
        $msg='Su cuenta se encuentra bloqueada por '.$this->modeloUser->motivo_bloqueo;
        $this->jError($msg,'Error de acceso');
        return false;
      }
      if (date('Y-m-d') > $this->modeloUser->vence) {
        $msg='Su cuenta ha expirado el '.$this->convertDateTime($this->modeloUser->vence,'d-m-Y');
        $this->jError($msg,'Error de acceso');
        return false;
      }
      $fecha=$this->modeloUser->ultimo_cambio;
      $dura=$this->modeloUser->duracion_contrasena;
      $vence=date('Y-m-d', strtotime($fecha." + {$dura} day"));
      /*if ($vence<date('Y-m-d')) {
        $this->jScript("advierte({$this->modeloUser->id})");
        return false;
      }*/
      return true;
    } else {
      $this->jWarning('Los datos introducidos son incorrectos.','Advertencia de acceso');  
      //$this->modeloLog->log($this->getController(),'denegado','Erroneo con usuario '.$this->Form->usuario);
      return false;
    }
  }
  
  private function defineAccesos($id) {
    //$accesos=$this->modeloAcceso->getAccesos($id);
    unset($_SESSION['app']);
    unset($_SESSION['permiso']);
    $accesos[]['aplicacion']='index';
    $accesos[]['aplicacion']='home';
    $accesos[]['aplicacion']='paises';
    $accesos[]['aplicacion']='ciudades';
    $accesos[]['aplicacion']='contactos';
    $accesos[]['aplicacion']='consulta';
    $accesos[]['aplicacion']='fin';
    foreach ($accesos as $acc) {
      $app=md5($acc['aplicacion']);
      $_SESSION['app'][]=$app;
      $_SESSION['permiso'][$app]=$acc['permiso'];
    }
    return count($accesos);
  }
  
  private function setCookies($id) {
    $error=false;
    setcookie('idContactos',$id,time()+60*60*24*30) or $error=true;
    $cookie=md5($id.$_SERVER['HTTP_USER_AGENT']);
    setcookie('secretContactos',$cookie,time()+60*60*24*30) or $error=true;
    if ($this->modeloUser->guardaCookie($id,$cookie)) {
      return true;
    }
    return $error;
  }
}
$app=new Login('login.html');
