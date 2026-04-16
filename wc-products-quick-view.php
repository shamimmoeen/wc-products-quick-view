<?php
/**
 * Plugin Name:       WCQV – Product Quick View for WooCommerce
 * Description:       Adds a Quick View button to WooCommerce product listings so customers can preview product details in a modal without leaving the page.
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

define( 'WCQV_VERSION', '2.0.0' );
define( 'WCQV_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WCQV_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WCQV_TEMPLATE_PATH', plugin_dir_path( __FILE__ ) . 'templates/' );

add_action( 'plugins_loaded', 'wcqv_init' );

function wcqv_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-settings.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/product-hooks.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-quick-view.php';

	// Load theme/plugin compatibility files.
	foreach ( glob( plugin_dir_path( __FILE__ ) . 'includes/compatibility/*.php' ) as $compat_file ) {
		require_once $compat_file;
	}

	if ( is_admin() ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin.php';
		WCQV_Admin::get_instance();
	}

	WCQV_Quick_View::get_instance();
}
