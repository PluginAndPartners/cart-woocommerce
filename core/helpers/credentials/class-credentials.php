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
	return;
}
/**
 * Class Credentials
 */
class Credentials {

	public $public_key;
	public $access_token;

	/**
	 * Credentials constructor
	 */
	public function __construct() {
		$public_key    = get_option( '_mp_public_key_prod', '' );
		$access_token  = get_option( '_mp_access_token_prod', '' );

		if ( empty( $public_key ) && empty( $access_token ) ) {
			$public_key   = get_option( '_mp_public_key_test', '' );
			$access_token = get_option( '_mp_access_token_test', '' );
		}

		$this->public_key = $public_key;
		$this->access_token = $access_token;
	}

	/**
	 * Get Access Token
	 * @return $access_token
	 */
	public function get_access_token() {
		return $this->access_token;
	}

	/**
	 * Get Public Key 
	 * @return $public_key
	 */
	public function get_public_key() {
		return $this->public_key;
	}
}
