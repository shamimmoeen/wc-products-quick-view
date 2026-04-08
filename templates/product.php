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
 * @var string $title_tag          HTML tag for the product title element. Override via wcqv_product_template_args.
 * @var bool   $enable_view_product Whether to show the "View product" link. Override via wcqv_product_template_args.
 *
 * Content is registered via action hooks — see includes/product-hooks.php.
 * Use remove_action / add_action on wcqv_product_gallery and
 * wcqv_product_summary to add, remove, or reorder sections.
 */

defined( 'ABSPATH' ) || exit;

// $product is used internally by the WooCommerce template functions called below.
global $post, $product;

// Expose template args to hooked callbacks that need them (title tag, view-product toggle).
$wcqv_template_args = array(
	'title_tag'           => $title_tag,
	'enable_view_product' => $enable_view_product,
);
?>
<div class="<?php echo esc_attr( implode( ' ', wcqv_get_classes( 'product' ) ) ); ?>">

	<div class="<?php echo esc_attr( implode( ' ', wcqv_get_classes( 'gallery' ) ) ); ?>">
		<?php do_action( 'wcqv_product_gallery', $product ); ?>
	</div>

	<div class="<?php echo esc_attr( implode( ' ', wcqv_get_classes( 'brief' ) ) ); ?>">
		<?php do_action( 'wcqv_product_summary', $product, $wcqv_template_args ); ?>
	</div>

</div>
