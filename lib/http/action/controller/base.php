<?php
namespace http\action\controller;
use http\Request;
use http\Response;
use http\transaction\Application as Transaction;

/**
 * Main handler for request actions
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Battlesuit
 * @subpackage http-action
 */
abstract class Base {
  
  /**
   * Cached controller name created in name()
   *
   * @access private
   * @var string
   */
  private $name;
  
  /**
   * Sent request
   *
   * @access protected
   * @var Request
   */
  protected $request;
  
  /**
   * Response to send
   *
   * @access protected
   * @var Response
   */
  protected $response;
  
  /**
   * Sent action name
   * Comes over _action data param
   *
   * @access protected
   * @var string
   */
  protected $action;
  
  /**
   * Request data
   *
   * @access protected
   * @var array
   */
  protected $data = array();
  
  /**
   * Redirect initialized?
   *
   * @access protected
   * @var boolean
   */
  protected $redirected = false;
  
  /**
   * Requested format (xml|js|html|txt etc.)
   * Comes over _format data param
   *
   * @access protected
   * @var string
   */
  protected $format;
  
  /**
   * The suffix will be removed from the controller name
   *
   * @access protected
   * @var string
   */
  protected $class_suffix = 'Controller';
  
  /**
   * Usable as a transaction processor
   *
   * @access public
   * @param Request $request
   * @return Response
   */
  function __invoke(Request $request) {
    return static::handle_transaction($request);
  }
  
  /**
   * Returns the underscored controller name
   *
   * @static
   * @access public
   * @return string
   */
  function name() {
    if(isset($this->name)) return $this->name;
    return $this->name = $this->generate_name();
  }
  
  /**
   * Generates the controllers name
   *
   * @access public
   * @return string
   */
  protected function generate_name() {
    $name = preg_replace("/$this->class_suffix$/i", '', get_class($this));
    
    if(($last_backslash_pos = strrpos($name, '\\')) !== false) {
      $name = substr($name, $last_backslash_pos+1);
    }

    return strtolower(preg_replace('/(\p{Ll})(\p{Lu})/', '$1_$2', $name));
  }
  
  /**
   * Builds the protected $response property as default
   * Users can override this one to build their own response instance
   * This method must always return a instance inherits the Response class
   *
   * @access protected
   * @return Response
   */
  protected function build_response() {
    return new Response();
  }
  
  /**
   * Constructs a controller instance and calls the process_transaction() method
   *
   * @static
   * @access public
   * @param Request $request
   * @return Response
   */
  static function handle_transaction(Request $request) {
    return Transaction::run(array(new static(), 'process_transaction'), $request)->response();
  }
  
  /**
   * First called method after ::handle_transaction
   *
   * @access public
   * @param Request $request
   * @return Response
   */
  function process_transaction(Request $request) {
    $this->request = $request;
    $this->data = $request->data;
    $this->response = $response = $this->build_response();
    
    if($this->param_exists('action')) {
      $this->action = $this->param('action');
    } else throw new Error("No action given: Please set the request data _action parameter");
    
    if($this->param_exists('format')) {
      $this->format = $this->param('format');
    }
    
    # set default content-type
    $response->content_type('text/html');
    
    $response = $this->process_action($this->action);
    if(!$response) return $this->response;
    elseif(is_string($response)) {
      $this->response->body($response);
      $response = $this->response;
    }
    
    return $response;
  }
  
  /**
   * Invokes the action and calls the before and after methods
   *
   * @access protected
   * @param string $action
   * @return mixed
   */
  protected function process_action($action) {
    $callbacks = new Callbacks();
    
    if(method_exists($this, 'action_callbacks')) {
      $this->action_callbacks($callbacks);
    }

    $callbacks->call('before', $action, $this);
    $returned_result = $this->invoke_action_method($action);
    $callbacks->call('after', $action, $this);
    
    return $returned_result;
  }
  
  /**
   * Invokes the action method
   * 
   * @access protected
   * @param string $method
   * @return Response or Null
   */
  protected function invoke_action_method($method) {
    
    # puts _action suffix to method name if method does not exist and tries again 
    if(!method_exists($this, $method)) $method = $method."_action";
    
    # call if method exists
    if(method_exists($this, $method)) return call_user_func(array($this, $method));
  }
  
  /**
   * Returns the current action request
   *
   * @access public
   * @return Request
   */
  function request() {
    return $this->request;
  }
  
  /**
   * Returns a special data param with leading underscores
   *
   * @access public
   * @param string $name
   * @return mixed
   */
  function param($name) {
    return $this->data["_$name"];
  }
  
  /**
   * Does a underscored param exists in the data-array
   *
   * @access public
   * @param string $name
   * @return boolean
   */
  function param_exists($name) {
    return array_key_exists("_$name", $this->data);
  }
  
  /**
   * Returns the current action name
   * 
   * @access public
   * @return string
   */
  function action() {
    return $this->action;
  }
  
  /**
   * Redirects to location or action
   *
   * @access public
   * @param mixed $location
   * @param mixed $options
   */
  function redirect_to($location, $options = array()) {
    $url = $location;

    if(is_object($location)) {
      $url = $this->url_for($location, $options);
    } elseif(strpos($location, '/') === false) {
      $url = $this->request->base_url()."/$this";
      
      if($location !== 'index') $url .= "/$location";
      
    } elseif(strpos($location, '://') === false) {
      $url = $this->request->base_url()."/".trim($location, '/');
    }
    
    $this->response->location($url);
    $this->redirected = true;
  }
  
  /**
   * Call block on specific request format
   * 
   * @access public
   * @param mixed $formats
   * @param callable $block
   * @return boolean|mixed
   */
  function respond_to($formats, $block = null) {
    $callback = $block;
    if(is_array($formats)) {
      foreach($formats as $format => $callback) {       
        if($this->accept_format($format)) goto accepted;
      }
    } elseif(is_string($formats)) {
      if(strpos($formats, '|') !== false) {
        $formats = explode('|', $formats);
        foreach($formats as $format => $callback) {
          $callback = $block;
          if($this->accept_format($format)) goto accepted;
        }
      } else {
        if($this->accept_format($formats)) goto accepted;
      }
    }
    
    return false;
    
    accepted:
    return call_user_func($callback);
  }
  
  /**
   * Accepts a given format e.g. xml, html, js, json..
   *
   * @access public
   * @param string $format
   * @return boolean
   */
  function accept_format($format) {
    if
    (
      (isset($this->format) and $this->format == $format) or
      $this->request->accepts_format($format)
    ) return true;
    
    return false;
  }
  
  /**
   * Returns the current action url
   *
   * @access public
   * @return string
   */
  function url() {
    return $this->request->base_url().$this->path();
  }
  
  /**
   * This path gets appended to the requests base_url()
   * Used to form the full action url
   *
   * @access protected
   * @return string
   */
  function path() {
    if(isset($this->path)) return $this->path;
    
    $path = $this->name();
    if(($dir = dirname(str_replace('\\', '/', get_class($this)))) !== '.') {
      $path = "$dir/$path";
    }
    
    return $this->path = $path;
  }
  
  /**
   * Form url for a specific target
   *
   * @access public
   * @param mixed $to
   * @return string
   */
  function url_for($to) {
    $url = $this->url();
    
    if(is_object($to) and method_exists($to, 'to_path')) {
      $url .= $to->to_path();
    } elseif(is_string($to)) {
      if($to[0] !== '/') $to = "/$to";
      $url .= $to;
    }
    
    return $url;
  }
  
  /**
   * To-string conversion returns the controllers name
   *
   * @access public
   * @return string
   */
  function __toString() {
    return $this->name();
  }
}
?>