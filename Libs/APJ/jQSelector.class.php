<?php
/**
* jQSelector</br>
* Class that defines the jQuery selector for jQ
* Clase que define el selector jQuery para jQ
*/
class jQSelector
{
  public $selector;
  public $method = array();
  public $arguments = array();

  public function __construct($selector) 
  {
    jQ::setSelector($this); 
    $this->selector = $selector;
  }

  /**
  * Calls to inexistent methods (Overloading)
  * Invoca metodos inexistentes (Sobrecarga)
  * @param $method (mixed)
  * @param $arguments (array)
  * @return {jQSelector|jQSelector}
  */
  public function __call($method, $arguments) 
  {
    array_push($this->method, $method);
    array_push($this->arguments, $arguments);
    return $this;
  }

  /**
  * Returns a new instance of jQSelector
  * Retorna una nueva instancia de jQSelector
  */
  public function newSelector() 
  {
    return new jQSelector($this->selector);
  }
}