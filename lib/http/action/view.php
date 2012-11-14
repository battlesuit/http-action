<?php
namespace http\action;
use display\Template;

class View {
  static $template_extension;
  private static $render_instance;
  
  public $base_dir;
  public $layouts_dir;
  private $template_content;
  private $assignments = array();
  private $controller;
  
  function __construct($base_dir = null, $controller = null) {
    $this->base_dir = $base_dir;
    $this->controller = $controller;
    
    if(isset($controller)) {
      $this->assignments = $controller->assignments();
    }
  }
  
  static function render_instance() {
    return self::$render_instance;
  }
  
  function controller() {
    return $this->controller;
  }
  
  function assign($name, $value) {
    return $this->assignments[$name] = $value;
  }
  
  function append($name, $value) {
    if(!isset($this->assignments[$name])) return $this->assignments[$name] = $value;
    else return $this->assignments[$name] .= $value;
  }
  
  function template_content() {
    return $this->template_content;
  }
  
  function obtain($name) {
    return isset($this->assignments[$name]) ? $this->assignments[$name] : null;
  }
  
  function render($options, array $assignments = array()) {
    self::$render_instance = $this;
    include_once __DIR__."/../../display/functions.php";
    
    if(is_string($options)) $template = $options;
    elseif(is_array($options)) extract($options);
    
    $layouts_dir = empty($this->layouts_dir) ? $this->base_dir."/layouts" : $this->layouts_dir;
    $base_dir = $this->base_dir;
    $output = null;
    $rooted = false;
    
    if(!empty($partial)) {
      $rooted = $partial[0] == '/';
      $partial_with_leading_underscore = $partial;
      if(strpos($partial, '/') !== false) {
        
        $partial_with_leading_underscore = preg_replace('#(\w+)$#', "_$1", $partial);
      } else $partial_with_leading_underscore = $rooted ? "/_$partial" : "_$partial";
      
      $template = $partial_with_leading_underscore;
      
      $layout = null;
    }
    
    ob_start();
    try {
      if(!empty($template)) {
        if(isset($this->controller) and $template[0] !== '/') {
          $base_dir .= $this->controller->templates_path();
        }
        
        if($template[0] !== '/') {
          $template = "/$template";
        }
        
        $template_obj = new Template($this->extend_templatefile($base_dir.$template));
        
        $output = $template_obj->render(array_merge($this->assignments, $assignments));
        if(empty($partial)) $this->template_content = $output;
        
        if(!empty($layout)) {
          $layout_file = $this->extend_templatefile($layout);
          if(!empty($layouts_dir)) $layout_file = $layouts_dir."/".$layout_file;
          $layout_template = new Template($layout_file);
          
          $output = $layout_template->render(array_merge($this->assignments, $assignments));
        }
      }
    } catch(\Exception $e) {
      ob_end_clean();
      throw $e;
    }
    ob_end_clean();
    
    
    return $output;
  }
  
  private function extend_templatefile($file) {
    if(strpos(basename($file), '.') === false) {
      if(isset(static::$template_extension)) {
        $ext = static::$template_extension;
      } else $ext = 'php';
      $file = $file.".$ext";
    }
    
    return $file;
  }
}
?>