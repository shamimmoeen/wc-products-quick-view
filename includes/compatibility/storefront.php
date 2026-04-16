<?php
/**
 * Storefront theme compatibility.
 *
 * @package WC_Products_Quick_View
 */

defined( 'ABSPATH' ) || exit;

// get_template() returns the parent theme slug, so this also covers any
// child theme built on Storefront.
if ( 'storefront' !== get_template() ) {
	return;
}

// Place the button before Storefront's add-to-cart (priority 10).
function wcqv_storefront_auto_position_priority() {
	return 9;
}
add_filter( 'wcqv_auto_position_priority', 'wcqv_storefront_auto_position_priority' );

// Move the sale badge from the gallery column to the summary column.
remove_action( 'wcqv_product_gallery', 'woocommerce_show_product_sale_flash', 10 );
add_action( 'wcqv_product_summary', 'woocommerce_show_product_sale_flash', 3 );
