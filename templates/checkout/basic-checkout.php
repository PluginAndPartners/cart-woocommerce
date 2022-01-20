<?php
/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 * @package MercadoPago
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="mp-checkout-pro-container">
	<div class="mp-checkout-pro-content">
		<?php if ( true === $test_mode ) : ?>
			<div class="mp-checkout-pro-test-mode">
				<test-mode
					title="<?php echo esc_html_e( 'Checkout Pro in Test Mode', 'woocommerce-mercadopago' ); ?>"
					description="<?php echo esc_html_e( 'Use Mercado Pago\'s payment methods without real charges. ', 'woocommerce-mercadopago' ); ?>"
					link-text="<?php echo esc_html_e( 'See the rules for the test mode.', 'woocommerce-mercadopago' ); ?>"
					link-src="<?php echo esc_html( $test_mode_link ); ?>"
				>
				</test-mode>
			</div>
		<?php endif; ?>

		<checkout-benefits
			title="<?php echo esc_html_e( 'Pay faster with Mercado Pago', 'woocommerce-mercadopago' ); ?>"
			items='[
				"<?php echo esc_html_e( 'Secure payment', 'woocommerce-mercadopago' ); ?>",
				"<?php echo esc_html_e( 'No need to fill out details', 'woocommerce-mercadopago' ); ?>",
				"<?php echo esc_html_e( 'Installments available', 'woocommerce-mercadopago' ); ?>"
			]'
			list-style-type-src="<?php echo esc_html( $list_style_type_src ); ?>"
			list-style-type-alt="<?php echo esc_html_e( 'List style type blue check', 'woocommerce-mercadopago' ); ?>"
		>
		</checkout-benefits>

		<div class="mp-checkout-pro-payment-methods">
			<payment-methods methods="<?php echo esc_html( $payment_methods ); ?>"></payment-methods>
		</div>
	</div>

	<?php if ( 'redirect' === $method ) : ?>
		<div class="mp-checkout-pro-redirect">
			<checkout-redirect
				text="<?php echo esc_html_e( 'By confirming your purchase, you will be redirected to your Mercado Pago account', 'woocommerce-mercadopago' ); ?>"
				alt="<?php echo esc_html_e( 'Checkout Pro redirect info image', 'woocommerce-mercadopago' ); ?>"
				src="<?php echo esc_html( $redirect_image ); ?>"
			>
			</checkout-redirect>
		</div>
	<?php endif; ?>
</div>

<!-- Terms and conditions link at checkout -->
<div class="mp-checkout-pro-terms-and-conditions">
	<terms-and-conditions
		description="<?php echo esc_html_e( 'By continuing, you agree with our', 'woocommerce-mercadopago' ); ?>"
		link-text="<?php echo esc_html_e( 'Terms and conditions', 'woocommerce-mercadopago' ); ?>"
		link-src="<?php echo esc_html( $link_terms_and_conditions ); ?>"
	>
	</terms-and-conditions>
</div>
