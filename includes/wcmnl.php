<?php 

namespace JituuShop\WCMNL;

use JituuShop\WCMNL\Scripts;
use JituuShop\WCMNL\Admin\Settings;
use WC_Validation;
use Exception;
use WP_Error;

class WCMNL {
    protected static $_instance;

    protected $scripts;
	protected $settings;

    protected function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    public static function instance() {
        if( null === static::$_instance ) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    public function init() {
        $this->scripts 	= Scripts::instance();
		$this->settings = Settings::instance();

    }

    public function toggler() {
        $this->get_template( 'toggler.php' );
    }

    public function phone_number_input() {
        $this->get_template( 'phone-input.php' );
    }

	/**
	 * Process the registration form.
	 *
	 * @throws Exception On registration error.
	 */
	public function process_registration() {
		$nonce_value = isset( $_POST['_wpnonce'] ) ? wp_unslash( $_POST['_wpnonce'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce_value = isset( $_POST['woocommerce-register-nonce'] ) ? wp_unslash( $_POST['woocommerce-register-nonce'] ) : $nonce_value; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( isset( $_POST['register'], $_POST['phone'] ) && ! isset( $_POST['email'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ) {

            $username = 'no' === get_option( 'woocommerce_registration_generate_username' ) && isset( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$password = 'no' === get_option( 'woocommerce_registration_generate_password' ) && isset( $_POST['password'] ) ? $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$phone    = wp_unslash( $_POST['phone'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			try {
				$validation_error  = new WP_Error();
				$validation_error  = apply_filters( 'wcmnl_process_registration_errors', $validation_error, $username, $password, $phone );
				$validation_errors = $validation_error->get_error_messages();

				if ( 1 === count( $validation_errors ) ) {
					throw new Exception( $validation_error->get_error_message() );
				} elseif ( $validation_errors ) {
					foreach ( $validation_errors as $message ) {
						wc_add_notice( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . $message, 'error' );
					}
					throw new Exception();
				}

				$new_customer = $this->create_new_customer( sanitize_text_field( $phone ), wc_clean( $username ), $password );

				if ( is_wp_error( $new_customer ) ) {
					throw new Exception( $new_customer->get_error_message() );
				}

				if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) ) {
					wc_add_notice( __( 'Your account was created successfully and a password has been sent to your phone number.', 'jituushop-wcmnl' ) );
				} else {
					wc_add_notice( __( 'Your account was created successfully.', 'jituushop-wcmnl' ) );
				}

				// Only redirect after a forced login - otherwise output a success notice.
				if ( apply_filters( 'woocommerce_registration_auth_new_customer', true, $new_customer ) ) {
					wc_set_customer_auth_cookie( $new_customer );

					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = wp_sanitize_redirect( wp_unslash( $_POST['redirect'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} elseif ( wc_get_raw_referer() ) {
						$redirect = wc_get_raw_referer();
					} else {
						$redirect = wc_get_page_permalink( 'myaccount' );
					}

					wp_redirect( wp_validate_redirect( apply_filters( 'woocommerce_registration_redirect', $redirect ), wc_get_page_permalink( 'myaccount' ) ) ); //phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
					exit;
				}
			} catch ( Exception $e ) {
				if ( $e->getMessage() ) {
					wc_add_notice( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . $e->getMessage(), 'error' );
				}
			}
		}
	}

	public function validate_phone_number( $errors, $username, $email ) {
        if( isset( $_POST['phone'] ) || isset( $_POST['billing_phone'] ) ) {
            $phone = sanitize_text_field( isset( $_POST['phone'] ) ? $_POST['phone'] : $_POST['billing_phone'] );

			if ( empty( $phone ) || ! WC_Validation::is_phone( $phone ) ) {
				return new WP_Error( 'registration-error-invalid-phone', __( 'Please provide a valid phone number.', 'woocommerce' ) );
			}
	
			if( is_phone_number_exists( $phone ) ) {
				return new WP_Error( 'registration-error-phone-exists', apply_filters( 'wcmnl_registration_error_phone_exists', __( 'An account is already registered with your phone number. <a href="#" class="showlogin">Please log in.</a>', 'jituushop-wcmnl' ), $phone ) );
			}
        }

		return $errors;
	}

    public function merge_phone_number_data( $data ) {
        if( isset( $_POST['phone'] ) || isset( $_POST['billing_phone'] ) ) {
            $phone = sanitize_text_field( isset( $_POST['phone'] ) ? $_POST['phone'] : $_POST['billing_phone'] );

            $data['meta_input'] = isset( $data['meta_input'] ) && is_array( $data['meta_input'] ) ? $data['meta_input'] : array();
            $data['meta_input'] = wp_parse_args( array(
                'phone_number' => $phone,
            ), $data['meta_input'] );
        }

        return $data;
    }

    public function phone_number_authenticate( &$username ) {
        if( is_email( $username ) ) return false;

        $phone_number = wc_sanitize_phone_number( $username );

        if( empty( $phone_number ) ) return false;

        $user_ids = get_user_ids_by_phone_number( $phone_number );

        if( ! empty( $user_ids ) ) {
            $userdata = get_user_by( 'id', absint( current( $user_ids ) ) );
            $username = $userdata->user_login;
        }
    }

	public function phone_number_field() {
		$this->get_template( 'myaccount/phone-number-field.php' );
	}

	public function save_account_details_required_fields( $fields ) {
		print_r( $_POST );
		$account_phone = isset( $_POST['account_phone'] ) ? sanitize_text_field( $_POST['account_phone'] ) : '';
		$account_email = sanitize_email( $_POST['account_email'] );
		if( ! empty( $account_phone ) ) {
			unset( $fields['account_email'] );
		}

		if( empty( $account_email ) ) {
			$fields['account_phone'] = __( 'Phone number', 'jituushop-wcmnl' );
		}

		return $fields;
	}

    private function define_constants() {
        $upload_dir = wp_upload_dir( null, false );

        define( 'JITUUSHOP_WCMNL_PLUGIN_VERSION', '1.0.0' );
        define( 'JITUUSHOP_WCMNL_PLUGIN_DIR', dirname( JITUUSHOP_WCMNL_PLUGIN_FILE ) );
        define( 'JITUUSHOP_WCMNL_PLUGIN_URL', untrailingslashit( plugin_dir_url ( JITUUSHOP_WCMNL_PLUGIN_FILE ) ) );
    }

    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ) );

        add_action( 'woocommerce_register_form_start', array( $this, 'toggler' ) );

        add_action( 'woocommerce_register_form_start', array( $this, 'phone_number_input' ) );

        //remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_registration' ) );
        add_action( 'wp_loaded', array( $this, 'process_registration' ) );

        add_filter( 'woocommerce_new_customer_data', array( $this, 'merge_phone_number_data' ) );

		add_filter( 'woocommerce_registration_errors', array( $this, 'validate_phone_number' ), 10, 3 );

        add_action( 'wp_authenticate', array( $this, 'phone_number_authenticate' ) );

		add_action( 'woocommerce_edit_account_form', array( $this, 'phone_number_field' ) );

		add_filter( 'woocommerce_save_account_details_required_fields', array( $this, 'save_account_details_required_fields' ) );
    }

    private function includes() {
        include JITUUSHOP_WCMNL_PLUGIN_DIR . '/includes/functions.php';
    }

    public function locate_template( $template_name, $template_path = '', $default_path = '' ) {
        $default_path = apply_filters( 'wcml_template_path', $default_path );

        if( ! $template_path ) {
            $template_path = 'jituushop-wcml';
        }

        if( ! $default_path ) {
            $default_path = JITUUSHOP_WCMNL_PLUGIN_DIR . '/templates/';
        }

        $template = locate_template( array( trailingslashit( $template_path ) . $template_name, $template_name ) );
        $template = apply_filters( 'wcml_locate_template', $template, $template_name, $template_path, $default_path );

        if( ! $template ) {
            $template = $default_path . $template_name;
        }

        return $template;
    }

    public function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
        if( $args && is_array( $args ) ) extract( $args );

        $located = $this->locate_template( $template_name, $template_path, $default_path );

        if( $located ) include $located;
    }

	/**
	 * Create a new customer.
	 *
	 * @param  string $phone    Customer phone.
	 * @param  string $username Customer username.
	 * @param  string $password Customer password.
	 * @param  array  $args     List of arguments to pass to `wp_insert_user()`.
	 * @return int|WP_Error Returns WP_Error on failure, Int (user ID) on success.
	 */
	public function create_new_customer( $phone, $username = '', $password = '', $args = array() ) {
		if ( empty( $phone ) || ! WC_Validation::is_phone( $phone ) ) {
			return new WP_Error( 'registration-error-invalid-phone', __( 'Please provide a valid phone number.', 'woocommerce' ) );
		}

		if ( is_phone_number_exists( $phone ) ) {
			return new WP_Error( 'registration-error-phone-exists', apply_filters( 'wcmnl_registration_error_phone_exists', __( 'An account is already registered with your phone number. <a href="#" class="showlogin">Please log in.</a>', 'jituushop-wcmnl' ), $phone ) );
		}

		if ( 'yes' === get_option( 'woocommerce_registration_generate_username', 'yes' ) && empty( $username ) ) {
			$username = $phone;
		}

		$username = sanitize_user( $username );

		if ( empty( $username ) || ! validate_username( $username ) ) {
			return new WP_Error( 'registration-error-invalid-username', __( 'Please enter a valid account username.', 'woocommerce' ) );
		}

		if ( username_exists( $username ) ) {
			return new WP_Error( 'registration-error-username-exists', __( 'An account is already registered with that username. Please choose another.', 'woocommerce' ) );
		}

		// Handle password creation.
		$password_generated = false;
		if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && empty( $password ) ) {
			$password           = wp_generate_password();
			$password_generated = true;
		}

		if ( empty( $password ) ) {
			return new WP_Error( 'registration-error-missing-password', __( 'Please enter an account password.', 'woocommerce' ) );
		}

		// Use WP_Error to handle registration errors.
		$errors = new WP_Error();

		do_action( 'wcmnl_register_post', $username, $phone, $errors );

		$errors = apply_filters( 'wcmnl_registration_errors', $errors, $username, $phone );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		$new_customer_data = apply_filters(
			'woocommerce_new_customer_data',
			array_merge(
				$args,
				array(
					'user_login' => $username,
					'user_pass'  => $password,
					'role'       => 'customer',
				)
			)
		);

		$customer_id = wp_insert_user( $new_customer_data );

		if ( is_wp_error( $customer_id ) ) {
			return $customer_id;
		}

		do_action( 'woocommerce_created_customer', $customer_id, $new_customer_data, $password_generated );

		return $customer_id;
	}
}