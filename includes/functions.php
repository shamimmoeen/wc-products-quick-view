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
		global $post;

		$product_name = $post ? get_the_title( $post->ID ) : '';

		$args = array(
			'button_class'         => 'wpqv__trigger',
			'spinner_class'        => 'wpqv__spinner wpqv__spinner--inline',
			'button_label'         => WPQV_Settings::get( 'button_label' ),
			'button_style'         => WPQV_Settings::get( 'button_style' ),
			'button_icon'          => WPQV_Settings::get( 'button_icon' ),
			'button_icon_position' => WPQV_Settings::get( 'button_icon_position' ),
			'button_position'      => WPQV_Settings::get( 'button_position' ),
			'product_name'         => $product_name,
		);

		/**
		 * Filters the arguments passed to the button template.
		 *
		 * @param array<string, mixed> $args Template arguments.
		 */
		$args = apply_filters( 'wpqv_button_template_args', $args );

		wc_get_template(
			'button.php',
			$args,
			'wc-products-quick-view/',
			WPQV_TEMPLATE_PATH
		);
	}
}

/**
 * Registers the quick view button on the correct WooCommerce hook based on
 * the saved position setting.
 *
 * 'wpqv_overlay' is not a real WC hook — it means the button is placed via
 * woocommerce_before_shop_loop_item_title with overlay CSS positioning.
 */
$wpqv_position = WPQV_Settings::get( 'button_position' );
$wpqv_priority = absint( WPQV_Settings::get( 'button_priority' ) );
$wpqv_hook     = ( 'wpqv_overlay' === $wpqv_position )
	? 'woocommerce_before_shop_loop_item_title'
	: $wpqv_position;

add_action( $wpqv_hook, 'wpqv_button', $wpqv_priority );
