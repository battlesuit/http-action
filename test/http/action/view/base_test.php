<?php
namespace http\action\view;

class BaseTest extends \http\action\TestUnit {
  function boot_up() {
    require_once $this->bench_dir()."/test_helpers.php";
  }
  
  function set_up() {
    $this->view = new Base();
    $this->view->helpers('TestHelpers', 'http\action\view\helpers\Markup');
  }
  
  function test_code_helper() {
    $output = $this->view->capture('echo code_tag("bla")');
    $this->assert_eq($output, '<code>bla</code>');
  }
  
  function test_assign() {
    $output = $this->view->capture('assign("foo", "bar")');
    $this->assert_eq($this->view->obtain('foo'), 'bar');
  }
  
  /*function test_render_file() {
    $output = $this->view->render(array('file' => $this->bench_dir()."/views/tests/hello_world.php"));
    $this->assert_eq($output, 'hello world');
  }
  
  function test_render_file_without_extension() {
    $output = $this->view->render(array('file' => $this->bench_dir()."/views/tests/hello_world"));
    $this->assert_eq($output, 'hello world');
  }
  
  function test_render_template() {
    $output = $this->view->render(array('template' => "tests/hello_world.php"));
    $this->assert_eq($output, 'hello world');
  }
  
  function test_render_template_without_extension() {
    $output = $this->view->render(array('template' => "tests/hello_world"));
    $this->assert_eq($output, 'hello world');
  }
  
  function test_quick_render() {
    $output = $this->view->render("tests/hello_world");
    $this->assert_eq($output, 'hello world');
  }
  
  function test_quick_render_without_slashes_and_presenter() {
    $output = $this->view->render("foo");
    $this->assert_eq($output, 'this is a foo');
  }
  
  function test_render_partial() {
    $output = $this->view->render(array('partial' => "tests/nav"));
    $this->assert_eq($output, 'navigation partial');
  }
  
  function test_render_views_partial() {
    $output = $this->view->render(array('partial' => "bar"));
    $this->assert_eq($output, 'bar partial');
  }
  
  function test_assign_and_obtain() {
    $this->view->assign('foo', 'bar');
    $value = $this->view->obtain('foo');
    $this->assert_eq($value, 'bar');
  }
  
  function test_assign_through_template() {
    $output = $this->view->render("tests/assign");
    $this->assert_eq($this->view->obtain('foo'), 'bar');
  }
  
  function test_append_string() {
    $this->view->append('foo', 'my name is');
    $this->view->append('foo', ' thomas');
    $this->assert_eq($this->view->obtain('foo'), 'my name is thomas');
  }
  
  function test_append_array() {
    $this->view->append('foo', array('foo', 'bar'));
    $this->view->append('foo', array('baz'));
    $this->assert_eq($this->view->obtain('foo'), array('foo', 'bar', 'baz'));
  }
  
  function test_append_through_template() {
    $this->view->assign('header', '<link />');
    $output = $this->view->render("tests/append");
    $this->assert_eq($this->view->obtain('header'), '<link /><style></style>');
  }
  
  function test_obtain_through_template() {
    $this->view->assign('foo', 'obtained value');
    $output = $this->view->render("tests/obtain");
    $this->assert_eq($output, 'obtained value');
  }
  
  function test_show_through_template() {
    $this->view->assign('foo', 'displayed directly');
    $output = $this->view->render("tests/show");
    $this->assert_eq($output, 'displayed directly');
  }
  
  function test_render_with_layout() {
    $output = $this->view->render(array('template' => 'tests/hello_world', 'layout' => 'base'));
    $this->assert_eq($output, '<html>hello world</html>');
  }
  
  function test_assignment_rendering() {
    $this->view->assign('hello', 'hello');
    $this->view->assign('world', 'world');
    $output = $this->view->render("tests/assignments");
    $this->assert_eq($output, 'hello world');
  }
  
  function test_set_presenter() {
    $this->view->presenter(new Presenter());
    $this->assert_instanceof($this->view->presenter(), 'http\action\Presenter');
  }*/
}
?>