<?php
class ApplicationPresenter extends http\action\Presenter {
  function lookup_templates_under() {
    return __DIR__."/../views";
  }
  
  function index() {
    return $this->render();
  }
}
?>