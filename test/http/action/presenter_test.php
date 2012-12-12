<?php
namespace http\action;
use http\Request;

class PresenterTest extends TestUnit {
  function boot_up() {
    require_once $this->bench_dir()."/presenters/application_presenter.php";
    require_once $this->bench_dir()."/presenters.php";
  }
  
  function set_up() {
    $this->presenter = new \UsersPresenter();
  }
  
  function test_index_default_render() {
    $response = \ApplicationPresenter::handle_transaction(new Request('http://localhost?_action=index'));
    $this->assert_eq("$response", "index action template");
  }
  
  function test_name() {
    $this->assert_eq($this->presenter->name(), 'users');
  }
  
  function test_assign() {
    $return = $this->presenter->assign('page', 'contact');
    $this->assert_eq($return, 'contact');
  }
  
  function test_assign_via_array() {
    $return = $this->presenter['page'] = 'contact';
    $this->assert_eq($return, 'contact');
  }
  
  function test_obtain() {
    $this->presenter->assign('page', 'contact');
    $this->assert_eq($this->presenter->obtain('page'), 'contact');
  }
  
  function test_obtain_via_array() {
    $this->presenter['page'] = 'contact';
    $this->assert_eq($this->presenter['page'], 'contact');
  }
}
?>