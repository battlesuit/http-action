<?php
namespace loader {
  require "../loader.php";
  
  import('test', 'http-action');
  scope('http\action', __DIR__."/http");
  scope('server', __DIR__);
}
?>