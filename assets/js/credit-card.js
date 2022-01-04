/* globals wc_mercadopago_ticket_params */
(function () {

  let mercado_pago_submit = false;

  const seller = {
    site_id: wc_mercadopago_params.site_id,
    public_key: wc_mercadopago_params.public_key
  };

  let objPaymentMethod = {};
  let additionalInfoNeeded = {};

  if (seller.public_key) {
    window.Mercadopago.setPublishableKey(seller.public_key);
  }

  getCardNumberInput().addEventListener('change', guessPaymentMethod);

  getForm().addEventListener('submit', (event) => {
    event.preventDefault();
    return mercadoPagoFormHandler();
  });

  function getForm() {
    return document.querySelector('form');
  }

  function getCardNumberInput() {
    return document.getElementById('mp-card-number');
  }

  function getAmount() {
    return document.getElementById('amount').value;
  }

  function getBin() {
    return getCardNumberInput().value.replace(/[ .-]/g, '').slice(0, 6);
  }

  function guessPaymentMethod() {
    hideErrors();
    clearHolderName();
    clearExpirationDate();
    clearSecurityCode();
    clearIssuer();
    clearInstallments();
    clearTax();
    clearDoc();

    const bin = getBin();

    if (bin.length < 6) {
      resetBackgroundCard();
      return;
    }

    if (bin.length >= 6) {
      Mercadopago.getPaymentMethod({ bin }, paymentMethodHandler);
    }
  }

  function paymentMethodHandler(status, response) {
    objPaymentMethod = response[0];

    setPaymentMethodId(objPaymentMethod.id);
    setImageCard(objPaymentMethod.secure_thumbnail);
    setCvvProperties(objPaymentMethod.settings[0].security_code);
    loadAdditionalInfo(objPaymentMethod.additional_info_needed);

    additionalInfoHandler();
  }

  function setPaymentMethodId(paymentMethodId) {
    document.getElementById('paymentMethodId').value = paymentMethodId;
  }

  function setImageCard(secureThumbnail) {
    getCardNumberInput().style.background = `url(${secureThumbnail}) 98% 50% no-repeat #fff`;
  }

  function setCvvProperties(security_code) {
    document.getElementById('mp-security-code').setAttribute('maxlength', security_code.length);
    document.getElementById(
      'mp-security-code-info',
    ).innerText = `Last ${security_code.length} digits in ${security_code.card_location}`;
    document.getElementById('mp-security-code').setAttribute('placeholder', countPlaceHolder());

    function countPlaceHolder() {
      let placeholder = '';

      for (let i = 0; i < security_code.length; i++) {
        placeholder += i + 1;
      }

      return placeholder;
    }
  }

  function loadAdditionalInfo(additional_info_needed) {
    additionalInfoNeeded = {
      issuer: false,
      cardholder_name: false,
      cardholder_identification_type: false,
      cardholder_identification_number: false,
    };

    for (let i = 0; i < additional_info_needed.length; i++) {
      if (additional_info_needed[i] === 'issuer_id') {
        additionalInfoNeeded.issuer = true;
      }

      if (additional_info_needed[i] === 'cardholder_name') {
        additionalInfoNeeded.cardholder_name = true;
      }

      if (additional_info_needed[i] === 'cardholder_identification_type') {
        additionalInfoNeeded.cardholder_identification_type = true;
      }

      if (additional_info_needed[i] === 'cardholder_identification_number') {
        additionalInfoNeeded.cardholder_identification_number = true;
      }
    }
  }

  function additionalInfoHandler() {
    if (additionalInfoNeeded.issuer) {
      showInstallments();
      showIssuers();
      Mercadopago.getIssuers(objPaymentMethod.id, getBin(), issuersHandler);
    } else {
      clearIssuer();
      setInstallments();
    }
		if (additionalInfoNeeded.cardholder_identification_type) {
      Mercadopago.getIdentificationTypes();
    }
  }

  function issuersHandler(status, response) {
    if (status === 200) {
      clearInstallmentsComponent();

      const issuersSelector = document.getElementById('mp-issuer');
      const fragment = document.createDocumentFragment();

      issuersSelector.options.length = 0;

      const option = new Option('Choose...', '-1');
      fragment.appendChild(option);

      for (let i = 0; i < response.length; i++) {
        const name = response[i].name === 'default' ? 'Otro' : response[i].name;
        fragment.appendChild(new Option(name, response[i].id));
      }

      issuersSelector.appendChild(fragment);
      issuersSelector.removeAttribute('disabled');
      issuersSelector.addEventListener('change', setInstallments);
    } else {
      clearIssuer();
    }
  }

  function setInstallments() {
    let params_installments = {};
    const amount = getAmount();
    let issuer = false;

    objPaymentMethod.additional_info_needed.forEach((info) => {
      if (info === 'issuer_id') {
        issuer = true;
      }
    });

    if (issuer) {
      const issuerId = document.getElementById('mp-issuer').value;

      params_installments = {
        bin: getBin(),
        amount,
        issuer_id: issuerId,
      };

      if (issuerId === '-1') {
        clearInstallmentsComponent();
        clearTax();
        return;
      }
    } else {
      params_installments = {
        bin: getBin(),
        amount,
      };
    }

    Mercadopago.getInstallments(params_installments, installmentHandler);
  }

  function installmentHandler(status, response) {
    if (status === 200) {
      let payerCosts = [];
      const installments = [];

      clearInstallmentsComponent();

      for (var i = 0; i < response.length; i++) {
        if (response[i].processing_mode === 'aggregator') {
          payerCosts = response[i].payer_costs;
        }
      }

      for (let j = 0; j < payerCosts.length; j++) {
        const installment = payerCosts[j].installments;
        const installmentRate = payerCosts[j].installment_rate === 0;

        installments.push({
          id: `installment-${installment}`,
          value: installment,
          rowText: `${installment}x ${formatCurrency(payerCosts[j].installment_amount)}`,
          rowObs: installmentRate ? 'No fee' : formatCurrency(payerCosts[j].total_amount),
          highlight: installmentRate ? 'true' : '',
          dataRate: argentinaResolution(payerCosts[j].labels),
        });
      }

      const inputTable = document.createElement('input-table');
      inputTable.setAttribute('name', 'mp-installments');
      inputTable.setAttribute('button-name', 'More options');
      inputTable.setAttribute('columns', JSON.stringify(installments));

      showInstallments();
      showInstallmentsComponent(inputTable);

      if (seller.site_id === 'MLA') {
        clearTax();
        const taxesElements = document.getElementsByClassName('mp-input-table-label');
        for (var i = 0; i < taxesElements.length; i++) {
          taxesElements[i].addEventListener('click', showTaxes);
        }
      }
    } else {
      clearInstallments();
      clearTax();
    }
  }

  function showInstallments() {
    const installmentsContainer = document.getElementById('mp-checkout-custom-installments');
    installmentsContainer.classList.remove('mp-checkout-custom-installments-display-none');
    installmentsContainer.classList.add('mp-checkout-custom-installments');
  }

  function showInstallmentsComponent(child) {
    const selectorInstallments = document.getElementById('mp-checkout-custom-installments-container');
    selectorInstallments.classList.add('mp-checkout-custom-installments-container');
    selectorInstallments.appendChild(child);
  }

  function showIssuers() {
    const issuersContainer = document.getElementById('mp-checkout-custom-issuers-container');
    issuersContainer.classList.remove('mp-checkout-custom-issuers-container-display-none');
    issuersContainer.classList.add('mp-checkout-custom-issuers-container');
  }

  function showTaxes() {
    const selectorInstallments = document.querySelectorAll('.mp-input-radio-radio');
    let tax = null;
    let display = 'block';

    selectorInstallments.forEach((installment) => {
      if (installment.checked) {
        tax = installment.getAttribute('datarate');
      }
    });

    let cft = '';
    let tea = '';

    if (tax != null) {
      const tax_split = tax.split('|');

      cft = tax_split[0].replace('_', ' ');
      tea = tax_split[1].replace('_', ' ');

      if (cft === 'CFT 0,00%' && tea === 'TEA 0,00%') {
        display = 'none';
        cft = '';
        tea = '';
      }
    }

    document.querySelector('#mp-checkout-custom-box-input-tax-cft').style.display = display;
    document.querySelector('#mp-checkout-custom-tax-cft-text').innerHTML = cft;
    document.querySelector('#mp-checkout-custom-tax-tea-text').innerHTML = tea;
  }

	function clearDoc() {
    document.querySelector('[data-checkout="docNumber"]').value = "";
  }

  function clearInstallments() {
    const installmentsContainer = document.getElementById('mp-checkout-custom-installments');
    if (installmentsContainer) {
      installmentsContainer.classList.remove('mp-checkout-custom-installments');
      installmentsContainer.classList.add('mp-checkout-custom-installments-display-none');
    }
  }

  function clearInstallmentsComponent() {
    const selectorInstallments = document.getElementById('mp-checkout-custom-installments-container');
    selectorInstallments.classList.remove('mp-checkout-custom-installments-container');
    if (selectorInstallments.firstElementChild) {
      selectorInstallments.removeChild(selectorInstallments.firstElementChild);
    }
  }

  function clearIssuer() {
    const issuersContainer = document.getElementById('mp-checkout-custom-issuers-container');
    issuersContainer.classList.remove('mp-checkout-custom-issuers-container');
    issuersContainer.classList.add('mp-checkout-custom-issuers-container-display-none');
    document.getElementById('mp-issuer').innerHTML = '';
  }

  function clearTax() {
    document.querySelector('#mp-checkout-custom-box-input-tax-cft').style.display = 'none';
    document.querySelector('#mp-checkout-custom-tax-cft-text').innerHTML = '';
    document.querySelector('#mp-checkout-custom-tax-tea-text').innerHTML = '';
  }

  function clearHolderName() {
    document.getElementById('mp-card-holder-name').value = '';
  }

  function clearExpirationDate() {
    document.getElementById('mp-card-expiration-date').value = '';
  }

  function clearSecurityCode() {
    document.getElementById('mp-security-code').value = '';
  }

  function resetBackgroundCard() {
    document.getElementById('mp-card-number').style.background = 'no-repeat #fff';
  }

  function argentinaResolution(payerCosts) {
    let dataInput = '';

    if (seller.site_id === 'MLA') {
      for (let l = 0; l < payerCosts.length; l++) {
        if (payerCosts[l].indexOf('CFT_') !== -1) {
          dataInput = payerCosts[l];
        }
      }

      return dataInput;
    }

    return dataInput;
  }

  function formatCurrency(value) {
    const formatter = new Intl.NumberFormat('es-AR', {
      style: 'currency',
      currency: 'ARS',
      currencyDisplay: 'narrowSymbol',
    });

    return formatter.format(value);
  }

  function focusInputError() {
    if (document.querySelectorAll('.mp-error') !== undefined) {
      document.querySelectorAll('.mp-error')[0].focus();
    }
  }

  function mercadoPagoFormHandler() {
    if (mercado_pago_submit) {
      mercado_pago_submit = false;
      return true;
    }

    if (validateInputsCreateToken()) {
      return createToken();
    }

    return false;
  }

  function validateInputsCreateToken() {
    hideErrors();

    const fixedInputs = validateFixedInputs();
    const additionalInputs = validateAdditionalInputs();

    if (fixedInputs || additionalInputs) {
      focusInputError();
      return false;
    }

    return true;
  }

  function validateFixedInputs() {
    // todo installments

    let emptyInputs = false;
    const form = getForm();
    const formInputs = form.querySelectorAll('[data-checkout]');
    const fixedInputs = ['cardNumber', 'cardExpirationDate', 'securityCode'];

    for (let x = 0; x < formInputs.length; x++) {
      const element = formInputs[x];

      if (fixedInputs.indexOf(element.getAttribute('data-checkout')) > -1) {
        if (element.value === '-1' || element.value === '') {
          const helper = form.querySelectorAll(`input-helper[data-main="${element.id}"]`);

          if (helper.length > 0) {
            helper[0].children[0].style.display = 'flex';
          }

          element.classList.add('mp-error');
          emptyInputs = true;
        }
      }
    }

    return emptyInputs;
  }

  function validateAdditionalInputs() {
    let emptyInputs = false;

    if (additionalInfoNeeded.issuer) {
      const issuer = document.getElementById('mp-issuer');
      if (issuer.value === '-1' || issuer.value === '') {
        issuer.parentElement.classList.add('mp-error');
        document.getElementById('mp-issuer-helper').style.display = 'flex';
        emptyInputs = true;
      }
    }

    if (additionalInfoNeeded.cardholder_name) {
      const cardHolderName = document.getElementById('mp-card-holder-name');
      if (cardHolderName.value === '-1' || cardHolderName.value === '') {
        cardHolderName.classList.add('mp-error');
        document.getElementById('mp-card-holder-name-helper').style.display = 'flex';
        emptyInputs = true;
      }
    }

    if (additionalInfoNeeded.cardholder_identification_type) {
      var inputDocType = document.querySelector("select[data-checkout=docType]");
      if (inputDocType.value === '-1' || inputDocType.value === '') {
        inputDocType.classList.add('mp-error');
        emptyInputs = true;
      }
    }

    if (additionalInfoNeeded.cardholder_identification_number) {
      const docNumber = document.getElementsByClassName('mp-document')[0];
      const docNumberDiv = document.getElementsByClassName('mp-input')[0];
      if (docNumber.value === '-1' || docNumber.value === '') {
        docNumberDiv.classList.add('mp-error');
        document.getElementById('mp-doc-number-helper').style.display = 'flex';
        emptyInputs = true;
      }
    }

    return emptyInputs;
  }

  function createToken() {
    hideErrors();
    Mercadopago.createToken(getForm(), sdkResponseHandler);
    return false;
  }

  function sdkResponseHandler(status, response) {
    console.log(status, response);

    if (status !== 200 && status !== 201) {
      showErrors(response);
      focusInputError();
    } else {
      const token = document.querySelector('#cardTokenId');
      token.value = response.id;

      console.log(`##### Card Token: ${response.id}`);

      // todo getForm().submit();
    }
  }

  function showErrors(response) {
    for (let x = 0; x < response.cause.length; x++) {
      let helper;
      const error = response.cause[x];

      const cardNumberErrors = ['205', 'E301'];
      const cardHolderNameErrors = ['221'];
      const expirationDateErrors = ['208', '209', '325', '326'];
      const securityCodeErrors = ['224', 'E302'];
      const docNumberErrors = ['324'];

      if (cardNumberErrors.includes(error.code)) {
        helper = document.querySelector('[data-main="mp-card-number"]');
      } else if (expirationDateErrors.includes(error.code)) {
        helper = document.querySelector('[data-main="mp-card-expiration-date"]');
      } else if (cardHolderNameErrors.includes(error.code)) {
        helper = document.querySelector('[data-main="mp-card-holder-name"]');
      } else if (securityCodeErrors.includes(error.code)) {
        helper = document.querySelector('[data-main="mp-security-code"]');
      }else if (docNumberErrors.includes(error.code)) {
        helper = document.querySelector('[data-main="docNumber"]');
      }

      if (helper) {
        const input = document.getElementById(helper.getAttribute('data-main'));
        helper.children[0].style.display = 'flex';
        input.classList.add('mp-error');
      }
    }
  }

  function hideErrors() {
    const fields = document.querySelectorAll('[data-checkout]');
    const inputSelect = document.querySelectorAll('.mp-input-select-select');

    for (var x = 0; x < fields.length; x++) {
      const field = fields[x];
      var helper = document.querySelectorAll(`input-helper[data-main="${field.id}"]`);

      if (helper.length > 0) {
        helper[0].children[0].style.display = 'none';
      }

      field.classList.remove('mp-error');
    }

    for (var x = 0; x < inputSelect.length; x++) {
      const select = inputSelect[x];
      var helper = document.querySelectorAll(`input-helper[data-main="${select.id}"]`);

      if (helper.length > 0) {
        helper[0].children[0].style.display = 'none';
      }

      select.parentElement.classList.remove('mp-error');
    }
  }
})();
