<?php
/**
 * Simple Lead Gen Form Ajax.
 *
 * @since   0.0.1
 * @package SLGF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Simple Lead Gen Form Ajax.
 *
 * @since 0.0.1
 */
class SLGF_Ajax {

	/**
	 * Nonce input name.
	 *
	 * @since    0.0.1
	 * @var string
	 */
	private $nonce_input = '_slgf_ajax_nonce';

	/**
	 * Nonce action name.
	 *
	 * @since    0.0.1
	 * @var string
	 */
	private $nonce_action = 'slgf_ajax_nonce';

	/**
	 * HTTP Request $_POST data.
	 *
	 * @since    0.0.1
	 * @var array
	 */
	private $data_post;

	/**
	 * HTTP Request $_GET data.
	 *
	 * @since    0.0.1
	 * @var array
	 */
	private $data_get;

	/**
	 * Response data
	 *
	 * @since    0.0.1
	 * @var array
	 */
	private $response_data = array();

	/**
	 * HTTP POST request data
	 *
	 * @since 0.0.1
	 * @param string $key Request data key.
	 * @param mixed  $sanitization_cb Callback to sanitize input data.
	 * @return mixed
	 */
	public function data_post( $key, $sanitization_cb = 'sanitize_text_field' ) {
		$value = isset( $this->data_post[ $key ] ) ? $this->data_post[ $key ] : null;

		if ( $sanitization_cb && is_callable( $sanitization_cb ) ) {
			$value = call_user_func( $sanitization_cb, $value );
		}

		return $value;
	}

	/**
	 * HTTP GET request data
	 *
	 * @since 0.0.1
	 * @param string $key Request data key.
	 * @param mixed  $sanitization_cb Callback to sanitize input data.
	 * @return mixed
	 */
	public function data_get( $key, $sanitization_cb = 'sanitize_text_field' ) {
		$value = isset( $this->data_get[ $key ] ) ? $this->data_get[ $key ] : null;

		if ( $sanitization_cb && is_callable( $sanitization_cb ) ) {
			$value = call_user_func( $sanitization_cb, $value );
		}

		return $value;
	}

	/**
	 * Set AJAX response error.
	 *
	 * @since    0.0.1
	 * @param string $msg Response error message.
	 * @param array  $data Response data.
	 * @param int    $code Response code.
	 */
	public function send_response_error( $msg = '', $data = array(), $code = 400 ) {
		if ( $data && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$this->set_response_data( $key, $value );
			}
		}
		$response = array(
			'success' => 0,
			'msg'     => $msg,
			'data'    => $this->response_data,
		);
		status_header( $code );
		wp_send_json( $response );
	}

	/**
	 * Set AJAX response success.
	 *
	 * @since    0.0.1
	 * @param string $msg Response success message.
	 * @param array  $data Response data.
	 */
	public function send_response_success( $msg = '', $data = array() ) {
		if ( $data && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$this->set_response_data( $key, $value );
			}
		}
		$response = array(
			'success' => 1,
			'msg'     => $msg,
			'data'    => $this->response_data,
		);
		wp_send_json( $response );
	}

	/**
	 * Set AJAX response data.
	 *
	 * @since    0.0.1
	 * @param array $data Response data.
	 */
	public function send_response( $data = array() ) {
		if ( $data && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$this->set_response_data( $key, $value );
			}
		}
		wp_send_json( $this->response_data );
	}

	/**
	 * Set AJAX response data.
	 *
	 * @since    0.0.1
	 * @param string $key Response data key.
	 * @param mixed  $value Response data value.
	 */
	public function set_response_data( $key, $value ) {
		$this->response_data[ $key ] = $value;
	}

	/**
	 * Set nonce input.
	 *
	 * @since  0.0.1
	 * @param string $nonce_input Nonce input name.
	 */
	public function set_nonce_input( $nonce_input ) {
		$this->nonce_input = $nonce_input;
	}

	/**
	 * Get nonce input.
	 *
	 * @since  0.0.1
	 * @return string
	 */
	public function nonce_input() {
		return $this->nonce_input;
	}

	/**
	 * Set nonce action.
	 *
	 * @since  0.0.1
	 * @param string $nonce_action Nonce action name.
	 */
	public function set_nonce_action( $nonce_action ) {
		$this->nonce_action = $nonce_action;
	}

	/**
	 * Get nonce action.
	 *
	 * @since  0.0.1
	 * @return string
	 */
	public function nonce_action() {
		return $this->nonce_action;
	}

	/**
	 * Print hidden nonce field.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	public function nonce_field() {
		wp_nonce_field( $this->nonce_action, $this->nonce_input );
	}

	/**
	 * Verify nonce.
	 *
	 * @since 0.0.1
	 */
	public function verify_nonce() {
		if ( defined( 'DOING_AJAX' )
		&& DOING_AJAX
		&& isset( $_REQUEST[ $this->nonce_input ] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $this->nonce_input ] ) ), $this->nonce_action ) ) {
			$this->data_post = wp_unslash( $_POST );
			$this->data_get  = wp_unslash( $_GET );
			return;
		}

		$this->send_response_error( __( 'Invalid Request', 'slgf' ) );
	}
}
