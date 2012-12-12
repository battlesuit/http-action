<?php
namespace http\action\view;

class Renderer {
  static $types = array(
    'template' => 'http\action\view\renderer\TemplateRenderer',
    'file' => 'http\action\view\renderer\TemplateRenderer'
  );
  
  private $lookup_dirs = array();
  
  function __construct($lookup_dirs = array()) {
    $this->lookup_dirs = (array)$lookup_dirs;
  }
  
  function render($context, array $options) {
    foreach($options as $key => $opt) {
      if(array_key_exists($key, static::$types)) {
        $renderer_class = static::$types[$key];
        $renderer = new $renderer_class($this->lookup_dirs);
        return $renderer->render($context, $options);
      }
    }
  }
}
?>