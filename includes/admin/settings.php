<?php 

namespace JituuShop\WCMNL\Admin;

class Settings {
    protected static $_instance;

    private function __construct() {
        add_filter( 'woocommerce_account_settings', array( $this, 'wcmnl_settings' ) );
    }

    public static function instance() {
        if( null === self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function wcmnl_settings( $account_settings ) {
        $account_settings = array_merge( $account_settings, array(
			array(
				'title' => __( 'Mobile number login settings', 'jituushop-wcmnl' ),
				'desc'  => __( 'Configure firebase account settings for registration with mobile phone number. You can skip these if you do not want send activation code to registered phone number.', 'woocommerce' ),
				'type'  => 'title',
				'id'    => 'wcml_firebase_config',
			),
			array(
				'title'    => __( 'API Key', 'jituushop-wcmnl' ),
				'desc_tip' => __( 'Google cloud console API key.', 'jituushop-wcmnl' ),
				'id'       => 'wcmnl_firebase_config[apiKey]',
				/* translators: %s privacy policy page name and link */
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Auth Domain', 'jituushop-wcmnl' ),
				//'desc_tip' => __( 'Google cloud console API key.', 'jituushop-wcmnl' ),
				'id'       => 'wcmnl_firebase_config[authDomain]',
				/* translators: %s privacy policy page name and link */
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Database URL', 'jituushop-wcmnl' ),
				//'desc_tip' => __( 'Google cloud console API key.', 'jituushop-wcmnl' ),
				'id'       => 'wcmnl_firebase_config[databaseURL]',
				/* translators: %s privacy policy page name and link */
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Project ID', 'jituushop-wcmnl' ),
				//'desc_tip' => __( 'Google cloud console API key.', 'jituushop-wcmnl' ),
				'id'       => 'wcmnl_firebase_config[projectId]',
				/* translators: %s privacy policy page name and link */
				'type'     => 'text',
			),
            array(
                'type' => 'sectionend',
                'id'   => 'wcml_firebase_config',
            ),            
        ) );

        return $account_settings;
    }
}