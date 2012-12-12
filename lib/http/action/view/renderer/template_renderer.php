<?php
namespace http\action\view\renderer;

class TemplateRenderer {
  private $lookup_dirs = array();
  private $format = 'php';
  
  function __construct($lookup_dirs = array()) {
    $this->lookup_dirs = (array)$lookup_dirs;
  }
  
  function find_template_file($path) {
    foreach($this->lookup_dirs as $dir) {
      $file = $dir."/".$this->formatize($path);
      if(file_exists($file)) return $file;
    }
    
    return false;
  }
  
  function capture_template($context, $name, $path = null, $block = null) {
    $file = $name;
    
    if(!empty($path)) {
      $file = "$path/$file";
    }
    
    if(($template_file = $this->find_template_file($file))) {
      return $this->capture_file($context, $template_file, array(), $block);
    }
    
    return '';
  }
  
  function render($context, array $options = array()) {
    $path = null;
    $output = '';
    extract($options);
    
    if(!empty($template)) {
      $output = $this->capture_template($context, $template, $path);
    } elseif(!empty($file) and ($template_file = $this->find_template_file($file))) {
      $output = $this->capture_file($context, $template_file);
    }

    if(!empty($layout)) {
      $output = $this->capture_template($context, $layout, 'layouts', function() use($output) {
        return $output;
      });
    }
    
    return $output;
  }
  
  function read_file($file) {
    return file_get_contents($file);
  }
  
  /**
   * Appends the views file extension if there isnt any
   *
   * @access private
   * @param string $file
   * @return string
   */
  private function formatize($file) {
    if((strpos(basename($file), '.') === false and !empty($this->format))) $file .= ".$this->format";
    return $file;
  }
  
  /**
   * Captures a file within a namespace
   *
   * @access protected
   * @param string $namespace
   * @param string $file
   * @param array $variables
   * @return string
   */
  protected function capture_file($context, $file, $variables = array(), $block = null) {
    $file_content = $this->read_file($file);
    return $context->capture(" ?>$file_content<?php ", $variables, $block);
  }
}
?>