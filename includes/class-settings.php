<?php
/**
 * Plugin settings.
 *
 * @package WC_Products_Quick_View
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPQV_Settings' ) ) {

	/**
	 * Static settings helper.
	 *
	 * All settings are stored under the single option key 'wpqv_settings' as a
	 * serialised array. Use WPQV_Settings::get() on the frontend and
	 * WPQV_Settings::all() wherever you need the full set at once.
	 */
	class WPQV_Settings {

		/**
		 * WordPress option key.
		 *
		 * @var string
		 */
		const OPTION_KEY = 'wpqv_settings';

		/**
		 * Returns the default values for every setting.
		 *
		 * Developers can adjust defaults via the wpqv_settings_defaults filter.
		 *
		 * @return array<string, mixed>
		 */
		public static function defaults() {
			$defaults = array(
				// Button.
				'button_position'      => 'woocommerce_after_shop_loop_item',
				'button_priority'      => 15,
				'button_label'         => 'Quick View',
				'button_style'         => 'default',
				'button_icon'          => 'none',
				'button_icon_position' => 'before',
				// Gallery.
				'enable_slider'        => 1,
				'enable_magnify'       => 0,
			);

			/**
			 * Filters the default settings.
			 *
			 * @param array<string, mixed> $defaults Default setting values.
			 */
			return apply_filters( 'wpqv_settings_defaults', $defaults );
		}

		/**
		 * Returns the full merged settings array (saved values + defaults).
		 *
		 * @return array<string, mixed>
		 */
		public static function all() {
			$saved = get_option( self::OPTION_KEY, array() );
			return wp_parse_args( $saved, self::defaults() );
		}

		/**
		 * Returns a single setting value.
		 *
		 * @param string $key Setting key.
		 * @return mixed
		 */
		public static function get( $key ) {
			$all = self::all();
			return isset( $all[ $key ] ) ? $all[ $key ] : null;
		}

		/**
		 * Returns the icon options array used for both the admin field renderer and
		 * the button template helper.
		 *
		 * Each entry has a 'label' string and an optional 'svg' string (safe,
		 * hardcoded inline SVG — not user input).
		 *
		 * @return array<string, array{label: string, svg: string}>
		 */
		public static function get_icon_options() {
			$icons = array(
				'none'   => array(
					'label' => __( 'None', 'wc-products-quick-view' ),
					'svg'   => '',
				),
				'eye'    => array(
					'label' => __( 'Eye', 'wc-products-quick-view' ),
					'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>',
				),
				'search' => array(
					'label' => __( 'Search / Magnify', 'wc-products-quick-view' ),
					'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>',
				),
				'zoom'   => array(
					'label' => __( 'Zoom in', 'wc-products-quick-view' ),
					'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14zm2.5-4h-2v2H9v-2H7V9h2V7h1v2h2v1z"/></svg>',
				),
			);

			/**
			 * Filters the available button icon options.
			 *
			 * @param array<string, array{label: string, svg: string}> $icons Icon options keyed by icon slug.
			 */
			return apply_filters( 'wpqv_icon_options', $icons );
		}

		/**
		 * Returns the inline SVG markup for a given icon key.
		 *
		 * Used by the button template to render the selected icon.
		 *
		 * @param string $icon_key Icon key (none/eye/search/zoom).
		 * @return string SVG markup or empty string.
		 */
		public static function get_icon_svg( $icon_key ) {
			$options = self::get_icon_options();
			if ( isset( $options[ $icon_key ] ) ) {
				return $options[ $icon_key ]['svg'];
			}
			return '';
		}
	}
}
