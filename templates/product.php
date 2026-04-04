<?php
/**
 * Quick view product template.
 *
 * Override this template by copying it to yourtheme/wc-products-quick-view/product.php
 *
 * @author  Mainul Hassan
 * @package WC_Products_Quick_View/Templates
 * @version 2.0.0
 *
 * Template variables:
 *
 * @var string $title_tag HTML tag for the product title element. Override via wpqv_product_template_args.
 */

defined( 'ABSPATH' ) || exit;

// $product is used internally by the WooCommerce template functions called below.
global $post, $product;
?>
<div <?php post_class( 'product wpqv__product' ); ?>>
	<?php woocommerce_show_product_images(); ?>
	<div class="wpqv__brief">
		<?php
		woocommerce_show_product_loop_sale_flash();

		// id="wpqv-product-title" is the aria-labelledby target on the <dialog>.
		echo '<' . esc_attr( $title_tag ) . ' id="wpqv-product-title" class="product_title">'
			. esc_html( get_the_title( $product->get_id() ) )
			. '</' . esc_attr( $title_tag ) . '>';

		woocommerce_template_loop_rating();
		woocommerce_template_loop_price();
		woocommerce_template_single_add_to_cart();
		woocommerce_template_single_excerpt();
		woocommerce_template_single_meta();
		?>
	</div>
</div>
