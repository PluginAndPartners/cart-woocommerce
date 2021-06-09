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
	exit;
}

/**
 * Class WC_WooMercadoPago_Notification
 */
class WC_WooMercadoPago_Notification {

	/**
	 * Undocumented variable
	 */
	public static $instance = null;


	public function __construct() {
		add_action( 'woocommerce_api_wc_mp_notification', array($this, 'check_mp_response'));
	}

	/**
	 *
	 * Init Mercado Pago Class
	 *
	 * @return WC_WooMercadoPago_Notification|null
	 * Singleton
	 */
	public static function init_notification_class() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get Orders
	 */
	public function get_order( $data ) {
		// @todo need fix Processing form data without nonce verification
		// @codingStandardsIgnoreLine
		if	(
				isset( $data['payment_id'] ) &&
				isset( $data['external_reference'] ) &&
				isset( $data['timestamp'] )
			) {

			$parameters                       = array();
			$parameters['payment_id'] 		  = $data['payment_id'];			
			$parameters['external_reference'] = $data['external_reference'];
			$parameters['timestamp'] 		  = $data['timestamp'];

			$credentials = new Credentials();

			$secret	= $credentials->get_access_token();

			if ( is_null($secret) || empty($secret) ) {
				$this->set_response( 500, null, 'Credentials not found' );
			}

			$key = Cryptography::encrypt( wp_json_encode($parameters), $secret );

			$token = Request::getBearerToken();

			if ( !$token ) {
				$this->set_response( 401, null, 'Unauthorized' );
			} elseif ( $key === $token ) {

				$order = wc_get_order( $data['external_reference'] );
				if ( $order ) {
					$order_id = $order->get_id();

					$response 						= array();
					$response['order_id'] 			= $order_id;
					$response['external_reference'] = $order_id;
					$response['status'] 			= $order->get_status();
					$response['created_at'] 		= $order->get_date_created();
					$response['total'] 				= $order->get_total();
					$response['timestamp'] 			= time();

					/*
					*** Creating hmac for response
					*/
					$hmac = Cryptography::encrypt( wp_json_encode($response), $secret );

					$response['hmac'] = $hmac;

					$this->set_response( 200, 'Success', $response );
				} else {
					$this->set_response( 404, null, 'Order not found' );
				}


			} else {
				$this->set_response( 401, null, 'Unathorized' );
			}


		} else {
			$this->set_response( 400, null, 'Missing fields');
		}
	}

	/**
	 * Endpoint
	 */
	public function check_mp_response() {
		if (isset($_SERVER['REQUEST_METHOD'])) {
			// @todo need fix Processing form data without nonce verification
			// @codingStandardsIgnoreLine
			$method = $_SERVER['REQUEST_METHOD'];

			if ( 'GET' === $method ) {
				// @todo need fix Processing form data without nonce verification
				// @codingStandardsIgnoreLine
				$this->get_order($_GET);
			} else {
				$this->set_response( 405, null, 'Method not allowed');
			}
		}
	}

	/**
	 * Set response
	 *
	 * @param int    $code         HTTP Code.
	 * @param string $code_message Message.
	 * @param string $body         Body.
	 */
	public function set_response( $code, $code_message, $body ) {
		status_header( $code, $code_message );
		// @todo need to implements better
		// @codingStandardsIgnoreLine
		die( $body );
	}
}
