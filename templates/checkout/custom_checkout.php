<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="mp-panel-custom-checkout">
	<div class="mp-row-checkout">
		<?php if ($site_id == 'MLA') : ?>
			<div class="mp-frame-links">
				<a class="mp-checkout-link mp-pr-10" id="button-show-payments">
					<?= __('With what cards can I pay', 'woocommerce-mercadopago') ?> ⌵
				</a>
				<span id="mp_promotion_link"> | </span>
				<a href="https://www.mercadopago.com.ar/cuotas" id="mp_checkout_link" class="mp-checkout-link mp-pl-10" target="_blank">
					<?= __('See current promotions', 'woocommerce-mercadopago') ?>
				</a>
			</div>
		<?php endif; ?>

		<div class="mp-frame-payments" id="mp-frame-payments">
			<div class="mp-col-md-12">
				<div class="frame-tarjetas">
					<?php if (count($credit_card) != 0) : ?>
						<p class="submp-title-checkout-custom"><?= __('Credit cards', 'woocommerce-mercadopago') ?></p>
						<?php foreach ($credit_card as $credit_image) : ?>
							<img src="<?= $credit_image ?>" class="mp-img-fluid mp-img-tarjetas" alt="" />
						<?php endforeach; ?>
					<?php endif; ?>

					<?php if (count($debit_card) != 0) : ?>
						<p class="submp-title-checkout-custom mp-pt-10"><?= __('Debit card', 'woocommerce-mercadopago') ?></p>
						<?php foreach ($debit_card as $debit_image) : ?>
							<img src="<?= $debit_image ?>" class="mp-img-fluid mp-img-tarjetas" alt="" />
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if ($coupon_mode == 'yes') : ?>
			<div class="mp-col-md-12" id="mercadopago-form-coupon">
				<div class="frame-tarjetas mp-text-justify">
					<p class="mp-subtitle-custom-checkout"><?= __('Enter your discount coupon', 'woocommerce-mercadopago') ?></p>

					<div class="mp-row-checkout mp-pt-10">
						<div class="mp-col-md-9 mp-pr-15">
							<input type="text" class="mp-form-control" id="couponCode" name="mercadopago_custom[coupon_code]" autocomplete="off" maxlength="24" placeholder="<?= __('Enter your coupon', 'woocommerce-mercadopago') ?>" />
						</div>

						<div class="mp-col-md-3">
							<input type="button" class="mp-button mp-pointer" id="applyCoupon" value="<?= esc_html__('Apply', 'woocommerce-mercadopago'); ?>">
						</div>
					</div>

					<span class="mp-discount" id="mpCouponApplyed"></span>
					<span class="mp-error" id="mpCouponError"><?= __('The code you entered is incorrect', 'woocommerce-mercadopago') ?></span>
				</div>
			</div>
		<?php endif; ?>

		<div class="mp-col-md-12">
			<div class="frame-tarjetas">
				<p class="mp-subtitle-custom-checkout"><?= __('Enter your card details', 'woocommerce-mercadopago') ?></p>

				<!-- Issuers for Mexico and Peru -->
				<?php if ($site_id == 'MLM' || $site_id == "MPE") : ?>
					<div id="mercadopago-form-customer-and-card">
						<div class="mp-row-checkout mp-pt-10">
							<div class="mp-col-md-12">
								<label for="paymentMethodIdSelector" class="mp-label-form"><?= esc_html__('Payment Method', 'woocommerce-mercadopago'); ?> <em>*</em></label>
								<select id="paymentMethodSelector" class="mp-form-control mp-pointer" name="mercadopago_custom[paymentMethodSelector]" data-checkout="cardId">
									<optgroup label="<?= esc_html__('Other cards', 'woocommerce-mercadopago'); ?>" id="payment-methods-list-other-cards">
										<option value="-1"><?= esc_html__('Another card', 'woocommerce-mercadopago'); ?></option>
									</optgroup>
								</select>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<!-- new card -->
				<div id="mercadopago-form">
					<div class="mp-row-checkout mp-pt-10">
						<div class="mp-col-md-12">
							<label for="mp-card-number" class="mp-label-form"><?= esc_html__('Card number', 'woocommerce-mercadopago'); ?> <em>*</em></label>
							<input type="text" onkeyup="maskDate(this, mcc);" class="mp-form-control mp-mt-5" id="mp-card-number" data-checkout="mp-card-number" autocomplete="off" maxlength="23" />

							<span class="mp-error mp-mt-5" id="mp-error-205" data-main="#mp-card-number"><?= esc_html__('Card number', 'woocommerce-mercadopago'); ?></span>
							<span class="mp-error mp-mt-5" id="mp-error-E301" data-main="#mp-card-number"><?= esc_html__('Invalid Card Number', 'woocommerce-mercadopago'); ?></span>
						</div>
					</div>

					<div class="mp-row-checkout mp-pt-10">
						<div class="mp-col-md-12">
							<label for="cardholderName" class="mp-label-form"><?= esc_html__('Name and surname of the cardholder', 'woocommerce-mercadopago'); ?> <em>*</em></label>
							<input type="text" class="mp-form-control mp-mt-5" id="cardholderName" name="mercadopago_custom[cardholderName]" data-checkout="cardholderName" autocomplete="off" />

							<span class="mp-error mp-mt-5" id="mp-error-221" data-main="#cardholderName"><?= esc_html__('Card number', 'woocommerce-mercadopago'); ?></span>
							<span class="mp-error mp-mt-5" id="mp-error-316" data-main="#cardholderName"><?= esc_html__('Invalid cardholder name', 'woocommerce-mercadopago'); ?></span>
						</div>
					</div>

					<div class="mp-row-checkout mp-pt-10">
						<div class="mp-col-md-6 mp-pr-15">
							<label for="cardholderName" class="mp-label-form"><?= esc_html__('Expiration date', 'woocommerce-mercadopago'); ?> <em>*</em></label>
							<input type="text" onkeyup="maskDate(this, mdate);" onblur="validateMonthYear()" class="mp-form-control mp-mt-5" id="cardExpirationDate" data-checkout="cardExpirationDate" name="mercadopago_custom[cardExpirationDate]" autocomplete="off" placeholder="MM/AAAA" maxlength="7" />
							<input type="hidden" id="cardExpirationMonth" name="mercadopago_custom[cardExpirationMonth]" data-checkout="cardExpirationMonth">
							<input type="hidden" id="cardExpirationYear" name="mercadopago_custom[cardExpirationYear]" data-checkout="cardExpirationYear">

							<span class="mp-error mp-mt-5" id="mp-error-208" data-main="#cardExpirationDate"><?= esc_html__('Invalid Expiration Date', 'woocommerce-mercadopago'); ?></span>
						</div>

						<div class="mp-col-md-6">
							<label for="securityCode" class="mp-label-form"><?= esc_html__('Last 3 numbers on the back', 'woocommerce-mercadopago'); ?> <em>*</em></label>
							<input type="text" onkeyup="maskDate(this, minteger);" class="mp-form-control mp-mt-5" id="securityCode" data-checkout="securityCode" autocomplete="off" maxlength="4" />

							<p class="mp-desc mp-mt-5 mp-mb-0" data-main="#securityCode"><?= esc_html__('Last 3 numbers on the back', 'woocommerce-mercadopago'); ?></p>
							<span class="mp-error mp-mt-5" id="mp-error-224" data-main="#securityCode"><?= esc_html__('Card number', 'woocommerce-mercadopago'); ?></span>
							<span class="mp-error mp-mt-5" id="mp-error-E302" data-main="#securityCode"><?= esc_html__('Invalid Card Number', 'woocommerce-mercadopago'); ?></span>
						</div>
					</div>

					<div class="mp-col-md-12">
						<div class="frame-tarjetas">
							<p class="mp-subtitle-custom-checkout"><?= __('In how many installments do you want to pay?', 'woocommerce-mercadopago') ?></p>

							<div class="mp-row-checkout mp-pt-10">
								<div class="mp-col-md-4 mp-pr-15">
									<div class="mp-issuer">
										<label for="mp-issuer" class="mp-label-form"><?= esc_html__('Issuer', 'woocommerce-mercadopago'); ?> </label>
										<select class="mp-form-control mp-pointer mp-mt-5" id="mp-issuer" data-checkout="issuer" name="mercadopago_custom[issuer]"></select>
									</div>
								</div>

								<div id="installments-div" class="mp-col-md-8">
									<?php if ($currency_ratio != 1) : ?>
										<label for="installments" class="mp-label-form">
											<div class="mp-tooltip">
												<?= esc_html__('', 'woocommerce-mercadopago'); ?>
												<span class="mp-tooltiptext">
													<?=
															esc_html__('Converted payment of', 'woocommerce-mercadopago') . " " .
																$woocommerce_currency . " " . esc_html__('for', 'woocommerce-mercadopago') . " " .
																$account_currency;
														?>
												</span>
											</div>
											<em>*</em>
										</label>
									<?php else : ?>
										<label for="mp-installments" class="mp-label-form"><?= __('Select the number of installment', 'woocommerce-mercadopago') ?></label>
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
							<p class="mp-subtitle-custom-checkout"><?= __('Enter your document number', 'woocommerce-mercadopago') ?></p>

							<div class="mp-row-checkout mp-pt-10">
								<div class="mp-col-md-4 mp-pr-15">
									<label for="docType" class="mp-label-form mp-pt-5"><?= esc_html__('Type', 'woocommerce-mercadopago'); ?></label>
									<select id="docType" class="mp-form-control mp-pointer mp-mt-04rem" data-checkout="docType" name="mercadopago_custom[docType]"></select>
								</div>

								<div class="mp-col-md-8">
									<label for="docNumber" class="mp-label-form"><?= esc_html__('Document number', 'woocommerce-mercadopago'); ?> <em>*</em></label>
									<input type="text" class="mp-form-control mp-mt-5" id="docNumber" data-checkout="docNumber" name="mercadopago_custom[docNumber]" autocomplete="off" />
									<p class="mp-desc mp-mt-5 mp-mb-0" data-main="#securityCode"><?= esc_html__('Only numbers', 'woocommerce-mercadopago'); ?></p>

									<span class="mp-error mp-mt-5" id="mp-error-214" data-main="#docNumber"><?= esc_html__('Card number', 'woocommerce-mercadopago'); ?></span>
									<span class="mp-error mp-mt-5" id="mp-error-324" data-main="#docNumber"><?= esc_html__('Invalid Document Number', 'woocommerce-mercadopago'); ?></span>
								</div>
							</div>
						</div>
					</div>

					<div class="mp-col-md-12 mp-pt-10">
						<div class="frame-tarjetas">
							<div class="mp-row-checkout">
								<p class="mp-obrigatory">
									<em>*</em> <?= esc_html__('Obligatory field', 'woocommerce-mercadopago'); ?>
								</p>
							</div>
						</div>
					</div>
				</div>
				<!-- end new card -->

				<!-- NOT DELETE LOADING-->
				<div id="mp-box-loading"></div>

			</div>
		</div>

		<div id="mercadopago-utilities">
			<input type="hidden" id="site_id" name="mercadopago_custom[site_id]" />
			<input type="hidden" id="mp-amount" value='<?php echo $amount; ?>' name="mercadopago_custom[amount]" />
			<input type="hidden" id="currency_ratio" value='<?php echo $currency_ratio; ?>' name="mercadopago_custom[currency_ratio]" />
			<input type="hidden" id="campaign_id" name="mercadopago_custom[campaign_id]" />
			<input type="hidden" id="campaign" name="mercadopago_custom[campaign]" />
			<input type="hidden" id="mp-discount" name="mercadopago_custom[discount]" />
			<input type="hidden" id="paymentMethodId" name="mercadopago_custom[paymentMethodId]" />
			<input type="hidden" id="token" name="mercadopago_custom[token]" />
			<input type="hidden" id="cardTruncated" name="mercadopago_custom[cardTruncated]" />
			<input type="hidden" id="CustomerAndCard" name="mercadopago_custom[CustomerAndCard]" />
			<input type="hidden" id="CustomerId" value='<?php echo $customerId; ?>' name="mercadopago_custom[CustomerId]" />
		</div>

	</div>
</div>

<script type="text/javascript">
	function execmascara() {
		v_obj.value = v_fun(v_obj.value)
	}

	//Card mask date input
	function maskDate(o, f) {
		v_obj = o
		v_fun = f
		setTimeout("execmascara()", 1);
	}

	function mcc(value) {
		value = value.replace(/\D/g, "");
		value = value.replace(/^(\d{4})(\d)/g, "$1 $2");
		value = value.replace(/^(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3");
		value = value.replace(/^(\d{4})\s(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3 $4");
		return value;
	}
	
</script>