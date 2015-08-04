<?php

/**
* main class
*/
if ( !class_exists( 'WC_Products_Quick_View' ) ) {
	/**
	* WC_Products_Quick_View
	*/
	class WC_Products_Quick_View {

		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 20 );

			add_action( 'wp_ajax_nopriv_show_product', array( $this, 'show_product' ) );
			add_action( 'wp_ajax_show_product', array( $this, 'show_product' ) );

			/*Add to cart variable product*/
			add_action( 'wp_ajax_nopriv_add_variable_product', array( $this, 'wc_add_variable_product' ) );
			add_action( 'wp_ajax_add_variable_product', array( $this, 'wc_add_variable_product' ) );

			/*Add wrapper for showing product*/
			add_action( 'wp_footer', array( $this, 'quick_view_wrapper' ) );

			// modify class for simple add to cart button
			remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
			add_action( 'woocommerce_simple_add_to_cart', array( $this, 'simple_add_to_cart' ), 30 );
		}

		public function load_scripts() {
			wp_enqueue_style( 'wc-product-quick-view', PATH . '/assets/css/style.css' );
			wp_enqueue_script( 'wc-add-to-cart-variable-product', PATH . '/assets/js/wc-add-to-cart-variable-product.js', array( 'jquery' ), '20150509', true );

			global $woocommerce;

			/*prettyphoto scripts*/
			$suffix           = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$lightbox_enabled = get_option( 'woocommerce_enable_lightbox' ) == 'yes' ? true : false;

			if ( $lightbox_enabled ) {
				wp_enqueue_script( 'prettyPhoto', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), '20150511', true );
				wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce->plugin_url() . '/assets/css/prettyPhoto.css' );
				wp_enqueue_script( 'wc-product-quick-view', PATH . '/assets/js/wc-product-quick-view.js', array( 'jquery' ), '20150508', true );
			} else {
				wp_enqueue_script( 'wc-product-quick-view', PATH . '/assets/js/wc-product-quick-view-without-prettyphoto.js', array( 'jquery' ), '20150508', true );
			}
		}

		public function simple_add_to_cart() {
			global $woocommerce, $product;

			ob_start();
			woocommerce_simple_add_to_cart();
			$button = ob_get_clean();

			/*modify button class so that AJAX add-to-cart script finds it*/
			$replacement = sprintf( 'data-product_id="%d" data-quantity="1" $1 add_to_cart_button product_type_simple ', $product->id );
			$button = preg_replace( '/(class="single_add_to_cart_button)/', $replacement, $button );

			echo $button;
		}

		public function quick_view_wrapper() {
			// Enqueue variation scripts
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			?>
			<!-- wc-product-quick-view -->
			<div id="wc-product-quick-view">
				<div class="wc-quick-view-modal">
					<div class="modal-container">
						<div class="modal-content">
							<div class="modal-loading">
								<div class="loading-wrapper">
									<img src="<?php echo PATH . '/assets/images/ajax-loader.gif'; ?>" alt="loading...">
								</div>
							</div>
							<div class="wc-quick-view-content">
								<!-- quick view content will goes here.. -->
							</div>
						</div>
					</div>
					<div class="modal-shadow"></div>
				</div>
			</div>
			<!-- #wc-product-quick-view -->
			<?php
		}

		public function show_product() {
			$product_id = $_POST['product_id'];
			$next_id    = ( isset( $_POST['next_product_id'] ) ) ? $_POST['next_product_id'] : NULL;
			$prev_id    = ( isset( $_POST['prev_product_id'] ) ) ? $_POST['prev_product_id'] : NULL;

			if ( $product_id ) {
				/*get product according to product_id*/
				$query_args = array( 
					'post_type'   => 'product',
					'post__in'    => array($product_id),
					'post_status' => 'publish'
				);

				$wp_query = new WP_Query( $query_args );

				$response = NULL;

				if ( $wp_query->have_posts() ) {
					ob_start();
					while ( $wp_query->have_posts() ) : $wp_query->the_post();

						$params = array(
							'next_id'          => $next_id,
							'prev_id'          => $prev_id,
							'next_class'       => 'quick-view-nav next',
							'prev_class'       => 'quick-view-nav prev'
						);
						wpqv_get_template_part( 'wpqv-product', $params );

					endwhile;
					$response = ob_get_contents();
				}
				ob_end_clean();

				/*reset post query*/
				wp_reset_query();


				echo json_encode( $response );
				die();
			}
		}

		public function wc_add_variable_product() {
			
			ob_start();
			
			$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
			$quantity = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_POST['quantity'] );
			$variation_id = $_POST['variation_id'];
			$variation  = $_POST['variation'];
			$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		
			if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation  ) ) {
				do_action( 'woocommerce_ajax_added_to_cart', $product_id );
				if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
					wc_add_to_cart_message( $product_id );
				}
		
				// Return fragments
				WC_AJAX::get_refreshed_fragments();
			} else {
				$this->json_headers();
		
				// If there was an error adding to the cart, redirect to the product page to show any errors
				$data = array(
					'error' => true,
					'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
				);
				echo json_encode( $data );
			}
			die();
		}
	}
}
new WC_Products_Quick_View();
