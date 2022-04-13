<?php
/**
 * Plugin Name: WooCommerce Mobile Number Login
 * Plugin URI: https://www.jituushop.com
 * Description: WooCommerce Mobile Number Login enable the customers register using their mobile phone number in place of email address. 
 * Author: drhtoo
 * Version: 1.0.0
 * Author URI: https://www.facebook.com/drpyaesonehtoo
 *
 * Text Domain: jituushop-wcmnl
 * Domain Path: /languages/
 *
 */

defined( 'ABSPATH' ) || exit;

if( ! defined( 'JITUUSHOP_WCMNL_PLUGIN_FILE' ) ) {
    define( 'JITUUSHOP_WCMNL_PLUGIN_FILE', __FILE__ );
}

include dirname( JITUUSHOP_WCMNL_PLUGIN_FILE ) . '/vendor/autoload.php';

use JituuShop\WCMNL\Dependencies;
use JituuShop\WCMNL\Updater;
use JituuShop\WCMNL\WCMNL;

if( Dependencies::check_dependencies() ) {
    $updater = new Updater( JITUUSHOP_WCMNL_PLUGIN_FILE );
    $updater->init();

    function WCMNL() {
        return WCMNL::instance();
    }

    $GLOBALS['WCMNL'] = WCMNL();
}