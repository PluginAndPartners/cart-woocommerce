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

        // Sets
        seller.site_id = wc_mercadopago_params.site_id;
        seller.public_key = wc_mercadopago_params.public_key;

        coupon_of_discounts.default = wc_mercadopago_params.coupon_mode;
        coupon_of_discounts.discount_action_url = wc_mercadopago_params.discount_action_url;
        coupon_of_discounts.payer_email = wc_mercadopago_params.payer_email;

        Mercadopago.setPublishableKey(seller.public_key);

        // Rules for Countries
        if (seller.site_id == 'MLB') {
            document.querySelector('.mp-issuer').style.display = 'none';
            document.getElementById('installments-div').classList.remove('mp-col-md-8');
            document.getElementById('installments-div').classList.add('mp-col-md-12');
        } else if (seller.site_id == 'MCO') {
            document.querySelector('.mp-issuer').style.display = 'none';
            document.getElementById('installments-div').classList.remove('mp-col-md-8');
            document.getElementById('installments-div').classList.add('mp-col-md-12');
        } else if (seller.site_id == 'MLA') {
            document.querySelector('.mp-issuer').style.display = 'block';
            document.querySelector('#mp-box-input-tax-cft').style.display = 'block';
            document.querySelector('#mp-box-input-tax-tea').style.display = 'block';
        } else if (seller.site_id == 'MLC') {
            document.querySelector('.mp-issuer').style.display = 'none';
            document.getElementById('installments-div').classList.remove('mp-col-md-8');
            document.getElementById('installments-div').classList.add('mp-col-md-12');
        }
        if (seller.site_id != "MLM") {
            Mercadopago.getIdentificationTypes();
        }
        if (seller.site_id == "MLM") {
            $('body').on('updated_checkout', function () {
                getCardPaymentMethods();
            });
        }

        $('body').on('focusout', '#mp-card-number', guessingPaymentMethod);

        /**
         * Get payment method credit_card, debit_card and prepaid_card.
         * Build select options
         */
        function getCardPaymentMethods() {
            var paymentMethodsSelector = document.getElementById('paymentMethodSelector');
            paymentMethodsSelector.innerHTML = "";

            var fragment = document.createDocumentFragment();
            var option = new Option(wc_mercadopago_params.choose + "...", "-1");
            fragment.appendChild(option);

            Mercadopago.getAllPaymentMethods(function (code, payment_methods) {
                for (var x = 0; x < payment_methods.length; x++) {
                    var pm = payment_methods[x];
                    if ((pm.payment_type_id == "credit_card" || pm.payment_type_id == "debit_card" ||
                        pm.payment_type_id == "prepaid_card") && pm.status == "active") {
                        option = new Option(pm.name, pm.id);
                        option.setAttribute("type_checkout", "custom");
                        fragment.appendChild(option);
                    }
                }
                paymentMethodsSelector.appendChild(fragment);
            });
        }

        function getBin() {
            var cardnumber = document.getElementById('mp-card-number');
            return cardnumber.value.replace(/[ .-]/g, "").slice(0, 6);
        }

        function guessingPaymentMethod(event) {
            var bin = getBin();

            if (event.type == "keyup") {
                if (bin.length >= 6) {
                    Mercadopago.getPaymentMethod({
                        "bin": bin
                    }, setPaymentMethodInfo);
                }
            } else {
                setTimeout(function () {
                    if (bin.length >= 6) {
                        Mercadopago.getPaymentMethod({
                            "bin": bin
                        }, setPaymentMethodInfo);
                    }
                }, 100);
            }
        };

        function getAmount() {
            return document.getElementById('mp-amount').value - document.getElementById('mp-discount').value;
        }

        function setPaymentMethodInfo(status, response) {
            if (status == 200) {
                console.log(response);
                var paymentMethodElement = document.getElementById('paymentMethodId');

                if (paymentMethodElement && wc_mercadopago_params.site_id != "MLM") {
                    paymentMethodElement.value = response[0].id;
                    document.getElementById('mp-card-number').style.background = "url(" +
                        response[0].secure_thumbnail + ") 98% 50% no-repeat #fff";
                }

                // Check if the issuer is necessary to pay.
                var issuerMandatory = false;
                var additionalInfo = response[0].additional_info_needed;
                for (var i = 0; i < additionalInfo.length; i++) {
                    if (additionalInfo[i] == "issuer_id") {
                        issuerMandatory = true;
                    }
                };

                if (issuerMandatory) {
                    getIssuersPaymentMethod(response[0].id);
                } else {
                    hideIssuer();
                }

            }

        }

        /**
         * Get instalments
         */
        function setInstallmentInfo(status, response) {
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

                    selectorInstallments.removeAttribute("disabled");
                    showTaxes();

                }
            }

        }

        function showTaxes() {
            var selectorIsntallments = document.getElementById('mp-installments');
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

        function hideIssuer() {
            var $issuer = document.getElementById('issuer');
            var opt = document.createElement("option");
            opt.value = "-1";
            opt.innerHTML = wc_mercadopago_params.other_bank;
            opt.style = "font-size: 12px;";

            $issuer.innerHTML = "";
            $issuer.appendChild(opt);
            $issuer.setAttribute("disabled", "disabled");
        }


        function getIssuersPaymentMethod(payment_method_id) {
            console.log(payment_method_id);
            Mercadopago.getIssuers(payment_method_id, showCardIssuers);
        }

        function setInstallments() {

            var issuerId = document.getElementById('mp-issuer').value;
            var amount = getAmount();

            if (issuerId === "-1") {
                return;
            }

            var params_installments = {
                "bin": getBin(),
                "amount": amount,
                "issuer_id": issuerId
            }

            if (seller.site_id == "MLM") {
                params_installments = {
                    "payment_method_id": document.getElementById('paymentMethodSelector').value,
                    "amount": amount,
                    "issuer_id": issuerId
                }
            }
            Mercadopago.getInstallments(params_installments, setInstallmentInfo);
        }

        function showCardIssuers(status, issuers) {
            if (status == 200) {
                // If the API does not return any bank.
                if (issuers.length > 0) {
                    var issuersSelector = document.getElementById('mp-issuer');
                    var fragment = document.createDocumentFragment();

                    issuersSelector.options.length = 0;
                    var option = new Option(wc_mercadopago_params.choose + "...", "-1");
                    fragment.appendChild(option);

                    for (var i = 0; i < issuers.length; i++) {
                        if (issuers[i].name != "default") {
                            option = new Option(issuers[i].name, issuers[i].id);
                        } else {
                            option = new Option("Otro", issuers[i].id);
                        }
                        fragment.appendChild(option);
                    }

                    issuersSelector.appendChild(fragment);
                    issuersSelector.removeAttribute("disabled");
                    $('body').on('change', '#mp-issuer', setInstallments);
                } else {
                    hideIssuer();
                }
            }
        }

    });

}(jQuery));
