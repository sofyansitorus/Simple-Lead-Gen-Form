<?php
/**
 * Plugin Name: Simple Lead Gen Form
 * Plugin URI:  https://github.com/sofyansitorus/Simple-Lead-Gen-Form
 * Description: A Simple Lead Gen Form Plugin for WordPress
 * Version:     0.0.1
 * Author:      Sofyan Sitorus
 * Author URI:  https://github.com/sofyansitorus
 * Donate link: https://github.com/sofyansitorus/Simple-Lead-Gen-Form
 * License:     GPLv2
 * Text Domain: slgf
 * Domain Path: /languages
 *
 * @link    https://github.com/sofyansitorus/Simple-Lead-Gen-Form
 *
 * @package SLGF
 * @version 0.0.1s
 */

/**
 * Copyright (c) 2018 Sofyan Sitorus (email : sofyansitorus@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Defines plugin named constants.
define( 'SLGF_FILE', __FILE__ );
define( 'SLGF_PATH', plugin_dir_path( SLGF_FILE ) );
define( 'SLGF_URL', plugin_dir_url( SLGF_FILE ) );
define( 'SLGF_SLUG', 'slgf' );
define( 'SLGF_VERSION', '0.0.1' );

/**
 * Autoloads files with classes when needed.
 *
 * @since  0.0.1
 * @param  string $class_name Name of the class being requested.
 */
function slgf_autoload_classes( $class_name ) {

	// If our class doesn't have our prefix, don't load it.
	if ( 0 !== strpos( $class_name, 'SLGF' ) ) {
		return;
	}

	// Set up our filename.
	$filename = strtolower( str_replace( '_', '-', $class_name ) );

	// Include our file.
	include SLGF_PATH . 'includes/class-' . $filename . '.php';
}
spl_autoload_register( 'slgf_autoload_classes' );

/**
 * Grab the SLGF object and return it.
 * Wrapper for SLGF::get_instance().
 *
 * @since  0.0.1
 * @return SLGF  Singleton instance of plugin class.
 */
function slgf() {
	return SLGF::get_instance();
}

add_action( 'plugins_loaded', array( slgf(), 'boot' ) );

// Activation and deactivation.
register_activation_hook( __FILE__, array( slgf(), 'activation_hook' ) );
register_deactivation_hook( __FILE__, array( slgf(), 'deactivation_hook' ) );
