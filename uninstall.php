<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @link       http://github.com/rstmsn/lnd-for-wp
 * @since      0.1.0
 *
 * @package    LND_For_WP
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function lnd_for_wp_uninstall(){
	delete_option('lnd-hostname');
	delete_option('lnd-macaroon');
	delete_option('lnd-macaroon-name');
	delete_option('lnd-ssl-warn');
	delete_option('lnd-conn-timeout');
	delete_option('lnd-hide-config');
	delete_option('lnd-tls-cert-name');
	delete_option('lnd-force-ssl');
}

lnd_for_wp_uninstall();
