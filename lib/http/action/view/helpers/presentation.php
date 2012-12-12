<?php
namespace http\action\view\helpers;
use http\action\view\Error;

class Presentation {
  protected $context;
  
  function initialize_context($context) {
    if(!isset($context->presenter)) {
      throw new Error("Contexts 'presenter' required for presentation helpers");
    }
    
    if(!$context->helper_exists('markup')) {
      throw new Error("Helper 'markup' required for presentation helpers");
    }
    
    $this->context = $context;
  }
  
  /**
   * Generates an a-tag
   *
   * @param mixed $to
   * @param mixed $options
   * @param array $attributes
   * @return string
   */
  function link_to($to, $options = array(), $attributes = array()) {
    if(is_string($options)) {
      $content = $options;
      $options = array();
    } else $path = null;
    
    if(is_string($to)) {
      $path = $to;
      $to = null;
    } else $content = null;
    
    extract($options);
    
    $url = $this->context->presenter->url_for($to);
    if(!isset($content)) {
      if(strpos($path, '/') !== false) $content = ucfirst(substr(strrchr($path, '/'), 1));
      else $content = ucfirst($path);
    }
    
    if(!empty($path)) $path = "/".ltrim($path, '/');
    return $this->context->link_tag($url.$path, $content, $attributes);
  }
}
?>