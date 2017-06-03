<?php
/**
* Class that defines the jQuery selector for jQ
* Clase que define el selector jQuery para jQ
* Versión: 1.17.0602
* Author: Ricardo Seiffert
*/
class jQSelector
{
  public $selector;
  public $method = array();
  public $arguments = array();

  /**
  * jQSelector constructor
  * Constructor de jQselector
  * @param {string} Selector
  */
  public function __construct($selector) {
    jQ::setSelector($this); 
    $this->selector = $selector;
  }

  /**
  * Calls to inexistent methods (Overloading)
  * Invoca metodos inexistentes (Sobrecarga)
  * @param (string) Method name
  * @param (array) Method arguments
  * @return (object) jQSelector
  */
  public function __call($method, $arguments) {
    array_push($this->method, $method);
    array_push($this->arguments, $arguments);
    return $this;
  }

  /**
  * Returns a new instance of jQSelector
  * Devuelve una nueva instancia de jQSelector
  * @return (object) new jQSelector from $this->selector
  */
  public function newSelector() 
  {
    return new jQSelector($this->selector);
  }
}