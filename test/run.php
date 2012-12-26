<?php
namespace test_bench {
  require 'loader.php';
  
  set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
  error_reporting(-1);
  
  class PackageTestBench extends Base {
    function initialize() {
      $this->add_test(new \http\action\controller\BaseTest());
      $this->add_test(new \http\action\PresenterTest());
      $this->add_test(new \http\action\view\renderer\TemplateRendererTest());
      
      $this->add_test(new \http\action\view\BaseTest());
    }
  }

  $bench = new PackageTestBench();
  $bench->run_and_present_as_text();
}
?>