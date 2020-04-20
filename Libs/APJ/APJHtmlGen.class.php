<?php
/**
* Html Generator class<br>
* Clase Generadora de Html
* Versión: 1.7.181009
*/
class APJHtmlGen
{
  private $content = NULL;
  private $tags = array();
  private $closed = array();
  // HTML 5 Tags that requires special closing
  private $specialClose = array();

  public function __construct() {
    $this->setSpecialClose();  
  }
  
  /**
  * Starts a new content<br>
  * Comienza un nuevo contenido
  */
  public function start() {
    $this->initialize();  
  }
  
  /**
  * Initializes properties and creates a new HTML Tag<br>
  * Inicializa las porpiedades y crea una nueva Etiqueta HTML
  * @param (tring) tag
  * @return APJHtmlGen
  */
  public function create($tag) {
    $this->initialize();
    $this->content = '<'.$tag;
    $this->tags[] = $tag;
    return $this;
  }
  
  /**
  * Closes previous Tag and adds a new one
  * Cierra la Etiqueta anterior y añade una nueva
  * @param (string) tag
  * @param (boolean) close last tag
  * @return APJHtmlGen
  */
  public function add($tag,$closeLast=true) {
    if ($closeLast) {
      $this->closeLast();
    }
    $this->content.= '<'.$tag;
    $this->tags[] = $tag;
    return $this;
  }
  
  /**
  * Adds a new attribute</br>
  * Agrega un nuevo atributo
  * @param (string) attribute
  * @param (string) value
  * @return APJHtmlGen
  */
  public function attr($attr,$value) {
    $this->content.=' '.$attr;
    if (strlen($value)) {
      $this->content.='="'.$value.'"'; 
    }
    return $this;
  }
  
  /**
  * Adds a style attribute<br>
  * Agrega un atributo style
  * @param string $style
  * @return APJHtmlGen
  */
  public function style($style) {
    $this->content.=' style="'.$style.'"';
    return $this;
  }  
  
  /**
  * Adds a src attribute<br>
  * Agrega un atributo src
  * @param (string) value
  * @return APJHtmlGen
  */
  public function src($style) {
    $this->content.=' src="'.$style.'"';
    return $this;
  }  
  
  /**
  * Adds a id attribute<br>
  * Agrega un atributo id
  * @param (string) value
  * @return APJHtmlGen
  */
  public function id($value) {
    $this->content.=' id="'.$value.'"';
    return $this;
  }
  
  /**
  * Adds a class attribute<br>
  * Agrega un atributo class
  * @param (string) value
  * @return APJHtmlGen
  */
  public function clas($value) {
    $this->content.=' class="'.$value.'"';
    return $this;
  }
  
  /**
  * Adds a value attribute
  * Agrega un attributo value
  * @param (string) value
  * @return APJHtmlGen
  */
  public function value($value) {
    $this->content.=' value="'.$value.'"';
    return $this;
  }
  
  /**
  * Adds a title attribute
  * Agrega un attributo title
  * @param (string) value
  * @return APJHtmlGen
  */
  public function title($value) {
    $this->content.=' title="'.$value.'"';
    return $this;
  }
  
  /**
  * Adds a text attribute
  * Agrega un attributo text
  * @param (string) value
  * @return APJHtmlGen
  */
  public function text($value) {
    $close="";
    if ($this->isOpen()) {
      $close=">";
    }
    $this->content.=$close.$value;
    return $this;
  }

  /**
  * Adds a text attribute
  * Agrega un attributo text
  * @param (string) value
  * @return APJHtmlGen
  */
  public function name($value) {
    $this->content.=' name="'.$value.'"';
    return $this;
  }
  
  /**
  * Adds a type attribute
  * Agrega un attributo type
  * @param (string) value
  * @return APJHtmlGen
  */
  public function type($value) {
    $this->content.=' type="'.$value.'"';
    return $this;
  }
  
  /**
  * Adds a onclick event
  * Agrega un evento onclick
  * @param (string) value
  * @return APJHtmlGen
  */
  public function onclick($value) {
    $this->content.=' onclick="'.$value.'"';
    return $this;
  }
  
  /**
  * Adds a onchange event
  * Agrega un evento onchange
  * @param (string) value
  * @return APJHtmlGen
  */
  public function onchange($value) {
    $this->content.=' onchange="'.$value.'"';
    return $this;
  }
  
  /**
  * Adds a literal
  * Agrega un literal
  * @param (string) literal
  * @return APJHtmlGen
  */
  public function literal($value) {
    $this->content.=$value;
    return $this;
  }
  
  /**
  * Close with ><br>
  * Cierra con >
  * @return APJHtmlGen
  */
  public function preClose() {
    $this->content.=">";
    return $this;
  }
  
  /**
  * Close last open Tag<br>
  * Cierra la última Etiqueta
  * @return APJHtmlGen
  */
  public function close() {
    $this->closeSingle();
    return $this;
  }
  
  /**
  * Close all open Tags<br>
  * Cierra todas las etiquetas abiertas
  */
  public function closeAll() {
    while (count($this->closed)<count($this->tags)) {
      $this->closeSingle();
    }
  }

  /**
  * Returns the final content with the option to close the open tags
  * Retorna el contenido final con la opción de cerrar las etiquetas abiertas
  * @param (boolean) closeAll (default true)
  * @return (string) Html result
  */
  public function end($closeAll=true) {
    if ($closeAll) {
      $this->closeAll();
    }
    return $this->getContent();
  }
  
  public function getContent() {
    return $this->content;  
  }
  
  private function closeLast() {
    if (strlen($this->content)>0 and substr($this->content,-1)!='>' and substr($this->content,-1)!=';') {
      $this->content.=">";
    }
  }
  
  private function closeSingle() {
    $tags = array_reverse($this->tags,true);
    foreach ($tags as $key=>$tag) {
      if (!array_key_exists($key,$this->closed)) {
        $this->closeTag($key);
        break;
      }
    }
  }
  
  private function closeTag($key) {
    $tag=$this->tags[$key];
    if ($this->inSpecial($tag)) {
      $this->content.="</".$tag.">";
    } elseif($this->isOpen()) {
      $this->content.=">";
    }
    $this->closed[$key]=$tag;
  }
  
  private function inSpecial($tag) {
    return in_array($tag,$this->specialClose);
  }
  
  private function isOpen() {
    return (substr($this->content,-1)!=">");
  }
  
  private function initialize() {
    $this->content = NULL;
    $this->tags = array();
    $this->closed = array();
  }
  
  private function setSpecialClose() {
    $this->specialClose = array('a','abr','address','article','aside','audio','b','bdi','blockquote','body','button','canvas','caption','cite','code','colgroup','datalist','dd','del','detail','dfn','dialog','div','dl','dt','em','embed','fieldset','figcaption','figure','footer','form','h1','h2','h3'.'h4','h5','h6','head','html','i','iframe','ins','kbd','label','legend','li','main','map','mark','menu','menuitem','meter','nav','noscript','object','ol','optgroup','option','output','p','picture','pre'.'progress','q','rp','rt','ruby','s','samp','script','section','select','small','span','strong','style','sub','summary','sup','table','tbody','td','textarea','tfoot','th','thead','time','title','tr','u','ul','var','video');
  }
}
