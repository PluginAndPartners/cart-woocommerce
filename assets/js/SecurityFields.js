var cardForm;
var isSubmitted = false;
var amount = document.getElementById('mp-amount').value;

var form = document.querySelector('form[name=checkout]')
var formId = 'checkout';
if (form) {
  form.id = formId;
} else {
  formId = 'order_review';
}

function submitChoCustom() {
  if (isSubmitted) {
    return true;
  }
  cardForm.createCardToken()
    .then(cardToken => {
      if (cardToken.token) {
        document.querySelector("#cardTokenId").value = cardToken.token;
        isSubmitted = true;
        jQuery('form.checkout, form#order_review').submit();
      } else {
        throw new Error('cardToken is empty');
      }
    })
    .catch(error => {
      console.log('ERRO:', error)
    });
  return false;
}

function init_cardForm() {
  var mp = new MercadoPago(wc_mercadopago_params.public_key);

  cardForm = mp.cardForm(
    {
      amount: amount,
      iframe: true,
      form: {
        id: formId,
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
          placeholder: wc_mercadopago_params.placeholders['cardExpirationDate'],
          mode: 'short',
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
          placeholder: wc_mercadopago_params.placeholders['issuer']
        },
        installments: {
          id: 'form-checkout__installments',
          placeholder: wc_mercadopago_params.placeholders['installments']
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
        },
        onPaymentMethodsReceived: (error, paymentMethods) => {
          try {
            if (paymentMethods) {
              setPaymentMethodId(paymentMethods[0].id)
              setCvvHint(paymentMethods[0].settings[0].security_code);
              changeCvvPlaceHolder(paymentMethods[0].settings[0].security_code.length)
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
        onError: function (errors) {
          errors.forEach(error => {
            if (error.message.includes("cardNumber")) { return showInputHelper('mp-card-number'); }
            else if (error.message.includes("cardholderName")) { return showInputHelper('mp-card-holder-name'); }
            else if (error.message.includes("expirationMonth") || error.message.includes("expirationYear")) { return showInputHelper('mp-expiration-date'); }
            else if (error.message.includes("CVV")) { return showInputHelper('mp-cvv'); }
            else if (error.message.includes("identificationNumber")) { return showInputHelper('mp-doc-number'); }
            else { return console.log("Erro desconhecido") }
          });
        },
        onSubmit: function (event) {
          event.preventDefault();
        },
        onValidityChange: function (error, field) {
          if (error) {
            if (field == 'cardNumber') { 
              document.getElementById('form-checkout__cardNumber-container').style.background = 'no-repeat #fff';
              removeAdditionFields();
            }
            return showInputHelper(inputHelperName(field));
          }
          return removeInputHelper(inputHelperName(field));
        }
      }
    }
  );
  function changeCvvPlaceHolder(cvvLength) {
    let text = '';
    for (let index = 0; index < cvvLength; index++) {
      text += index + 1
    }
    cardForm.update('securityCode', { placeholder: text });
  }
}

function removeAdditionFields(){
  hideDocumentField();
  hideInstallments();
  hideIssuer();
}

function inputHelperName(field) {
  let inputHelperName = {
    cardNumber: 'mp-card-number',
    cardholderName: 'mp-card-holder-name',
    expirationDate: 'mp-expiration-date',
    CVV: 'mp-cvv',
    identificationNumber: 'mp-doc-number',
  }
  return inputHelperName[field];
}

function hideErrors() {
  let input_helpers = document.querySelectorAll('input-helper');
  input_helpers.forEach((input_helper) => {
    input_helper.querySelector('div').style.display = "none";;
  });
}


clearInputs = function () {
  hideErrors();
  document.getElementById('form-checkout__cardNumber-container').style.background = 'no-repeat #fff';
  document.getElementById('form-checkout__cardExpirationDate-container').value = '';
  document.getElementById('form-checkout__identificationNumber').value = '';
  document.getElementById('form-checkout__securityCode-container').value = '';
  document.getElementById('form-checkout__cardholderName').value = '';
}

showInputHelper = function (name) {
  let div_input_helper = document.querySelector('input-helper[input-id=' + name + '-helper]');
  let input_helper = div_input_helper.querySelector('div');
  input_helper.style.display = "flex";
}

removeInputHelper = function (name) {
  let div_input_helper = document.querySelector('input-helper[input-id=' + name + '-helper]');
  let input_helper = div_input_helper.querySelector('div');
  input_helper.style.display = "none";
}

const setChangeEventOnInstallments = function (siteId, response) {
  let payerCosts = [];
  const installments = [];

  clearInstallmentsComponent();
  payerCosts = response.payer_costs;
  if (payerCosts) { showInstallments(); }

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
  setupTaxEvents();
  document.getElementById('more-options').addEventListener('click', () => {
    setTimeout(() => {
      setupTaxEvents();
    }, 100);
  });

  if (siteId === 'MLA') {
    clearTax();
  }

  function setupTaxEvents() {
    const taxesElements = document.getElementsByClassName('mp-input-table-label');
    for (var i = 0; i < taxesElements.length; i++) {
      let installmentValue = taxesElements[i].getElementsByTagName('input')[0].value;
      taxesElements[i].addEventListener('click', showTaxes);
      taxesElements[i].addEventListener('click', () => {
        document.getElementById('form-checkout__installments').value = installmentValue;
        document.getElementById('cardInstallments').value = installmentValue;
      });
    }
  }
}

function formatCurrency(value) {
  const formatter = new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    currencyDisplay: 'narrowSymbol',
  });

  return formatter.format(value);
}

function argentinaResolution(payerCosts) {
  let dataInput = '';

  if (getCountry() === 'MLA') {
    for (let l = 0; l < payerCosts.length; l++) {
      if (payerCosts[l].indexOf('CFT_') !== -1) {
        dataInput = payerCosts[l];
      }
    }

    return dataInput;
  }

  return dataInput;
}

function clearTax() {
  document.querySelector('#mp-checkout-custom-box-input-tax-cft').style.display = 'none';
  document.querySelector('#mp-checkout-custom-tax-cft-text').innerHTML = '';
  document.querySelector('#mp-checkout-custom-tax-tea-text').innerHTML = '';
}

function showInstallments() {
  document.getElementById('mp-checkout-custom-installments').style.display = 'block';
}

function hideInstallments() {
  document.getElementById('mp-checkout-custom-installments').style.display = 'none'
}

function showInstallmentsComponent(child) {
  const selectorInstallments = document.getElementById('mp-checkout-custom-installments-container');
  selectorInstallments.classList.add('mp-checkout-custom-installments-container');
  selectorInstallments.appendChild(child);
}

function clearInstallmentsComponent() {
  const selectorInstallments = document.getElementById('mp-checkout-custom-installments-container');
  selectorInstallments.classList.remove('mp-checkout-custom-installments-container');
  if (selectorInstallments.firstElementChild) {
    selectorInstallments.removeChild(selectorInstallments.firstElementChild);
  }
}

function showDocumentField(){
  document.getElementById('mp-doc-div').style.display = 'block';
}

function hideDocumentField(){
  document.getElementById('mp-doc-div').style.display = 'none';
}

function showIssuer(){
  document.getElementById('form-checkout__issuer').style.display = 'block';
}

function hideIssuer(){
  document.getElementById('form-checkout__issuer').style.display = 'none';
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

const getCountry = function () {
  return 'MLB';
}

const setCvvHint = function (security_code) {
  document.getElementById(
    'mp-security-code-info',
  ).innerText = `Last ${security_code.length} digits in ${security_code.card_location}`;
}

const setImageCard = function (secureThumbnail) {
  document.getElementById('form-checkout__cardNumber-container').style.background = 'url(' + secureThumbnail + ') 98% 50% no-repeat #fff';
  document.getElementById('form-checkout__cardNumber-container').style.backgroundSize = 'auto 24px';
}

const setPaymentMethodId = function (id) {
  document.getElementById('paymentMethodId').value = id;
}

const handleInstallments = function (payment_type_id) {
  if (payment_type_id === 'debit_card') {
    document.getElementById('form-checkout__installments').setAttribute("disabled", "disabled");
  } else {
    document.getElementById('form-checkout__installments').removeAttribute("disabled");
  }
}

const loadAdditionalInfo = function (sdkAdditionalInfoNeeded) {
  additionalInfoNeeded = {
    issuer: false,
    cardholder_name: false,
    cardholder_identification_type: false,
    cardholder_identification_number: false
  };

  for (let i = 0; i < sdkAdditionalInfoNeeded.length; i++) {
    if (sdkAdditionalInfoNeeded[i] === 'issuer_id') {
      additionalInfoNeeded.issuer = true;
    }
    if (sdkAdditionalInfoNeeded[i] === 'cardholder_name') {
      additionalInfoNeeded.cardholder_name = true;
    }
    if (sdkAdditionalInfoNeeded[i] === 'cardholder_identification_type') {
      additionalInfoNeeded.cardholder_identification_type = true;
    }
    if (sdkAdditionalInfoNeeded[i] === 'cardholder_identification_number') {
      additionalInfoNeeded.cardholder_identification_number = true;
    }
  }
}

const additionalInfoHandler = function () {
  if (additionalInfoNeeded.cardholder_name) {
    document.getElementById('form-checkout__cardholderName').style.display = 'block';
  } else {
    document.getElementById('form-checkout__cardholderName').style.display = 'none';
  }

  if (additionalInfoNeeded.issuer) {
    document.getElementById('form-checkout__issuer').style.display = 'block';
  } else {
    document.getElementById('form-checkout__issuer').style.display = 'none';
  }
  if (additionalInfoNeeded.cardholder_identification_type && additionalInfoNeeded.cardholder_identification_number) {
    showDocumentField();
  } else {
    hideDocumentField();
  }
}

jQuery("form.checkout").on(
  "checkout_place_order_woo-mercado-pago-custom",
  function () {
    return submitChoCustom();
  }
);

// If payment fail, retry on next checkout page
jQuery("form#order_review").submit(function () {
  return submitChoCustom();
});
