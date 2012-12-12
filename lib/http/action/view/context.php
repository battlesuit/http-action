<?php
namespace http\action\view;

/**
 * Evaluable contexts for capturing code snippets and creating namespaced functions
 * Every context stands for a specific namespace
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Battlesuit
 * @subpackage http-action
 */
abstract class Context {
  
  /**
   * Used namespace definition
   *
   * @access public
   * @var string
   */
  protected $namespace = __NAMESPACE__;
  
  /**
   * Every evaluation stores a context instance inside this array so that
   * dynamically created functions can access the scope
   *
   * @static
   * @access private
   * @var array
   */
  private static $class_scopes = array();
  
  /**
   * Lists all created functions for this namespace
   *
   * @access private
   * @var array
   */
  private $created_functions = array();
  
  /**
   * capture()'s block callable
   * Call this block by instance invocation (__invoke)
   * 
   * @access private
   * @var callable
   */
  private $captured_block;
  
  /**
   * Constructs the context instance
   * By default it uses the original namespace but feel free to use your own
   *
   * @access public
   * @param string $namespace
   */
  function __construct($namespace = __NAMESPACE__) {
    $this->namespace = $namespace;
  }
  
  /**
   * Calls to missing functions gets delegated to user defined callbacks
   * under the created_functions array
   * If nothing hits the spot an error is thrown
   *
   * @access public
   * @param string $method
   * @param array $arguments
   */
  function __call($method, array $arguments) {
    if(isset($this->created_functions[$method])) {
      return call_user_func_array($this->created_functions[$method], $arguments);
    }
    
    $class = get_class($this);
    throw new Error("Method $method does not exist for $class");
  }
  
  /**
   * Calls the captured block callable if there exists one
   * Throws no error if the callable does not exist
   *
   * @access public
   * @return mixed
   */
  function __invoke() {
    if(!isset($this->captured_block)) return;
    return call_user_func($this->captured_block, $this);
  }
  
  /**
   * Creates a function within the instance scope
   * Every function gets evaluated by eval_functions() and are present and
   * accessible within the current namespace
   *
   * Possible usage:
   *
   *  $context->create_function('say_hello_world', function() {
   *    return 'hello world';
   *  });
   *
   * $context->create_function('say_hello_world', 'return "hello world"');
   * $context->create_function('say_hello_world', array($object, 'my_hello_world_method'));
   * 
   * @access public
   * @param string $name
   * @param mixed $callback
   * @param string $params
   */
  function create_function($name, $callback = null, $params = '') {
    if(!is_callable($callback) and is_string($callback)) {
      if($callback[strlen($callback)-1] !== ';') $callback .= ';';
      $callback = create_function($params, $callback);
    } elseif(is_null($callback)) {
      $callback = array($this, $name);
    }
    
    $this->created_functions[$name] = $callback;
  }
  
  /**
   * Captures a code snippet evaled by this instance with optionally passed
   * variables or a callable block
   * Passed variables become available in the evaled scope
   * This method is the main entry point for renderers
   * 
   * @access public
   * @param string $code
   * @param array $variables (block can be passed here as well)
   * @param callable $block
   * @return string
   */
  function capture($code, $variables = array(), $block = null) {
    if(is_callable($variables)) {
      $block = $variables;
      $variables = array();
    }
    
    if(is_callable($block)) $this->captured_block = $block;
    
    ob_start();
    $this->evaluate($code, $variables);
    return ob_get_clean();
  }
  
  /**
   * Evaluation worker function
   * Sets the scope instance, defines all functions within the namespace and
   * evals the code
   *
   * @access public
   * @param string $code
   * @param array $variables
   */
  function evaluate($code, array $variables = array()) {
    self::$class_scopes[get_called_class()] = $this;
    $this->define_functions();

    extract($variables);
    if($code[strlen($code)-1] !== ';') $code .= ';';
    eval("namespace $this->namespace; $code");
  }
  
  /**
   * Defines all created_functions within the namespace
   * Getting called by evaluate() method
   *
   * @access protected
   */
  protected function define_functions() {
    foreach($this->created_functions as $name => $content) $this->define_function($name, $content);
  }
  
  /**
   * Defines a single method inside the current namespace
   *
   * @access public
   * @param string $name
   * @param callable $callback
   */
  function define_function($name, $callback) {
    if(function_exists("$this->namespace\\$name")) return;
    if(!is_callable($callback)) throw new Error("Invalid callable given for function $name");
    
    if(is_array($callback)) $r = new \ReflectionMethod($callback[0], $callback[1]);
    else $r = new \ReflectionFunction($callback);
    
    $params = $comma = '';
    foreach($r->getParameters() as $param) {
      $param_value = "\$".$param->name;
      if($param->isDefaultValueAvailable()) {
        $default_value = $param->getDefaultValue();
        if(is_null($default_value)) $default_value = 'null';
        elseif(is_string($default_value)) {
          if(empty($default_value)) $default_value = "''";
          else $default_value = "'$default_value'";
        }
        elseif(is_array($default_value)) $default_value = 'array()';
        elseif(is_bool($default_value)) {
          if($default_value) $default_value = "true";
          else $default_value = "false";
        }
        
        $param_value .= " = ".$default_value;
      }
      
      $params .= $comma.$param_value;
      $comma = ',';
    }
    
    $caller_class = get_class($this);

    $code = "namespace $this->namespace; function $name($params) { return call_user_func_array(array(\\$caller_class::access_scope(), '$name'), func_get_args()); }";
    eval($code);
  }
  
  /**
   * Entry point for all defined function callbacks
   *
   * @static
   * @access public
   * @return Context
   */
  static function access_scope() {
    $class = get_called_class();
    if(isset(self::$class_scopes[$class])) return self::$class_scopes[$class];
  }
}
?>