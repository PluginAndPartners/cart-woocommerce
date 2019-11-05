(function ($) {
    'use strict';

    $(function () {
        console.log(wc_mercadopago_params);

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
         * @param {number} status 
         * @param {object} response 
         */
        function paymentMethodHandler(status, response) {
            if (status == 200) {
                objPaymentMethod = response[0];
                setPaymentMethodId(objPaymentMethod.id);
                setImageCard(objPaymentMethod.secure_thumbnail);
                additionalInfoHandler(objPaymentMethod.additional_info_needed)
            } else {
                document.getElementById('mp-card-number').innerHTML = '';
            }
        }

        /**
         * Check what information is necessary to pay and show inputs
         * @param {array} additional_info_needed 
         */
        function additionalInfoHandler(additional_info_needed) {
            var issuer = false;
            var cardholderName = false;
            var cardholderIdentificationType = false;
            var cardholderIdentificationNumber = false;

            for (var i = 0; i < additional_info_needed.length; i++) {
                if (additional_info_needed[i] == 'issuer_id') {
                    issuer = true;
                    document.getElementById('mp-issuer-div').style.display = 'block';
                    document.getElementById('installments-div').classList.remove('mp-col-md-12');
                    document.getElementById('installments-div').classList.add('mp-col-md-8');
                    Mercadopago.getIssuers(objPaymentMethod.id, issuersHandler);
                }
                if (additional_info_needed[i] == 'cardholder_name') {
                    cardholderName = true;
                }
                if (additional_info_needed[i] == 'cardholder_identification_type') {
                    cardholderIdentificationType = true;
                    document.getElementById('mp-doc-div').style.display = 'inline-block';
                    document.getElementById('mp-doc-type-div').style.display = "block";
                    Mercadopago.getIdentificationTypes();
                }
                if (additional_info_needed[i] == 'cardholder_identification_number') {
                    cardholderIdentificationNumber = true;
                    document.getElementById('mp-doc-div').style.display = 'inline-block';
                    document.getElementById('mp-doc-number-div').style.display = "block";
                }
            };

            if (!issuer) {
                clearIssuer();
                setInstallments();
            }
            if (!cardholderIdentificationType) {
                document.getElementById('mp-doc-type-div').style.display = 'none'
            }
            if (!cardholderIdentificationNumber) {
                document.getElementById('mp-doc-number-div').style.display = 'none';
            }
            if (!cardholderIdentificationNumber && !cardholderIdentificationType) {
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
         * @param {string} paymentMethodId 
         */
        function setPaymentMethodId(paymentMethodId) {
            var paymentMethodElement = document.getElementById('paymentMethodId');
            paymentMethodElement.value = paymentMethodId;
        }

        /**
         * Set Imagem card on element
         * @param {string} secureThumbnail 
         */
        function setImageCard(secureThumbnail) {
            document.getElementById('mp-card-number').style.background = 'url(' + secureThumbnail + ') 98% 50% no-repeat #fff';
        }

        /**
         * Get instalments
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

    });

}(jQuery));
