<?php 

namespace JituuShop\WCMNL;

class Scripts {
    protected static $_instance;

    protected $styles = array();
    protected $scripts = array();
    protected $localized_scripts = array();

    private function __construct()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
        add_action( 'wp_print_scripts', array( $this, 'localize_printed_scripts' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'localize_printed_scripts' ), 5 );
    }

    public static function instance() {
        if( null === static::$_instance ) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    public function load_scripts() {
        $this->register_scripts();
        $this->register_styles();

        $pages = array();

        $firebase_config = get_option( 'wcmnl_firebase_config', array(
            'apiKey'       => '',
            'authDomain'   => '',
            'databaseURL'  => '',
            'projectId'    => '',
        ) );

        if( ! empty( $firebase_config['apiKey'] ) && 
            ! empty( $firebase_config['authDomain'] ) && 
            ! empty( $firebase_config['databaseURL'] ) && 
            ! empty( $firebase_config['projectId'] ) )  {
            $this->enqueue_script( 'fireauth' );
        }

        if( is_account_page() || is_checkout() || ( is_array( $pages ) && ! empty( $pages ) && is_page( $pages ) ) ) {
            $this->enqueue_script( 'wcmnl' );
            $this->enqueue_style( 'intl-tel-input' );
        }
    }

    public function enqueue_style( $handle, $path = '', $deps = array(), $version = JITUUSHOP_WCMNL_PLUGIN_VERSION, $media = 'all', $has_rtl = false ) {
		if ( ! in_array( $handle, $this->styles ) && $path ) {
			$this->register_style( $handle, $path, $deps, $version, $media, $has_rtl );
		}
		wp_enqueue_style( $handle );
	}    

    public function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = JITUUSHOP_WCMNL_PLUGIN_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, $this->scripts ) && $path ) {
			$this->register_script( $handle, $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle );
	}    

    public function localize_printed_scripts() {
        foreach( $this->scripts as $handle ) {
            $this->localize_script( $handle );
        }
    }

    protected function register_scripts() {
        $scripts = apply_filters( 'wcmnl_register_scripts', array(
            'firebase'          => array(
                'src'           => 'https://www.gstatic.com/firebasejs/8.6.2/firebase-app.js',
                'version'       => '8.6.2',
            ),
            'fireauth'          => array(
                'src'           => 'https://www.gstatic.com/firebasejs/8.6.7/firebase-auth.js', 
                'depend'        => array( 'firebase' ),
                'version'       => '8.6.2',
            ),
            'intl-tel-input'    => array(
                'src'           => JITUUSHOP_WCMNL_PLUGIN_URL . '/assets/js/intlTelInput.min.js',
                'version'       => '17.0.3',
            ),
            'wcmnl'             => array(
                'src'           => JITUUSHOP_WCMNL_PLUGIN_URL . '/assets/js/wcmnl.js',
                'depend'        => array( 'jquery', 'intl-tel-input' ),
                'version'       => JITUUSHOP_WCMNL_PLUGIN_VERSION,
            ),
        ) );

        foreach( $scripts as $handle => $prop ) {
            $depend = isset( $prop['depend'] ) ? $prop['depend'] : '';
            $ver    = isset( $prop['version'] ) ? $prop['version'] : JITUUSHOP_WCMNL_PLUGIN_VERSION;
            $this->register_script( $handle, $prop['src'], $depend, $ver );
        }
    }

    protected function register_styles() {
        $styles = apply_filters( 'wcmnl_register_styles', array(
            'intl-tel-input'    => array(
                'src'           => JITUUSHOP_WCMNL_PLUGIN_URL . '/assets/css/intlTelInput.min.css',
                'version'       => '17.0.3',
            ),
            'wcmnl'             => array(
                'src'           => JITUUSHOP_WCMNL_PLUGIN_URL . '/assets/css/app.css',
                'version'       => JITUUSHOP_WCMNL_PLUGIN_VERSION,
            )
        ) );

        foreach( $styles as $handle => $prop ) {
            $depend = isset( $prop['depend'] ) ? $prop['depend'] : '';
            $ver    = isset( $prop['version'] ) ? $prop['version'] : JITUUSHOP_WCMNL_PLUGIN_VERSION;
            $this->register_style( $handle, $prop['src'], $depend, $ver );
        }
    }

    protected function register_script( $handle, $src = '', $deps = array(), $ver = JITUUSHOP_WCMNL_PLUGIN_VERSION, $in_footer = true ) {
        $this->scripts[] = $handle;
		wp_register_script( $handle, $src, $deps, $ver, $in_footer );

    }

    protected function register_style( $handle, $src = '', $deps = array(), $ver = JITUUSHOP_WCMNL_PLUGIN_VERSION, $media = 'all', $has_rtl = false ) {
		$this->styles[] = $handle;
		wp_register_style( $handle, $src, $deps, $ver, $media );

		if ( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
    }

    protected function localize_script( $handle ) {
        if( ! in_array( $handle, $this->localized_scripts, true ) && wp_script_is( $handle ) ) {
            $data = $this->get_script_data( $handle );

            if( ! $data ) return;

            $param = str_replace( '-', '_', $handle ) . '_params';
            $this->localized_scripts[] = $handle;
            wp_localize_script( $handle, $param, $data );
        }
    }

    protected function get_script_data( $handle ) {
        switch( $handle ) {
            case 'wcmnl' :
                $firebase_config = get_option( 'wcmnl_firebase_config', array(
                    'apiKey'       => '',
                    'authDomain'   => '',
                    'databaseURL'  => '',
                    'projectId'    => '',
                ) );
                
                $data = array(
                    'utilsScript'    => JITUUSHOP_WCMNL_PLUGIN_URL . '/assets/js/utils.js',
                );

                if( ! empty( $firebase_config['apiKey'] ) && 
                    ! empty( $firebase_config['authDomain'] ) && 
                    ! empty( $firebase_config['databaseURL'] ) && 
                    ! empty( $firebase_config['projectId'] ) ) {
                    $data['firebaseConfig'] = $firebase_config;
                }
                    
                break;
            
            default: 
                $data = '';
        }

        return apply_filters( 'wcmnl_get_script_data', $data, $handle );
    }


}