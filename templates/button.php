<?php
/**
 * Quick view button template.
 *
 * Override this template by copying it to yourtheme/wc-products-quick-view/button.php
 *
 * @author  Mainul Hassan
 * @package WC_Products_Quick_View/Templates
 * @version 2.0.0
 *
 * Template variables:
 *
 * @var string $button_class         Base CSS class for the trigger button element.
 * @var string $spinner_class        CSS classes for the inline loading spinner span.
 * @var string $button_label         Visible button label text.
 * @var string $button_style         'default' or 'theme'.
 * @var string $button_icon          Icon key: 'none', 'eye', 'search', or 'zoom'.
 * @var string $button_icon_position 'before' or 'after' the label text.
 * @var string $button_position      Active WC hook name or 'wpqv_overlay'.
 * @var string $product_name         Product title used to build the accessible aria-label.
 */

global $post;

$is_overlay = ( 'wpqv_overlay' === $button_position );

// Build element class list.
$element_classes = $button_class . ' button';
if ( $is_overlay ) {
	$element_classes .= ' wpqv__trigger--overlay';
}
if ( 'theme' === $button_style ) {
	$element_classes .= ' wpqv__trigger--theme';
}

// Accessible label: "Quick View: Product Name".
/* translators: %s: product name */
$aria_label = sprintf( __( 'Quick View: %s', 'wc-products-quick-view' ), $product_name );

// Retrieve the icon SVG (empty string when icon is 'none').
$icon_svg = WPQV_Settings::get_icon_svg( $button_icon );
?>
<?php if ( 'default' === $button_style ) : ?>
<div class="wpqv__button-wrap">
<?php endif; ?>

	<button
		type="button"
		class="<?php echo esc_attr( $element_classes ); ?>"
		data-product_id="<?php echo esc_attr( $post->ID ); ?>"
		aria-label="<?php echo esc_attr( $aria_label ); ?>"
	>
		<?php if ( $icon_svg && 'before' === $button_icon_position ) : ?>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is from hardcoded preset, not user input. ?>
			<?php echo $icon_svg; ?>
		<?php endif; ?>

		<?php echo esc_html( $button_label ); ?>

		<?php if ( $icon_svg && 'after' === $button_icon_position ) : ?>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is from hardcoded preset, not user input. ?>
			<?php echo $icon_svg; ?>
		<?php endif; ?>

		<span class="<?php echo esc_attr( $spinner_class ); ?>" aria-hidden="true"></span>
	</button>

<?php if ( 'default' === $button_style ) : ?>
</div>
<?php endif; ?>
