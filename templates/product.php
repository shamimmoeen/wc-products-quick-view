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
 * @var int    $next_id    Product ID for the next navigation button (0 if none).
 * @var int    $prev_id    Product ID for the previous navigation button (0 if none).
 * @var string $next_class CSS classes for the next navigation button element.
 * @var string $prev_class CSS classes for the previous navigation button element.
 */

// $product is used internally by the WooCommerce template functions called below.
global $post, $product;
?>
<button type="button" class="wpqv__close" aria-label="<?php esc_attr_e( 'Close quick view', 'wc-products-quick-view' ); ?>">
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
</button>

<div class="wpqv__nav">
	<div class="wpqv__nav-inner">
		<?php if ( ! empty( $prev_id ) ) : ?>
		<div class="wpqv__nav-side wpqv__nav-side--prev">
			<button type="button" class="<?php echo esc_attr( $prev_class ); ?>" data-product_id="<?php echo esc_attr( $prev_id ); ?>" aria-label="<?php esc_attr_e( 'Previous product', 'wc-products-quick-view' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6z"/></svg>
			</button>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $next_id ) ) : ?>
		<div class="wpqv__nav-side wpqv__nav-side--next">
			<button type="button" class="<?php echo esc_attr( $next_class ); ?>" data-product_id="<?php echo esc_attr( $next_id ); ?>" aria-label="<?php esc_attr_e( 'Next product', 'wc-products-quick-view' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
			</button>
		</div>
		<?php endif; ?>
	</div>
</div>

<div <?php post_class( 'product wpqv__product' ); ?>>
	<?php woocommerce_show_product_images(); ?>
	<div class="wpqv__brief">
		<?php
		woocommerce_show_product_loop_sale_flash();

		// id="wpqv-product-title" is referenced by aria-labelledby on the
		// <dialog> element so the product name becomes the dialog's accessible
		// name. h2 is appropriate here: <dialog> creates its own landmark
		// context and this is its top-level heading.
		echo '<h2 id="wpqv-product-title" class="product_title">' . esc_html( get_the_title() ) . '</h2>';

		woocommerce_template_loop_rating();
		woocommerce_template_loop_price();
		woocommerce_template_single_add_to_cart();
		woocommerce_template_single_excerpt();
		woocommerce_template_single_meta();
		?>
	</div>
</div>

<div class="clear wpqv__bottom-nav">
	<?php if ( ! empty( $prev_id ) ) : ?>
		<button type="button" class="button <?php echo esc_attr( $prev_class ); ?>" data-product_id="<?php echo esc_attr( $prev_id ); ?>"><?php esc_html_e( 'Previous', 'wc-products-quick-view' ); ?></button>
	<?php endif; ?>
	<?php if ( ! empty( $next_id ) ) : ?>
		<button type="button" class="button <?php echo esc_attr( $next_class ); ?>" data-product_id="<?php echo esc_attr( $next_id ); ?>"><?php esc_html_e( 'Next', 'wc-products-quick-view' ); ?></button>
	<?php endif; ?>
</div>
