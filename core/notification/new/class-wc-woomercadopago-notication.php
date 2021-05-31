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
class WC_WooMercadoPago_Notification extends WC_WooMercadoPago_Notification_Abstract {

	/**
	 * Get Orders
	 */
	public function get_order() {
		parent::get_order();
		// @todo need fix Processing form data without nonce verification
		// @codingStandardsIgnoreLine
		$data = $_GET;
		exit("Morreu");
		if ( 
				isset( $data['payment_id'] ) && 
				isset( $data['external_reference'] ) && 
				isset( $data['timestamp'] )
			) {
			
			$parameters = new StdClass;
			$parameters->payment_id = $data['payment_id'];
			$parameters->external_reference = $data['external_reference'];
			$parameters->timestamp = $data['timestamp'];
			
			$secret = $this->mp->get_access_token();

			$key = Cryptography::encrypt( $parameters, $secret );

			$token = Request::getBearerToken();
			
			if ( !$token ) {
				$this->set_response( 422, null , __( 'Unauthorized', 'woocommerce-mercadopago' ) );
			} elseif ( $key == $token ) {	

				$order = wc_get_order( $data['external_reference'] );
				$order_id = $order->get_id();
				
				$response = new StdClass;
				$response->order_id = $order_id;
				$response->external_reference = $order_id;
				$response->status = $order->get_status();
				$response->created_at = $order->get_date_created();
				$response->total = $order->get_total();
				$response->timestamp = time();

				/*
				*** Creating hmac for response
				*/
				$hmac = Cryptography::encrypt( $response, $secret );
				$response->hmac = $hmac;

				$this->set_response( 200, 'OK', $response );
			} else {
				$this->set_response( 401, null , __( 'Unauthorized', 'woocommerce-mercadopago' ) );
			}
			
			
		}
}