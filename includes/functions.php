<?php
/**
 * Plugin functions.
 *
 * @package WC_Products_Quick_View
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wcqv_button' ) ) {
	/**
	 * Output the quick view button for the current product in the loop.
	 *
	 * @param array<string, string> $overrides Optional per-instance overrides for button_label,
	 *                                         button_style, button_icon, or button_icon_position.
	 *                                         Falls back to saved settings for any key not provided.
	 */
	function wcqv_button( $overrides = array() ) {
		global $post;

		$product_name         = $post ? get_the_title( $post->ID ) : '';
		$button_style         = ! empty( $overrides['button_style'] ) ? $overrides['button_style'] : WCQV_Settings::get( 'button_style' );
		$button_icon          = ! empty( $overrides['button_icon'] ) ? $overrides['button_icon'] : WCQV_Settings::get( 'button_icon' );
		$button_icon_position = ! empty( $overrides['button_icon_position'] ) ? $overrides['button_icon_position'] : WCQV_Settings::get( 'button_icon_position' );
		$button_label         = ! empty( $overrides['button_label'] ) ? $overrides['button_label'] : WCQV_Settings::get( 'button_label' );

		$element_classes = 'wcqv__trigger button';
		if ( 'plugin' === $button_style ) {
			$element_classes .= ' wcqv__trigger--plugin';
		}
		if ( wp_is_block_theme() ) {
			$element_classes .= ' wp-element-button';
		}

		/* translators: %s: product name (in quotes) */
		$aria_label = sprintf( __( 'Quick View: &ldquo;%s&rdquo;', 'wc-products-quick-view' ), $product_name );

		$args = array(
			'spinner_class'        => 'wcqv__trigger-spinner',
			'product_id'           => $post ? $post->ID : 0,
			'button_label'         => $button_label,
			'button_style'         => $button_style,
			'button_icon'          => $button_icon,
			'button_icon_position' => $button_icon_position,
			'element_classes'      => $element_classes,
			'wrapper_classes'      => 'wcqv__button-wrap' . ( wp_is_block_theme() ? ' wcqv__button-wrap--centered' : '' ),
			'aria_label'           => $aria_label,
			'icon_svg'             => WCQV_Settings::get_icon_svg( $button_icon ),
		);

		// Apply multilingual translation only when the label comes from settings,
		// not when it was explicitly set via a shortcode attribute or filter override.
		if ( empty( $overrides['button_label'] ) ) {
			// WPML translation.
			$args['button_label'] = apply_filters(
				'wpml_translate_single_string',
				$args['button_label'],
				'wc-products-quick-view',
				'button_label'
			);

			// Polylang translation.
			if ( function_exists( 'pll__' ) ) {
				$args['button_label'] = pll__( $args['button_label'] );
			}
		}

		/**
		 * Filters the arguments passed to the button template.
		 *
		 * @param array<string, mixed> $args Template arguments.
		 */
		$args = apply_filters( 'wcqv_button_template_args', $args );

		wc_get_template(
			'button.php',
			$args,
			'wc-products-quick-view/',
			WCQV_TEMPLATE_PATH
		);
	}
}

/**
 * Outputs the quick view button via shortcode.
 *
 * Usage: [wcqv_button] or [wcqv_button product_id="123" button_label="Preview" button_style="plugin" button_icon="eye" button_icon_position="after"]
 *
 * @param array<string, mixed> $atts Shortcode attributes.
 * @return string Button HTML.
 */
function wcqv_button_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'product_id'           => 0,
			'button_label'         => '',
			'button_style'         => '',
			'button_icon'          => '',
			'button_icon_position' => '',
		),
		$atts,
		'wcqv_button'
	);

	$product_id = absint( $atts['product_id'] );

	if ( $product_id ) {
		global $post;
		$post = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
	}

	$overrides = array_filter( array(
		'button_label'         => $atts['button_label'],
		'button_style'         => $atts['button_style'],
		'button_icon'          => $atts['button_icon'],
		'button_icon_position' => $atts['button_icon_position'],
	) );

	ob_start();
	wcqv_button( $overrides );
	$output = ob_get_clean();

	if ( $product_id ) {
		wp_reset_postdata();
	}

	return $output;
}
add_shortcode( 'wcqv_button', 'wcqv_button_shortcode' );

/**
 * Registers the button label string with WPML and Polylang on init so it
 * appears in their translation UIs without any manual configuration.
 */
add_action( 'init', 'wcqv_register_translatable_strings' );

function wcqv_register_translatable_strings() {
	$label = WCQV_Settings::get( 'button_label' );
	if ( ! $label ) {
		return;
	}
	// WPML String Translation.
	do_action( 'wpml_register_single_string', 'wc-products-quick-view', 'button_label', $label );
	// Polylang.
	if ( function_exists( 'pll_register_string' ) ) {
		pll_register_string( 'button_label', $label, __( 'WCQV – Product Quick View for WooCommerce', 'wc-products-quick-view' ) );
	}
}

/**
 * Returns the class list for a named plugin element.
 *
 * Use the wcqv_element_classes filter to add or remove classes for any element.
 *
 * @param string $element Element key: 'content', 'product', 'gallery', 'brief', 'close'.
 * @return string[] Sanitized, unique class list.
 */
function wcqv_get_classes( $element ) {
	$defaults = array(
		'content' => array( 'wcqv__content', 'single-product' ),
		'product' => array( 'wcqv__product', 'product' ),
		'gallery' => array( 'wcqv__gallery' ),
		'brief'   => array( 'wcqv__brief', 'summary', 'entry-summary' ),
		'close'   => array( 'wcqv__close' ),
	);

	$classes = isset( $defaults[ $element ] ) ? $defaults[ $element ] : array();

	/**
	 * Filters the class list for a named plugin element.
	 *
	 * @param string[] $classes Class list for the element.
	 * @param string   $element Element key.
	 */
	$classes = (array) apply_filters( 'wcqv_element_classes', $classes, $element );

	return array_map( 'sanitize_html_class', array_filter( array_unique( $classes ) ) );
}

/**
 * Registers the quick view button on the correct WooCommerce hook.
 *
 * Deferred to 'init' so compatibility files can add their filters before
 * hook/priority resolution. 'auto' defaults to woocommerce_after_shop_loop_item
 * (after add to cart); supported themes adjust via wcqv_auto_position_hook and
 * wcqv_auto_position_priority filters. 'none' skips registration.
 *
 * To prevent auto-registration: remove_action( 'init', 'wcqv_register_button_hook' )
 */
add_action( 'init', 'wcqv_register_button_hook' );

/**
 * Performs the add_action() call that places the quick view button in the loop.
 *
 * Hooked to 'init' — themes, child themes, and plugins can remove or re-add
 * this function to take full control of when and where the button is output.
 */
function wcqv_register_button_hook() {
	$wcqv_position = WCQV_Settings::get( 'button_position' );

	if ( 'none' === $wcqv_position ) {
		return;
	}

	if ( 'auto' === $wcqv_position ) {
		/**
		 * Filters the WooCommerce hook used when position is set to 'auto'.
		 *
		 * Compatibility files can return a different hook for specific themes.
		 *
		 * @param string $hook Default hook name.
		 */
		$wcqv_hook = apply_filters( 'wcqv_auto_position_hook', 'woocommerce_after_shop_loop_item' );

		/**
		 * Filters the priority used when position is set to 'auto'.
		 *
		 * Defaults to the saved button_priority setting (after add to cart).
		 * Compatibility files for supported themes return 9 to place the button
		 * just before WooCommerce's add-to-cart (priority 10).
		 *
		 * @param int $priority Default priority.
		 */
		$wcqv_priority = apply_filters( 'wcqv_auto_position_priority', absint( WCQV_Settings::get( 'button_priority' ) ) );
	} else {
		$wcqv_hook     = $wcqv_position;
		$wcqv_priority = absint( WCQV_Settings::get( 'button_priority' ) );
	}

	add_action( $wcqv_hook, 'wcqv_button', $wcqv_priority );
}
