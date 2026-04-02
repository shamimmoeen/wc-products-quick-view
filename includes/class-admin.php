<?php
/**
 * Admin settings page.
 *
 * @package WC_Products_Quick_View
 */

if ( ! class_exists( 'WPQV_Admin' ) ) {

	/**
	 * Registers the Quick View settings page under the WooCommerce menu.
	 */
	class WPQV_Admin {

		/**
		 * Single instance of the class.
		 *
		 * @var WPQV_Admin|null
		 */
		private static $instance = null;

		/**
		 * Returns the single instance.
		 *
		 * @return WPQV_Admin
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/**
		 * Adds the settings page under WooCommerce.
		 */
		public function add_menu_page() {
			add_submenu_page(
				'woocommerce',
				__( 'Quick View Settings', 'wc-products-quick-view' ),
				__( 'Quick View', 'wc-products-quick-view' ),
				'manage_options',
				'wpqv-settings',
				array( $this, 'render_page' )
			);
		}

		/**
		 * Enqueues admin stylesheet on the plugin's own page only.
		 *
		 * @param string $hook Current admin page hook.
		 */
		public function enqueue_assets( $hook ) {
			if ( 'woocommerce_page_wpqv-settings' !== $hook ) {
				return;
			}
			wp_enqueue_style(
				'wpqv-admin',
				WPQV_URL . '/assets/css/admin.css',
				array(),
				WPQV_VERSION
			);
		}

		/**
		 * Registers all settings, sections, and fields via the WordPress Settings API.
		 */
		public function register_settings() {
			register_setting(
				'wpqv_settings_group',
				WPQV_Settings::OPTION_KEY,
				array(
					'sanitize_callback' => array( $this, 'sanitize_settings' ),
				)
			);

			// ----------------------------------------------------------------
			// Section: Button
			// ----------------------------------------------------------------
			add_settings_section(
				'wpqv_section_button',
				__( 'Button', 'wc-products-quick-view' ),
				'__return_false',
				'wpqv-settings'
			);

			add_settings_field(
				'button_position',
				__( 'Position', 'wc-products-quick-view' ),
				array( $this, 'field_button_position' ),
				'wpqv-settings',
				'wpqv_section_button'
			);

			add_settings_field(
				'button_priority',
				__( 'Hook priority', 'wc-products-quick-view' ),
				array( $this, 'field_button_priority' ),
				'wpqv-settings',
				'wpqv_section_button'
			);

			add_settings_field(
				'button_label',
				__( 'Button label', 'wc-products-quick-view' ),
				array( $this, 'field_button_label' ),
				'wpqv-settings',
				'wpqv_section_button'
			);

			add_settings_field(
				'button_style',
				__( 'Button style', 'wc-products-quick-view' ),
				array( $this, 'field_button_style' ),
				'wpqv-settings',
				'wpqv_section_button'
			);

			add_settings_field(
				'button_icon',
				__( 'Icon', 'wc-products-quick-view' ),
				array( $this, 'field_button_icon' ),
				'wpqv-settings',
				'wpqv_section_button'
			);

			add_settings_field(
				'button_icon_position',
				__( 'Icon position', 'wc-products-quick-view' ),
				array( $this, 'field_button_icon_position' ),
				'wpqv-settings',
				'wpqv_section_button'
			);

			// ----------------------------------------------------------------
			// Section: Gallery
			// ----------------------------------------------------------------
			add_settings_section(
				'wpqv_section_gallery',
				__( 'Gallery', 'wc-products-quick-view' ),
				'__return_false',
				'wpqv-settings'
			);

			add_settings_field(
				'enable_slider',
				__( 'Image slider', 'wc-products-quick-view' ),
				array( $this, 'field_enable_slider' ),
				'wpqv-settings',
				'wpqv_section_gallery'
			);

			add_settings_field(
				'enable_magnify',
				__( 'Hover magnify', 'wc-products-quick-view' ),
				array( $this, 'field_enable_magnify' ),
				'wpqv-settings',
				'wpqv_section_gallery'
			);
		}

		// ====================================================================
		// Field renderers
		// ====================================================================

		/**
		 * Renders the button position select field.
		 */
		public function field_button_position() {
			$value = WPQV_Settings::get( 'button_position' );
			$options = array(
				'woocommerce_before_shop_loop_item_title' => __( 'Before title (after image)', 'wc-products-quick-view' ),
				'woocommerce_after_shop_loop_item_title'  => __( 'After title', 'wc-products-quick-view' ),
				'woocommerce_after_shop_loop_item'        => __( 'After add to cart button', 'wc-products-quick-view' ),
				'wpqv_overlay'                            => __( 'Over product image (icon overlay)', 'wc-products-quick-view' ),
			);
			echo '<select name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_position]" id="wpqv_button_position">';
			foreach ( $options as $key => $label ) {
				echo '<option value="' . esc_attr( $key ) . '"' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
			echo '<p class="description">' . esc_html__( 'Where the Quick View button appears on product cards.', 'wc-products-quick-view' ) . '</p>';
		}

		/**
		 * Renders the hook priority number field.
		 */
		public function field_button_priority() {
			$value = absint( WPQV_Settings::get( 'button_priority' ) );
			echo '<input type="number" min="1" max="99" step="1"
				name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_priority]"
				id="wpqv_button_priority"
				value="' . esc_attr( $value ) . '"
				class="small-text">';
			echo '<p class="description">' . esc_html__( 'Lower numbers run earlier; adjust to place the button among other elements at the same hook.', 'wc-products-quick-view' ) . '</p>';
		}

		/**
		 * Renders the button label text field.
		 */
		public function field_button_label() {
			$value = WPQV_Settings::get( 'button_label' );
			echo '<input type="text"
				name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_label]"
				id="wpqv_button_label"
				value="' . esc_attr( $value ) . '"
				class="regular-text">';
		}

		/**
		 * Renders the button style radio field.
		 */
		public function field_button_style() {
			$value = WPQV_Settings::get( 'button_style' );
			$options = array(
				'default' => __( 'Default (plugin-styled)', 'wc-products-quick-view' ),
				'theme'   => __( 'Inherit from theme', 'wc-products-quick-view' ),
			);
			foreach ( $options as $key => $label ) {
				echo '<label style="display:block;margin-bottom:6px;">';
				echo '<input type="radio"
					name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_style]"
					value="' . esc_attr( $key ) . '"'
					. checked( $value, $key, false ) . '> ';
				echo esc_html( $label );
				echo '</label>';
			}
			echo '<p class="description">' . esc_html__( '"Inherit from theme" removes plugin button styles so your theme\'s button design applies.', 'wc-products-quick-view' ) . '</p>';
		}

		/**
		 * Renders the icon radio field with inline SVG previews.
		 */
		public function field_button_icon() {
			$value = WPQV_Settings::get( 'button_icon' );
			$icons = self::get_icon_options();
			foreach ( $icons as $key => $data ) {
				echo '<label class="wpqv-admin-icon-option">';
				echo '<input type="radio"
					name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_icon]"
					value="' . esc_attr( $key ) . '"'
					. checked( $value, $key, false ) . '> ';
				if ( ! empty( $data['svg'] ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVGs are hardcoded in this file, not user input.
					echo $data['svg'];
				}
				echo ' ' . esc_html( $data['label'] );
				echo '</label>';
			}
		}

		/**
		 * Renders the icon position radio field.
		 */
		public function field_button_icon_position() {
			$value = WPQV_Settings::get( 'button_icon_position' );
			$options = array(
				'before' => __( 'Before label', 'wc-products-quick-view' ),
				'after'  => __( 'After label', 'wc-products-quick-view' ),
			);
			foreach ( $options as $key => $label ) {
				echo '<label style="margin-right:16px;">';
				echo '<input type="radio"
					name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_icon_position]"
					value="' . esc_attr( $key ) . '"'
					. checked( $value, $key, false ) . '> ';
				echo esc_html( $label );
				echo '</label>';
			}
		}

		/**
		 * Renders the enable slider checkbox.
		 */
		public function field_enable_slider() {
			$value = WPQV_Settings::get( 'enable_slider' );
			echo '<label>';
			echo '<input type="checkbox"
				name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[enable_slider]"
				id="wpqv_enable_slider"
				value="1"' . checked( 1, $value, false ) . '>';
			echo ' ' . esc_html__( 'Enable image slider in the modal (WooCommerce flexslider)', 'wc-products-quick-view' );
			echo '</label>';
		}

		/**
		 * Renders the enable magnify checkbox.
		 */
		public function field_enable_magnify() {
			$value = WPQV_Settings::get( 'enable_magnify' );
			echo '<label>';
			echo '<input type="checkbox"
				name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[enable_magnify]"
				id="wpqv_enable_magnify"
				value="1"' . checked( 1, $value, false ) . '>';
			echo ' ' . esc_html__( 'Enable hover magnify on product images', 'wc-products-quick-view' );
			echo '</label>';
		}

		// ====================================================================
		// Sanitization
		// ====================================================================

		/**
		 * Sanitizes the settings array before saving.
		 *
		 * @param array<string, mixed> $input Raw POST values.
		 * @return array<string, mixed>
		 */
		public function sanitize_settings( $input ) {
			$clean    = array();
			$defaults = WPQV_Settings::defaults();

			$valid_positions = array(
				'woocommerce_before_shop_loop_item_title',
				'woocommerce_after_shop_loop_item_title',
				'woocommerce_after_shop_loop_item',
				'wpqv_overlay',
			);

			$clean['button_position'] = isset( $input['button_position'] ) && in_array( $input['button_position'], $valid_positions, true )
				? $input['button_position']
				: $defaults['button_position'];

			$clean['button_priority'] = isset( $input['button_priority'] )
				? min( 99, max( 1, absint( $input['button_priority'] ) ) )
				: $defaults['button_priority'];

			$clean['button_label'] = isset( $input['button_label'] )
				? sanitize_text_field( $input['button_label'] )
				: $defaults['button_label'];

			$valid_styles = array( 'default', 'theme' );
			$clean['button_style'] = isset( $input['button_style'] ) && in_array( $input['button_style'], $valid_styles, true )
				? $input['button_style']
				: $defaults['button_style'];

			$valid_icons = array_keys( self::get_icon_options() );
			$clean['button_icon'] = isset( $input['button_icon'] ) && in_array( $input['button_icon'], $valid_icons, true )
				? $input['button_icon']
				: $defaults['button_icon'];

			$valid_icon_positions = array( 'before', 'after' );
			$clean['button_icon_position'] = isset( $input['button_icon_position'] ) && in_array( $input['button_icon_position'], $valid_icon_positions, true )
				? $input['button_icon_position']
				: $defaults['button_icon_position'];

			$clean['enable_slider']  = ! empty( $input['enable_slider'] ) ? 1 : 0;
			$clean['enable_magnify'] = ! empty( $input['enable_magnify'] ) ? 1 : 0;

			return $clean;
		}

		// ====================================================================
		// Page renderer
		// ====================================================================

		/**
		 * Renders the settings page HTML.
		 */
		public function render_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			?>
			<div class="wrap wpqv-settings-page">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'wpqv_settings_group' );
					do_settings_sections( 'wpqv-settings' );
					submit_button();
					?>
				</form>
			</div>
			<?php
		}

		// ====================================================================
		// Helpers
		// ====================================================================

		/**
		 * Returns the icon options array used for both the field renderer and
		 * the button template helper.
		 *
		 * Each entry has a 'label' string and an optional 'svg' string (safe,
		 * hardcoded inline SVG — not user input).
		 *
		 * @return array<string, array{label: string, svg: string}>
		 */
		public static function get_icon_options() {
			return array(
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
