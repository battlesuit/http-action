<?php
namespace http\action;
use http\action\view\Renderer;
use http\action\view\Base as View;

/**
 * Request presentation handler
 * Provides the render() method
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Battlesuit
 * @subpackage http-action
 */
class Presenter extends Controller implements \ArrayAccess {
  
  /**
   * Layout name
   *
   * @access public
   * @var string
   */
  public $layout;
  
  /**
   * Base views directory
   *
   * @access public
   * @var string
   */
  public $template_lookup_dirs = array();
  
  /**
   * After every render call this property gets filled with the output string
   *
   * @access public
   * @var string
   */
  public $rendered_output;
  
  /**
   * Default rendering namespace for all view contexts
   *
   * @access public
   * @var string
   */
  public $rendering_namespace = 'http\action\view';

  /**
   * Defined view helper classes which are passed to every view context
   *
   * @access public
   * @var array
   */
  public $helpers = array(
    'http\action\view\helpers\Markup',
    'http\action\view\helpers\Rendering',
    'http\action\view\helpers\Presentation'
  );
  
  /**
   * Renders automatically by 'template' => [action], 'path' => [path]
   *
   * @access public
   * @var boolean
   */
  public $auto_render = true;
 
  /**
   * Override controllers class_suffix
   *
   * @access protected
   * @var string
   */
  protected $class_suffix = 'Presenter';
  
  /**
   * View already rendered?
   *
   * @access protected
   * @var boolean
   */
  private $rendered = false;
  
  /**
   * All assignments passed to every view instance
   *
   * @access private
   * @var array
   */
  private $assignments = array();
  
  /**
   * Builds the View instance
   *
   * @access protected
   * @return View
   */
  protected function view_context() {
    $context = new View($this->rendering_namespace, $this->assignments, $this->helpers);
    $context->renderer = $this->render_engine();
    $context->presenter = $this;
    return $context;
  }
  
  /**
   * Returns the rendering engine
   *
   * @access protected
   * @return Renderer
   */
  protected function render_engine() {
    return new Renderer($this->lookup_templates_under());
  }
  
  /**
   * Implements auto rendering functionality to action processing
   *
   * @access protected
   * @param string $action
   * @return mixed
   */
  protected function process_action($action) {
    ob_start();
    $returned_result = parent::process_action($action);
    $captured_result = ob_get_clean();
    
    if($this->auto_render and !$this->rendered and empty($returned_result) and empty($captured_result)) {
      return $this->render();
    }
    
    if(!empty($captured_result)) {
      $returned_result = $captured_result;
    }
    
    return $returned_result;
  }
  
  /**
   * Renders a view and returns and fills the response body
   *
   * @access public
   * @param mixed $options
   * @param array $assignments
   * @return Response
   */
  function render($options = null, array $assignments = array()) {
    if($this->rendered) {
      throw new Error("Double render occured. View already rendered");
    }

    $context = $this->view_context();
    $options = $this->prepare_render_options($options);
  
    $output = $context->renderer->render($context, $options);
    $this->rendered = true;
    return $this->rendered_output = $output;
  }
  
  /**
   * Prepares the render options passed to render() method
   *
   * @access protected
   * @param mixed $options
   * @return array
   */
  protected function prepare_render_options($options) {
    if(is_string($options)) {
      $options = (strpos($options, '/') !== false) ? array('file' => $options) : array('template' => $options);
    } elseif(is_null($options)) $options = array();
    
    if(!array_key_exists('template', $options)) {
      $options['template'] = $this->action;
    }
    
    if(!array_key_exists('layout', $options) and !empty($this->layout)) {
      $options['layout'] = $this->layout;
    }
    
    if(!array_key_exists('path', $options)) {
      $options['path'] = $this->path();
    }
    
    return $options;
  } 
  
  /**
   * Returns the views directory
   * Used for building the view in build_view()
   *
   * @access public
   * @return string
   */
  function lookup_templates_under() {
    return $this->template_lookup_dirs;
  }
  
  /**
   * Reads the render status
   *
   * @access public
   * @return boolean
   */
  function rendered() {
    return $this->rendered;
  }
  
  /**
   * Assigns a variable
   *
   * @access public
   * @param string $name
   * @param mixed $value
   * @return mixed
   */
  function assign($name, $value) {
    return $this->assignments[$name] = $value;
  }
  
  /**
   * Obtains a assigned variable
   *
   * @access public
   * @param string $name
   * @return mixed 
   */
  function obtain($name) {
    return $this->assignments[$name];
  }
  
  /**
   * ArrayAccess::offsetSet() implementation
   *
   * @access public
   * @param string $name
   * @param mixed $value
   */
  function offsetSet($name, $value) {
    return $this->assign($name, $value);
  }

  /**
   * ArrayAccess::offsetUnset() implementation
   *
   * @access public
   * @param string $name
   */
  function offsetUnset($name) {
    unset($this->assignments[$name]);
  }

  /**
   * ArrayAccess::offsetGet() implementation
   *
   * @access public
   * @param string $name
   * @return mixed
   */
  function offsetGet($name) {
    return $this->obtain($name);
  }

  /**
   * ArrayAccess::offsetExists() implementation
   *
   * @access public
   * @param string $name
   * @return boolean
   */
  function offsetExists($name) {
    return array_key_exists($name, $this->assignments);
  }
}
?>