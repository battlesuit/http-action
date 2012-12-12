<?php
namespace http\action\view\helpers;

/**
 * Markup helper methods which are used by default in every presenters view_context
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Battlesuit
 * @subpackage http-action
 */
class Markup {
  
  /**
   * Generates a classic closuring tag <[tag]></[tag]>
   *
   * @access public
   * @param string $name
   * @param string $content
   * @param array $attributes
   * @return string <></>
   */
  function content_tag($name, $content, array $attributes = array()) {
    return "<$name".(!empty($attributes) ? " ".$this->tag_attributes($attributes) : "").">$content</$name>";
  }

  /**
   * Generates a rudimental tag
   *
   * @access public
   * @param string $name
   * @param array $attributes
   * @return string </>
   */
  function tag($name, array $attributes = array()) {
    return "<$name".(!empty($attributes) ? $this->tag_attributes($attributes, true) : "")." />";
  }
  
  /**
   * Generate tag attributes
   *
   * @access public
   * @param array $attributes
   * @return string
   */
  function tag_attributes(array $attributes, $leading_space = false) {
    $str = $ws = null;
    if($leading_space) $ws = ' ';

    foreach($attributes as $key => $value) {
      if($value === true) $value = 'true';
      elseif($value === false) $value = 'false';
      
      $str .= "{$ws}{$key}=\"$value\"";
      $ws = ' ';
    }

    return $str;
  }
  
  /**
   * Generates an a-tag
   *
   * @access public
   * @param string $href
   * @param string $text
   * @param array $attributes
   * @return string
   */
  function link_tag($href, $text, array $attributes = array()) {
    return $this->content_tag('a', $text, array('href' => $href) + $attributes);
  }

  /**
   * Generates a form-tag
   *
   * @param string $action
   * @param string $content
   * @param string $method
   * @param array $attributes
   * @return string
   */
  function form_tag($action, $content, $method = 'post', array $attributes = array()) {
    return $this->content_tag('form', $content, array('action' => $action, 'method' => $method) + $attributes);
  }

  /**
   * Generates a button-tag
   *
   * @access public
   * @param string $content
   * @param array $attributes
   * @return string
   */
  function button_tag($content, array $attributes = array()) {
    return $this->content_tag('button', $content, $attributes);
  }


  /**
   * Generate one or more style-tags
   *
   * @access public
   * @param array $names
   * @return string link-tag(s) or null
   */
  function stylesheet_links(array $names, $base_url = null) {
    $tags = null;
    if(!empty($names)) {     
      foreach($names as $name) $tags .= $this->stylesheet_link($name, $base_url)."\n";
    }
    return $tags;
  }

  /**
   * Generate a single style-tag
   *
   * @access public
   * @param mixed $name
   * @return string link-tag
   */
  function stylesheet_link($name, $base_url = null) {
    $attributes = array();
    if(is_array($name)) {
      $info = $name;
      $name = key($info);
      $attributes = (array)$info[$name];
    }
    
    $css_file = "$name.css";
    
    if(!empty($base_url)) $css_file = "$base_url/$css_file";

    return $this->tag('link', $attributes + array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'all', 'href' => $css_file));
  }

  /**
   * Generate one or more script-tags
   *
   * @access public
   * @param array $sources
   * @return string script-tag(s) or null
   */
  function javascript_tags(array $sources, $base_url = null) {
    $tags = null;
    if(!empty($sources)) {
      foreach($sources as $source) $tags .= $this->javascript_tag($source, $base_url);
    }
    return $tags;
  }

  /**
   * Generate a single script-tag
   *
   * @access public
   * @param string $source
   * @return string script-tag
   */
  function javascript_tag($source, $base_url = null) {  
    if(!empty($base_url) and strpos($source, 'http://') === false) $source = "$base_url/$source.js";
    else $source = "$source.js";
    
    return $this->content_tag('script', null, array('type' => 'text/javascript', 'src' => $source))."\n";
  }

  /**
   * Generates a link-tag with type image/x-icon
   *
   * @access public
   * @param string $name
   * @return string link-tag
   */
  function favicon_link($name) {
    return $this->tag('link', array('type' => 'image/x-icon', 'rel' => 'shortcut icon', 'href' => "$name.ico"))."\n";
  }

  /**
   * Generates a meta-tag with a http-equiv attribute
   *
   * @access public
   * @param string $http_equiv
   * @param string $content
   * @param array $attributes
   * @return string
   */
  function http_equiv_meta_tag($http_equiv, $content, array $attributes = array()) {
    return $this->meta_tag($http_equiv, $content, true, $attributes);
  }

  /**
   * Generate multiple meta-tags
   *
   * @access public
   * @param array $tags
   * @return string
   */
  function meta_tags(array $tags) {
    if(!empty($tags)) {
      $out_tags = null;
      foreach($tags as $name => $content) $out_tags .= $this->meta_tag($name, $content);
      return $out_tags;
    }
  }

  /**
   * Generate meta-tag
   *
   * @access public
   * @param string $name
   * @param string $content
   * @param array $attributes
   * @return string meta-tag
   */
  function meta_tag($name_or_http_equiv, $content, $http_equiv = false, array $attributes = array()) {
    $attributes[$http_equiv ? 'http-equiv' : 'name'] = $name_or_http_equiv;
    $attributes['content'] = $content;
    return $this->tag('meta', $attributes)."\n";
  }

  /**
   * Wrap content into div-tag
   *
   * @access public
   * @param string $content
   * @param array $attributes
   * @return string
   */
  function div_tag($content, array $attributes = array()) {
    return $this->content_tag('div', $content, $attributes);
  }

  /**
   * Generate img-tag
   *
   * @access public
   * @param string $source
   * @param array $attributes
   * @return string img-tag
   */
  function image_tag($source, array $attributes = array()) {
    return $this->tag('img', array('src' => $source) + $attributes);
  }

  /**
   * Generates a textarea-tag
   *
   * @access public
   * @param string $name
   * @param string $value
   * @param array $attributes
   * @return string
   */
  function textarea_tag($name, $value = null, array $attributes = array()) {
    if(is_array($value)) {
      $attributes = $value;
      $value = null;
    }

    return $this->content_tag('textarea', $value, array('name' => $name) + $attributes);
  }

  /**
   * Generates a input-tag
   *
   * @access public
   * @param string $name
   * @param string $type
   * @param string $value
   * @param array $attributes
   * @return string
   */
  function input_tag($name, $type = null, $value = null, array $attributes = array()) {
    if(empty($type)) $type = 'text';
    
    if(is_array($value)) {
      $attributes = $value;
      $value = null;
    }

    return $this->tag('input', array('type' => $type, 'value' => $value, 'name' => $name) + $attributes);
  }

  /**
   * Generates a select-tag with options
   *
   * @access public
   * @param string $name
   * @param array $options
   * @param mixed $selected_value
   * @param array $attributes
   * @return string
   */
  function select_tag($name, array $options = array(), $selected_value = null, array $attributes = array()) {
    $option_tags = '';
    if(!empty($options)) {
      foreach($options as $value => $text) {
        $option_tag_attributes = array('value' => $value);
        if($selected_value == $value) {
          $option_tag_attributes['selected'] = true;
        }
        $option_tags .= $this->content_tag('option', $text, $option_tag_attributes);
      }
    }

    return $this->content_tag('select', $option_tags, array('name' => $name) + $attributes);
  }
}
?>