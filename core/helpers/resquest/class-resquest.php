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
 * Class Request
 */
class Request {
	/** 
	 * Get header Authorization
	 * */
	public static function getAuthorizationHeader() {
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			// @todo need fix Processing form data without nonce verification
			// @codingStandardsIgnoreLine
			$headers = trim($_SERVER['Authorization']);
		} elseif ( isset($_SERVER['HTTP_AUTHORIZATION']) ) {
			// @todo need fix Processing form data without nonce verification
			// @codingStandardsIgnoreLine
			$headers = trim($_SERVER['HTTP_AUTHORIZATION']);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}

	/**
	* Get access token from header
	* */
	public static function getBearerToken() {
		$headers = self::getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}
}
