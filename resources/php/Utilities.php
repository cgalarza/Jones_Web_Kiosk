<?php

  class Utilities {

    /**
    * Get parameter from webpage. Return default value (if given), if not return
    * an empty string.
    */
    public static function getParam($param, $default = ''){
      return (isset($_GET[$param])) ? $_GET[$param] : $default;
    }

  }

?>
