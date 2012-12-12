<?php
namespace http\action\view\helpers;
use http\action\view\Error;

class Presentation {
  protected $context;
  protected $included_stylesheets = array();
  
  function initialize_context($context) {
    if(!isset($context->presenter)) {
      throw new Error("Contexts 'presenter' required for presentation helpers");
    }
    
    if(!$context->helper_exists('markup')) {
      throw new Error("Helper 'markup' required for presentation helpers");
    }
    
    $this->context = $context;
  }
  
  function presenter() {
    return $this->context->presenter;
  }
  
  /**
   * Returns the current request instance
   * 
   * @return http\Request
   */
  function request() {
    return $this->presenter()->request();
  }
  
  function base_url() {
    return $this->request()->base_url();
  }
  
  /**
   * Returns the current script url which is always a file-url
   * e.g. http://domain.de/to/my/script.php
   *
   * @return string
   */
  function script_url() {
    return $this->request()->script_url();
  }
  
  /**
   * Generates a base-tag with the current requests base_url
   *
   * @param string $path
   * @return string
   */
  function base_tag($path = null) {
    if(!empty($path)) $path = trim($path, '/')."/";
    return $this->context->tag('base', array('href' => $this->base_url()."/$path"))."\n";
  }
  
  /**
   * Generates a url to a target
   *
   * @param mixed $to
   * @param mixed $options
   * @return string
   */
  function url_for($to, $options = array()) {
    return $this->presenter()->url_for($to, $options);
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
    
    $url = $this->presenter()->url_for($to);
    if(!isset($content)) {
      if(strpos($path, '/') !== false) $content = ucfirst(substr(strrchr($path, '/'), 1));
      else $content = ucfirst($path);
    }
    
    if(!empty($path)) $path = "/".ltrim($path, '/');
    return $this->context->link_tag($url.$path, $content, $attributes);
  }
  
  /**
   * Generate one or more style-tags
   *
   * @return string link-tag(s)
   */
  function stylesheets() {
    $sheets = func_get_args();
    foreach($this->included_stylesheets as $name) $sheets[] = $name;
    return $this->context->stylesheet_links($sheets, $this->base_url()."/stylesheets");
  }
  
  function include_stylesheets() {
    foreach(func_get_args() as $name) $this->included_stylesheets[] = $name;
  }

  /**
   * Generate one or more script-tags
   *
   * @return string script-tag(s)
   */
  function javascripts() {
    return $this->context->javascript_tags(func_get_args(), $this->base_url()."/javascripts");
  }
}
?>