<?php
namespace display {
  use http\action\View;
  use display\html\Helpers;
  
  function view() {
    return View::render_instance();
  }
  
  function controller() {
    return view()->controller();
  }
  
  function template_content() {
    return view()->template_content();
  }
  
  function request() {
    return controller()->request();
  }
  
  function script_url() {
    return request()->script_url();
  }
  
  function base_url() {
    return request()->base_url();
  }
  
  function assign($name, $value) {
    return view()->assign($name, $value);
  }
  
  function append($name, $value) {
    return view()->append($name, $value);
  }
  
  function obtain($name) {
    return view()->obtain($name);
  }
  
  function show($name) {
    echo view()->obtain($name);
  }
  
  function link_to($to, $options = array(), $attributes = array()) {
    return controller()->link_to($to, $options, $attributes);
  }
  
  function url_for($to, $options = array()) {
    return controller()->url_for($to, $options);
  }
  
  function render($options, array $assignments = array()) {
    return view()->render($options, $assignments);
  }
  
  function partial($path, array $assignments = array()) {
    return render(array('partial' => $path), $assignments);
  }
  
  function capture_for($name, $block) {
    append($name, capture($block));
  }
  
  class Stylesheets {
    static $includes = array();
  }
  
  function include_stylesheets() {
    foreach(func_get_args() as $name) Stylesheets::$includes[] = $name;
  }
  
  /**
   * Generates a base-tag with the current request_url
   *
   * @return string
   */
  function base_tag($path = null) {
    if(!empty($path)) $path = trim($path, '/')."/";
    return tag('base', array('href' => base_url()."/$path"))."\n";
  }
  
  /**
   * Generate one or more style-tags
   *
   * @param mixed $names
   * @return string link-tag(s)
   */
  function stylesheets() {
    $sheets = func_get_args();
    foreach(Stylesheets::$includes as $name) $sheets[] = $name;
    return Helpers::stylesheet_links($sheets, base_url()."/stylesheets");
  }

  /**
   * Generate one or more script-tags
   *
   * @param mixed $sources
   * @return string script-tag(s)
   */
  function javascripts() {
    return Helpers::javascript_tags(func_get_args(), base_url()."/javascripts");
  }
}
?>