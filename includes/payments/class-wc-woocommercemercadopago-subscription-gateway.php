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
 * Class WC_WooMercadoPago_Subscription_Gateway
 */
class WC_WooMercadoPago_Subscription_Gateway extends WC_WooMercadoPago_Payment_Abstract {

	const ID = 'woo-mercado-pago-subscription';

	/**
	 * WC_WooMercadoPago_Subscription constructor.
	 *
	 * @throws WC_WooMercadoPago_Exception Load payment exception.
	 */
	public function __construct() {
		$this->id = self::ID;

		if ( ! $this->validate_section() ) {
			return;
		}

		$this->description        = __( 'Create a subscription payments. Your customers assigne your plans.', 'woocommerce-mercadopago' );
		$this->form_fields        = array();
		$this->method_title       = __( 'Mercado Pago - Custom Checkout', 'woocommerce-mercadopago' );
		$this->title              = __( 'Pay with Subscription', 'woocommerce-mercadopago'  );
		$this->method_description = $this->description;
		$this->field_forms_order  = $this->get_fields_sequence();
		parent::__construct();
		$this->form_fields         = $this->get_form_mp_fields( 'Subscription' );
		$this->hook                = new WC_WooMercadoPago_Hook_Subscription( $this );
		$this->currency_convertion = true;
	}

}
