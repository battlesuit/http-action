<?php
namespace display {
  use http\action\View;
  use display\html\Helpers;
  
  /**
   * Returns the current rendering view instance
   *
   * @return View
   */
  function view() {
    return View::render_instance();
  }
  
  /**
   * Accesses the applied presenter instance
   *
   * @return http\action\Presenter
   */
  function presenter() {
    return view()->presenter();
  }
  
  /**
   * Returns the rendered template content
   * You should use this in layout templates to display subcontents
   *
   * @return string
   */
  function template_content() {
    return view()->template_content();
  }
  
  /**
   * Accesses the current request instance
   * 
   * @return http\Request
   */
  function request() {
    return controller()->request();
  }
  
  /**
   * Returns the current script url which is always a file-url
   * e.g. http://domain.de/to/my/script.php
   *
   * @return string
   */
  function script_url() {
    return request()->script_url();
  }
  
  /**
   * Same as script_url but without the ending file
   *
   * @return string
   */
  function base_url() {
    return request()->base_url();
  }
  
  /**
   * Assigns a new view/template variable
   *
   * @param string $name
   * @param mixed $value
   * @return mixed
   */
  function assign($name, $value) {
    return view()->assign($name, $value);
  }
  
  /**
   * Appends a value to a existing or non-existing variable
   * 
   * @param string $name
   * @param mixed $value
   */
  function append($name, $value) {
    return view()->append($name, $value);
  }
  
  /**
   * Returns a view/template variable
   *
   * @param string $name
   * @return mixed
   */
  function obtain($name) {
    return view()->obtain($name);
  }
  
  /**
   * Directly shows(echos) a obtained value
   *
   * @param string $name
   */
  function show($name) {
    echo obtain($name);
  }
  
  /**
   * Generates a link to a target
   *
   * @param mixed $to
   * @param mixed $options
   * @param array $attributes
   * @return string
   */
  function link_to($to, $options = array(), array $attributes = array()) {
    return presenter()->link_to($to, $options, $attributes);
  }
  
  /**
   * Generates a url to a target
   *
   * @param mixed $to
   * @param mixed $options
   * @return string
   */
  function url_for($to, $options = array()) {
    return presenter()->url_for($to, $options);
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
   * Generates a base-tag with the current requests base_url
   *
   * @param string $path
   * @return string
   */
  function base_tag($path = null) {
    if(!empty($path)) $path = trim($path, '/')."/";
    return tag('base', array('href' => base_url()."/$path"))."\n";
  }
  
  /**
   * Generate one or more style-tags
   *
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
   * @return string script-tag(s)
   */
  function javascripts() {
    return Helpers::javascript_tags(func_get_args(), base_url()."/javascripts");
  }
}
?>