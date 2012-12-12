<?php
namespace http\action\view\renderer;
use http\action\TestUnit;
use http\action\view\Base as View;

class TemplateRendererTest extends TestUnit {
  function render(array $options) {
    return $this->renderer->render(new View(), $options);
  }
  
  function set_up() {
    $this->renderer = new TemplateRenderer($this->views_dir());
  }
  
  function test_render_file() {
    $output = $this->render(array('file' => 'tests/hello_world'));
    $this->assert_eq($output, 'hello world');
  }
  
  function test_render_template() {
    $output = $this->render(array('template' => 'foo'));
    $this->assert_eq($output, 'this is a foo');
  }
  
  function test_render_template_with_path() {
    $output = $this->render(array('template' => 'index', 'path' => 'application'));
    $this->assert_eq($output, 'index action template');
    
    $output = $this->render(array('template' => 'echo_my_name', 'path' => 'tests/deep_tpls'));
    $this->assert_eq($output, 'my name is thomas');
  }
  
  function test_render_file_with_layout() {
    $output = $this->render(array('file' => 'tests/deep_tpls/echo_my_name', 'layout' => 'base'));
    $this->assert_eq($output, '<html>my name is thomas</html>');
  }
  
  function test_render_template_with_layout() {
    $output = $this->render(array('template' => 'index', 'path' => 'application', 'layout' => 'base'));
    $this->assert_eq($output, '<html>index action template</html>');
  }
}
?>