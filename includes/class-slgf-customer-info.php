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
				'id'       => 'dqwqwrqwr',
				'title'    => 'slgf-customer',
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
					$field['format_value_cb']  = array( $this, 'format_value_current_date_time' );
					$field['presave_value_cb'] = array( $this, 'presave_value_current_date_time' );
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
	public function format_value_current_date_time( $value ) {
		return date( 'Y-m-d\TH:i', $value );
	}

	/**
	 * Format value before saved into database.
	 *
	 * @since  0.1.0
	 * @param mixed $value Raw value.
	 * @return array Fields data array.
	 */
	public function presave_value_current_date_time( $value ) {
		return strtotime( $value );
	}
}
