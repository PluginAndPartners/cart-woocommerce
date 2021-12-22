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

<div class="mp-panel-custom-checkout">
	<?php
		// @codingStandardsIgnoreLine
		echo $checkout_alert_test_mode;
	?>
	<div class="mp-row-checkout">
		<?php if ( 'yes' === $wallet_button ) : ?>
			<div class='mp-wallet-button-container'>
				<div class='mp-wallet-button-title''>
					<img src='<?php echo esc_url( plugins_url( '../assets/images/mp_logo.png', plugin_dir_path( __FILE__ ) ) ); ?>'>
					Paga con Mercado Pago
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
					<p><?php echo esc_html__( 'With what cards can I pay', 'woocommerce-mercadopago' ); ?></p>
				</div>
				<img src="<?php echo esc_url( plugins_url( '../assets/images/chefron-up.png', plugin_dir_path( __FILE__ ) ) ); ?>" class='mp-checkout-custom-available-payments-collapsible'>
			</div>
			<div class='mp-checkout-custom-available-payments-content'>
				<payment-methods methods='<?php echo wp_json_encode($payment_methods); ?>'>
				</payment-methods>
				<hr>
				<?php if ( 'MLA' === $site_id ) : ?>
					<span id="mp_promotion_link"> | </span>
					<a href="https://www.mercadopago.com.ar/cuotas" id="mp_checkout_link" class="mp-checkout-link mp-pl-10" target="_blank">
						<?php echo esc_html__( 'See current promotions', 'woocommerce-mercadopago' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class='mp-checkout-custom-card-form'>
			<p class='mp-checkout-custom-card-form-title'>
				<?php echo esc_html__( 'Enter your card details', 'woocommerce-mercadopago' ); ?>
			</p>
			<div class='mp-checkout-custom-card-row'>
				<input-label isOptinal=false message="<?php echo esc_html__( 'Card number', 'woocommerce-mercadopago' ); ?>" for='mp-card-number'></input-label>
				<input 
					type="text" 
					class="mp-checkout-custom-card-input" 
					id="mp-card-number" 
					autocomplete="off" 
					maxlength="19"
					placeholder="1234 1234 1234 1234" 
					onkeyup="mpCreditMaskDate(this, mpMcc);"
				/>
				<input-helper isVisible=false message="Número incompleto" for='mp-card-number'></input-helper>
			</div>

			<div class='mp-checkout-custom-card-row'>
				<input-label 
					message="<?php echo esc_html__( 'Name and surname of the cardholder', 'woocommere-mercadopago' ); ?>" 
					isOptinal=false
				></input-label>
				<input 
					type="text" 
					class="mp-checkout-custom-card-input" 
					id="mp-card-holder-name" 
					placeholder="Ex.: María López" 
				/>
				<input-helper 
					isVisible=false 
					message="Dado obrigatório"
				></input-helper>
			</div>

			<div class='mp-checkout-custom-card-row mp-checkout-custom-flex-direction-row'>
				<div class='mp-checkout-custom-card-column'>
					<input-label 
						message="<?php echo esc_html__( 'Expiration date', 'woocommerce-mercadopago' ); ?>" 
						isOptinal=false
					></input-label>
					<input 
						type="text" 
						class="mp-checkout-custom-card-input" 
						id="mp-card-expiration-date"						
						maxlength="5" 
						placeholder="mm/aa" 
					/>
					<input-helper isVisible=false message="Dado obrigatório"></input-helper>
				</div>
				<div class='mp-checkout-custom-card-column'>
					<input-label 
						message="<?php echo esc_html__( 'Security code', 'woocommerce-mercadopago' ); ?>"
						isOptinal=false
					></input-label>
					<input 
						type="text" 
						class="mp-checkout-custom-card-input" 
						id="mp-security-code" 
						maxlength="4"
						placeholder="123" 
					/>
					<input-helper 
						isVisible=false 
						message="Dado obrigatório"
					></input-helper>
				</div>
			</div>

			<input-document 
				label-message = '<?php echo esc_html__( 'Document number', 'woocommerce-mercadopago' ); ?> '
				helper-message = '<?php echo esc_html__( 'Invalid Document Number', 'woocommerce-mercadopago' ); ?>'
				input-name = 'docNumber'
				select-name = 'docType'
				flag-error = 'docNumberError'
				documents = '["CPF","CNPJ","CI","Outro"]'
				validate = true
			></input-document>

			<div name='mp-installments'>
			</div>
		</div>

		<div class="mp-col-md-12">
			<div class="frame-tarjetas">
				<!-- Title enter your card details -->
				<p class="mp-subtitle-custom-checkout"><?php echo esc_html__( 'Enter your card details', 'woocommerce-mercadopago' ); ?></p>

				<div id="mercadopago-form">
					<!-- Input Card number -->
					<div class="mp-row-checkout mp-pt-10">
						<div class="mp-col-md-12">
							<label for="mp-card-number" class="mp-label-form"><?php echo esc_html__( 'Card number', 'woocommerce-mercadopago' ); ?> <em>*</em></label>
							<input type="text" onkeyup="mpCreditMaskDate(this, mpMcc);" class="mp-form-control mp-mt-5" id="mp-card-number" data-checkout="cardNumber" autocomplete="off" maxlength="23" />

							<span class="mp-error mp-mt-5" id="mp-error-205" data-main="#mp-card-number"><?php echo esc_html__( 'Card number', 'woocommerce-mercadopago' ); ?></span>
							<span class="mp-error mp-mt-5" id="mp-error-E301" data-main="#mp-card-number"><?php echo esc_html__( 'Invalid Card Number', 'woocommerce-mercadopago' ); ?></span>
						</div>
					</div>
					<!-- Input Name and Surname -->
					<div class="mp-row-checkout mp-pt-10" id="mp-card-holder-div">
						<div class="mp-col-md-12">
							<label for="mp-card-holder-name" class="mp-label-form"><?php echo esc_html__( 'Name and surname of the cardholder', 'woocommerce-mercadopago' ); ?> <em>*</em></label>
							<input type="text" class="mp-form-control mp-mt-5" id="mp-card-holder-name" data-checkout="cardholderName" autocomplete="off" />

							<span class="mp-error mp-mt-5" id="mp-error-221" data-main="#mp-card-holder-name"><?php echo esc_html__( 'Invalid Card Number', 'woocommerce-mercadopago' ); ?></span>
							<span class="mp-error mp-mt-5" id="mp-error-E301" data-main="#mp-card-holder-name"><?php echo esc_html__( 'Invalid Card Number', 'woocommerce-mercadopago' ); ?></span>
						</div>
					</div>

					<div class="mp-row-checkout mp-pt-10">
						<!-- Input expiration date -->
						<div class="mp-col-md-6 mp-pr-15">
							<label for="mp-card-expiration-date" class="mp-label-form"><?php echo esc_html__( 'Expiration date', 'woocommerce-mercadopago' ); ?> <em>*</em></label>
							<input type="text" onkeyup="mpCreditMaskDate(this, mpDate);" onblur="mpValidateMonthYear()" class="mp-form-control mp-mt-5" id="mp-card-expiration-date" data-checkout="cardExpirationDate" autocomplete="off" placeholder="MM/AAAA" maxlength="7" />
							<input type="hidden" id="cardExpirationMonth" data-checkout="cardExpirationMonth">
							<input type="hidden" id="cardExpirationYear" data-checkout="cardExpirationYear">
							<span class="mp-error mp-mt-5" id="mp-error-208" data-main="#mp-card-expiration-date"><?php echo esc_html__( 'Invalid Expiration Date', 'woocommerce-mercadopago' ); ?></span>
						</div>
						<!-- Input Security Code -->
						<div class="mp-col-md-6">
							<label for="mp-security-code" class="mp-label-form"><?php echo esc_html__( 'Security code', 'woocommerce-mercadopago' ); ?> <em>*</em></label>
							<input type="text" onkeyup="mpCreditMaskDate(this, mpInteger);" class="mp-form-control mp-mt-5" id="mp-security-code" data-checkout="securityCode" autocomplete="off" maxlength="4" />
							<p class="mp-desc mp-mt-5 mp-mb-0" data-main="#mp-security-code"><?php echo esc_html__( 'Last 3 numbers on the back', 'woocommerce-mercadopago' ); ?></p>
							<span class="mp-error mp-mt-5" id="mp-error-224" data-main="#mp-security-code"><?php echo esc_html__( 'Check the informed security code.', 'woocommerce-mercadopago' ); ?></span>
							<span class="mp-error mp-mt-5" id="mp-error-E302" data-main="#mp-security-code"><?php echo esc_html__( 'Check the informed security code.', 'woocommerce-mercadopago' ); ?></span>
						</div>
					</div>

					<div class="mp-col-md-12">
						<div class="frame-tarjetas">
							<!-- Title installments -->
							<p class="mp-subtitle-custom-checkout"><?php echo esc_html__( 'In how many installments do you want to pay', 'woocommerce-mercadopago' ); ?></p>

							<!-- Select issuer -->
							<div class="mp-row-checkout mp-pt-10">
								<div id="mp-issuer-div" class="mp-col-md-4 mp-pr-15">
									<div class="mp-issuer">
										<label for="mp-issuer" class="mp-label-form"><?php echo esc_html__( 'Issuer', 'woocommerce-mercadopago' ); ?> </label>
										<select class="mp-form-control mp-pointer mp-mt-5" id="mp-issuer" data-checkout="issuer" name="mercadopago_custom[issuer]"></select>
									</div>
								</div>

								<!-- Select installments -->
								<div id="installments-div" class="mp-col-md-12">
									<?php if ( 1 !== $currency_ratio ) : ?>
										<label for="installments" class="mp-label-form">
											<div class="mp-tooltip">
												<span class="mp-tooltiptext">
													<?php
													echo esc_html__( 'Converted payment of', 'woocommerce-mercadopago' ) . ' ' .
													esc_html( $woocommerce_currency ) . ' ' . esc_html__( 'for', 'woocommerce-mercadopago' ) . ' ' .
													esc_html( $account_currency );
													?>
												</span>
											</div>
											<em>*</em>
										</label>
									<?php else : ?>
										<label for="mp-installments" class="mp-label-form"><?php echo esc_html__( 'Select the number of installment', 'woocommerce-mercadopago' ); ?></label>
									<?php endif; ?>

									<select class="mp-form-control mp-pointer mp-mt-5" id="mp-installments" data-checkout="installments" name="mercadopago_custom[installments]"></select>

									<div id="mp-box-input-tax-cft">
										<div id="mp-box-input-tax-tea">
											<div id="mp-tax-tea-text"></div>
										</div>
										<div id="mp-tax-cft-text"></div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div id="mp-doc-div" class="mp-col-md-12 mp-doc">
						<div class="frame-tarjetas">
							<!-- Title document -->
							<p class="mp-subtitle-custom-checkout"><?php echo esc_html__( 'Enter your document number', 'woocommerce-mercadopago' ); ?></p>

							<div id="mp-doc-type-div" class="mp-row-checkout mp-pt-10">
								<!-- Select Doc Type -->
								<div class="mp-col-md-4 mp-pr-15">
									<label for="docType" class="mp-label-form">
										<?php echo esc_html__( 'Type', 'woocommerce-mercadopago' ); ?>
									</label>
									<select id="docType" class="mp-form-control mp-pointer mp-mt-04rem" data-checkout="docType"></select>
								</div>

								<!-- Input Doc Number -->
								<div id="mp-doc-number-div" class="mp-col-md-8">
									<label for="docNumber" class="mp-label-form"><?php echo esc_html__( 'Document number', 'woocommerce-mercadopago' ); ?> <em>*</em></label>
									<input type="text" class="mp-form-control mp-mt-04rem" id="docNumber" data-checkout="docNumber" autocomplete="off" />
									<p class="mp-desc mp-mt-5 mp-mb-0" data-main="#mp-security-code"><?php echo esc_html__( 'Only numbers', 'woocommerce-mercadopago' ); ?></p>
									<span class="mp-error mp-mt-5" id="mp-error-324" data-main="#docNumber"><?php echo esc_html__( 'Invalid Document Number', 'woocommerce-mercadopago' ); ?></span>
									<span class="mp-error mp-mt-5" id="mp-error-E301" data-main="#docNumber"><?php echo esc_html__( 'Invalid Document Number', 'woocommerce-mercadopago' ); ?></span>
								</div>
							</div>
						</div>
					</div>

					<div class="mp-col-md-12 mp-pt-10">
						<div class="frame-tarjetas">
							<div class="mp-row-checkout">
								<p class="mp-obrigatory">
									<em>*</em> <?php echo esc_html__( 'Obligatory field', 'woocommerce-mercadopago' ); ?>
								</p>
							</div>
						</div>
					</div>
				</div>

				<!-- NOT DELETE LOADING-->
				<div id="mp-box-loading"></div>

			</div>
		</div>

		<div id="mercadopago-utilities">
			<input type="hidden" id="mp-amount" value='<?php echo esc_textarea( $amount ); ?>' name="mercadopago_custom[amount]" />
			<input type="hidden" id="currency_ratio" value='<?php echo esc_textarea( $currency_ratio ); ?>' name="mercadopago_custom[currency_ratio]" />
			<input type="hidden" id="campaign_id" name="mercadopago_custom[campaign_id]" />
			<input type="hidden" id="campaign" name="mercadopago_custom[campaign]" />
			<input type="hidden" id="mp-discount" name="mercadopago_custom[discount]" />
			<input type="hidden" id="paymentMethodId" name="mercadopago_custom[paymentMethodId]" />
			<input type="hidden" id="token" name="mercadopago_custom[token]" />
			<input type="hidden" id="mp_checkout_type" name="mercadopago_custom[checkout_type]" value="custom" />
		</div>

	</div>
</div>
<!-- Terms and conditions link at checkout -->
<div>       
	<p class="mp-terms-and-conditions"> 
		<?php echo esc_html($text_prefix); ?> 		
		<a target="_blank" href="<?php echo esc_html($link_terms_and_conditions); ?>">  <?php echo esc_html($text_suffix); ?> </a>
	</p> 		
</div>

<script type="text/javascript">
	function mpCreditExecmascara() {
		v_obj.value = v_fun(v_obj.value)
	}

	//Card mask date input
	function mpCreditMaskDate(o, f) {
		v_obj = o
		v_fun = f
		setTimeout("mpCreditExecmascara()", 1);
	}

	function mpMcc(value) {
		if(mpIsMobile()){
			return value;
		}
		value = value.replace(/\D/g, "");
		value = value.replace(/^(\d{4})(\d)/g, "$1 $2");
		value = value.replace(/^(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3");
		value = value.replace(/^(\d{4})\s(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3 $4");
		return value;
	}

	function mpDate(v) {
		v = v.replace(/\D/g, "");
		v = v.replace(/(\d{2})(\d)/, "$1/$2");
		v = v.replace(/(\d{2})(\d{2})$/, "$1$2");
		return v;
	}

	// Explode date to month and year
	function mpValidateMonthYear() {
		var date = document.getElementById('mp-card-expiration-date').value.split('/');
		document.getElementById('cardExpirationMonth').value = date[0];
		document.getElementById('cardExpirationYear').value = date[1];
	}

	function mpInteger(v) {
		return v.replace(/\D/g, "");
	}

	function mpIsMobile() {
		try{
			document.createEvent("TouchEvent");
			return true;
		}catch(e){
			return false;
		}
	}

	function submitWalletButton(event) {
		event.preventDefault();
		jQuery('#mp_checkout_type').val('wallet_button');
		jQuery('form.checkout, form#order_review').submit();
	}

</script>

<script>

	avaiablePayment = document.getElementsByClassName('mp-checkout-custom-available-payments')[0];
	collapsible = avaiablePayment.getElementsByClassName('mp-checkout-custom-available-payments-header')[0];

	collapsible.addEventListener("click", function () {
		icon = collapsible.getElementsByClassName('mp-checkout-custom-available-payments-collapsible')[0];
		content = avaiablePayment.getElementsByClassName('mp-checkout-custom-available-payments-content')[0];

		if (content.style.maxHeight) {
			content.style.maxHeight = null;
			icon.src = "<?php echo esc_url( plugins_url( '../assets/images/chefron-up.png', plugin_dir_path( __FILE__ ) ) ); ?>";
		} else {
			content.style.maxHeight = content.scrollHeight + "px";
			icon.src = "<?php echo esc_url( plugins_url( '../assets/images/chefron-down.png', plugin_dir_path( __FILE__ ) ) ); ?>";
		}
	});
</script>
