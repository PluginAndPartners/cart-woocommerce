<?php

/**
 * Class WC_WooMercadoPago_Helpers_CurrencyConverter
 */
class WC_WooMercadoPago_Helpers_CurrencyConverter
{
	const CONFIG_KEY    = 'currency_conversion';
	const DEFAULT_RATIO = 1;

	private static $instance;

	private $ratios = [];
	private $cache = [];
	private $enabledCache = [];
	private $accessTokenCache = [];
	private $currencyCache = [];
	private $supportedCurrencies;

	/**
	 * Private constructor to make class singleton
	 */
	private function __construct()
	{
		return $this;
	}

	/**
	 * @return static
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * @param WC_WooMercadoPago_PaymentAbstract $method
	 * @return $this
	 */
	private function init(WC_WooMercadoPago_PaymentAbstract $method)
	{
		$methodId = $this->getMethodId($method);

		if (!isset($this->ratios[$methodId])) {
			try {
				if (!$this->isEnabled($method)) {
					$this->setRatio($methodId);
					return $this;
				}

				$accountCurrency = $this->getAccountCurrency($method);
				$localCurrency = get_woocommerce_currency();

				if (!$accountCurrency || $accountCurrency == $localCurrency) {
					$this->setRatio($methodId);
					return $this;
				}

				$this->setRatio($methodId, $this->loadRatio($localCurrency, $accountCurrency));
			} catch (Exception $e) {
				$this->setRatio($methodId);
			}
		}

		return $this;
	}

	/**
	 * @param WC_WooMercadoPago_PaymentAbstract $method
	 * @return mixed|null
	 */
	private function getAccountCurrency(WC_WooMercadoPago_PaymentAbstract $method)
	{
		$key = $this->getMethodId($method);

		if (isset($this->currencyCache[$key])) {
			return $this->currencyCache[$key];
		}

		$siteId = $this->getSiteId($this->getAccessToken($method));

		if (!$siteId) {
			return null;
		}

		$configs = $this->getCountryConfigs();

		if (!isset($configs[$siteId]) || !isset($configs[$siteId]['currency'])) {
			return null;
		}

		return isset($configs[$siteId]) ? $configs[$siteId]['currency'] : null;
	}

	/**
	 * @return array
	 */
	private function getCountryConfigs()
	{
		try {
			$configInstance = new WC_WooMercadoPago_Configs();
			return $configInstance->getCountryConfigs();
		} catch (Exception $e) {
			return [];
		}
	}

	/**
	 * @param WC_WooMercadoPago_PaymentAbstract $method
	 * @return mixed
	 */
	private function getAccessToken(WC_WooMercadoPago_PaymentAbstract $method)
	{
		$key = $this->getMethodId($method);

		if (isset($this->accessTokenCache[$key])) {
			return $this->accessTokenCache[$key];
		}

		$type = $method->getOption('checkout_credential_production') == 'no'
			? '_mp_access_token_test'
			: '_mp_access_token_prod';

		$this->accessTokenCache[$key] = $method->getOption($type);

		return $this->accessTokenCache[$key];
	}

	/**
	 * @param WC_WooMercadoPago_PaymentAbstract $method
	 * @return mixed
	 */
	public function isEnabled(WC_WooMercadoPago_PaymentAbstract $method)
	{
		$key = $this->getMethodId($method);

		if (isset($this->enabledCache[$key])) {
			return $this->enabledCache[$key];
		}

		$this->enabledCache[$key] = $method->getOption(self::CONFIG_KEY, 0) == 'yes' ? true : false;

		return $this->enabledCache[$key];
	}

	/**
	 * @param WC_WooMercadoPago_PaymentAbstract $method
	 * @return mixed
	 */
	private function getMethodId(WC_WooMercadoPago_PaymentAbstract $method)
	{
		return $method->id;
	}

	/**
	 * @param $mehotdId
	 * @param int $value
	 */
	private function setRatio($mehotdId, $value = self::DEFAULT_RATIO)
	{
		$this->ratios[$mehotdId] = $value;
	}

	/**
	 * @param WC_WooMercadoPago_PaymentAbstract $method
	 * @return int|mixed
	 */
	private function getRatio(WC_WooMercadoPago_PaymentAbstract $method)
	{
		$this->init($method);
		return isset($this->ratios[$this->getMethodId($method)])
			? $this->ratios[$this->getMethodId($method)]
			: self::DEFAULT_RATIO;
	}

	/**
	 * @param $fromCurrency
	 * @param $toCurrency
	 * @return int
	 */
	public function loadRatio($fromCurrency, $toCurrency)
	{
		$cacheKey = $fromCurrency . '--' . $toCurrency;

		if (isset($this->cache[$cacheKey])) {
			return $this->cache[$cacheKey];
		}

		$ratio = self::DEFAULT_RATIO;

		try {
			$result = MeliRestClient::get(
				['uri' => sprintf('/currency_conversions/search?from=%s&to=%s', $fromCurrency, $toCurrency)]
			);

			if (isset($result['response'], $result['response']['ratio'])) {
				$ratio = $result['response']['ratio'] > 0 ? $result['response']['ratio'] : self::DEFAULT_RATIO;
			}
		} catch (Exception $e) {
			//error getting from API
		}

		$this->cache[$cacheKey] = $ratio;
		return $ratio;
	}

    /**
     * @param $accessToken
     * @return string|null
     */
	private function getSiteId($accessToken)
	{
        try {
            $mp = WC_WooMercadoPago_Module::getMpInstanceSingleton();
            if(empty($mp)){
                return null;
            }
            $result = $mp->get(sprintf('/users/me?access_token=%s', $accessToken));
            return isset($result['response'], $result['response']['site_id']) ? $result['response']['site_id'] : null;
        } catch (Exception $e) {
            return null;
        }
	}

	/**
	 * @param WC_WooMercadoPago_PaymentAbstract $method
	 * @return float
	 */
	public function ratio(WC_WooMercadoPago_PaymentAbstract $method)
	{
		$this->init($method);
		return $this->getRatio($method);
	}

	/**
	 * @param WC_WooMercadoPago_PaymentAbstract $method
	 * @return string|void
	 */
	public function getDescription(WC_WooMercadoPago_PaymentAbstract $method)
	{
		try {
			$localCurrency = get_woocommerce_currency();

			$alertImage = plugins_url('assets/images/warning.png', dirname(dirname(__FILE__)));
			$errorImage = plugins_url('assets/images/error.png', dirname(dirname(__FILE__)));
			$checkImage = plugins_url('assets/images/check.png', dirname(dirname(__FILE__)));
			$imageTag   = '<img src="%s" style="padding:2px;display:block;float:left;margin-right:5px;margin-bottom:10px;" />';

			//if currency conversion is not enabled, validates messages that need to be shown
			if (!$this->isEnabled($method)) {
				//if woocommerce configures currency is differente from mercado pago account currency
				//alert user for possible problems!
				if ($localCurrency != $this->getAccountCurrency($method)) {
					$imageTag = sprintf($imageTag, $alertImage);
					$tag = __('ATTENTION');
					return sprintf(__('%s <b>%s:</b> The currency %s defined in WooCommerce is different from the one used in your credentials country.'
						. '<br />The currency for transactions in this payment method will be %s.', 'woocommerce-mercadopago-module'),
						$imageTag, $tag, $localCurrency, $this->getAccountCurrency($method));
				}

				return __('If the used currency in WooCommerce is different or not supported by Mercado Pago, convert values of your transactions using Mercado Pago currency ratio.', 'woocommerce-mercadopago-module');
			}

			//currency conversion is enabled but local currency is not supported by mercado pago currency conversion API
			if (!$this->isCurrencySupported($localCurrency)) {
				$imageTag = sprintf($imageTag, $errorImage);
				$tag = __('ERROR');
				return sprintf(__('%s <b>%s:</b> It was not possible to convert the unsupported currency %s to %s.'
					. ' Currency conversions should be made outside this module.', 'woocommerce-mercadopago-module'),
					$imageTag, $tag, $localCurrency, $this->getAccountCurrency($method));
			}

			//conversion is enabled and everything working fine
			$imageTag = sprintf($imageTag, $checkImage);
			$tag = __('CURRENCY CONVERTED');
			return sprintf(__('%s <b>%s:</b> The currency conversion ratio from %s to %s is: %s', 'woocommerce-mercadopago-module'),
				$imageTag, $tag, $localCurrency, $this->getAccountCurrency($method), $this->getRatio($method));

		} catch (Exception $e) {
			return '';
		}
	}

	/**
	 * @param $currency
	 * @return bool
	 */
	private function isCurrencySupported($currency)
	{
		foreach ($this->getSupportedCurrencies() as $country) {
			if ($country['id'] == $currency) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array|bool
	 */
	public function getSupportedCurrencies()
	{
		if (is_null($this->supportedCurrencies)) {
			try {
				$result = MeliRestClient::get(['uri' => '/currencies']);

				if (!isset($result['response'])) {
					return false;
				}

				$this->supportedCurrencies = $result['response'];
			} catch (Exception $e) {
				$this->supportedCurrencies = [];
			}
		}

		return $this->supportedCurrencies;
	}

	/**
	 * @param WC_WooMercadoPago_PaymentAbstract $method
	 * @return array
	 */
	public function getField(WC_WooMercadoPago_PaymentAbstract $method)
	{
		return [
			'title'       => __('Convert Currency', 'woocommerce-mercadopago'),
			'type'        => 'checkbox',
			'default'     => 'no',
			'label'       => __('Convert Currency', 'woocommerce-mercadopago'),
			'description' => $this->getDescription($method)
		];
	}
}
