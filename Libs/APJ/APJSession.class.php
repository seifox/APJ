<?php
/*
  APJ Session
  Versión: 1.16.0307
*/
class APJSession
{
  /**
  * Verifies that the session is still active</br>
  * Verifica que la sesion está aún activa
  * @param $name (string) session name
  * @return boolean
  */
  static function active($name) {
    self::setName($name);
    session_start();
    return self::same();
  }
  /**
  * Start session</br>
  * Comienza la sesión
  * @param $name (string) session name
  * @param $limit (int) session time limit (secs)
  * @param $path (string) path to session data
  * @param $domain (string) session domain
  * @param $secure (boolean) session uses security
  */
   static function start($name, $limit = 0, $path = null, $domain = null, $secure = null) {
      self::setName($name);
      $domain = isset($domain) ? $domain : isset($_SERVER['SERVER_NAME']);
      $https = isset($secure) ? $secure : isset($_SERVER['HTTPS']);
      if (!empty($path)) {
        session_set_cookie_params($limit, $path, $domain, $https, true);
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
  * Set session name</br>
  * Define el nombre de la sesión
  * @param $name (string)
  */
  static protected function setName($name) {
    $hashname = md5($name . '_Session');
    session_name($hashname);
  }
  
  /**
  * Basic session information</br>
  * Información básica de la sesión
  */
  static protected function basicData() {
    $_SESSION['IPaddress'] = md5($_SERVER['REMOTE_ADDR']);
    $_SESSION['userAgent'] = md5($_SERVER['HTTP_USER_AGENT']);
  }
  
  /**
  * Determines if is the same Ip and machine</br>
  * Determina si es la misma Ip y maquina
  * @return (boolean) 
  */
  static function same() {
    if(!isset($_SESSION['IPaddress']) or !isset($_SESSION['userAgent']))
      return false;
    if ($_SESSION['IPaddress'] != md5($_SERVER['REMOTE_ADDR']))
      return false;

    if( $_SESSION['userAgent'] != md5($_SERVER['HTTP_USER_AGENT']))
      return false;

    return true;
  }

  /**
  * Session validation</br>
  * Validación de sesión
  * @return (boolean)
  */
  static protected function validate() {
    if(isset($_SESSION['OBSOLETE']) and !isset($_SESSION['EXPIRES']) )
      return false;

    if(isset($_SESSION['EXPIRES']) and $_SESSION['EXPIRES'] < time())
      return false;

    return true;
  } 
   
  /**
  * Regenerates the session</br>
  * Regenera la sesión
  */
  static function regenerate() {
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
  * Destroys the session</br>
  * Destruye la sesión
  */
  static function destroy() {
    $_SESSION = array();
    session_destroy();
  }  
}