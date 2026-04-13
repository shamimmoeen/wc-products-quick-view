<?php
/**
 * Admin settings page.
 *
 * @package WC_Products_Quick_View
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCQV_Admin' ) ) {

	/**
	 * Registers the Quick View settings as a WooCommerce Settings tab.
	 */
	class WCQV_Admin {

		/**
		 * Single instance of the class.
		 *
		 * @var WCQV_Admin|null
		 */
		private static $instance = null;

		/**
		 * Returns the single instance.
		 *
		 * @return WCQV_Admin
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
			add_action( 'woocommerce_settings_wcqv',       array( $this, 'render_settings'  ) );
			add_action( 'woocommerce_update_options_wcqv', array( $this, 'save_settings'    ) );
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
			$tabs['wcqv'] = __( 'Quick View', 'wc-products-quick-view' );
			return $tabs;
		}

		/**
		 * Enqueues admin stylesheet on the WCQV settings tab only.
		 *
		 * @param string $hook Current admin page hook.
		 */
		public function enqueue_assets( $hook ) {
			if ( 'woocommerce_page_wc-settings' !== $hook ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( empty( $_GET['tab'] ) || 'wcqv' !== $_GET['tab'] ) {
				return;
			}

			$min      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$css_file = '/assets/css/admin' . $min . '.css';

			wp_enqueue_style(
				'wcqv-admin',
				WCQV_URL . $css_file,
				array(),
				filemtime( WCQV_PATH . $css_file )
			);
		}

		/**
		 * Adds a Settings link to the plugin's row on the Plugins page.
		 *
		 * @param array<string> $links Existing action links.
		 * @return array<string>
		 */
		public function add_action_links( $links ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=wcqv' ) ) . '">'
				. esc_html__( 'Settings', 'wc-products-quick-view' ) . '</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		/**
		 * Returns the available button position options.
		 *
		 * Developers can add or remove positions via the wcqv_button_positions filter.
		 *
		 * @return array<string, string>
		 */
		public static function get_position_options() {
			$options = array(
				'auto'                                    => __( 'Automatic', 'wc-products-quick-view' ),
				'woocommerce_before_shop_loop_item_title' => __( 'Before title', 'wc-products-quick-view' ),
				'woocommerce_after_shop_loop_item_title'  => __( 'After title', 'wc-products-quick-view' ),
				'woocommerce_after_shop_loop_item'        => __( 'After add to cart button', 'wc-products-quick-view' ),
				'none'                                    => __( 'None', 'wc-products-quick-view' ),
			);

			/**
			 * Filters the available button position options.
			 *
			 * @param array<string, string> $options Position options keyed by hook name.
			 */
			return apply_filters( 'wcqv_button_positions', $options );
		}

		// ====================================================================
		// Settings page renderer
		// ====================================================================

		/**
		 * Renders all settings fields for the WCQV tab inside WooCommerce's form.
		 *
		 * WooCommerce owns the <form> and nonce; we output section headings,
		 * descriptions, and a standard <table class="form-table"> for each section.
		 */
		public function render_settings() {
			?>
			<h2><?php esc_html_e( 'Button', 'wc-products-quick-view' ); ?></h2>
			<div id="wcqv-button-description">
				<p><?php esc_html_e( 'Controls the Quick View trigger button displayed on product cards.', 'wc-products-quick-view' ); ?></p>
			</div>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" class="titledesc">
							<label for="wcqv_button_position"><?php esc_html_e( 'Position', 'wc-products-quick-view' ); ?></label>
						</th>
						<td class="forminp forminp-select">
							<?php $this->field_button_position(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<label for="wcqv_button_priority"><?php esc_html_e( 'Hook priority', 'wc-products-quick-view' ); ?></label>
						</th>
						<td class="forminp forminp-number">
							<?php $this->field_button_priority(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<label for="wcqv_button_label"><?php esc_html_e( 'Button label', 'wc-products-quick-view' ); ?></label>
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

			<h2><?php esc_html_e( 'Modal', 'wc-products-quick-view' ); ?></h2>
			<div id="wcqv-modal-description">
				<p><?php esc_html_e( 'Controls the content and layout inside the quick view modal.', 'wc-products-quick-view' ); ?></p>
			</div>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" class="titledesc">
							<?php esc_html_e( 'Body scroll lock', 'wc-products-quick-view' ); ?>
						</th>
						<td class="forminp forminp-checkbox">
							<?php $this->field_scroll_lock(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<?php esc_html_e( 'View product link', 'wc-products-quick-view' ); ?>
						</th>
						<td class="forminp forminp-checkbox">
							<?php $this->field_enable_view_product(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<label for="wcqv_dialog_width"><?php esc_html_e( 'Dialog width', 'wc-products-quick-view' ); ?></label>
						</th>
						<td class="forminp forminp-number">
							<?php $this->field_dialog_width(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<label for="wcqv_dialog_max_height"><?php esc_html_e( 'Dialog max-height', 'wc-products-quick-view' ); ?></label>
						</th>
						<td class="forminp forminp-number">
							<?php $this->field_dialog_max_height(); ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							<label for="wcqv_gallery_column_width"><?php esc_html_e( 'Gallery column width', 'wc-products-quick-view' ); ?></label>
						</th>
						<td class="forminp forminp-number">
							<?php $this->field_gallery_column_width(); ?>
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
			$value   = WCQV_Settings::get( 'button_position' );
			$options = self::get_position_options();
			echo '<select name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[button_position]" id="wcqv_button_position" class="wc-enhanced-select">';
			foreach ( $options as $key => $label ) {
				echo '<option value="' . esc_attr( $key ) . '"' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
			echo '<p class="description">' . wp_kses(
				__( '"Automatic" places the button after the add to cart button; for supported themes it is moved before it. "None" — use the <code>[wcqv_button]</code> shortcode.', 'wc-products-quick-view' ),
				array( 'code' => array() )
			) . '</p>';
		}

		/**
		 * Renders the hook priority number field.
		 */
		public function field_button_priority() {
			$value = absint( WCQV_Settings::get( 'button_priority' ) );
			echo '<input type="number" style="width: 60px;" min="1" max="99" step="1"
				name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[button_priority]"
				id="wcqv_button_priority"
				value="' . esc_attr( $value ) . '"
				class="small-text">';
			echo '<p class="description">' . esc_html__( 'Lower numbers run earlier; adjust to place the button among other elements at the same hook.', 'wc-products-quick-view' ) . '</p>';
		}

		/**
		 * Renders the button label text field.
		 */
		public function field_button_label() {
			$value = WCQV_Settings::get( 'button_label' );
			echo '<input type="text"
				name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[button_label]"
				id="wcqv_button_label"
				value="' . esc_attr( $value ) . '"
				class="regular-text">';
		}

		/**
		 * Renders the button style radio field.
		 */
		public function field_button_style() {
			$value   = WCQV_Settings::get( 'button_style' );
			$options = array(
				'theme'  => __( 'Inherit from theme', 'wc-products-quick-view' ),
				'plugin' => __( 'Plugin style', 'wc-products-quick-view' ),
			);
			echo '<fieldset><ul>';
			foreach ( $options as $key => $label ) {
				echo '<li><label>';
				echo '<input type="radio"
					name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[button_style]"
					value="' . esc_attr( $key ) . '"'
					. checked( $value, $key, false ) . '> ';
				echo esc_html( $label );
				echo '</label></li>';
			}
			echo '</ul></fieldset>';
			echo '<p class="description">' . esc_html__( '"Plugin style" applies a built-in dark button design. "Inherit from theme" lets your theme\'s button styles apply without interference.', 'wc-products-quick-view' ) . '</p>';
		}

		/**
		 * Renders the icon radio field with inline SVG previews.
		 */
		public function field_button_icon() {
			$value = WCQV_Settings::get( 'button_icon' );
			$icons = WCQV_Settings::get_icon_options();
			echo '<fieldset><ul>';
			foreach ( $icons as $key => $data ) {
				echo '<li><label class="wcqv-admin-icon-option">';
				echo '<input type="radio"
					name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[button_icon]"
					value="' . esc_attr( $key ) . '"'
					. checked( $value, $key, false ) . '> ';
				if ( ! empty( $data['svg'] ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVGs are hardcoded in WCQV_Settings, not user input.
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
			$value   = WCQV_Settings::get( 'button_icon_position' );
			$options = array(
				'before' => __( 'Before label', 'wc-products-quick-view' ),
				'after'  => __( 'After label', 'wc-products-quick-view' ),
			);
			echo '<fieldset><ul>';
			foreach ( $options as $key => $label ) {
				echo '<li><label>';
				echo '<input type="radio"
					name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[button_icon_position]"
					value="' . esc_attr( $key ) . '"'
					. checked( $value, $key, false ) . '> ';
				echo esc_html( $label );
				echo '</label></li>';
			}
			echo '</ul></fieldset>';
		}

		/**
		 * Renders the body scroll lock checkbox.
		 */
		public function field_scroll_lock() {
			$value = WCQV_Settings::get( 'scroll_lock' );
			echo '<fieldset>';
			echo '<legend class="screen-reader-text"><span>' . esc_html__( 'Body scroll lock', 'wc-products-quick-view' ) . '</span></legend>';
			echo '<label for="wcqv_scroll_lock">';
			echo '<input type="checkbox"
				name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[scroll_lock]"
				id="wcqv_scroll_lock"
				value="1"' . checked( 1, $value, false ) . '>';
			echo ' ' . esc_html__( 'Prevent page scrolling while the modal is open', 'wc-products-quick-view' );
			echo '</label>';
			echo '</fieldset>';
		}

		/**
		 * Renders the enable view product link checkbox.
		 */
		public function field_enable_view_product() {
			$value = WCQV_Settings::get( 'enable_view_product' );
			echo '<fieldset>';
			echo '<legend class="screen-reader-text"><span>' . esc_html__( 'View product link', 'wc-products-quick-view' ) . '</span></legend>';
			echo '<label for="wcqv_enable_view_product">';
			echo '<input type="checkbox"
				name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[enable_view_product]"
				id="wcqv_enable_view_product"
				value="1"' . checked( 1, $value, false ) . '>';
			echo ' ' . esc_html__( 'Show a "View product" link inside the modal', 'wc-products-quick-view' );
			echo '</label>';
			echo '</fieldset>';
		}

		/**
		 * Renders the dialog width number field.
		 */
		public function field_dialog_width() {
			$value = absint( WCQV_Settings::get( 'dialog_width' ) );
			echo '<input type="number" style="width: 80px;" min="600" max="1600" step="10"
				name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[dialog_width]"
				id="wcqv_dialog_width"
				value="' . esc_attr( $value ) . '"
				class="small-text">';
			echo '<p class="description">' . esc_html__( 'Maximum width of the modal dialog in pixels. Default: 1050.', 'wc-products-quick-view' ) . '</p>';
		}

		/**
		 * Renders the dialog max-height number field.
		 */
		public function field_dialog_max_height() {
			$value = absint( WCQV_Settings::get( 'dialog_max_height' ) );
			echo '<input type="number" style="width: 70px;" min="50" max="100" step="5"
				name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[dialog_max_height]"
				id="wcqv_dialog_max_height"
				value="' . esc_attr( $value ) . '"
				class="small-text">';
			echo '<p class="description">' . esc_html__( 'Maximum height of the dialog as a percentage of the viewport height. Default: 85.', 'wc-products-quick-view' ) . '</p>';
		}

		/**
		 * Renders the gallery column width number field.
		 */
		public function field_gallery_column_width() {
			$value = absint( WCQV_Settings::get( 'gallery_column_width' ) );
			echo '<input type="number" style="width: 70px;" min="20" max="80" step="5"
				name="' . esc_attr( WCQV_Settings::OPTION_KEY ) . '[gallery_column_width]"
				id="wcqv_gallery_column_width"
				value="' . esc_attr( $value ) . '"
				class="small-text">';
			echo '<p class="description">' . esc_html__( 'Width of the gallery column as a percentage of the dialog. The product info column takes the remainder. Default: 50.', 'wc-products-quick-view' ) . '</p>';
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
			$raw   = isset( $_POST[ WCQV_Settings::OPTION_KEY ] ) ? wp_unslash( $_POST[ WCQV_Settings::OPTION_KEY ] ) : array();
			$input = is_array( $raw ) ? $raw : array();
			$clean = $this->sanitize_settings( $input );
			update_option( WCQV_Settings::OPTION_KEY, $clean );

			do_action( 'wpml_register_single_string', 'wc-products-quick-view', 'button_label', $clean['button_label'] );
			if ( function_exists( 'pll_register_string' ) ) {
				pll_register_string( 'button_label', $clean['button_label'], __( 'WCQV – Product Quick View for WooCommerce', 'wc-products-quick-view' ) );
			}
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
			$defaults = WCQV_Settings::defaults();

			$valid_positions = array_keys( self::get_position_options() );

			$clean['button_position'] = isset( $input['button_position'] ) && in_array( $input['button_position'], $valid_positions, true )
				? $input['button_position']
				: $defaults['button_position'];

			$clean['button_priority'] = isset( $input['button_priority'] )
				? min( 99, max( 1, absint( $input['button_priority'] ) ) )
				: $defaults['button_priority'];

			$clean['button_label'] = isset( $input['button_label'] )
				? sanitize_text_field( $input['button_label'] )
				: $defaults['button_label'];

			$valid_styles          = array( 'theme', 'plugin' );
			$clean['button_style'] = isset( $input['button_style'] ) && in_array( $input['button_style'], $valid_styles, true )
				? $input['button_style']
				: $defaults['button_style'];

			$valid_icons          = array_keys( WCQV_Settings::get_icon_options() );
			$clean['button_icon'] = isset( $input['button_icon'] ) && in_array( $input['button_icon'], $valid_icons, true )
				? $input['button_icon']
				: $defaults['button_icon'];

			if ( '' === $clean['button_label'] && 'none' === $clean['button_icon'] ) {
				$clean['button_label'] = $defaults['button_label'];
			}

			$valid_icon_positions          = array( 'before', 'after' );
			$clean['button_icon_position'] = isset( $input['button_icon_position'] ) && in_array( $input['button_icon_position'], $valid_icon_positions, true )
				? $input['button_icon_position']
				: $defaults['button_icon_position'];

			$clean['scroll_lock']         = ! empty( $input['scroll_lock'] ) ? 1 : 0;

			$clean['enable_view_product'] = ! empty( $input['enable_view_product'] ) ? 1 : 0;

			$clean['dialog_width'] = isset( $input['dialog_width'] )
				? min( 1600, max( 600, absint( $input['dialog_width'] ) ) )
				: $defaults['dialog_width'];

			$clean['dialog_max_height'] = isset( $input['dialog_max_height'] )
				? min( 100, max( 50, absint( $input['dialog_max_height'] ) ) )
				: $defaults['dialog_max_height'];

			$clean['gallery_column_width'] = isset( $input['gallery_column_width'] )
				? min( 80, max( 20, absint( $input['gallery_column_width'] ) ) )
				: $defaults['gallery_column_width'];

			return $clean;
		}
	}
}
