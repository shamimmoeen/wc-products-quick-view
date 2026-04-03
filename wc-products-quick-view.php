<?php
/**
 * Plugin Name:       WPQV – Quick View for WooCommerce
 * Description:       Adds a Quick View button to the WooCommerce products loop so customers can preview product details in a modal without leaving the page.
 * Version:           2.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Mainul Hassan
 * Author URI:        https://mainulhassan.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-products-quick-view
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPQV_VERSION', '2.0.0' );
define( 'WPQV_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WPQV_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPQV_TEMPLATE_PATH', plugin_dir_path( __FILE__ ) . 'templates/' );

add_action( 'plugins_loaded', 'wpqv_init' );

function wpqv_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-settings.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-quick-view.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin.php';

	WPQV_Admin::get_instance();
	WPQV_Quick_View::get_instance();
}
