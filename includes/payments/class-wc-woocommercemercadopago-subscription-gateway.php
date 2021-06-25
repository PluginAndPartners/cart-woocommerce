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

		$this->description               = __( 'Create a subscription payments. Your customers assigne your plans.', 'woocommerce-mercadopago' );
		$this->form_fields               = array();
		$this->method_title              = __( 'Mercado Pago - Custom Checkout', 'woocommerce-mercadopago' );
		$this->title                     = __( 'Pay with Subscription', 'woocommerce-mercadopago'  );
		$this->method_description        = $this->description;
		$this->method                    = $this->get_option_mp( 'method', 'redirect' );
		$this->success_url_subscripition = $this->get_option_mp( 'success_url_subscripition', '' );
		$this->field_forms_order         = $this->get_fields_sequence();
		parent::__construct();
		$this->form_fields               = $this->get_form_mp_fields( 'Subscription' );
		$this->hook                      = new WC_WooMercadoPago_Hook_Subscription( $this );
		$this->currency_convertion       = true;
	}


	/**
	 * Get MP fields label
	 *
	 * @param string $label Label.
	 * @return array
	 */
	public function get_form_mp_fields( $label ) {
		if ( is_admin() && $this->is_manage_section() ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script(
				'woocommerce-mercadopago-subscription-config-script',
				plugins_url( '../assets/js/subscription_config_mercadopago' . $suffix . '.js', plugin_dir_path( __FILE__ ) ),
				array(),
				WC_WooMercadoPago_Constants::VERSION,
				true
			);
		}

		if ( empty( $this->checkout_country ) ) {
			$this->field_forms_order = array_slice( $this->field_forms_order, 0, 7 );
		}

		if ( ! empty( $this->checkout_country ) && empty( $this->get_access_token() ) && empty( $this->get_public_key() ) ) {
			$this->field_forms_order = array_slice( $this->field_forms_order, 0, 22 );
		}

		$form_fields = array();

		$form_fields['checkout_subscription_header'] = $this->field_checkout_subscription_header();

		if ( ! empty( $this->checkout_country ) && ! empty( $this->get_access_token() ) && ! empty( $this->get_public_key() ) ) {
			$form_fields['checkout_options_title']           = $this->field_checkout_options_title();
			$form_fields['checkout_payments_title']          = $this->field_checkout_payments_title();
			$form_fields['checkout_payments_subtitle']       = $this->field_checkout_payments_subtitle();
			$form_fields['checkout_payments_description']    = $this->field_checkout_options_description();
			$form_fields['checkout_payments_advanced_title'] = $this->field_checkout_payments_advanced_title();
			$form_fields['method']                           = $this->field_method();
			$form_fields['success_url']                      = $this->field_success_url();
		}

		$form_fields_abs = parent::get_form_mp_fields( $label );
		if ( count( $form_fields_abs ) === 1 ) {
			return $form_fields_abs;
		}
		$form_fields_merge = array_merge( $form_fields_abs, $form_fields );
		return $this->sort_form_fields( $form_fields_merge, $this->field_forms_order );
	}

	/**
	 * Get fields sequence
	 *
	 * @return array
	 */
	public function get_fields_sequence() {
		return array(
			// Necessary to run.
			'title',
			'description',
			// Checkout Básico. Acepta todos los medios de pago y lleva tus cobros a otro nivel.
			'checkout_subscription_header',
			'checkout_steps',
			// ¿En qué país vas a activar tu tienda?
			'checkout_country_title',
			'checkout_country',
			'checkout_btn_save',
			// Carga tus credenciales.
			'checkout_credential_title',
			'checkout_credential_mod_test_title',
			'checkout_credential_mod_test_description',
			'checkout_credential_mod_prod_title',
			'checkout_credential_mod_prod_description',
			'checkout_credential_prod',
			'checkout_credential_link',
			'checkout_credential_title_test',
			'checkout_credential_description_test',
			'_mp_public_key_test',
			'_mp_access_token_test',
			'checkout_credential_title_prod',
			'checkout_credential_description_prod',
			'_mp_public_key_prod',
			'_mp_access_token_prod',
			// No olvides de homologar tu cuenta.
			'checkout_homolog_title',
			'checkout_homolog_subtitle',
			'checkout_homolog_link',
			// Configure Mercado Pago for WooCommerce.
			'checkout_options_title',
			'mp_statement_descriptor',
			'_mp_category_id',
			'_mp_store_identificator',
			'_mp_integrator_id',
			// Advanced settings.
			'checkout_advanced_settings',
			'_mp_debug_mode',
			'_mp_custom_domain',
			// Set up the payment experience in your store.
			'checkout_payments_title',
			'checkout_payments_subtitle',
			'checkout_payments_description',
			'enabled',
			WC_WooMercadoPago_Helpers_CurrencyConverter::CONFIG_KEY,
			// Advanced settings.
			'checkout_payments_advanced_title',
			'checkout_payments_advanced_description',
			'method',
			'success_url',
			// Support session.
			'checkout_support_title',
			'checkout_support_description',
			'checkout_support_description_link',
			'checkout_support_problem',
			// Everything ready for the takeoff of your sales?
			'checkout_ready_title',
			'checkout_ready_description',
			'checkout_ready_description_link',
		);
	}

	/**
	 * Is available?
	 *
	 * @return bool
	 * @throws WC_WooMercadoPago_Exception Load access token exception.
	 */
	public function is_available() {
		if ( parent::is_available() ) {
			return true;
		}

		if ( isset( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ) {
			if ( $this->mp instanceof MP ) {
				$access_token = $this->mp->get_access_token();
				if ( false === WC_WooMercadoPago_Credentials::validate_credentials_test( $this->mp, $access_token )
					&& true === $this->sandbox ) {
					return false;
				}

				if ( false === WC_WooMercadoPago_Credentials::validate_credentials_prod( $this->mp, $access_token )
					&& false === $this->sandbox ) {
					return false;
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Get clientID when update version 3.0.17 to 4 latest
	 *
	 * @return string
	 */
	public function get_client_id() {
		$client_id = get_option( '_mp_client_id', '' );
		if ( ! empty( $client_id ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Field checkout header
	 *
	 * @return array
	 */
	public function field_checkout_subscription_header() {
		return array(
			'title' => sprintf(
				/* translators: %s checkout */
				__( 'Checkout Subscription %s', 'woocommerce-mercadopago' ),
				'<div class="row">
                <div class="mp-col-md-12 mp_subtitle_header">
                ' . __( 'Create a subscrtiption payment and take your charges to another level', 'woocommerce-mercadopago' ) . '
                 </div>
              <div class="mp-col-md-12">
                <p class="mp-text-checkout-body mp-mb-0">
                  ' . __( 'Turn your online store into your customers preferred payment gateway. Choose if the final payment experience will be inside or outside your store.', 'woocommerce-mercadopago' ) . '
                </p>
              </div>
            </div>'
			),
			'type'  => 'title',
			'class' => 'mp_title_header',
		);
	}

	/**
	 * Field checkout options title
	 *
	 * @return array
	 */
	public function field_checkout_options_title() {
		return array(
			'title' => __( 'Configure Mercado Pago for WooCommerce', 'woocommerce-mercadopago' ),
			'type'  => 'title',
			'class' => 'mp_title_bd',
		);
	}

	/**
	 * Field checkout options description
	 *
	 * @return array
	 */
	public function field_checkout_options_description() {
		return array(
			'title' => __( 'Enable the experience of the Checkout Subscription in your online store', 'woocommerce-mercadopago' ),
			'type'  => 'title',
			'class' => 'mp_small_text',
		);
	}

	/**
	 * Field checkout payments title
	 *
	 * @return array
	 */
	public function field_checkout_payments_title() {
		return array(
			'title' => __( 'Set payment preferences in your store', 'woocommerce-mercadopago' ),
			'type'  => 'title',
			'class' => 'mp_title_bd',
		);
	}

	/**
	 * Field checkout payments advanced title
	 *
	 * @return array
	 */
	public function field_checkout_payments_advanced_title() {
		return array(
			'title' => __( 'Advanced settings', 'woocommerce-mercadopago' ),
			'type'  => 'title',
			'class' => 'mp_subtitle_bd',
		);
	}

	/**
	 * Field method
	 *
	 * @return array
	 */
	public function field_method() {
		return array(
			'title'       => __( 'Payment experience', 'woocommerce-mercadopago' ),
			'type'        => 'select',
			'description' => __( 'Define what payment experience your customers will have, whether inside or outside your store.', 'woocommerce-mercadopago' ),
			'default'     => ( 'iframe' === $this->method ) ? 'redirect' : $this->method,
			'options'     => array(
				'redirect' => __( 'Redirect', 'woocommerce-mercadopago' ),
				'modal'    => __( 'Modal', 'woocommerce-mercadopago' ),
			),
		);
	}

	/**
	 * Field success url
	 *
	 * @return array
	 */
	public function field_success_url() {
		// Validate back URL.
		if ( ! empty( $this->success_url ) && filter_var( $this->success_url, FILTER_VALIDATE_URL ) === false ) {
			$success_back_url_message = '<img width="14" height="14" src="' . plugins_url( 'assets/images/warning.png', plugin_dir_path( __FILE__ ) ) . '"> ' .
				__( 'This seems to be an invalid URL.', 'woocommerce-mercadopago' ) . ' ';
		} else {
			$success_back_url_message = __( 'Choose the URL that we will show your customers when they finish their purchase.', 'woocommerce-mercadopago' );
		}
		return array(
			'title'       => __( 'Success URL', 'woocommerce-mercadopago' ),
			'type'        => 'text',
			'description' => $success_back_url_message,
			'default'     => '',
		);
	}

	/**
	 * Payment Fields
	 */
	public function payment_fields() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// add css.
		wp_enqueue_style(
			'woocommerce-mercadopago-basic-checkout-styles',
			plugins_url( '../assets/css/basic_checkout_mercadopago' . $suffix . '.css', plugin_dir_path( __FILE__ ) ),
			array(),
			WC_WooMercadoPago_Constants::VERSION
		);

		// validate active payments methods.
		$debito       = 0;
		$credito      = 0;
		$efectivo     = 0;
		$method       = $this->get_option_mp( 'method', 'redirect' );
		$tarjetas     = get_option( '_checkout_payments_methods', '' );
		$cho_tarjetas = array();

		// change type account_money to ticket.
		foreach ( $tarjetas as $key => $value ) {
			if ( 'account_money' === $value['type'] ) {
				$all_payments[ $key ]['type'] = 'ticket';
			} else {
				continue;
			}
		}

		foreach ( $tarjetas as $tarjeta ) {
			if ( 'yes' === $this->get_option_mp( $tarjeta['config'], '' ) ) {
				$cho_tarjetas[] = $tarjeta;
				if ( 'credit_card' === $tarjeta['type'] ) {
					++$credito;
				} elseif ( 'debit_card' === $tarjeta['type'] || 'prepaid_card' === $tarjeta['type'] ) {
					++$debito;
				} else {
					++$efectivo;
				}
			}
		}

		$parameters = array(
			'debito'         => $debito,
			'credito'        => $credito,
			'efectivo'       => $efectivo,
			'tarjetas'       => $cho_tarjetas,
			'method'         => $method,
			'plugin_version' => WC_WooMercadoPago_Constants::VERSION,
			'cho_image'      => plugins_url( '../assets/images/redirect_checkout.png', plugin_dir_path( __FILE__ ) ),
		);

		wc_get_template( 'checkout/basic-checkout.php', $parameters, 'woo/mercado/pago/module/', WC_WooMercadoPago_Module::get_templates_path() );
	}

	/**
	 * Process payment
	 *
	 * @param int $order_id Order Id.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order  = wc_get_order( $order_id );
		$amount = $this->get_order_total();

		if ( method_exists( $order, 'update_meta_data' ) ) {
			$order->update_meta_data( '_used_gateway', get_class( $this ) );

			$order->save();
		} else {
			update_post_meta( $order_id, '_used_gateway', get_class( $this ) );
		}

		if ( 'redirect' === $this->method || 'iframe' === $this->method ) {
			$this->log->write_log( __FUNCTION__, 'customer being redirected to Mercado Pago.' );
			return array(
				'result'   => 'success',
				'redirect' => $this->create_preference( $order ),
			);
		} elseif ( 'modal' === $this->method ) {
			$this->log->write_log( __FUNCTION__, 'preparing to render Checkout Pro view.' );
			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true ),
			);
		}
	}

	/**
	 * Create preference
	 *
	 * @param object $order Order.
	 * @return bool
	 */
	public function create_preference( $order ) {
		$preferences_basic = new WC_WooMercadoPago_Preference_Basic( $this, $order );
		$preferences       = $preferences_basic->get_preference();
		try {
			$checkout_info = $this->mp->create_preference( wp_json_encode( $preferences ) );
			$this->log->write_log( __FUNCTION__, 'Created Preference: ' . wp_json_encode( $checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			if ( $checkout_info['status'] < 200 || $checkout_info['status'] >= 300 ) {
				$this->log->write_log( __FUNCTION__, 'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message'] );
				return false;
			} elseif ( is_wp_error( $checkout_info ) ) {
				$this->log->write_log( __FUNCTION__, 'WordPress gave error, payment creation failed with error: ' . $checkout_info['response']['message'] );
				return false;
			} else {
				$this->log->write_log( __FUNCTION__, 'payment link generated with success from mercado pago, with structure as follow: ' . wp_json_encode( $checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
				if ( $this->sandbox ) {
					return $checkout_info['response']['sandbox_init_point'];
				}
				return $checkout_info['response']['init_point'];
			}
		} catch ( WC_WooMercadoPago_Exception $ex ) {
			$this->log->write_log( __FUNCTION__, 'payment creation failed with exception: ' . wp_json_encode( $ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			return false;
		}
	}

	/**
	 * Get Id
	 *
	 * @return string
	 */
	public static function get_id() {
		return self::ID;
	}
}
