<?php 

namespace JituuShop\WCMNL;

class Dependencies {
    public static function check_dependencies() {
        $active_plugins = (array) get_option( 'active_plugins', array() );

        if( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
        }

        $checked = true;

        if( ! in_array( 'woocommerce/woocommerce.php', $active_plugins ) && ! array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) {
            add_action( 'admin_notices', array( __CLASS__, 'woocommerce_missing_notice' ) );
            $checked = false;
        }

        return $checked;
    }

    public static function woocommerce_missing_notice() {
        echo '<div class="error"><p>' . sprintf( esc_html__( '%s Plugin requires WooCommerce to be installed and active. You can download %s here.', 'jituushop-wcmnl' ), '<strong>WooCommerce Mobile Number Login </strong>', '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>' ) . '</p></div>';

		return true;
    }    
}