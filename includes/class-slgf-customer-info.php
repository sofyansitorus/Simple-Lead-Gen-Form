<?php
/**
 * Simple Lead Gen Form Customer Info.
 *
 * @since   0.0.1
 * @package SLGF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Simple Lead Gen Form Customer Info class.
 *
 * @since 0.0.1
 */
class SLGF_Customer_Info extends SLGF_Base_Metabox {
	/**
	 * Parent plugin class.
	 *
	 * @var    SLGF
	 * @since  0.0.1
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.0.1
	 *
	 * @param  SLGF $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		parent::__construct(
			array(
				'id'       => 'slgf-customer-info',
				'title'    => __( 'Customer Info', 'slgf' ),
				'screen'   => 'slgf-customer',
				'priority' => 'high',
				'context'  => 'normal',
			)
		);
	}

	/**
	 * Get metabox fields.
	 *
	 * @since  0.1.0
	 * @return array Fields data array.
	 */
	protected function metabox_fields() {
		$fields = array();
		foreach ( $this->plugin->shortcode->fields() as $key => $field ) {
			switch ( $key ) {
				case 'full_name':
					$field['core_field'] = 'post_title';
					break;
				case 'message':
					$field['core_field'] = 'post_content';
					break;
				case 'current_date_time':
					$field['type']             = 'datetime-local';
					$field['value_input_cb']   = array( $this, 'value_input_current_date_time' );
					$field['value_presave_cb'] = array( $this, 'value_presave_current_date_time' );
					break;
			}
			$fields[ $key ] = $field;
		}
		return $fields;
	}

	/**
	 * Format value before rendered into the form callback.
	 *
	 * @since  0.1.0
	 * @param mixed $value Raw value.
	 * @return array Fields data array.
	 */
	public function value_input_current_date_time( $value ) {
		return date( 'Y-m-d\TH:i', $value );
	}

	/**
	 * Format value before saved into database.
	 *
	 * @since  0.1.0
	 * @param mixed $value Raw value.
	 * @return array Fields data array.
	 */
	public function value_presave_current_date_time( $value ) {
		return strtotime( $value );
	}
}
