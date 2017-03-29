<?php
require_once("init.php");
class Home extends APJController
{
  private $timeOut = 10000;
  
  public function __construct($page) {
    $this->sessionControl();
    parent::__construct($page);
  }
  
}
$app=new Home('home.html');