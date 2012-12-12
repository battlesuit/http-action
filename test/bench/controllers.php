<?php
namespace {
  use http\action\controller\Base as ActionController;
  
  class UnderscoredControllerName extends ActionController {
    
  }
  
  class ControllerWithSuffixController extends ActionController {
    
  }
  
  class VeryLongControllerNameWithSuffixController extends ActionController {
    
  }
  
  class BeforeAndAfterActionController extends ActionController {
    function action_callbacks($callbacks) {
      $callbacks->before_action('before_action');
      $callbacks->after_action('after_action');
    }
    
    function before_action() {
      $this->response->write('before');
    }
    
    function trigger_action() {
      $this->response->write('trigger');
    }
    
    function after_action() {
      $this->response->write('after');
    }
  }
  
  class BeforeAndAfterActionViaDelegation extends ActionController {
    function action_callbacks($callbacks) {
      $callbacks->before_action('custom_before_action');
      $callbacks->after_action('custom_after_action');
    }
    
    function custom_before_action() {
      $this->response->write('before');
    }
    
    function trigger_action() {
      $this->response->write('trigger');
    }
    
    function custom_after_action() {
      $this->response->write('after');
    }
  }
  
  class BeforeAndAfterActionViaDelegationExcept extends ActionController {
    function action_callbacks($callbacks) {
      $callbacks->before_action('custom_before_action', array('trigger'));
      $callbacks->after_action('custom_after_action', array('trigger', 'activate'));
    }
    
    function custom_before_action() {
      $this->response->write('before');
    }
    
    function trigger_action() {
      $this->response->write('trigger');
    }
    
    function activate() {
      $this->response->write('activate');
    }
    
    function custom_after_action() {
      $this->response->write('after');
    }
  }
  
  class RedirectionsController extends ActionController {
    function index() {
      
    }
    
    function redirect_to_index() {
      $this->redirect_to('index');
    }
    
    function redirect_to_add() {
      $this->redirect_to('add');
    }
    
    function redirect_to_url() {
      $this->redirect_to('http://google.de');
    }
  }
  
  class RespondingController extends ActionController {
    function index() {
      return $this->respond_to('xml', function() {
        return new http\Response(200, "<data></data>", array('content_type' => 'text/xml'));
      });
    }
    
    function respond_many() {
      return $this->respond_to(array(
        'xml' => function() {
          return new http\Response(200, "<nodes></nodes>", array('content_type' => 'text/xml'));
        },
        'js' => function() {
          return new http\Response(200, "function alertSomehting() { alert('something'); }", array('content_type' => 'text/javascript'));
        }
      ));
    }
  }
}
?>