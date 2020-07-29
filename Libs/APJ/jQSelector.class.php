<?php
/**
* Class that defines the jQuery selector for jQ
* Clase que define el selector jQuery para jQ
* Version: 1.9.200512
* Author: Ricardo Seiffert
*/
class jQSelector
{
  public $selector;
  public $method = array();
  public $arguments = array();

  /**
  * jQSelector constructor<br>
  * Constructor de jQselector
  * @param {string} Selector
  */
  public function __construct($selector) {
    jQ::setSelector($this); 
    $this->selector = $selector;
  }

  /**
  * Calls to inexistent methods (Overloading)<br>
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
  * Returns a new instance of jQSelector<br>
  * Devuelve una nueva instancia de jQSelector
  * @return (object) new jQSelector from $this->selector
  */
  public function newSelector() 
  {
    return new jQSelector($this->selector);
  }
}