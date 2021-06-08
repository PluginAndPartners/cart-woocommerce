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
 * Class Cryptography
 */
class Cryptography {
	public static function verify( $key, $hmac ) {
		if (hash_equals($key, $hmac)) {
			return true;
		} else {
			return false;
		}
	}
	public static function encrypt( $data, $secret ) {
		if (!empty($secret) && !empty($data)) {
			try {
				$hmac = hash_hmac('sha256', $data, $secret);
				$key  = base64_encode($hmac);
				return $key;
			} catch (Exception $e) {
				$message =  "Erro ao encriptar. <br> $e";
				return $message;
			}
		} else {
			throw 'Parametros vazios';
		}
	}
}
