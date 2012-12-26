<?php
/**
 * Initializes the http-action bundle
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Battlesuit
 * @subpackage http-action
 */
namespace loader {
  if(defined('loader\available')) {
    
   /**
    * All the autoloading is done here
    * This function is getting called by the loader\Bundles::autoload
    * 
    */
    scope('http\action', __DIR__);
    import('http', 'str-inflections');
  }
}
?>