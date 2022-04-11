<?php 

namespace JituuShop\WCMNL;

class WCMNL {
    protected static $_instance;

    public static function init() {
        if( null === static::$_instance ) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }
}