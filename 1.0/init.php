<?php
/**
 * Plugin Name: WC Products Quick View
 * Description: A plugin to get preview of woocommerce products from products loop.
 * Version: 1.0
 * Author: Shamim Al Mamun
 * Author URI: https://github.com/shamimmoeen
 * Text Domain: wpqv
 * Domain Path: /languages/
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume 
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @since     1.0
 * @copyright Copyright (c) 2015, Shamim Al Mamun
 * @author    Shamim Al Mamun
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin directory
if ( !function_exists( 'WC_Products_Quick_View_Path' ) ) {
	function WC_Products_Quick_View_Path() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}
}

define( 'PATH', WC_Products_Quick_View_Path() );
define( 'TEMPLATE_PATH', plugin_dir_path( __FILE__ ) . 'templates/' );

if ( !function_exists( 'WC_Products_Quick_View_Constructor' ) ) {
	function WC_Products_Quick_View_Constructor() {
		if ( !class_exists( 'WooCommerce' ) ) {
			return;
		}

		// load language file
		load_plugin_textdomain( 'wpqv', false, plugin_dir_path( __FILE__ ) . 'languages/' );

		require_once 'functions.php';
		require_once 'class.wc-quick-view.php';
	}
}
add_action( 'plugins_loaded', 'WC_Products_Quick_View_Constructor' );
