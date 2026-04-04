<?php
/**
 * Plugin functions.
 *
 * @package WC_Products_Quick_View
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wpqv_button' ) ) {
	/**
	 * Output the quick view button for the current product in the loop.
	 */
	function wpqv_button() {
		global $post;

		$product_name    = $post ? get_the_title( $post->ID ) : '';
		$button_position = WPQV_Settings::get( 'button_position' );
		$button_style    = WPQV_Settings::get( 'button_style' );
		$button_icon     = WPQV_Settings::get( 'button_icon' );
		$is_overlay      = ( 'wpqv_overlay' === $button_position );

		$element_classes = 'wpqv__trigger button';
		if ( $is_overlay ) {
			$element_classes .= ' wpqv__trigger--overlay';
		}
		if ( 'theme' === $button_style ) {
			$element_classes .= ' wpqv__trigger--theme';
		}

		/* translators: %s: product name */
		$aria_label = sprintf( __( 'Quick View: %s', 'wc-products-quick-view' ), $product_name );

		$args = array(
			'button_class'         => 'wpqv__trigger',
			'spinner_class'        => 'wpqv__spinner wpqv__spinner--inline',
			'product_id'           => $post ? $post->ID : 0,
			'button_label'         => WPQV_Settings::get( 'button_label' ),
			'button_style'         => $button_style,
			'button_icon'          => $button_icon,
			'button_icon_position' => WPQV_Settings::get( 'button_icon_position' ),
			'button_position'      => $button_position,
			'product_name'         => $product_name,
			'is_overlay'           => $is_overlay,
			'element_classes'      => $element_classes,
			'aria_label'           => $aria_label,
			'icon_svg'             => WPQV_Settings::get_icon_svg( $button_icon ),
		);

		// WPML translation.
		$args['button_label'] = apply_filters( 'wpml_translate_single_string', $args['button_label'], 'wc-products-quick-view', 'button_label' );
		// Polylang translation.
		if ( function_exists( 'pll__' ) ) {
			$args['button_label'] = pll__( $args['button_label'] );
		}

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
 * Registers the button label string with WPML and Polylang on init so it
 * appears in their translation UIs without any manual configuration.
 */
add_action( 'init', 'wpqv_register_translatable_strings' );

function wpqv_register_translatable_strings() {
	$label = WPQV_Settings::get( 'button_label' );
	if ( ! $label ) {
		return;
	}
	// WPML String Translation.
	do_action( 'wpml_register_single_string', 'wc-products-quick-view', 'button_label', $label );
	// Polylang.
	if ( function_exists( 'pll_register_string' ) ) {
		pll_register_string( 'button_label', $label, __( 'WPQV – Quick View for WooCommerce', 'wc-products-quick-view' ) );
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
