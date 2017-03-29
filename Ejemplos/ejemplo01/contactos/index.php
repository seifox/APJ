<?php
require_once("init.php");
class Index extends APJController
{
  private $timeOut = 10000;
  
  public function __construct($page) {
    $this->sessionControl();
    parent::__construct($page);
  }
  
  public function contenedor($params) {
    $page=$params[0];
    if ($page=="Fin") {
      session_destroy(); 
      $this->redirect('login.php?end=true',true);
      return true;
    }
    $controlador=strtolower($page.'.php');
    $this->iframe($controlador);
  }
  
  public function nombreAplicacion() {
    return APPNAME;
  }
  
  private function iframe($lnk) {
    $this->jQ("#mainiframe")->attr('src',$lnk);
  }
}

$app=new Index('index.html');