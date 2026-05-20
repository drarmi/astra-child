<?php
/**
 * Sticky purchase bar: inner docks in document flow; fixed via JS when main cart scrolls away.
 *
 * @package Astra Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'is_product' ) || ! is_product() ) {
	return;
}

global $product;

if ( ! $product instanceof WC_Product ) {
	$product = wc_get_product( get_queried_object_id() );
}

if ( ! $product instanceof WC_Product ) {
	return;
}

if ( ! apply_filters( 'nova_sticky_cart_bar_enabled', true, $product ) ) {
	return;
}

$purchasable = $product->is_purchasable() && ( $product->is_in_stock() || $product->backorders_allowed() );
if ( ! $purchasable && ! $product->is_type( 'external' ) ) {
	return;
}

$pid        = (int) $product->get_id();
$thumb_id   = (int) $product->get_image_id();
$thumb_html = $thumb_id ? wp_get_attachment_image( $thumb_id, 'woocommerce_thumbnail', false, array( 'class' => 'nova-sticky-cart__thumb-img', 'loading' => 'lazy' ) ) : wc_placeholder_img( 'woocommerce_thumbnail' );

$max_qty = $product->get_max_purchase_quantity();
$max_qty = is_numeric( $max_qty ) && $max_qty > 0 ? (int) $max_qty : '';

$simple_like = $product->is_type( 'simple' ) || $product->is_type( 'subscription' ) || $product->is_type( 'external' );
$anchor_id   = 'product-' . $pid;
?>
<div id="nova-sticky-cart-bar" class="nova-sticky-cart" role="region" aria-label="<?php esc_attr_e( 'Product purchase', 'woocommerce' ); ?>" data-product-id="<?php echo esc_attr( (string) $pid ); ?>" data-anchor="<?php echo esc_attr( $anchor_id ); ?>">
	<div class="nova-sticky-cart__inner ast-container">
		<div class="nova-sticky-cart__product">
			<div class="nova-sticky-cart__thumb" aria-hidden="true">
				<?php echo $thumb_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image / wc_placeholder_img ?>
			</div>
			<span class="nova-sticky-cart__title"><?php echo esc_html( $product->get_name() ); ?></span>
		</div>
		<div class="nova-sticky-cart__actions">
			<?php if ( $simple_like ) : ?>
				
					<div class="nova-sticky-cart__qty-wrap">
						<label class="screen-reader-text" for="nova-sticky-cart-qty"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></label>
						<div class="nova-qty nova-qty--stepper">
							<input
								type="number"
								id="nova-sticky-cart-qty"
								class="nova-sticky-cart__qty input-text qty text"
								name="nova_sticky_qty_display"
								value="1"
								min="1"
								<?php echo $max_qty ? 'max="' . esc_attr( (string) $max_qty ) . '"' : ''; ?>
								step="1"
							/>
							<div class="nova-qty__spin">
								<button type="button" class="nova-qty__btn nova-qty__btn--up" aria-label="<?php echo esc_attr__( 'Increase quantity', 'woocommerce' ); ?>"><?php echo nova_get_qty_stepper_svg_up(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
								<button type="button" class="nova-qty__btn nova-qty__btn--down" aria-label="<?php echo esc_attr__( 'Reduce quantity', 'woocommerce' ); ?>"><?php echo nova_get_qty_stepper_svg_down(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
							</div>
						</div>
					</div>
				
				
					<button type="button" class="button alt nova-sticky-cart__submit">
						<?php echo esc_html( $product->add_to_cart_text() ); ?>
						<div class="nova-sticky-cart__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
					</button>
				
			<?php else : ?>
				<div class="nova-sticky-cart__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
				<a class="button alt nova-sticky-cart__choose" href="#<?php echo esc_attr( $anchor_id ); ?>">
					<?php echo esc_html( $product->add_to_cart_text() ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
