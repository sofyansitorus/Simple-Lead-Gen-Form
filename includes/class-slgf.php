<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since   0.0.1
 * @package SLGF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main initiation class.
 *
 * @since  0.0.1
 */
final class SLGF {

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $basename = '';

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.0.1
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    SLGF
	 * @since  0.0.1
	 */
	protected static $single_instance = null;

	/**
	 * Plugin state is booted.
	 *
	 * @since0.0.1
	 * @var bool
	 */
	protected $is_booted = false;

	/**
	 * Instance of SLGF_Customer
	 *
	 * @since0.0.1
	 * @var SLGF_Customer
	 */
	protected $customer;

	/**
	 * Instance of SLGF_Shortcode
	 *
	 * @since0.0.1
	 * @var SLGF_Shortcode
	 */
	protected $shortcode;

	/**
	 * Instance of SLGF_Customer_Meta
	 *
	 * @since0.0.1
	 * @var SLGF_Customer_Meta
	 */
	protected $customer_meta;

	/**
	 * Instance of SLGF_Customer_Category
	 *
	 * @since0.0.1
	 * @var SLGF_Customer_Category
	 */
	protected $customer_category;

	/**
	 * Instance of SLGF_Customer_Tag
	 *
	 * @since0.0.1
	 * @var SLGF_Customer_Tag
	 */
	protected $customer_tag;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.0.1
	 * @return  SLGF A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.0.1
	 */
	protected function __construct() {
		$this->basename = plugin_basename( SLGF_FILE );
		$this->url      = plugin_dir_url( SLGF_FILE );
		$this->path     = plugin_dir_path( SLGF_FILE );
	}

	/**
	 * Boot the plugin.
	 * This method is the entry point of the plugin.
	 *
	 * @since  0.0.1
	 */
	public function boot() {

		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() || $this->is_booted ) {
			return;
		}

		// Load plugin classes.
		$this->load_plugin_classes();

		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 0 );

		// Set plugin state is booted.
		$this->is_booted = true;
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.0.1
	 */
	public function load_plugin_classes() {
		$this->customer          = new SLGF_Customer( $this );
		$this->customer_category = new SLGF_Customer_Category( $this );
		$this->customer_tag      = new SLGF_Customer_Tag( $this );
		$this->customer_meta     = new SLGF_Customer_Info( $this );
		$this->shortcode         = new SLGF_Shortcode( $this );
	}

	/**
	 * Activate the plugin.
	 *
	 * @since  0.0.1
	 */
	public function activation_hook() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.0.1
	 */
	public function deactivation_hook() {
		// Add deactivation cleanup functionality here.
		flush_rewrite_rules();
	}

	/**
	 * Init hooks
	 *
	 * @since  0.0.1
	 */
	public function load_plugin_textdomain() {
		// Load translated strings for plugin.
		load_plugin_textdomain( 'slgf', false, dirname( $this->basename ) . '/languages/' );
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.0.1
	 *
	 * @return boolean True if requirements met, false if not.
	 */
	public function check_requirements() {
		// Bail early if plugin meets requirements.
		if ( $this->is_meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		// Deactivate our plugin.
		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  0.0.1
	 */
	public function deactivate_me() {
		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that plugin requirements are met.
	 *
	 * @since  0.0.1
	 *
	 * @return boolean True if requirements are met.
	 */
	public function is_meets_requirements() {
		// Do checks for required classes / functions or similar.
		// Add detailed messages to $this->activation_errors array.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since  0.0.1
	 */
	public function requirements_not_met_notice() {

		// Compile default message.
		// translators: %s URL to plugin managment admin page.
		$default_message = sprintf( __( 'Simple Lead Gen Form is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'slgf' ), admin_url( 'plugins.php' ) );

		// Default details to null.
		$details = null;

		// Add details if any exist.
		if ( $this->activation_errors && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// Output errors.
		?>
		<div id="message" class="error">
			<p><?php echo wp_kses_post( $default_message ); ?></p>
			<?php echo wp_kses_post( $details ); ?>
		</div>
		<?php
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $field Field to get.
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return SLGF_VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'customer':
			case 'shortcode':
			case 'customer_meta':
			case 'customer_category':
			case 'customer_tag':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $filename Name of the file to be included.
	 * @return boolean          Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once $file;
		}
		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		return trailingslashit( SLGF_PATH ) . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		return trailingslashit( SLGF_URL ) . $path;
	}
}
