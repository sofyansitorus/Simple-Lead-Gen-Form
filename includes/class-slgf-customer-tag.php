<?php
/**
 * Simple Lead Gen Form Customer Tag.
 *
 * @since   0.0.1
 * @package SLGF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Simple Lead Gen Form Customer Tag.
 *
 * @since 0.0.1
 */
class SLGF_Customer_Tag extends SLGF_Base_Taxonomy {
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
	 * Register Taxonomy.
	 *
	 * See documentation in Taxonomy_Core, and in wp-includes/taxonomy.php.
	 *
	 * @since  0.0.1
	 *
	 * @param  SLGF $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		parent::__construct(
			// Should be an array with Singular, Plural, and Registered name.
			array(
				__( 'Tag', 'slgf' ),
				__( 'Tags', 'slgf' ),
				'slgf-customer-tag',
			),
			// Register taxonomy arguments.
			array(
				'hierarchical' => false,
			),
			// Post types to attach to.
			array(
				'slgf-customer',
			)
		);
	}
}
