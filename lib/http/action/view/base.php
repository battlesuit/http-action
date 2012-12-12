<?php
namespace http\action\view;

/**
 * View base context used in presenter
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Battlesuit
 * @subpackage http-action
 */
class Base extends Context {
  private $helpers = array();
  private $helpers_applied = false;
  private $instance_variables = array();
  private $assignments = array();
  
  /**
   * Constructs a View instance
   *
   * @access public
   * @param string $namespace
   * @param array $assignments
   * @param array $helpers
   */
  function __construct($namespace = __NAMESPACE__, array $assignments = array(), $helpers = array()) {
    parent::__construct($namespace);
    
    $this->assignments = $assignments;
    call_user_func_array(array($this, 'helpers'), (array)$helpers);
  }
  
  function __set($name, $value) {
    return $this->instance_variables[$name] = $value;
  }
  
  function __get($name) {
    if(isset($this->instance_variables[$name])) return $this->instance_variables[$name];
    else throw new Error("Property '$name' does not exist");
  }
  
  function __isset($name) {
    return isset($this->instance_variables[$name]);
  }
  
  function add_helper($helper) {
    if(is_object($helper)) {
      $class = get_class($helper);
    } else $class = $helper;
    
    $name = strtolower(preg_replace('/(\p{Ll})(\p{Lu})/', '$1_$2', basename($class)));
    return $this->helpers[$name] = $helper;
  }
  
  function helper_exists($name) {
    return array_key_exists($name, $this->helpers);
  }

  function helpers() {
    if(func_num_args() > 0) {
      foreach(func_get_args() as $helper) $this->add_helper($helper);
    }
    
    return $this->helpers;
  }
  
  protected function create_helpers() {
    if($this->helpers_applied) return;
    
    foreach($this->helpers as $helper) {
      if(is_string($helper) and class_exists($helper, true)) $helper = new $helper();
      elseif(!is_object($helper)) throw new Error("Helper class $helper does not exist");
      
      $methods = get_class_methods($helper);
      foreach($methods as $method) {
        if(strpos($method, '_') === 0) continue;
        elseif($method == 'initialize_context') {
          call_user_func(array($helper, $method), $this);
          continue;
        }
        
        $this->create_function($method, array($helper, $method));
      }
    }
    
    $this->helpers_applied = true;
  }
  
  /**
   * !override
   * Creates default functions, helpers and merges in all assignments
   *
   * @access public
   * @param string $code
   * @param array $variables
   */
  function evaluate($code, array $variables = array()) {
    $this->create_helpers();
    $this->create_function('context', array($this, 'access_scope'));
    $this->create_function('assign');
    $this->create_function('obtain');
    $this->create_function('append');
    $this->create_function('assignment_exists');
    $this->create_function('show');
    
    parent::evaluate($code, array_merge($this->assignments, $variables));
  }
  
  function capture_for($name, $code, $variables = array(), $block = null) {
    $this->append($name, $this->capture($code, $variables, $block));
  }
  
  /**
   * Directly shows(echos) a obtained value
   *
   * @param string $name
   */
  function show($name) {
    echo $this->obtain($name);
  }
  
  /**
   * Writes an assignment and returns the value
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
   * Reads an assignment
   * This method only returns a value if the assignment exists.
   * So no error is thrown in accessing a non existant index
   *
   * @access public
   * @param string $name
   * @return mixed
   */
  function obtain($name) {
    return isset($this->assignments[$name]) ? $this->assignments[$name] : null;
  }
  
  /**
   * Removes an assignment
   *
   * @access public
   * @param string $name
   */
  function remove($name) {
    unset($this->assignments[$name]);
  }
  
  /**
   * Appends a string or array to a existing template variable
   * A non existing variable will be created
   *
   * @access public
   * @param string $name
   * @param mixed $value
   * @return mixed
   */
  function append($name, $value) {
    if(!isset($this->assignments[$name])) return $this->assignments[$name] = $value;
    
    if(is_string($value)) {
      return $this->assignments[$name] .= $value;
    } elseif(is_array($value)) {
      return $this->assignments[$name] = array_merge($this->assignments[$name], $value);
    } 
  }
  
  /**
   * Tests if a template variable exists
   *
   * @access public
   * @param string $name
   * @return boolean
   */
  function assignment_exists($name) {
    return array_key_exists($name, $this->assignments);
  }
}
?>