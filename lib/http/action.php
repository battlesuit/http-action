<?php
namespace {
  spl_autoload_register(function($class) {
    $underscored_class = preg_replace('/(\p{Ll})(\p{Lu})/', '$1_$2', $class);
    $file = __DIR__."/../".str_replace('\\', '/', strtolower($underscored_class)).".php";
    
    if(file_exists($file)) require $file;
  });
}
?>