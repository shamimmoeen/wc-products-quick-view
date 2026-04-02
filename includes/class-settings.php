<?php
/**
 * Plugin settings.
 *
 * @package WC_Products_Quick_View
 */

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
				'button_label'         => __( 'Quick View', 'wc-products-quick-view' ),
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
	}
}
