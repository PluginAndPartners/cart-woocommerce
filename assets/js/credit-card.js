(function ($) {
    'use strict';

    $(function () {

        var mercado_pago_submit = false;

        var seller = {
            site_id: '',
            public_key: ''
        }
        var coupon_of_discounts = {
            discount_action_url: '',
            payer_email: '',
            default: true,
            status: false
        }

        var objPaymentMethod = {};
        var additionalInfoNeeded = {}

        // Sets
        seller.site_id = wc_mercadopago_params.site_id;
        seller.public_key = wc_mercadopago_params.public_key;

        coupon_of_discounts.default = wc_mercadopago_params.coupon_mode;
        coupon_of_discounts.discount_action_url = wc_mercadopago_params.discount_action_url;
        coupon_of_discounts.payer_email = wc_mercadopago_params.payer_email;

        Mercadopago.setPublishableKey(seller.public_key);

        $('body').on('focusout', '#mp-card-number', guessingPaymentMethod);

        /**
         * Get Bin from Card Number
         */
        function getBin() {
            var cardnumber = document.getElementById('mp-card-number');
            return cardnumber.value.replace(/[ .-]/g, "").slice(0, 6);
        }

        /**
         * Execute before event focusout on input Card Number
         * 
         * @param {object} event 
         */
        function guessingPaymentMethod(event) {
            objPaymentMethod = {};
            var bin = getBin();

            if (bin.length < 6) {
                resetBackgroundCard();
                clearInstallments();
                clearTax();
                clearIssuer();
                return;
            }

            if (event.type == "keyup") {
                if (bin.length >= 6) {
                    Mercadopago.getPaymentMethod({
                        "bin": bin
                    }, paymentMethodHandler);
                }
            } else {
                setTimeout(function () {
                    if (bin.length >= 6) {
                        Mercadopago.getPaymentMethod({
                            "bin": bin
                        }, paymentMethodHandler);
                    }
                }, 100);
            }
        };

        /**
        * Get Amount end calculate discount for hide inputs
        */
        function getAmount() {
            return document.getElementById('mp-amount').value - document.getElementById('mp-discount').value;
        }

        /**
         * Handle payment Method response
         * 
         * @param {number} status 
         * @param {object} response 
         */
        function paymentMethodHandler(status, response) {
            if (status == 200) {
                objPaymentMethod = response[0];
                setPaymentMethodId(objPaymentMethod.id);
                setImageCard(objPaymentMethod.secure_thumbnail);
                loadAdditionalInfo(objPaymentMethod.additional_info_needed);
                additionalInfoHandler();
            } else {
                document.getElementById('mp-card-number').innerHTML = '';
            }
        }

        /**
         * 
         * Load Additional Info to use for build payment form
         * 
         * @param {array} additional_info_needed 
         */
        function loadAdditionalInfo(additional_info_needed) {
            additionalInfoNeeded = {
                'issuer': false,
                'cardholder_name': false,
                'cardholder_identification_type': false,
                'cardholder_identification_number': false
            }

            for (var i = 0; i < additional_info_needed.length; i++) {
                if (additional_info_needed[i] == 'issuer_id') {
                    additionalInfoNeeded.issuer = true;
                }
                if (additional_info_needed[i] == 'cardholder_name') {
                    additionalInfoNeeded.cardholder_name = true;
                }
                if (additional_info_needed[i] == 'cardholder_identification_type') {
                    additionalInfoNeeded.cardholder_identification_type = true;
                }
                if (additional_info_needed[i] == 'cardholder_identification_number') {
                    additionalInfoNeeded.cardholder_identification_number = true;
                }
            };
        }

        /**
         * Check what information is necessary to pay and show inputs 
         */
        function additionalInfoHandler() {
            if (additionalInfoNeeded.issuer) {
                document.getElementById('mp-issuer-div').style.display = 'block';
                document.getElementById('installments-div').classList.remove('mp-col-md-12');
                document.getElementById('installments-div').classList.add('mp-col-md-8');
                Mercadopago.getIssuers(objPaymentMethod.id, issuersHandler);
            } else {
                clearIssuer();
                setInstallments();
            }

            if (additionalInfoNeeded.cardholder_name) {

            }

            if (additionalInfoNeeded.cardholder_identification_type) {
                document.getElementById('mp-doc-div').style.display = 'inline-block';
                document.getElementById('mp-doc-type-div').style.display = "block";
                Mercadopago.getIdentificationTypes();
            } else {
                document.getElementById('mp-doc-type-div').style.display = 'none'
            }

            if (additionalInfoNeeded.cardholder_identification_number) {
                document.getElementById('mp-doc-div').style.display = 'inline-block';
                document.getElementById('mp-doc-number-div').style.display = "block";
            } else {
                document.getElementById('mp-doc-number-div').style.display = 'none';
            }

            if (!additionalInfoNeeded.cardholder_identification_type &&
                !additionalInfoNeeded.cardholder_identification_number) {
                document.getElementById('mp-doc-div').style.display = 'none';
            }
        }

        /**
        * Remove background image from imput
        */
        function resetBackgroundCard() {
            document.getElementById('mp-card-number').style.background = 'no-repeat #fff';
        }

        /**
         * Set value on paymentMethodId element
         * 
         * @param {string} paymentMethodId 
         */
        function setPaymentMethodId(paymentMethodId) {
            var paymentMethodElement = document.getElementById('paymentMethodId');
            paymentMethodElement.value = paymentMethodId;
        }

        /**
         * Set Imagem card on element
         * 
         * @param {string} secureThumbnail 
         */
        function setImageCard(secureThumbnail) {
            document.getElementById('mp-card-number').style.background = 'url(' + secureThumbnail + ') 98% 50% no-repeat #fff';
        }

        /**
         * Get instalments
         * 
         * @param {number} status 
         * @param {object} response 
         */
        function installmentHandler(status, response) {
            if (status == 200) {
                var selectorInstallments = document.getElementById('mp-installments');

                if (response.length > 0) {

                    var html_option = "<option value='-1'>" + wc_mercadopago_params.choose + "...</option>";
                    var payerCosts = response[0].payer_costs;

                    for (var i = 0; i < payerCosts.length; i++) {
                        // Resolution 51/2017
                        var dataInput = "";
                        if (seller.site_id == "MLA") {
                            var tax = payerCosts[i].labels;
                            if (tax.length > 0) {
                                for (var l = 0; l < tax.length; l++) {
                                    if (tax[l].indexOf("CFT_") !== -1) {
                                        dataInput = "data-tax='" + tax[l] + "'";
                                    }
                                }
                            }
                        }
                        html_option += "<option value='" + payerCosts[i].installments + "' " + dataInput + ">" +
                            (payerCosts[i].recommended_message || payerCosts[i].installments) +
                            "</option>";
                    }

                    // Not take the user's selection if equal.
                    if (selectorInstallments.innerHTML != html_option) {
                        selectorInstallments.innerHTML = html_option;
                    }
                    if (seller.site_id == "MLA") {
                        clearTax();
                        $('body').on('change', '#mp-installments', showTaxes);;
                    }
                }
            } else {
                clearInstallments();
                clearTax();
            }

        }

        /**
        * Show taxes resolution 51/2017 for MLA
        */
        function showTaxes() {
            var selectorIsntallments = document.querySelector('#mp-installments');
            var tax = selectorIsntallments.options[selectorIsntallments.selectedIndex].getAttribute("data-tax");
            var cft = "";
            var tea = "";
            if (tax != null) {
                var tax_split = tax.split("|");
                cft = tax_split[0].replace("_", " ");
                tea = tax_split[1].replace("_", " ");
                if (cft == "CFT 0,00%" && tea == "TEA 0,00%") {
                    cft = "";
                    tea = "";
                }
            }
            document.querySelector('#mp-tax-cft-text').innerHTML = cft;
            document.querySelector('#mp-tax-tea-text').innerHTML = tea;
        }

        /**
        * Clear input select
        */
        function clearInstallments() {
            document.getElementById('mp-installments').innerHTML = '';
        }

        /**
        * Clear Tax
        */
        function clearTax() {
            document.querySelector('#mp-tax-cft-text').innerHTML = '';
            document.querySelector('#mp-tax-tea-text').innerHTML = '';
        }

        /**
         * Clear input select and change to default layout
         */
        function clearIssuer() {
            document.getElementById('mp-issuer-div').style.display = 'none';
            document.getElementById('installments-div').classList.remove('mp-col-md-8');
            document.getElementById('installments-div').classList.add('mp-dis-md-12');
            document.getElementById('mp-issuer').innerHTML = '';
        }

        /**
         * Call insttalments with issuer ou not, depends on additionalInfoHandler()
         */
        function setInstallments() {
            var params_installments = {};
            var amount = getAmount();
            var issuer = false;
            for (var i = 0; i < objPaymentMethod.additional_info_needed.length; i++) {
                if (objPaymentMethod.additional_info_needed[i] == 'issuer_id') {
                    issuer = true;
                }
            }
            if (issuer) {
                var issuerId = document.getElementById('mp-issuer').value;
                params_installments = {
                    "bin": getBin(),
                    "amount": amount,
                    "issuer_id": issuerId
                }

                if (issuerId === "-1") {
                    return;
                }
            } else {
                params_installments = {
                    "bin": getBin(),
                    "amount": amount
                }
            }
            Mercadopago.getInstallments(params_installments, installmentHandler);
        }

        /**
         * Handle issuers response and build select
         * 
         * @param {status} status 
         * @param {object} response 
         */
        function issuersHandler(status, response) {
            if (status == 200) {
                // If the API does not return any bank.
                if (response.length > 0) {
                    var issuersSelector = document.getElementById('mp-issuer');
                    var fragment = document.createDocumentFragment();

                    issuersSelector.options.length = 0;
                    var option = new Option(wc_mercadopago_params.choose + "...", "-1");
                    fragment.appendChild(option);

                    for (var i = 0; i < response.length; i++) {
                        if (response[i].name != "default") {
                            option = new Option(response[i].name, response[i].id);
                        } else {
                            option = new Option("Otro", response[i].id);
                        }
                        fragment.appendChild(option);
                    }

                    issuersSelector.appendChild(fragment);
                    issuersSelector.removeAttribute("disabled");
                    $('body').on('change', '#mp-issuer', setInstallments);
                } else {
                    clearIssuer();
                }
            }
        }

        /**
         * Get form
         */
        function getForm() {
            return document.querySelector('#mercadopago-form');
        }

        /**
         * Validate Additional Inputs
         * 
         * @return {bool}
         */
        function validateAdditionalInputs() {
            if (additionalInfoNeeded.issuer) {
                var inputMpIssuer = document.getElementById('mp-issuer');
                if (inputMpIssuer.value == -1 || inputMpIssuer.value == "") {
                    inputMpIssuer.focus();
                    return false;
                }
            }
            if (additionalInfoNeeded.cardholder_name) {
                var inputCardholderName = document.getElementById('mp-card-holder-name');
                if (inputCardholderName.value == -1 || inputCardholderName.value == "") {
                    inputCardholderName.focus();
                    return false;
                }
            }
            if (additionalInfoNeeded.cardholder_identification_type) {
                var inputDocType = document.getElementById('docType');
                if (inputDocType.value == -1 || inputDocType.value == "") {
                    docType.focus();
                    return false;
                }
            }
            if (additionalInfoNeeded.cardholder_identification_number) {
                var docNumber = document.getElementById('docNumber');
                if (docNumber.value == -1 || docNumber.value == "") {
                    docNumber.focus();
                    return false;
                }
            }
            return true;
        }

        /** 
        * Validate Inputs to Create Token
        * 
        * @return {bool}
        */
        function validateInputsCreateToken() {
            var form_inputs = getForm().querySelectorAll("[data-checkout]");
            var fixed_inputs = [
                'cardNumber',
                'cardExpirationDate',
                'securityCode',
                'installments'
            ];

            for (var x = 0; x < form_inputs.length; x++) {
                var element = form_inputs[x];
                // Check is a input to create token.
                if (fixed_inputs.indexOf(element.getAttribute("data-checkout")) > -1) {
                    if (element.value == -1 || element.value == "") {
                        element.focus();
                        return false;
                    }
                }
            }

            if (objPaymentMethod.length == 0) {
                document.getElementById('mp-card-number').focus();
                return false;
            }

            if (!validateAdditionalInputs()) {
                return false;
            }

            return true;
        }

        function hideErrors() {

            for (var x = 0; x < document.querySelectorAll("[data-checkout]").length; x++) {
                var $field = document.querySelectorAll("[data-checkout]")[x];
                $field.classList.remove("mp-error-input");
                $field.classList.remove("mp-form-control-error");
            }
    
            for (var x = 0; x < document.querySelectorAll(".mp-error").length; x++) {
                var $span = document.querySelectorAll(".mp-error")[x];
                $span.style.display = "none";
            }
    
            return;
    
        }

        /**
         *  Create Token
         * 
         *  @return {bool} 
         */
        function createToken() {
            hideErrors();

            // Show loading.
            document.querySelector('#mp-box-loading').style.background =
                'url(' + wc_mercadopago_params.loading + ') 0 50% no-repeat #fff';

            // Form.
            var form = getForm();

            Mercadopago.createToken(form, sdkResponseHandler);

            return false;
        }

        function sdkResponseHandler(status, response) {
            // Hide loading.
            document.querySelector('#mp-box-loading').style.background = "";

            if (status != 200 && status != 201) {
                showErrors(response);
            } else {
                var token = document.querySelector('#token');
                token.value = response.id;
                mercado_pago_submit = true;
                $('form.checkout, form#order_review').submit();
            }
        }

        function showErrors(response) {
            var form = getForm();
            for (var x = 0; x < response.cause.length; x++) {
                var error = response.cause[x];

                if (error.code == 208 || error.code == 209 || error.code == 325 || error.code == 326) {
                    var span = form.querySelector("#mp-error-208");
                } else {
                    var span = form.querySelector("#mp-error-" + error.code);
                }

                if (span != undefined) {
                    var input = form.querySelector(span.getAttribute("data-main"));
                    span.style.display = "inline-block";
                    input.classList.add("mp-form-control-error");
                }
            }
            return;
        }

        /**
         * Handler submit
         * 
         * @return {bool}
         */
        function mercadoPagoformHandler() {
            if (mercado_pago_submit) {
                mercado_pago_submit = false;

                return true;
            }

            if (!document.getElementById('payment_method_woo-mercado-pago-custom').checked) {
                return true;
            }

            if (validateInputsCreateToken()) {
                return createToken();
            }

            return false;
        }

        // Process when submit the checkout form.
        $('form.checkout').on('checkout_place_order_woo-mercado-pago-custom', function () {
            return mercadoPagoformHandler();
        });

        // If payment fail, retry on next checkout page
        $('form#order_review').submit(function () {
            return mercadoPagoformHandler();
        });

    });

}(jQuery));
