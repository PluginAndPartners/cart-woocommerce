// MLA
//const mp = new MercadoPago('APP_USR-c836d248-1bea-4983-9076-eb37c75e3c2d');

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
  if (payerCosts) {
    document.getElementById('mp-checkout-custom-installments').style = 'display: block;'
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
  document.getElementById('more-options').addEventListener('click', () => {
    setTimeout(() => {
      setupTaxEvents()
    }, 100);
  });

  if (siteId === 'MLA') {
    clearTax();
    setupTaxEvents();
  }

  function setupTaxEvents() {
    const taxesElements = document.getElementsByClassName('mp-input-table-label');
    for (var i = 0; i < taxesElements.length; i++) {
      let installmentValue = taxesElements[i].getElementsByTagName('input')[0].value;
      taxesElements[i].addEventListener('click', showTaxes);
      taxesElements[i].addEventListener('click', () => {
        document.getElementById('form-checkout__installments').value = installmentValue;
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
  const installmentsContainer = document.getElementById('mp-checkout-custom-installments');
  installmentsContainer.classList.remove('mp-checkout-custom-installments-display-none');
  installmentsContainer.classList.add('mp-checkout-custom-installments');
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
  document.getElementById('form-checkout__cardNumber-container').style.backgroundSize = '7%';
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
    document.getElementById('mp-doc-div').style.display = 'block';
  } else {
    document.getElementById('mp-doc-div').style.display = 'none';
  }
}
