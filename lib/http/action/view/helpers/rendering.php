<?php
namespace http\action\view\helpers;
use http\action\view\Error;

class Rendering {
  protected $renderer;
  protected $context;
  
  function initialize_context($context) {
    if(!isset($context->renderer)) {
      throw new Error("Contexts 'renderer' required for rendering helpers");
    }
    
    $this->context = $context;
    $this->renderer = $context->renderer;
  }
  
  function render($options) {
    return $this->renderer->render($this->context, $options);
  }
}
?>