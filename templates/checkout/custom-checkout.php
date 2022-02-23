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

<form id="form-checkout">
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

				<?php if ( 'mla' === $site_id ) : ?>
					<span id="mp_promotion_link"> | </span>
					<a href="https://www.mercadopago.com.ar/cuotas" id="mp_checkout_link" class="mp-checkout-link mp-pl-10" target="_blank">
						<?php echo esc_html__( 'See current promotions', 'woocommerce-mercadopago' ); ?>
					</a>
				<?php endif; ?>
				<hr>
			</div>
		</div>

		<div class='mp-checkout-custom-card-form'>
			<p class='mp-checkout-custom-card-form-title'>Completa los datos de tu tarjeta</p>
			<div class='mp-checkout-custom-card-row'>
				<input-label isOptinal=false message="Número de cartão" for='mp-card-number'></input-label>
					<div class="mp-checkout-custom-card-input" id="form-checkout__cardNumber-container"></div>
					<input-helper isVisible=false message="Dado obrigatório" input-id="mp-card-number-helper">
				</input-helper>
			</div>

			<div class='mp-checkout-custom-card-row' id="mp-card-holder-div">
				<input-label message="Nome" isOptinal=false></input-label>
				<input type="text" class="mp-checkout-custom-card-input" placeholder="Ex.: María López"
					id="form-checkout__cardholderName" name="mp-card-holder-name" data-checkout="cardholderName" />
				<input-helper isVisible=false message="Dado obrigatório" input-id="mp-card-holder-name-helper"
					data-main="mp-card-holder-name">
				</input-helper>
			</div>

			<div class='mp-checkout-custom-card-row mp-checkout-custom-dual-column-row'>
				<div class='mp-checkout-custom-card-column'>
				<input-label message="Vencimento" isOptinal=false></input-label>
				<div id="form-checkout__cardExpirationDate-container"
					class="mp-checkout-custom-card-input mp-checkout-custom-left-card-input">
				</div>
				<input-helper isVisible=false message="Dado obrigatório" input-id="mp-expiration-date-helper">
				</input-helper>
				</div>

				<div class='mp-checkout-custom-card-column'>
					<input-label message="Security code" isOptinal=false></input-label>
					<div id="form-checkout__securityCode-container" class="mp-checkout-custom-card-input"></div>
					<p id="mp-security-code-info" class="mp-checkout-custom-info-text"></p>
					<input-helper isVisible=false message="Dado obrigatório" input-id="mp-cvv-helper">
					</input-helper>
				</div>
			</div>

			<div id="mp-doc-div" class="mp-checkout-custom-input-document" style="display: none;">
				<input-document label-message="Documento" helper-message="Documento Inválido"
					input-name="identificationNumber" hidden-id="form-checkout__identificationNumber"
					input-data-checkout="docNumber" select-name="identificationType"
					select-id="form-checkout__identificationType" select-data-checkout="docType"
					flag-error="docNumberError">
				</input-document>
			</div>
		</div>

		<div id="mp-checkout-custom-installments" class="mp-checkout-custom-installments-display-none">
			<p class='mp-checkout-custom-card-form-title'>Selecciona la cantidad de cuotas</p>
			<div id="mp-checkout-custom-issuers-container" class="mp-checkout-custom-issuers-container-display-none">
				<div class="mp-input-select-input">
					<select name="issuer" id="form-checkout__issuer" class="mp-input-select-select"></select>
				</div>
			</div>

			<div id="mp-checkout-custom-installments-container" class="mp-checkout-custom-installments-container"></div>
			<select style="display: none;" data-checkout="installments" name="installments"
				id="form-checkout__installments" class="mp-input-select-select">
			</select>

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
				link-src="<?php echo esc_html($link_terms_and_conditions); ?>"
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

	// MLB
	var mp_security_fields_loaded = false;
	jQuery( document ).on( 'updated_checkout', function() {
		if(!mp_security_fields_loaded){
			const mp = new MercadoPago('APP_USR-f1b66425-a272-402b-9fc0-5b7a3a93d55f');
			const cardForm = mp.cardForm({
				amount: '1000.5',
				iframe: true,
				form: {
					id: 'form-checkout',
					cardNumber: {
						id: 'form-checkout__cardNumber-container',
						placeholder: '0000 0000 0000 0000',
						style: {
							"font-size": "16px",
							"height": "40px",
							"padding": "14px"
						}
					},
					cardholderName: {
						id: 'form-checkout__cardholderName',
						placeholder: "Ex.: María López",
					},
					cardExpirationDate: {
						id: 'form-checkout__cardExpirationDate-container',
						placeholder: 'mm/aaaa',
						style: {
							"font-size": "16px",
							"height": "40px",
							"padding": "14px"
						}
					},
					securityCode: {
						id: 'form-checkout__securityCode-container',
						placeholder: '123',
						style: {
							"font-size": "16px",
							"height": "40px",
							"padding": "14px"
						}
					},
					identificationType: {
						id: 'form-checkout__identificationType',
					},
					identificationNumber: {
						id: 'form-checkout__identificationNumber',
					},
					issuer: {
						id: 'form-checkout__issuer',
						placeholder: 'Banco emissor'
					},
					installments: {
						id: 'form-checkout__installments',
						placeholder: 'Parcelas'
					},
				},
				callbacks: {
				onFormMounted: function (error) {
					if (error) return console.log('Callback para tratar o erro: montando o cardForm ', error)
				},
				onInstallmentsReceived: (error, installments) => {
					if (error) {
					return console.warn('Installments handling error: ', error)
					}
					setChangeEventOnInstallments(getCountry(), installments);
				},
				onCardTokenReceived: (error, token) => {
					if (error) {
					return console.warn('Token handling error: ', error);
					}
					// this.placeOrder();
				},
				onPaymentMethodsReceived: (error, paymentMethods) => {
					try {
					if (paymentMethods) {
						setCvvHint(paymentMethods[0].settings[0].security_code);
						clearInputs();
						removeInputHelper('mp-card-number');
						setImageCard(paymentMethods[0].thumbnail);
						handleInstallments(paymentMethods[0].payment_type_id);
						loadAdditionalInfo(paymentMethods[0].additional_info_needed);
						additionalInfoHandler();
					}
					else {
						showInputHelper('mp-card-number');
					}
					} catch (error) {
					showInputHelper('mp-card-number');
					}
				},
				onSubmit: function (event) {
					event.preventDefault();
					alert("Implementar no plugin");
					const {
					paymentMethodId: payment_method_id,
					issuerId: issuer_id,
					amount,
					token,
					installments,
					identificationNumber,
					identificationType
					} = cardForm.getCardFormData();

					fetch('/process_payment', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({
						token,
						issuer_id,
						payment_method_id,
						transaction_amount: Number(amount),
						installments: Number(installments),
						description: 'product description',
						payer: {
						identification: {
							type: identificationType,
							number: identificationNumber
						}
						}
					})
					})
				},
				onValidityChange: function (error, field) {
					if (error) {
					if (field == 'cardNumber') { document.getElementById('form-checkout__cardNumber-container').style.background = 'no-repeat #fff'; }
					return showInputHelper(inputHelperName(field));
					}
					return removeInputHelper(inputHelperName(field));
				}
				}
			});
			mp_security_fields_loaded = true;
		}
	});

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

</script>
