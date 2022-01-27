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

<div class='mp-checkout-custom-container'>
	<?php if ( true === $test_mode ) : ?>
		<div class="mp-checkout-pro-test-mode">
			<test-mode
				title="<?php echo esc_html_e( 'Checkout Custom in Test Mode', 'woocommerce-mercadopago' ); ?>"
				description="<?php echo esc_html_e( 'Use the test-specific cards that are in the ', 'woocommerce-mercadopago' ); ?>"
				link-text="<?php echo esc_html_e( 'test mode rules.', 'woocommerce-mercadopago' ); ?>"
				link-src="<?php echo esc_html( $test_mode_link ); ?>"
			>
			</test-mode>
		</div>
	<?php endif; ?>

	<?php if ( 'yes' === $wallet_button ) : ?>
		<div class='mp-wallet-button-container'>
			<div class='mp-wallet-button-title'>
				<img src="<?php echo esc_url( plugins_url( '../assets/images/mp_logo.png', plugin_dir_path( __FILE__ ) ) ); ?>">
				<span>Paga con Mercado Pago</span>
			</div>

			<div class='mp-wallet-button-description'>
				Paga más rápido con tus tarjetas guardadas y sin completar datos.
			</div>

			<div class='mp-wallet-button-button'>
				<button id="mp-wallet-button" onclick="submitWalletButton(event)">
					Pagar con Mercado Pago
				</button>
			</div>
		</div>
	<?php endif; ?>

	<div class='mp-checkout-custom-available-payments'>
		<div class='mp-checkout-custom-available-payments-header'>
			<div class="mp-checkout-custom-available-payments-title">
				<img src="<?php echo esc_url( plugins_url( '../assets/images/purple_card.png', plugin_dir_path( __FILE__ ) ) ); ?>" class='mp-icon'>
				<p>¿Con qué tarjeta puedes pagar?</p>
			</div>

			<img
				src="<?php echo esc_url( plugins_url( '../assets/images/chefron-down.png', plugin_dir_path( __FILE__ ) ) ); ?>"
				class='mp-checkout-custom-available-payments-collapsible'
			/>
		</div>

		<div class='mp-checkout-custom-available-payments-content'>
			<payment-methods methods='<?php echo wp_json_encode($payment_methods); ?>'></payment-methods>
			<hr>
		</div>
	</div>

	<div class='mp-checkout-custom-card-form'>
		<p class='mp-checkout-custom-card-form-title'>Completa los datos de tu tarjeta</p>
		<div class='mp-checkout-custom-card-row'>
			<input-label isOptinal=false message="Número de cartão" for='mp-card-number'></input-label>
			<input
				type="text"
				class="mp-checkout-custom-card-input"
				autocomplete="off"
				maxlength="23"
				placeholder="1234 1234 1234 1234"
				id="mp-card-number"
				name="mp-card-number"
				data-checkout="cardNumber"
			/>
			<input-helper
				isVisible=false
				message="Número incompleto"
				input-id="mp-card-number-helper"
				data-main="mp-card-number"
			>
			</input-helper>
		</div>

		<div class='mp-checkout-custom-card-row' id="mp-card-holder-div">
			<input-label message="Nome" isOptinal=false></input-label>
			<input
				type="text"
				class="mp-checkout-custom-card-input"
				placeholder="Ex.: María López"
				id="mp-card-holder-name"
				name="mp-card-holder-name"
				data-checkout="cardholderName"
			>
			<input-helper
				isVisible=false
				message="Dado obrigatório"
				input-id="mp-card-holder-name-helper"
				data-main="mp-card-holder-name"
			>
			</input-helper>
		</div>

		<div class='mp-checkout-custom-card-row mp-checkout-custom-dual-column-row'>
			<div class='mp-checkout-custom-card-column'>
				<input-label message="Vencimento" isOptinal=false></input-label>
				<input
					type="text"
					class="mp-checkout-custom-card-input mp-checkout-custom-left-card-input"
					maxlength="5"
					placeholder="mm/aa"
					id="mp-card-expiration-date"
					name="mp-card-expiration-date"
					data-checkout="cardExpirationDate"
					onblur="mpValidateMonthYear()"
				/>
				<input-helper
					isVisible=false
					message="Dado obrigatório"
					input-id="mp-card-expiration-date-helper"
					data-main="mp-card-expiration-date"
				>
				</input-helper>
			</div>

			<div class='mp-checkout-custom-card-column'>
				<input-label message="Código de Segurança" isOptinal=false for='mp-card-holder-name'></input-label>
				<input
					type="text"
					class="mp-checkout-custom-card-input"
					placeholder="123"
					id="mp-security-code"
					name="mp-security-code"
					data-checkout="securityCode"
				/>
				<p id="mp-security-code-info" class="mp-checkout-custom-info-text"></p>
				<input-helper
					isVisible=false
					message="Dado obrigatório"
					input-id="mp-security-code-helper"
					data-main="mp-security-code"
				>
				</input-helper>
			</div>
		</div>

		<div id="mp-doc-div" class="mp-checkout-custom-input-document" style='display: none;'>
			<input-document
				label-message = "Documento"
				helper-message = "Documento Inválido"
				input-name = "docNumber"
				input-data-checkout="docNumber"
				select-name = "docType"
				select-data-checkout="docType"
				flag-error = "docNumberError"
			>
			</input-document>
			<input-helper
				isVisible=false
				message="Dado obrigatório"
				input-id="mp-doc-number-helper"
				data-main="docNumber"
			>
			</input-helper>
		</div>
	</div>

	<div id="mp-checkout-custom-installments" class="mp-checkout-custom-installments-display-none">
		<p class='mp-checkout-custom-card-form-title'>Selecciona la cantidad de cuotas</p>
		<input-helper
			isVisible=false
			message="Dado obrigatório"
			input-id="mp-checkout-custom-installments-helper"
			data-main="mp-checkout-custom-installments"
		>
		</input-helper>
		<div id="mp-checkout-custom-issuers-container" class="mp-checkout-custom-issuers-container-display-none">
			<input-select
				optional="false"
				label="<?php echo esc_html_e( 'Bank', 'woocommerce-mercadopago' ); ?>"
				option-placeholder="<?php echo esc_html_e( 'Choose an option', 'woocommerce-mercadopago' ); ?>"
				name="mp-issuer"
				data-checkout="issuer"
			>
			</input-select>
			<input-helper
				isVisible=false
				message="Dado obrigatório"
				input-id="mp-issuer-helper"
				data-main="mp-issuer"
			>
			</input-helper>
		</div>

		<div id="mp-checkout-custom-installments-container" class="mp-checkout-custom-installments-container"></div>

		<div id="mp-checkout-custom-box-input-tax-cft">
			<div id="mp-checkout-custom-box-input-tax-tea">
				<div id="mp-checkout-custom-tax-tea-text"></div>
			</div>
			<div id="mp-checkout-custom-tax-cft-text"></div>
		</div>
	</div>

	<div class="mp-checkout-custom-terms-and-conditions">
		<terms-and-conditions
			description="By continuing, you agree with our"
			link-text="Terms and conditions"
			link-src="https://developers.mercadopago.com"
		>
		</terms-and-conditions>
	</div>
	<div id="mercadopago-utilities">
		<input type="hidden" id="mp-amount" value='<?php echo esc_textarea( $amount ); ?>' name="mercadopago_custom[amount]" />
		<input type="hidden" id="currency_ratio" value='<?php echo esc_textarea( $currency_ratio ); ?>' name="mercadopago_custom[currency_ratio]" />
		<input type="hidden" id="paymentMethodId" name="mercadopago_custom[paymentMethodId]" />
		<input type="hidden" id="mp_checkout_type" name="mercadopago_custom[checkout_type]" value="custom" />
		<input type="hidden" id="cardExpirationMonth" data-checkout="cardExpirationMonth">
		<input type="hidden" id="cardExpirationYear" data-checkout="cardExpirationYear">
		<input type="hidden" id="cardTokenId" name="mercadopago_custom[token]" />
	</div>
</div>
</form>

<script type="text/javascript">
	function submitWalletButton(event) {
		event.preventDefault();
		jQuery('#mp_checkout_type').val('wallet_button');
		jQuery('form.checkout, form#order_review').submit();
	}

	var availablePayment = document.getElementsByClassName('mp-checkout-custom-available-payments')[0];
	var collapsible = availablePayment.getElementsByClassName('mp-checkout-custom-available-payments-header')[0];

	collapsible.addEventListener("click", function () {
		var icon = collapsible.getElementsByClassName('mp-checkout-custom-available-payments-collapsible')[0];
		var content = availablePayment.getElementsByClassName('mp-checkout-custom-available-payments-content')[0];

		if (content.style.maxHeight) {
			content.style.maxHeight = null;
			content.style.padding = "0px";
			icon.src = "<?php echo esc_url( plugins_url( '../assets/images/chefron-down.png', plugin_dir_path( __FILE__ ) ) ); ?>";
		} else {
			content.style.maxHeight = content.scrollHeight + "px";
			content.style.padding = "24px 0px 0px";
			icon.src = "<?php echo esc_url( plugins_url( '../assets/images/chefron-up.png', plugin_dir_path( __FILE__ ) ) ); ?>";
		}
	});

	var cardNumber = document.getElementById('mp-card-number');
	cardNumber.addEventListener('input', (e) => {
		e.target.value = e.target.value.replace(/\D/g, "")
			.replace(/^(\d{4})(\d)/g, "$1 $2")
			.replace(/^(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3")
			.replace(/^(\d{4})\s(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3 $4");
	});	

	var cardExpirationDate = document.getElementById('mp-card-expiration-date');
	cardExpirationDate.addEventListener('input', (e) => {
		e.target.value = e.target.value.replace(/\D/g, "")
		.replace(/(\d{2})(\d)/, "$1/$2")
		.replace(/(\d{2})(\d{2})$/, "$1$2");
	});

	function mpValidateMonthYear() {
		var date = document.getElementById('mp-card-expiration-date').value.split('/');
		document.getElementById('cardExpirationMonth').value = date[0];
		document.getElementById('cardExpirationYear').value = `20${date[1]}`;
	}

	cardNumber.addEventListener('change', function(){
		guessPaymentMethod()
	});

	document.querySelector('form[name=checkout]').addEventListener('submit', (event) => {
		event.preventDefault();
		return mercadoPagoFormHandler();
	});

</script>
