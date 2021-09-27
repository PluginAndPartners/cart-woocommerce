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

namespace Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class Credentials
 */
class Credentials {

	private $is_production_mode;

	/**
	 * Credentials constructor
	 */
	public function __construct( $payment_method ) {
		$key     = 'woocommerce_' . $payment_method . '_settings';
		$options = get_option( $key );

		$this->is_production_mode = ( 'no' === $options['checkbox_checkout_test_mode'] );
	}

	/**
	 * Get Access Token
	 *
	 * @return $access_token
	 */
	public function get_access_token() {
		return $this->is_production_mode ? get_option( '_mp_access_token_prod', '' ) : get_option( '_mp_access_token_test', '' );
	}

	/**
	 * Get Public Key
	 *
	 * @return $public_key
	 */
	public function get_public_key() {
		return $this->is_production_mode ? get_option( '_mp_public_key_prod', '' ) : get_option( '_mp_public_key_test', '' );
	}

}
