<?php
/**
 * Main plugin class.
 *
 * @package WC_Products_Quick_View
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCQV_Quick_View' ) ) {

	/**
	 * WCQV_Quick_View class.
	 */
	class WCQV_Quick_View {

		/**
		 * Single instance of the class.
		 *
		 * @var WCQV_Quick_View
		 */
		private static $instance = null;

		/**
		 * Returns the single instance of the class.
		 *
		 * @return WCQV_Quick_View
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
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 20 );
			add_action( 'wp_ajax_nopriv_wcqv_show_product', array( $this, 'show_product' ) );
			add_action( 'wp_ajax_wcqv_show_product',        array( $this, 'show_product' ) );
			add_action( 'wp_footer', array( $this, 'quick_view_wrapper' ) );
		}

		/**
		 * Enqueue scripts and styles.
		 */
		public function load_scripts() {
			$min         = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$style_file  = '/assets/css/quick-view' . $min . '.css';
			$script_file = '/assets/js/quick-view' . $min . '.js';

			wp_enqueue_style(
				'wcqv',
				WCQV_URL . $style_file,
				array(),
				filemtime( WCQV_PATH . $style_file )
			);

			wp_enqueue_script( 'wc-single-product' );
			wp_enqueue_script( 'wc-add-to-cart-variation' );

			wp_enqueue_script(
				'wcqv',
				WCQV_URL . $script_file,
				array( 'wc-add-to-cart-variation' ),
				filemtime( WCQV_PATH . $script_file ),
				true
			);

			wp_localize_script(
				'wcqv',
				'wcqv_params',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'i18n'     => apply_filters(
						'wcqv_i18n',
						array(
							'loading'       => __( 'Loading product, please wait.', 'wc-products-quick-view' ),
							'loading_btn'   => __( 'Loading…', 'wc-products-quick-view' ),
							'error_loading' => __( 'Could not load the product. Please try again.', 'wc-products-quick-view' ),
							'loaded'          => __( 'Quick view loaded', 'wc-products-quick-view' ),
							'close'           => __( 'Close quick view', 'wc-products-quick-view' ),
						)
					),
				)
			);
		}

		/**
		 * Output the quick view modal wrapper in the footer.
		 *
		 * Uses the native <dialog> element with showModal() for built-in focus
		 * trapping, Esc handling, ::backdrop, and correct aria-modal semantics.
		 */
		public function quick_view_wrapper() {
			?>
			<div
				id="wcqv-page-alert"
				class="screen-reader-text"
				aria-live="assertive"
				aria-atomic="true"
			></div>

			<dialog id="wcqv-dialog" aria-labelledby="wcqv-dialog-title" aria-describedby="wcqv-live" tabindex="-1">

				<span id="wcqv-dialog-title" class="screen-reader-text">
					<?php esc_html_e( 'Product quick view', 'wc-products-quick-view' ); ?>
				</span>

				<div
					id="wcqv-live"
					class="screen-reader-text"
					aria-live="polite"
					aria-atomic="true"
				></div>

				<div class="wcqv__panel">

					<button type="button" class="<?php echo esc_attr( implode( ' ', wcqv_get_classes( 'close' ) ) ); ?>"
						aria-label="<?php esc_attr_e( 'Close quick view', 'wc-products-quick-view' ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
					</button>

					<div class="wcqv__body">

						<div class="wcqv__loading" aria-hidden="true">
							<div class="wcqv__loading-inner">
								<span class="wcqv__spinner" aria-hidden="true"></span>
								<span><?php esc_html_e( 'Loading product…', 'wc-products-quick-view' ); ?></span>
							</div>
						</div>

						<div id="wcqv-content" class="<?php echo esc_attr( implode( ' ', wcqv_get_classes( 'content' ) ) ); ?>" aria-busy="false"></div>

					</div>

				</div>

			</dialog>
			<?php
		}

		/**
		 * AJAX handler: load product content for the quick view modal.
		 */
		public function show_product() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing -- reads only public product data; $product->is_visible() ensures no private or draft products are exposed.
			$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			if ( ! $product_id ) {
				wp_send_json_error();
			}

			global $product, $post;
			$product  = wc_get_product( $product_id );
			$response = '';

			if ( $product && $product->is_visible() ) {
				$post = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );

				$template_args = array(
					'title_tag'           => 'h2',
					'enable_view_product' => (bool) WCQV_Settings::get( 'enable_view_product' ),
				);

				/**
				 * Filters the arguments passed to the product modal template.
				 *
				 * @param array<string, mixed> $template_args Template arguments.
				 * @param int                  $product_id    Current product ID.
				 */
				$template_args = apply_filters( 'wcqv_product_template_args', $template_args, $product_id );

				ob_start();
				wc_get_template(
					'product.php',
					$template_args,
					'wc-products-quick-view/',
					WCQV_TEMPLATE_PATH
				);
				$response = ob_get_clean();

				wp_reset_postdata();
			}

			wp_send_json_success( array( 'html' => $response ) );
		}
	}
}
