<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://github.com/rstmsn/lnd-for-wp/
 * @package           lnd-for-wp
 *
 * @wordpress-plugin
 * Plugin Name:       LND For WP
 * Plugin URI:        http://github.com/rstmsn/lnd-for-wp/
 * Description:       Manage and use your LND node.
 * Version:           0.1.1
 * Author:            RSTMSN
 * Author URI:        http://github.com/rstmsn/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lnd-for-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 0.1.0 and use SemVer - https://semver.org
 */
define( 'LND_FOR_WP_VERSION', '0.1.1' );

/**
 * The code that runs during plugin activation.
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-lnd-for-wp-activator.php';
	LND_For_WP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-lnd-for-wp-deactivator.php';
	LND_For_WP_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-lnd-for-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_LND_For_WP() {

	$plugin = new LND_For_WP();
	$plugin->run();

}
run_LND_For_WP();