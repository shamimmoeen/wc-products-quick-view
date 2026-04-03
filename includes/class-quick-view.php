<?php
/**
 * Main plugin class.
 *
 * @package WC_Products_Quick_View
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPQV_Quick_View' ) ) {

	/**
	 * WPQV_Quick_View class.
	 */
	class WPQV_Quick_View {

		/**
		 * Single instance of the class.
		 *
		 * @var WPQV_Quick_View
		 */
		private static $instance = null;

		/**
		 * Returns the single instance of the class.
		 *
		 * @return WPQV_Quick_View
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
			add_action( 'wp_ajax_nopriv_show_product', array( $this, 'show_product' ) );
			add_action( 'wp_ajax_show_product', array( $this, 'show_product' ) );
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
				'wc-product-quick-view',
				WPQV_URL . $style_file,
				array(),
				filemtime( WPQV_PATH . $style_file )
			);

			// Slider and magnify are controlled by settings.
			// PhotoSwipe (nested lightbox) is never loaded in the modal context.
			if ( WPQV_Settings::get( 'enable_slider' ) ) {
				wp_enqueue_script( 'wc-flexslider' );
			}
			if ( WPQV_Settings::get( 'enable_magnify' ) ) {
				wp_enqueue_script( 'wc-zoom' );
			}

			wp_enqueue_script( 'wc-single-product' );

			wp_enqueue_script(
				'wc-product-quick-view',
				WPQV_URL . $script_file,
				array( 'jquery' ),
				filemtime( WPQV_PATH . $script_file ),
				true
			);

			wp_localize_script(
				'wc-product-quick-view',
				'wpqv_params',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'i18n'     => array(
						'loading'       => __( 'Loading product, please wait.', 'wc-products-quick-view' ),
						'added_to_cart' => __( 'Product added to cart.', 'wc-products-quick-view' ),
					),
				)
			);
		}

		/**
		 * Output the quick view modal wrapper in the footer.
		 *
		 * Uses the native <dialog> element with showModal() for built-in focus
		 * trapping, Esc handling, ::backdrop, and correct aria-modal semantics.
		 *
		 * aria-labelledby points to #wpqv-product-title which is the <h2>
		 * rendered inside the product template after a product loads. The dialog
		 * accessible name therefore matches the product being viewed.
		 */
		public function quick_view_wrapper() {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			?>
			<dialog id="wpqv-dialog" aria-labelledby="wpqv-product-title">

				<?php // Polite live region: announces loading state, product name, and add-to-cart feedback. ?>
				<div
					id="wpqv-live"
					class="screen-reader-text"
					aria-live="polite"
					aria-atomic="true"
				></div>

				<div class="wpqv__modal">
					<div class="wpqv__container">
						<div class="wpqv__modal-content">

							<div class="wpqv__loading" aria-hidden="true">
								<div class="wpqv__loading-inner">
									<span class="wpqv__spinner wpqv__spinner--modal"></span>
								</div>
							</div>

							<div class="wpqv__content"></div>

						</div>
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
			$next_id    = isset( $_POST['next_product_id'] ) ? absint( $_POST['next_product_id'] ) : 0;
			$prev_id    = isset( $_POST['prev_product_id'] ) ? absint( $_POST['prev_product_id'] ) : 0;
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			if ( ! $product_id ) {
				wp_send_json_error();
			}

			$product  = wc_get_product( $product_id );
			$response = '';

			if ( $product && $product->is_visible() ) {
				$post = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );

				$template_args = array(
					'next_id'    => $next_id,
					'prev_id'    => $prev_id,
					'next_class' => 'wpqv__nav-btn wpqv__nav-btn--next quick-view-nav next',
					'prev_class' => 'wpqv__nav-btn wpqv__nav-btn--prev quick-view-nav prev',
				);

				/**
				 * Filters the arguments passed to the product modal template.
				 *
				 * @param array<string, mixed> $template_args Template arguments.
				 * @param int                  $product_id    Current product ID.
				 */
				$template_args = apply_filters( 'wpqv_product_template_args', $template_args, $product_id );

				ob_start();
				wc_get_template(
					'product.php',
					$template_args,
					'wc-products-quick-view/',
					WPQV_TEMPLATE_PATH
				);
				$response = ob_get_clean();

				wp_reset_postdata();
			}

			wp_send_json_success( $response );
		}
	}
}
