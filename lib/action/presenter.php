<?php
namespace action;
use display\Template;

class Presenter extends Controller {
  public $layout;
  public $views_dir;
  public $layouts_dir;
  private $view;
  public $rendered_output;
  
  function view() {
    if(isset($this->view)) return $this->view;
    $view = new View($this->views_dir(), $this);
    $view->layouts_dir = $this->layouts_dir();
    return $this->view = $view;
  }
  
  function render($options, array $assignments = array()) {
    if(is_string($options)) $options = array('template' => $options);
    if(!empty($this->layout)) $options['layout'] = $this->layout;
    
    $output = $this->rendered_output = $this->view()->render($options, array_merge($this->assignments, $assignments));
    $this->response->body($output);
    return $this->response;
  }
  
  function views_dir() {
    return $this->views_dir;
  }
  
  function layouts_dir() {
    return $this->layouts_dir;
  }
  
  function templates_path() {
    
  }
}
?>