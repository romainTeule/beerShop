<?php
 
 class TempData {
    public static function get($offset) {
        $value = $_SESSION[$offset];
        unset($_SESSION[$offset]);
        return $value;
    }

    public static function set($offset, $value) {
        $_SESSION[$offset] = $value;
    }
}   
    
?>