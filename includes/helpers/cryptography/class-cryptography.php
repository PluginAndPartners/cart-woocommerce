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
 * Class Cryptography
 */
class Cryptography {

	public static function obj_to_string( $parameters ) {

		ksort($parameters);

		$data = '';

		foreach ( $parameters as $key => $value ) {
			$data .= $key . '=' . $value . '&';
		}

		$data = substr( $data, 0, -1 );

		return $data;
	}

	public static function verify( $key, $hmac ) {
		if ( hash_equals($key, $hmac) ) {
			return true;
		} else {
			return false;
		}
	}
	public static function encrypt( $data, $secret ) {
		if ( ! empty($secret) && ! empty($data) ) {
			try {
				$string = self::obj_to_string($data);
				$hmac   = hash_hmac('sha256', $string, $secret, true);
				$key    = base64_encode($hmac);// phpcs:ignore
				return $key;
			} catch ( Exception $e ) {
				$message = "Error while encrypting. <br> $e";
				return $message;
			}
		} else {
			throw new Exception('Empty parameters');
		}
	}


}
