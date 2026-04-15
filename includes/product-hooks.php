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
add_action( 'wcqv_product_gallery', 'wcqv_product_gallery_output',        20 );

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
 * Outputs the custom product image gallery inside the modal.
 *
 * Renders all gallery images as stacked items for CSS crossfade switching.
 * Nav buttons and dots are only output when there are multiple images.
 *
 * @param WC_Product $product Current product.
 */
function wcqv_product_gallery_output( $product ) {
	$thumbnail_id = $product->get_image_id();
	$gallery_ids  = $product->get_gallery_image_ids();

	// Build full image list: thumbnail first, then gallery images.
	$image_ids = array_filter( array_merge( array( $thumbnail_id ), $gallery_ids ) );
	$image_ids = array_values( $image_ids );
	$count     = count( $image_ids );

	echo '<div class="wcqv__gallery-stage">';
	echo '<div class="wcqv__gallery-items">';

	if ( 0 === $count ) {
		// No images — show WooCommerce placeholder.
		echo '<div class="wcqv__gallery-item is-active" aria-hidden="false">';
		echo wc_placeholder_img( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	} else {
		$product_title = get_the_title( $product->get_id() );

		foreach ( $image_ids as $index => $attachment_id ) {
			$is_active  = 0 === $index;
			$alt        = trim( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
			$alt        = $alt ? $alt : $product_title;
			$image_html = wp_get_attachment_image(
				$attachment_id,
				'woocommerce_single',
				false,
				array(
					'alt'   => $alt,
					'class' => '',
				)
			);

			printf(
				'<div class="wcqv__gallery-item%s" aria-hidden="%s">%s</div>',
				$is_active ? ' is-active' : '',
				$is_active ? 'false' : 'true',
				$image_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		}
	}

	echo '</div>'; // .wcqv__gallery-items

	if ( $count > 1 ) {
		// Prev button — disabled in PHP since gallery starts at index 0.
		printf(
			'<button type="button" class="wcqv__gallery-nav wcqv__gallery-nav--prev" aria-label="%s" disabled>' .
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>' .
			'</button>',
			esc_attr__( 'Previous image', 'wc-products-quick-view' )
		);

		printf(
			'<button type="button" class="wcqv__gallery-nav wcqv__gallery-nav--next" aria-label="%s">' .
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>' .
			'</button>',
			esc_attr__( 'Next image', 'wc-products-quick-view' )
		);

		printf(
			'<div class="wcqv__dots" role="group" aria-label="%s">',
			esc_attr__( 'Choose product image', 'wc-products-quick-view' )
		);

		for ( $i = 0; $i < $count; $i++ ) {
			$is_active = 0 === $i;
			printf(
				'<button type="button" class="wcqv__dot%s" aria-label="%s"%s><span class="wcqv__dot-inner" aria-hidden="true"></span></button>',
				$is_active ? ' is-active' : '',
				/* translators: 1: current image number, 2: total image count */
				esc_attr( sprintf( __( 'Show image %1$d of %2$d', 'wc-products-quick-view' ), $i + 1, $count ) ),
				$is_active ? ' aria-current="true"' : ''
			);
		}

		echo '</div>'; // .wcqv__dots
	}

	echo '</div>'; // .wcqv__gallery-stage
}

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
