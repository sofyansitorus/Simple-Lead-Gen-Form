<?php
/**
 * Simple Lead Gen Form Customer.
 *
 * @since   0.0.1
 * @package SLGF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Simple Lead Gen Form Customer post type class.
 *
 * @since 0.0.1
 */
class SLGF_Customer extends SLGF_Base_Post_Type {
	/**
	 * Parent plugin class.
	 *
	 * @var SLGF
	 * @since  0.0.1
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * Register Custom Post Types.
	 *
	 * See documentation in SLGF_Base_Post_Type, and in wp-includes/post.php.
	 *
	 * @since  0.0.1
	 *
	 * @param  SLGF $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Register this cpt.
		// First parameter should be an array with Singular, Plural, and Registered name.
		parent::__construct(
			array(
				esc_html__( 'Customer', 'slgf' ),
				esc_html__( 'Customers', 'slgf' ),
				'slgf-customer',
			),
			array(
				'supports'  => false,
				'menu_icon' => 'dashicons-businessman', // https://developer.wordpress.org/resource/dashicons/.
				'public'    => false,
			)
		);
	}

	/**
	 * Registers admin columns to display. Hooked in via SLGF_CPT_Base.
	 *
	 * @since  0.0.1
	 *
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array.
	 */
	public function columns( $columns ) {
		$columns = array_merge(
			$columns, array(
				'phone_number'      => __( 'Phone Number', 'slgf' ),
				'email_address'     => __( 'Email Address', 'slgf' ),
				'desired_budget'    => __( 'Desired Budget', 'slgf' ),
				'current_date_time' => __( 'Submit Date Time', 'slgf' ),
			)
		);
		unset( $columns['date'] );
		return $columns;
	}

	/**
	 * Handles admin column display. Hooked in via SLGF_CPT_Base.
	 *
	 * @since  0.0.1
	 *
	 * @param array   $column   Column currently being rendered.
	 * @param integer $post_id  ID of post to display column for.
	 */
	public function columns_display( $column, $post_id ) {
		switch ( $column ) {
			case 'current_date_time':
				echo esc_html( date( get_option( 'date_format' ) . ' H:i:s', get_post_meta( $post_id, $column, true ) ) );
				break;
			case 'phone_number':
			case 'email_address':
			case 'desired_budget':
				echo esc_html( get_post_meta( $post_id, $column, true ) );
				break;
		}
	}
}
