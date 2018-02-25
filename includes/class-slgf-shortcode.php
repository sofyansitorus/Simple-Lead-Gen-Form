<?php
/**
 * Simple Lead Gen Form Shortcode.
 *
 * @since   0.0.1
 * @package SLGF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Simple Lead Gen Form Shortcode.
 *
 * @since 0.0.1
 */
class SLGF_Shortcode {

	/**
	 * User local time API URL.
	 *
	 * @since 0.0.1
	 *
	 * @var string
	 */
	private $api_url = 'https://timezoneapi.io/api/ip/';

	/**
	 * Form sequence.
	 *
	 * @since 0.0.1
	 *
	 * @var int
	 */
	private static $form_sequence = 1;

	/**
	 * Constructor.
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		$this->ajax = new SLGF_Ajax();
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 0.0.1
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );
		add_action( 'wp_ajax_nopriv_slgf_lead_form', array( $this, 'ajax_callback_handler' ) );
		add_action( 'wp_ajax_slgf_lead_form', array( $this, 'ajax_callback_handler' ) );
		add_shortcode( 'slgf_lead_form', array( $this, 'build_shortcode' ) );
	}

	/**
	 * AJAX callback handler for slgf_lead_form action
	 *
	 * @since 0.0.1
	 * @throws Exception If filed validation not passed.
	 */
	public function ajax_callback_handler() {
		// Verify nonce.
		$this->ajax->verify_nonce();

		$customer_data = array();
		foreach ( $this->fields() as $key => $field ) {
			try {
				$field = wp_parse_args(
					$field, array(
						'sanitize_cb' => 'sanitize_text_field',
						'required'    => true,
					)
				);

				$value = $this->ajax->data_post( $key, $field['sanitize_cb'] );

				if ( empty( $value ) && $field['required'] ) {
					$label = $this->ajax->data_post( $key . '__label', 'sanitize_text_field' );
					if ( empty( $label ) ) {
						$label = $field['label'];
					}
					// translators: %s is field name.
					throw new Exception( sprintf( __( '%s field is required', 'slgf' ), $label ) );
				}

				if ( 'current_date_time' === $key ) {
					if ( empty( $value ) ) {
						$value = $this->get_user_date_time();
						if ( is_wp_error( $value ) ) {
							throw new Exception( $value->get_error_message() );
						}
					}
					if ( ! empty( $value ) ) {
						$value = strtotime( $value );
					}
					if ( empty( $value ) ) {
						$value = current_time( 'timestamp' );
					}
				}

				$customer_data[ $key ] = $value;
			} catch ( \Exception $e ) {
				$this->ajax->send_response_error( $e->getMessage(), array( 'field' => $key ) );
			}
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $customer_data['full_name'],
				'post_content' => $customer_data['message'],
				'meta_input'   => $customer_data,
				'post_type'    => 'slgf-customer',
				'post_status'  => 'pending',
			), true
		);

		if ( is_wp_error( $post_id ) ) {
			$this->ajax->send_response_error( $post_id->get_error_message() );
		}

		$this->ajax->send_response_success( __( 'Thank you, your submission has been received', 'slgf' ) );
	}

	/**
	 * Register scripts and styles for the shortcode.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function register_scripts_and_styles() {
		wp_register_style( 'slgf-lead-form', slgf()->url( 'assets/css/slgf-lead-form.min.css' ), false, false );
		wp_register_script( 'slgf-lead-form', slgf()->url( 'assets/js/slgf-lead-form.min.js' ), array( 'jquery' ), false, true );
		wp_localize_script(
			'slgf-lead-form', 'slgf_params', array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'api_url'          => $this->api_url,
				'txt_err_required' => __( 'input is required', 'slgf' ),
				'txt_err_invalid'  => __( 'input is invalid', 'slgf' ),
				'fields'           => $this->fields(),
			)
		);
	}

	/**
	 * Build the shortcode output.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function build_shortcode( $atts ) {

		wp_enqueue_style( 'slgf-lead-form' );
		wp_enqueue_script( 'slgf-lead-form' );

		$atts = shortcode_atts(
			array(
				'full_name_label'      => '',
				'full_name_attrs'      => '',
				'phone_number_label'   => '',
				'phone_number_attrs'   => '',
				'email_address_label'  => '',
				'email_address_attrs'  => '',
				'desired_budget_label' => '',
				'desired_budget_attrs' => '',
				'message_label'        => '',
				'message_attrs'        => '',
				'submit_button'        => __( 'Submit', 'slgf' ),
			), $atts, 'slgf_lead_form'
		);

		$fields = array();

		foreach ( $this->fields() as $key => $field ) {
			if ( ! empty( $atts[ $key . '_label' ] ) ) {
				$field['label'] = $atts[ $key . '_label' ];
			}
			if ( ! empty( $atts[ $key . '_attrs' ] ) ) {
				$field_attrs = explode( ';', $atts[ $key . '_attrs' ] );
				foreach ( $field_attrs as $field_attr ) {
					$attrs = explode( ':', $field_attr );
					if ( 2 === count( $attrs ) && isset( $field['attrs'][ $attrs[0] ] ) ) {
						$field['attrs'][ $attrs[0] ] = $attrs[1];
					}
				}
			}

			$fields[ $key ] = $field;
		}

		ob_start();
		?>
		<form id="slgf-form-<?php echo esc_attr( self::$form_sequence ); ?>" class="slgf-form" method="POST">
			<?php $this->ajax->nonce_field(); ?>
			<input type="hidden" name="action" value="slgf_lead_form">
			<input type="hidden" name="form_sequence" value="<?php echo esc_attr( self::$form_sequence ); ?>">
			<div class="slgf-alertbox"></div>
			<?php
			foreach ( $fields as $key => $field ) {
				?>
				<input 
				type="hidden" 
				name="<?php echo esc_attr( $key ); ?>__label" 
				value="<?php echo esc_attr( $field['label'] ); ?>">
				<?php
				$is_required_class = $field['required'] ? 'is-required' : '';
				switch ( $field['type'] ) {
					case 'hidden':
						?>
						<input 
						<?php
						if ( $field['attrs'] ) {
							foreach ( $field['attrs'] as $attr_key => $attr_value ) {
								echo esc_html( $attr_key ) . '="' . esc_attr( $attr_value ) . '" ';
							}
						}
						?>
						type="hidden" 
						name="<?php echo esc_attr( $key ); ?>" 
						value="<?php echo esc_attr( $field['default_value'] ); ?>">
						<?php
						break;
					case 'textarea':
						?>
						<div class="slgf-row <?php echo esc_attr( $is_required_class ); ?>">
							<label for="slgf-field-<?php echo esc_attr( $key ); ?>-<?php echo esc_attr( self::$form_sequence ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<div class="slgf-field-wrap">
								<textarea 
								<?php
								if ( $field['attrs'] ) {
									foreach ( $field['attrs'] as $attr_key => $attr_value ) {
										echo esc_html( $attr_key ) . '="' . esc_attr( $attr_value ) . '" ';
									}
								}
								?>
								id="slgf-field-<?php echo esc_attr( $key ); ?>-<?php echo esc_attr( self::$form_sequence ); ?>" 
								name="<?php echo esc_attr( $key ); ?>" 
								class="slgf-field"><?php echo esc_textarea( $field['default_value'] ); ?></textarea>
								<div class="slgf-field-error"></div>
							</div>
						</div>
						<?php
						break;
					default:
						?>
						<div class="slgf-row <?php echo esc_attr( $is_required_class ); ?>">
							<label for="slgf-field-<?php echo esc_attr( $key ); ?>-<?php echo esc_attr( self::$form_sequence ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<div class="slgf-field-wrap">
								<input 
								<?php
								if ( $field['attrs'] ) {
									foreach ( $field['attrs'] as $attr_key => $attr_value ) {
										echo esc_attr( $attr_key ) . '="' . esc_attr( $attr_value ) . '" ';
									}
								}
								?>
								type="<?php echo esc_html( $field['type'] ); ?>" 
								id="slgf-field-<?php echo esc_attr( $key ); ?>-<?php echo esc_attr( self::$form_sequence ); ?>" 
								name="<?php echo esc_attr( $key ); ?>" 
								value="<?php echo esc_attr( $field['default_value'] ); ?>" 
								class="slgf-field">
								<div class="slgf-field-error"></div>
							</div>
						</div>
						<?php
						break;
				}
			}
			?>
			<button type="submit"><span class="spinner"></span><span class="text"><?php echo esc_html( $atts['submit_button'] ); ?></span></button>
		</form>
		<?php
		$output = ob_get_clean();

		self::$form_sequence++;

		return $output;
	}

	/**
	 * Customer data meta fields.
	 *
	 * @since 0.0.1
	 * @return array
	 */
	public function fields() {
		return array(
			// Add full_name field.
			'full_name'         => array(
				'label'         => __( 'Full Name', 'slgf' ),
				'type'          => 'text',
				'required'      => true,
				'sanitize_cb'   => 'sanitize_text_field',
				'default_value' => '',
				'attrs'         => array(
					'placeholder' => __( 'Enter your full name', 'slgf' ),
					'maxlength'   => 100,
				),
			),
			// Add phone_number field.
			'phone_number'      => array(
				'label'         => __( 'Phone Number', 'slgf' ),
				'type'          => 'text',
				'required'      => true,
				'sanitize_cb'   => 'sanitize_text_field',
				'default_value' => '',
				'attrs'         => array(
					'placeholder' => __( 'Enter your phone number', 'slgf' ),
					'maxlength'   => 100,
				),
			),
			// Add email_address field.
			'email_address'     => array(
				'label'         => __( 'Email Address', 'slgf' ),
				'type'          => 'email',
				'required'      => true,
				'sanitize_cb'   => 'sanitize_email',
				'default_value' => '',
				'attrs'         => array(
					'placeholder' => __( 'Enter your email address', 'slgf' ),
					'maxlength'   => 100,
				),
			),
			// Add desired_budget field.
			'desired_budget'    => array(
				'label'         => __( 'Desired Budget', 'slgf' ),
				'type'          => 'number',
				'required'      => true,
				'sanitize_cb'   => 'sanitize_text_field',
				'default_value' => '',
				'attrs'         => array(
					'placeholder' => __( 'Enter your desired budget', 'slgf' ),
					'maxlength'   => 100,
				),
			),
			// Add message field.
			'message'           => array(
				'label'         => __( 'Message', 'slgf' ),
				'type'          => 'textarea',
				'required'      => true,
				'sanitize_cb'   => 'sanitize_textarea_field',
				'default_value' => '',
				'attrs'         => array(
					'placeholder' => __( 'Enter your message', 'slgf' ),
					'maxlength'   => 500,
					'cols'        => 80,
					'rows'        => 8,
				),
			),
			// Add current_date_time field.
			'current_date_time' => array(
				'label'         => __( 'Submit Date Time', 'slgf' ),
				'type'          => 'hidden',
				'required'      => false,
				'sanitize_cb'   => 'sanitize_text_field',
				'default_value' => '',
				'attrs'         => array(
					'maxlength' => 100,
				),
			),
		);
	}

	/**
	 * Get current user local date time.
	 *
	 * @since 0.0.1
	 *
	 * @param string $ip User IP address.
	 * @throws Exception If API request is error.
	 * @return string|null|WP_Error
	 */
	public function get_user_date_time( $ip = null ) {
		try {
			if ( is_null( $ip ) ) {
				$ip = $this->get_user_ip();
			}

			// Make HTTP request to API server.
			$response = wp_remote_get( add_query_arg( array( 'ip' => $ip ), $this->api_url ) );
			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}

			$body = wp_remote_retrieve_body( $response );

			// Check if API response is empty.
			if ( empty( $body ) ) {
				throw new Exception( __( 'API response is empty', 'slgf' ) );
			}

			$json = json_decode( $body, true );

			// Check if JSON data is valid.
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$error_msg = __( 'Error while decoding API response', 'slgf' );
				if ( function_exists( 'json_last_error_msg' ) ) {
					$error_msg .= ': ' . json_last_error_msg();
				}
				throw new Exception( $error_msg );
			}

			if ( ! empty( $json['data']['datetime']['date_time_txt'] ) ) {
				return $json['data']['datetime']['date_time_txt'];
			}

			throw new Exception( __( 'Unable to get user local time', 'slgf' ) );

		} catch ( Exception $e ) {
			return new WP_Error( 'get_user_date_time', $e->getMessage() );
		}
	}

	/**
	 * Get current user IP address.
	 *
	 * @since 0.0.1
	 * @return string
	 */
	private function get_user_ip() {
		$ip = '';
		foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = filter_var( wp_unslash( $_SERVER[ $key ] ), FILTER_VALIDATE_IP );
				break;
			}
		}

		return $ip;
	}
}
