<?php
namespace http\action\controller;
use http\Request;

class BaseTest extends \http\action\TestUnit {
  function boot_up() {
    require_once $this->bench_dir()."/controllers/application_controller.php";
    require_once $this->bench_dir()."/controllers/admin/accounts_controller.php";
    require_once $this->bench_dir()."/controllers/users.php";
    require_once $this->bench_dir()."/controllers.php";
  }
  
  function set_up() {
    $this->controller = new \controllers\Users();
  }
  
  function test_name() {
    $this->assert_eq($this->controller->name(), 'users');
    
    $contr = new \UnderscoredControllerName();
    $this->assert_eq($contr->name(), 'underscored_controller_name');
  }
  
  function test_path() {
    $path = $this->controller->path();
    $this->assert_eq($path, 'controllers/users');
    
    $c = new \controllers\admin\AccountsController();
    $this->assert_eq($c->path(), 'controllers/admin/accounts');
  }
  
  function test_to_string() {
    $this->assert_eq("$this->controller", 'users');
  }
  
  function test_name_suffixed() {
    $contr = new \ControllerWithSuffixController();
    $this->assert_eq($contr->name(), 'controller_with_suffix');
    
    $contr = new \VeryLongControllerNameWithSuffixController();
    $this->assert_eq($contr->name(), 'very_long_controller_name_with_suffix');
  }
  
  function test_before_and_after_by_method() {
    $response = \BeforeAndAfterActionController::handle_transaction(new Request('http://domain.de?_action=trigger_action'));
    $this->assert_eq("$response", 'beforetriggerafter');
  }
  
  function test_action_method_suffix() {
    $response = \BeforeAndAfterActionController::handle_transaction(new Request('http://domain.de?_action=trigger'));
    $this->assert_eq("$response", 'beforetriggerafter');
  }
  
  function test_before_and_after_action_via_delegation() {
    $response = \BeforeAndAfterActionViaDelegation::handle_transaction(new Request('http://domain.de?_action=trigger'));
    $this->assert_eq("$response", 'beforetriggerafter');
  }
  
  function test_before_and_after_action_via_delegation_except() {
    $response = \BeforeAndAfterActionViaDelegationExcept::handle_transaction(new Request('http://domain.de?_action=trigger'));
    $this->assert_eq("$response", 'trigger');
    
    $response = \BeforeAndAfterActionViaDelegationExcept::handle_transaction(new Request('http://domain.de?_action=activate'));
    $this->assert_eq("$response", 'beforeactivate');
  }
  
  function test_no_action_given() {
    try {
      $this->controller->process_transaction(new Request());
    } catch(\ErrorException $e) {
      return;
    }
    
    $this->fail_assertion('ErrorException expected');
  }
  
  function test_default_content_type() {
    $response = $this->controller->process_transaction(new Request('http://domain.de?_action=trigger'));
    $this->assert_eq($response['content_type'], 'text/html; charset=utf-8');
  }
  
  function test_request_reader() {
    $this->controller->process_transaction(new Request('http://domain.de?_action=trigger'));
    $this->assert_instanceof($this->controller->request(), 'http\Request');
  }
  
  function test_redirection() {
    $controller = new \RedirectionsController();
    $response = $controller->process_transaction(new Request('http://domain.de?_action=redirect_to_index'));
    $this->assert_eq($response['location'], 'http://domain.de/redirections');
    
    $response = $controller->process_transaction(new Request('http://domain.de?_action=redirect_to_add'));
    $this->assert_eq($response['location'], 'http://domain.de/redirections/add');
    
    $response = $controller->process_transaction(new Request('http://domain.de?_action=redirect_to_url'));
    $this->assert_eq($response['location'], 'http://google.de');
  }
  
  function test_accept_format() {
    $controller = new \RespondingController();
    $controller->process_transaction(new Request('http://domain.de?_action=index'));
    $this->assert_false($controller->accept_format('xml'));
    
    $controller->process_transaction(new Request('http://domain.de?_action=index', 'get', array(), array('accept' => 'text/xml')));
    $this->assert_true($controller->accept_format('xml'));
  }
  
  function test_respond_to() {
    $controller = new \RespondingController();
    $response = $controller->process_transaction(new Request('http://domain.de?_action=index'));
    $this->assert_empty($response->flat_body());
    
    $response = $controller->process_transaction(new Request('http://domain.de?_action=index', 'get', array(), array('accept' => 'text/xml')));
    $this->assert_eq($response->flat_body(), "<data></data>");
  }

  function test_respond_to_many() {
    $controller = new \RespondingController();
    $response = $controller->process_transaction(new Request('http://domain.de?_action=respond_many'));
    $this->assert_empty($response->flat_body());
    
    $response = $controller->process_transaction(new Request('http://domain.de?_action=respond_many', 'get', array(), array('accept' => 'text/xml')));
    $this->assert_eq($response->flat_body(), '<nodes></nodes>');
    $this->assert_eq($response['content_type'], 'text/xml');
    
    $response = $controller->process_transaction(new Request('http://domain.de?_action=respond_many', 'get', array(), array('accept' => 'text/javascript')));
    $this->assert_eq($response->flat_body(), "function alertSomehting() { alert('something'); }");
  }
  
  function test_respond_via_format() {
    $controller = new \RespondingController();
    $response = $controller->process_transaction(new Request('http://domain.de?_action=respond_many&_format=js'));
    $this->assert_eq($response->flat_body(), "function alertSomehting() { alert('something'); }");
  }
}
?>