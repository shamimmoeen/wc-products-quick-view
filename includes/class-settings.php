<?php
/**
 * Plugin settings.
 *
 * @package WC_Products_Quick_View
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCQV_Settings' ) ) {

	/**
	 * Static settings helper.
	 *
	 * All settings are stored under the single option key 'wcqv_settings' as a
	 * serialised array. Use WCQV_Settings::get() on the frontend and
	 * WCQV_Settings::all() wherever you need the full set at once.
	 */
	class WCQV_Settings {

		/**
		 * WordPress option key.
		 *
		 * @var string
		 */
		const OPTION_KEY = 'wcqv_settings';

		/**
		 * Returns the default values for every setting.
		 *
		 * Developers can adjust defaults via the wcqv_settings_defaults filter.
		 *
		 * @return array<string, mixed>
		 */
		public static function defaults() {
			$defaults = array(
				// Button.
				'button_position'      => 'auto',
				'button_priority'      => 15,
				'button_label'         => 'Quick View',
				'button_style'         => 'theme',
				'button_icon'          => 'none',
				'button_icon_position' => 'before',
				// Modal.
				'enable_view_product'  => 1,
				'dialog_width'         => 1050,
				'dialog_max_height'    => 85,
				'gallery_column_width' => 50,
			);

			/**
			 * Filters the default settings.
			 *
			 * @param array<string, mixed> $defaults Default setting values.
			 */
			return apply_filters( 'wcqv_settings_defaults', $defaults );
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
				// Heroicons v2 Outline (MIT). https://heroicons.com
				'eye'    => array(
					'label' => __( 'Eye', 'wc-products-quick-view' ),
					'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2.03555 12.3224C1.96647 12.1151 1.9664 11.8907 2.03536 11.6834C3.42372 7.50972 7.36079 4.5 12.0008 4.5C16.6387 4.5 20.5742 7.50692 21.9643 11.6776C22.0334 11.8849 22.0335 12.1093 21.9645 12.3166C20.5761 16.4903 16.6391 19.5 11.9991 19.5C7.36119 19.5 3.42564 16.4931 2.03555 12.3224Z"/><path d="M15 12C15 13.6569 13.6569 15 12 15C10.3431 15 9 13.6569 9 12C9 10.3431 10.3431 9 12 9C13.6569 9 15 10.3431 15 12Z"/></svg>',
				),
				'search' => array(
					'label' => __( 'Search / Magnify', 'wc-products-quick-view' ),
					'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 21L15.8033 15.8033M15.8033 15.8033C17.1605 14.4461 18 12.5711 18 10.5C18 6.35786 14.6421 3 10.5 3C6.35786 3 3 6.35786 3 10.5C3 14.6421 6.35786 18 10.5 18C12.5711 18 14.4461 17.1605 15.8033 15.8033Z"/></svg>',
				),
				'zoom'   => array(
					'label' => __( 'Zoom in', 'wc-products-quick-view' ),
					'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 21L15.8033 15.8033M15.8033 15.8033C17.1605 14.4461 18 12.5711 18 10.5C18 6.35786 14.6421 3 10.5 3C6.35786 3 3 6.35786 3 10.5C3 14.6421 6.35786 18 10.5 18C12.5711 18 14.4461 17.1605 15.8033 15.8033ZM10.5 7.5V13.5M13.5 10.5H7.5"/></svg>',
				),
			);

			/**
			 * Filters the available button icon options.
			 *
			 * @param array<string, array{label: string, svg: string}> $icons Icon options keyed by icon slug.
			 */
			return apply_filters( 'wcqv_icon_options', $icons );
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
