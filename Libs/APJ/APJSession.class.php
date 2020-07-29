<?php
/**
* Controls the user session<br>
* Controla la sesión de usuarios
* Version: 1.9.200512
* Author: Ricardo Seiffert
*/
class APJSession
{
  /**
  * Verifies that the session is still active<br>
  * Verifica que la sesion está aún activa
  * @param (string) session name
  * @return boolean true if active
  */
  public static function active($name) {
    self::setName($name);
    session_start();
    return self::same();
  }
  /**
  * Start session<br>
  * Inicia la sesión
  * @param (string) session name
  * @param (int) session time limit (secs)
  * @param (string) path to session data (optional)
  * @param (string) session domain(optional)
  * @param (boolean) session uses security (optional)
  */
   public static function start($name, $limit = 0, $path = null, $domain = null, $secure = null) {
      self::setName($name);
      $domain = isset($domain) ? $domain : isset($_SERVER['SERVER_NAME']);
      $https = isset($secure) ? $secure : isset($_SERVER['HTTPS']);
      if (!empty($path)) {
        session_set_cookie_params($limit, $path, $domain, $https, true);
      }
      if (session_status()!=PHP_SESSION_ACTIVE) {
        $timeOut = (SESSION_LIMIT)?SESSION_LIMIT:1440;
        ini_set('session.gc_maxlifetime',$timeOut);
      }
      session_start();
      if(self::validate()) {
        if(!self::same()) {
          self::regenerate();
          self::basicData();
        } elseif(rand(1, 100) <= 5) {
          self::regenerate();
        }
      } else {
        self::destroy();
        session_start();
      }
   }
   
  /**
  * Set hashed session name (md5)<br>
  * Define el nombre de la sesión codificada (md5)
  * @param (string) Starting session name
  */
  private static function setName($name) {
    if (session_status()!=PHP_SESSION_ACTIVE) {
      $hashname = md5($name . '_Session');
      session_name($hashname);
    }
  }
  
  /**
  * Basic session information<br>
  * Información básica de la sesión
  */
  private static function basicData() {
    $_SESSION['IPaddress'] = md5($_SERVER['REMOTE_ADDR']);
    $_SESSION['userAgent'] = md5($_SERVER['HTTP_USER_AGENT']);
  }
  
  /**
  * Determines if is the same Ip and machine<br>
  * Determina si es la misma Ip y maquina
  * @return (boolean) Same session
  */
  private static function same() {
    if(!isset($_SESSION['IPaddress']) or !isset($_SESSION['userAgent']))
      return false;
    if ($_SESSION['IPaddress'] != md5($_SERVER['REMOTE_ADDR']))
      return false;

    if( $_SESSION['userAgent'] != md5($_SERVER['HTTP_USER_AGENT']))
      return false;

    return true;
  }

  /**
  * Session validation<br>
  * Validación de sesión
  * @return (boolean) Valid session
  */
  private static function validate() {
    if(isset($_SESSION['OBSOLETE']) and !isset($_SESSION['EXPIRES']) )
      return false;

    if(isset($_SESSION['EXPIRES']) and $_SESSION['EXPIRES'] < time())
      return false;

    return true;
  } 
   
  /**
  * Regenerates the session<br>
  * Regenera la sesión
  */
  private static function regenerate() {
    if(isset($_SESSION['OBSOLETE']) and $_SESSION['OBSOLETE'] == true)
      return;

    $_SESSION['OBSOLETE'] = true;
    $_SESSION['EXPIRES'] = time() + 10;

    session_regenerate_id(false);

    $newSession = session_id();
    session_write_close();

    session_id($newSession);
    session_start();

    unset($_SESSION['OBSOLETE']);
    unset($_SESSION['EXPIRES']);
  }  

  /**
  * Destroys the session<br>
  * Destruye la sesión
  */
  public static function destroy() {
    $_SESSION = array();
    $_COOKIE = array();
    session_destroy();
  }  
}