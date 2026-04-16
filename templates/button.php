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
 * @var int    $product_id            Product ID for the data attribute.
 * @var string $spinner_class         CSS class for the inline loading spinner span.
 * @var string $button_label          Visible button label text.
 * @var string $button_style          'theme' or 'plugin'.
 * @var string $button_icon           Icon key: 'none', 'eye', 'search', or 'zoom'.
 * @var string $button_icon_position  'before' or 'after' the label text.
 * @var string $element_classes       Full class string for the button element.
 * @var string $wrapper_classes       Full class string for the wrapper div.
 * @var string $aria_label            Accessible label for the button.
 * @var string $icon_svg              Inline SVG markup for the selected icon, or empty string.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="<?php echo esc_attr( $wrapper_classes ); ?>">

	<button
		type="button"
		class="<?php echo esc_attr( $element_classes ); ?>"
		data-product_id="<?php echo esc_attr( $product_id ); ?>"
		aria-label="<?php echo esc_attr( $aria_label ); ?>"
		aria-haspopup="dialog"
		aria-controls="wcqv-dialog"
	>
		<?php if ( 'before' === $button_icon_position ) : ?>
			<?php if ( $icon_svg ) : ?>
				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is from hardcoded preset, not user input. ?>
				<?php echo $icon_svg; ?>
			<?php endif; ?>
			<span class="<?php echo esc_attr( $spinner_class ); ?>" aria-hidden="true"></span>
		<?php endif; ?>

		<span class="wcqv__trigger-label"><?php echo esc_html( $button_label ); ?></span>

		<?php if ( 'after' === $button_icon_position ) : ?>
			<?php if ( $icon_svg ) : ?>
				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is from hardcoded preset, not user input. ?>
				<?php echo $icon_svg; ?>
			<?php endif; ?>
			<span class="<?php echo esc_attr( $spinner_class ); ?>" aria-hidden="true"></span>
		<?php endif; ?>
	</button>

</div>
