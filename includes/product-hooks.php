<?php
/**
 * Default hook registrations for the quick view product modal.
 *
 * Developers can remove or reorder any of these using remove_action / add_action.
 * All callbacks receive $product as the first argument. Callbacks that need
 * template args (title, view-product link) also receive $template_args as
 * the second argument — register them with accepted_args = 2.
 *
 * @package WC_Products_Quick_View
 */

defined( 'ABSPATH' ) || exit;

// -------------------------------------------------------------------------
// Gallery
// -------------------------------------------------------------------------

add_action( 'wcqv_product_gallery', 'woocommerce_show_product_sale_flash', 10 );
add_action( 'wcqv_product_gallery', 'woocommerce_show_product_images',     20 );

// -------------------------------------------------------------------------
// Summary
// -------------------------------------------------------------------------

add_action( 'wcqv_product_summary', 'wcqv_product_title_output',              5,  2 );
add_action( 'wcqv_product_summary', 'woocommerce_template_single_rating',     10    );
add_action( 'wcqv_product_summary', 'woocommerce_template_single_price',      15    );
add_action( 'wcqv_product_summary', 'woocommerce_template_single_excerpt',    20    );
add_action( 'wcqv_product_summary', 'woocommerce_template_single_add_to_cart', 25   );
add_action( 'wcqv_product_summary', 'woocommerce_template_single_meta',       30    );
add_action( 'wcqv_product_summary', 'wcqv_product_view_product_link',         35, 2 );

// -------------------------------------------------------------------------
// Callback implementations
// -------------------------------------------------------------------------

/**
 * Outputs the product title inside the modal summary.
 *
 * @param WC_Product           $product       Current product.
 * @param array<string, mixed> $template_args Template args from the product template.
 */
function wcqv_product_title_output( $product, $template_args = array() ) {
	$title_tag = isset( $template_args['title_tag'] ) ? $template_args['title_tag'] : 'h2';
	echo '<' . esc_attr( $title_tag ) . ' id="wcqv-product-title" class="product_title">'
		. esc_html( get_the_title( $product->get_id() ) )
		. '</' . esc_attr( $title_tag ) . '>';
}

/**
 * Outputs the "View product" link inside the modal summary.
 *
 * @param WC_Product           $product       Current product.
 * @param array<string, mixed> $template_args Template args from the product template.
 */
function wcqv_product_view_product_link( $product, $template_args = array() ) {
	if ( empty( $template_args['enable_view_product'] ) ) {
		return;
	}
	?>
	<div class="wcqv__view-product-wrap">
		<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="wcqv__view-product">
			<?php esc_html_e( 'View product', 'wc-products-quick-view' ); ?>
		</a>
	</div>
	<?php
}
