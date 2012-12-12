<?php
namespace http\action;

class TestUnit extends \test_case\Unit {  
  function bench_dir() {
    return realpath(__DIR__."/../../bench");
  }
  
  function views_dir() {
    return $this->bench_dir()."/views";
  }
}
?>