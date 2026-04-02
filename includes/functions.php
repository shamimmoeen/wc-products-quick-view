<?php
/**
 * Plugin functions.
 *
 * @package WC_Products_Quick_View
 */

if ( ! function_exists( 'wpqv_button' ) ) {
	/**
	 * Output the quick view button for the current product in the loop.
	 */
	function wpqv_button() {
		wc_get_template(
			'button.php',
			array(
				'button_class' => 'wc-quick-view',
				'spinner_class' => 'wpqv-button-spinner',
			),
			'wc-products-quick-view/',
			WPQV_TEMPLATE_PATH
		);
	}
}

add_action( 'woocommerce_after_shop_loop_item', 'wpqv_button', 15 );
