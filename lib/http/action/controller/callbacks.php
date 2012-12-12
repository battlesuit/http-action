<?php
namespace http\action\controller;

/**
 * Action callback container used in http\action\Controller
 * At this moment it supports before and after action callbacks
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Battlesuit
 * @subpackage http-action
 */
class Callbacks {
  
  /**
   * Stack of callbacks
   *
   * @access private
   * @var array
   */
  private $stack = array(
    'before' => array(),
    'after' => array()
  );
  
  /**
   * Adds a after action listener
   *
   * @access public
   * @param mixed $callback
   * @param mixed $exceptions
   */
  function after_action($callback, $exceptions = array()) {
    $this->add('after', $callback, $exceptions);
  }
  
  /**
   * Adds a before action listener
   *
   * @access public
   * @param mixed $callback
   * @param mixed $exceptions
   */  
  function before_action($callback, $exceptions = array()) {
    $this->add('before', $callback, $exceptions);
  }
  
  /**
   * Adds a action listener
   *
   * @access public
   * @param string $when
   * @param mixed $callback
   * @param mixed $exceptions
   */
  function add($when, $callback, $exceptions = array()) {
    $exceptions = (array)$exceptions;
    $this->stack[$when][] = compact('callback', 'exceptions');
  }
  
  /**
   * Calls a action listener
   *
   * @access public
   * @param string $when
   * @param string $action
   * @param object $target
   */
  function call($when, $action, $target) {
    $stack = $this->stack[$when];
    
    foreach($stack as $child) {
      extract($child);
      
      if(array_search($action, $exceptions) !== false) continue;
      
      if(is_string($callback) and method_exists($target, $callback)) call_user_func(array($target, $callback));
      elseif(is_callable($callback)) call_user_func($callback, $target);
    }
  }
}
?>