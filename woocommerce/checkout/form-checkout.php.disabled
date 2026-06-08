<?php
/**
 * Checkout form — three sections: personal details, payment, order review.
 *
 * Payment (`#payment`) lives inside `#customer_details`, not inside `#order_review`.
 *
 * @see woocommerce/templates/checkout/form-checkout.php
 * @package Astra Child
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

$nova_section_nums = function_exists( 'nova_checkout_get_section_numbers' )
	? nova_checkout_get_section_numbers()
	: array(
		'personal' => '1',
		'shipping' => '2',
		'payment'  => '3',
	);

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout nova-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="wcf-col2-set col2-set nova-checkout__customer" id="customer_details">

			<section class="nova-checkout__section nova-checkout__section--personal" aria-labelledby="nova-checkout-personal-heading">
				<header class="nova-checkout__section-head">
					<span class="nova-checkout__section-num" aria-hidden="true">1</span>
					<h2 id="nova-checkout-personal-heading" class="nova-checkout__section-title">
						<?php echo esc_html( apply_filters( 'nova_checkout_personal_section_title', 'פרטים אישיים' ) ); ?>
					</h2>
				</header>

				<div class="nova-checkout__section-body">
					<?php
					/**
					 * Personal / billing (and shipping) fields.
					 *
					 * @since 1.0.0
					 */
					do_action( 'nova_checkout_before_personal_details' );
					?>

					<div class="nova-checkout__personal-fields">
						<div class="wcf-col-1 col-1">
							<?php do_action( 'woocommerce_checkout_billing' ); ?>
						</div>

						<div class="wcf-col-2 col-2">
							<?php do_action( 'woocommerce_checkout_shipping' ); ?>
						</div>

						<?php
						if ( function_exists( 'woocommerce_checkout_additional_fields' ) ) {
							woocommerce_checkout_additional_fields();
						}
						?>
					</div>

					<?php do_action( 'nova_checkout_after_personal_details' ); ?>
				</div>
			</section>

			<?php do_action( 'nova_checkout_before_payment_section' ); ?>

			<section class="nova-checkout__section nova-checkout__section--payment" aria-labelledby="nova-checkout-payment-heading">
				<header class="nova-checkout__section-head">
					<span class="nova-checkout__section-num" aria-hidden="true"><?php echo esc_html( $nova_section_nums['payment'] ); ?></span>
					<h2 id="nova-checkout-payment-heading" class="nova-checkout__section-title">
						<?php echo esc_html( apply_filters( 'nova_checkout_payment_section_title', 'פרטי תשלום' ) ); ?>
					</h2>
				</header>

				<div class="nova-checkout__section-body">
					<?php
					/**
					 * Payment gateways and place-order button (`#payment`).
					 *
					 * @since 1.0.0
					 */
					do_action( 'nova_checkout_before_payment_details' );
					woocommerce_checkout_payment();
					?>

					<footer class="nova-checkout__payment-footer">
						<?php if ( apply_filters( 'nova_checkout_show_payment_secure_note', true ) ) : ?>
							<p class="nova-checkout__payment-secure">
								<?php echo esc_html( apply_filters( 'nova_checkout_payment_secure_note', 'התשלום באתר בטוח ומאובטח SSL לפי החוק.' ) ); ?>
							</p>
						<?php endif; ?>

						<?php
						/**
						 * Card brand icons row (bottom of payment section).
						 *
						 * @since 1.0.0
						 */
						do_action( 'nova_checkout_payment_icons' );
						?>
					</footer>

					<?php do_action( 'nova_checkout_after_payment_details' ); ?>
				</div>
			</section>

		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>

	<div class="wcf-order-wrap nova-checkout__section nova-checkout__section--order">

		<?php do_action( 'cartflows_woocommerce_checkout_before_order_heading' ); ?>

		<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

		<h3 id="order_review_heading"><?php echo wp_kses_post( apply_filters( 'cartflows_woo_your_order_text', esc_html__( 'Your order', 'woocommerce' ) ) ); ?></h3>

		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php do_action( 'woocommerce_checkout_order_review' ); ?>
		</div>

		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

	</div>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
