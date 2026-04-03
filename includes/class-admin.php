<?php
/**
 * Admin settings page.
 *
 * @package WC_Products_Quick_View
 */

if ( ! class_exists( 'WPQV_Admin' ) ) {

	/**
	 * Registers the Quick View settings as a WooCommerce Settings tab.
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
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_wpqv',       array( $this, 'render_settings'  ) );
			add_action( 'woocommerce_update_options_wpqv', array( $this, 'save_settings'    ) );
			add_action( 'admin_enqueue_scripts',           array( $this, 'enqueue_assets'   ) );
			add_filter(
				'plugin_action_links_wc-products-quick-view/wc-products-quick-view.php',
				array( $this, 'add_action_links' )
			);
		}

		/**
		 * Adds the Quick View tab to the WooCommerce Settings tab bar.
		 *
		 * @param array<string, string> $tabs Existing tabs.
		 * @return array<string, string>
		 */
		public function add_settings_tab( $tabs ) {
			$tabs['wpqv'] = __( 'Quick View', 'wc-products-quick-view' );
			return $tabs;
		}

		/**
		 * Enqueues admin stylesheet on the WPQV settings tab only.
		 *
		 * @param string $hook Current admin page hook.
		 */
		public function enqueue_assets( $hook ) {
			if ( 'woocommerce_page_wc-settings' !== $hook ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( empty( $_GET['tab'] ) || 'wpqv' !== $_GET['tab'] ) {
				return;
			}

			$min      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$css_file = '/assets/css/admin' . $min . '.css';

			wp_enqueue_style(
				'wpqv-admin',
				WPQV_URL . $css_file,
				array(),
				filemtime( WPQV_PATH . $css_file )
			);
		}

		/**
		 * Adds a Settings link to the plugin's row on the Plugins page.
		 *
		 * @param array<string> $links Existing action links.
		 * @return array<string>
		 */
		public function add_action_links( $links ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=wpqv' ) ) . '">'
				. esc_html__( 'Settings', 'wc-products-quick-view' ) . '</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		// ====================================================================
		// Settings page renderer
		// ====================================================================

		/**
		 * Renders all settings fields for the WPQV tab inside WooCommerce's form.
		 *
		 * WooCommerce owns the <form> and nonce; we output section headings,
		 * descriptions, and a standard <table class="form-table"> for each section.
		 */
		public function render_settings() {
			// ----------------------------------------------------------------
			// Section: Button
			// ----------------------------------------------------------------
			?>
			<h2><?php esc_html_e( 'Button', 'wc-products-quick-view' ); ?></h2>
			<div id="wpqv_button_options-description">
				<p><?php esc_html_e( 'Controls the Quick View trigger button displayed on product cards.', 'wc-products-quick-view' ); ?></p>
			</div>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" class="titledesc">
							<label for="wpqv_button_position"><?php esc_html_e( 'Position', 'wc-products-quick-view' ); ?></label>
						</th>
						<td class="forminp forminp-select">
							<?php $this->field_button_position(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<label for="wpqv_button_priority"><?php esc_html_e( 'Hook priority', 'wc-products-quick-view' ); ?></label>
						</th>
						<td class="forminp forminp-number">
							<?php $this->field_button_priority(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<label for="wpqv_button_label"><?php esc_html_e( 'Button label', 'wc-products-quick-view' ); ?></label>
						</th>
						<td class="forminp forminp-text">
							<?php $this->field_button_label(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<?php esc_html_e( 'Button style', 'wc-products-quick-view' ); ?>
						</th>
						<td class="forminp forminp-radio">
							<?php $this->field_button_style(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<?php esc_html_e( 'Icon', 'wc-products-quick-view' ); ?>
						</th>
						<td class="forminp forminp-radio">
							<?php $this->field_button_icon(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<?php esc_html_e( 'Icon position', 'wc-products-quick-view' ); ?>
						</th>
						<td class="forminp forminp-radio">
							<?php $this->field_button_icon_position(); ?>
						</td>
					</tr>
				</tbody>
			</table>

			<?php
			// ----------------------------------------------------------------
			// Section: Gallery
			// ----------------------------------------------------------------
			?>
			<h2><?php esc_html_e( 'Gallery', 'wc-products-quick-view' ); ?></h2>
			<div id="wpqv_gallery_options-description">
				<p><?php esc_html_e( 'Controls how product images behave inside the quick view modal.', 'wc-products-quick-view' ); ?></p>
			</div>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" class="titledesc">
							<?php esc_html_e( 'Image slider', 'wc-products-quick-view' ); ?>
						</th>
						<td class="forminp forminp-checkbox">
							<?php $this->field_enable_slider(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<?php esc_html_e( 'Hover magnify', 'wc-products-quick-view' ); ?>
						</th>
						<td class="forminp forminp-checkbox">
							<?php $this->field_enable_magnify(); ?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		}

		// ====================================================================
		// Field renderers — output <td> content only
		// ====================================================================

		/**
		 * Renders the button position select field.
		 */
		public function field_button_position() {
			$value   = WPQV_Settings::get( 'button_position' );
			$options = array(
				'woocommerce_before_shop_loop_item_title' => __( 'Before title (after image)', 'wc-products-quick-view' ),
				'woocommerce_after_shop_loop_item_title'  => __( 'After title', 'wc-products-quick-view' ),
				'woocommerce_after_shop_loop_item'        => __( 'After add to cart button', 'wc-products-quick-view' ),
				'wpqv_overlay'                            => __( 'Over product image (icon overlay)', 'wc-products-quick-view' ),
			);
			echo '<select name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_position]" id="wpqv_button_position" class="wc-enhanced-select">';
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
			echo '<input type="number" style="width: 60px;" min="1" max="99" step="1"
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
			$value   = WPQV_Settings::get( 'button_style' );
			$options = array(
				'default' => __( 'Default (plugin-styled)', 'wc-products-quick-view' ),
				'theme'   => __( 'Inherit from theme', 'wc-products-quick-view' ),
			);
			echo '<fieldset><ul>';
			foreach ( $options as $key => $label ) {
				echo '<li><label>';
				echo '<input type="radio"
					name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_style]"
					value="' . esc_attr( $key ) . '"'
					. checked( $value, $key, false ) . '> ';
				echo esc_html( $label );
				echo '</label></li>';
			}
			echo '</ul></fieldset>';
			echo '<p class="description">' . esc_html__( '"Inherit from theme" removes plugin button styles so your theme\'s button design applies.', 'wc-products-quick-view' ) . '</p>';
		}

		/**
		 * Renders the icon radio field with inline SVG previews.
		 */
		public function field_button_icon() {
			$value = WPQV_Settings::get( 'button_icon' );
			$icons = WPQV_Settings::get_icon_options();
			echo '<fieldset><ul>';
			foreach ( $icons as $key => $data ) {
				echo '<li><label class="wpqv-admin-icon-option">';
				echo '<input type="radio"
					name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_icon]"
					value="' . esc_attr( $key ) . '"'
					. checked( $value, $key, false ) . '> ';
				if ( ! empty( $data['svg'] ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVGs are hardcoded in WPQV_Settings, not user input.
					echo $data['svg'];
				}
				echo ' ' . esc_html( $data['label'] );
				echo '</label></li>';
			}
			echo '</ul></fieldset>';
		}

		/**
		 * Renders the icon position radio field.
		 */
		public function field_button_icon_position() {
			$value   = WPQV_Settings::get( 'button_icon_position' );
			$options = array(
				'before' => __( 'Before label', 'wc-products-quick-view' ),
				'after'  => __( 'After label', 'wc-products-quick-view' ),
			);
			echo '<fieldset><ul>';
			foreach ( $options as $key => $label ) {
				echo '<li><label>';
				echo '<input type="radio"
					name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[button_icon_position]"
					value="' . esc_attr( $key ) . '"'
					. checked( $value, $key, false ) . '> ';
				echo esc_html( $label );
				echo '</label></li>';
			}
			echo '</ul></fieldset>';
		}

		/**
		 * Renders the enable slider checkbox.
		 */
		public function field_enable_slider() {
			$value = WPQV_Settings::get( 'enable_slider' );
			echo '<fieldset>';
			echo '<legend class="screen-reader-text"><span>' . esc_html__( 'Image slider', 'wc-products-quick-view' ) . '</span></legend>';
			echo '<label for="wpqv_enable_slider">';
			echo '<input type="checkbox"
				name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[enable_slider]"
				id="wpqv_enable_slider"
				value="1"' . checked( 1, $value, false ) . '>';
			echo ' ' . esc_html__( 'Enable image slider in the modal (WooCommerce flexslider)', 'wc-products-quick-view' );
			echo '</label>';
			echo '</fieldset>';
		}

		/**
		 * Renders the enable magnify checkbox.
		 */
		public function field_enable_magnify() {
			$value = WPQV_Settings::get( 'enable_magnify' );
			echo '<fieldset>';
			echo '<legend class="screen-reader-text"><span>' . esc_html__( 'Hover magnify', 'wc-products-quick-view' ) . '</span></legend>';
			echo '<label for="wpqv_enable_magnify">';
			echo '<input type="checkbox"
				name="' . esc_attr( WPQV_Settings::OPTION_KEY ) . '[enable_magnify]"
				id="wpqv_enable_magnify"
				value="1"' . checked( 1, $value, false ) . '>';
			echo ' ' . esc_html__( 'Enable hover magnify on product images', 'wc-products-quick-view' );
			echo '</label>';
			echo '</fieldset>';
		}

		// ====================================================================
		// Save handler
		// ====================================================================

		/**
		 * Saves settings when WooCommerce fires the tab update action.
		 *
		 * WooCommerce verifies the woocommerce-settings nonce before this hook
		 * fires, so no additional nonce check is required here.
		 */
		public function save_settings() {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$input = isset( $_POST[ WPQV_Settings::OPTION_KEY ] ) ? (array) $_POST[ WPQV_Settings::OPTION_KEY ] : array();
			update_option( WPQV_Settings::OPTION_KEY, $this->sanitize_settings( $input ) );
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

			$valid_styles          = array( 'default', 'theme' );
			$clean['button_style'] = isset( $input['button_style'] ) && in_array( $input['button_style'], $valid_styles, true )
				? $input['button_style']
				: $defaults['button_style'];

			$valid_icons          = array_keys( WPQV_Settings::get_icon_options() );
			$clean['button_icon'] = isset( $input['button_icon'] ) && in_array( $input['button_icon'], $valid_icons, true )
				? $input['button_icon']
				: $defaults['button_icon'];

			$valid_icon_positions          = array( 'before', 'after' );
			$clean['button_icon_position'] = isset( $input['button_icon_position'] ) && in_array( $input['button_icon_position'], $valid_icon_positions, true )
				? $input['button_icon_position']
				: $defaults['button_icon_position'];

			$clean['enable_slider']  = ! empty( $input['enable_slider'] ) ? 1 : 0;
			$clean['enable_magnify'] = ! empty( $input['enable_magnify'] ) ? 1 : 0;

			return $clean;
		}
	}
}
