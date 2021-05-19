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
 * Class WC_WooMercadoPago_Pix_Gateway
 */
class WC_WooMercadoPago_Pix_Gateway extends WC_WooMercadoPago_Payment_Abstract {

	const ID = 'woo-mercado-pago-pix';

	/**
	 * WC_WooMercadoPago_PixGateway constructor.
	 *
	 * @throws WC_WooMercadoPago_Exception Load payment exception.
	 */
	public function __construct() {
		$this->id = self::ID;

		if ( ! $this->validate_section() ) {
			return;
		}

		$this->description        = __( 'Accept payments via Pix Transfer and receive the funds instantly. Your customers can pay at any time, without date or time restrictions.', 'woocommerce-mercadopago' );
		$this->form_fields        = array();
		$this->method_title       = __( 'Mercado Pago - Custom Checkout', 'woocommerce-mercadopago' );
		$this->title              = __( 'Pay with PIX ', 'woocommerce-mercadopago' );
		$this->method_description = $this->description;
		$this->date_expiration    = (int) $this->get_option_mp( 'checkout_pix_date_expiration', '1' );
		$this->type_payments      = $this->get_option_mp( 'type_payments', 'no' );
		$this->payment_type       = 'pix';
		$this->checkout_type      = 'custom';
		$this->activated_payment  = get_option( '_mp_payment_methods_pix', '' );
		$this->field_forms_order  = $this->get_fields_sequence();
		parent::__construct();
		$this->update_pix_method();
		$this->form_fields         = $this->get_form_mp_fields( 'Pix' );
		$this->hook                = new WC_WooMercadoPago_Hook_Pix( $this );
		$this->notification        = new WC_WooMercadoPago_Notification_Webhook( $this );
		$this->currency_convertion = true;
		add_action( 'woocommerce_email_before_order_table', array(__CLASS__,'get_pix_template'), 20, 4 );
	}

	/**
	 * Get form mp fields
	 *
	 * @param string $label Label.
	 * @return array
	 */
	public function get_form_mp_fields( $label ) {
		if ( is_admin() && $this->is_manage_section() ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script(
				'woocommerce-mercadopago-pix-config-script',
				plugins_url( '../assets/js/pix_config_mercadopago' . $suffix . '.js', plugin_dir_path( __FILE__ ) ),
				array(),
				WC_WooMercadoPago_Constants::VERSION,
				false
			);
		}

		if ( empty( $this->checkout_country ) ) {
			$this->field_forms_order = array_slice( $this->field_forms_order, 0, 7 );
		}

		if ( ! empty( $this->checkout_country ) && empty( $this->get_access_token() ) && empty( $this->get_public_key() ) ) {
			$this->field_forms_order = array_slice( $this->field_forms_order, 0, 22 );
		}

		$form_fields                        = array();
		$form_fields['checkout_pix_header'] = $this->field_checkout_pix_header();
		if ( ! empty( $this->checkout_country ) && ! empty( $this->get_access_token() ) && ! empty( $this->get_public_key() ) ) {
			if ( empty( $this->activated_payment ) || ! is_array( $this->activated_payment ) || ! in_array( 'pix', $this->activated_payment['pix'], true ) ) {
				$form_fields['checkout_steps_pix'] = $this->field_checkout_steps_pix();

				// @todo need fix Processing form data without nonce verification
			  	// @codingStandardsIgnoreLine
			  	if ( isset( $_GET['section'] ) && $_GET['section'] == $this->id ) {
					add_action( 'admin_notices', array( $this, 'enable_pix_payment_notice' ) );
				}
			}
				$form_fields['checkout_pix_options_title']           = $this->field_checkout_pix_options_title();
				$form_fields['checkout_pix_payments_title']          = $this->field_checkout_pix_payments_title();
				$form_fields['checkout_pix_payments_description']    = $this->field_checkout_pix_options_description();
				$form_fields['checkout_pix_payments_advanced_title'] = $this->field_checkout_pix_payments_advanced_title();
				$form_fields['checkout_pix_date_expiration']         = $this->field_pix_date_expiration();
				$form_fields['checkout_about_pix']                   = $this->field_checkout_about_pix();
		}

		$form_fields_abs = parent::get_form_mp_fields( $label );
		if ( 1 === count( $form_fields_abs ) ) {
			return $form_fields_abs;
		}
		$form_fields_merge = array_merge( $form_fields_abs, $form_fields );
		$fields            = $this->sort_form_fields( $form_fields_merge, $this->field_forms_order );

		if ( empty( $this->activated_payment ) || ! is_array( $this->activated_payment ) || ! in_array( 'pix', $this->activated_payment['pix'], true ) ) {
			$form_fields_not_show = array_flip( $this->get_fields_not_show() );
			$fields               = array_diff_key( $fields, $form_fields_not_show );
		}

		return $fields;
	}

	/**
	 * Update Pix Method
	 *
	 * @return void
	 */
	public function update_pix_method() {
		$wc_country       = WC_WooMercadoPago_Module::get_woocommerce_default_country();
		$site_id          = get_option( '_site_id_v1', '' );
		$_mp_access_token = $this->get_access_token();
		if ( ( 'BR' === $wc_country && '' === $site_id ) || ( 'MLB' === $site_id ) ) {
			WC_WooMercadoPago_Credentials::update_pix_method( $this->mp, $_mp_access_token );
		}
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
			// Checkout de pagos con dinero en efectivo<br> Aceptá pagos al instante y maximizá la conversión de tu negocio.
			'checkout_pix_header',
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
			// Steps configuration pix.
			'checkout_steps_pix',
			// Set up the payment experience in your store.
			'checkout_pix_options_title',
			'mp_statement_descriptor',
			'_mp_category_id',
			'_mp_store_identificator',
			'_mp_integrator_id',
			// Advanced settings.
			'checkout_advanced_settings',
			'_mp_debug_mode',
			'_mp_custom_domain',
			// Configure the personalized payment experience in your store.
			'checkout_pix_payments_title',
			'checkout_payments_subtitle',
			'checkout_pix_payments_description',
			'enabled',
			'checkout_pix_date_expiration',
			WC_WooMercadoPago_Helpers_CurrencyConverter::CONFIG_KEY,
			// About PIX.
			'checkout_about_pix',
			// Advanced configuration of the personalized payment experience.
			'checkout_pix_payments_advanced_title',
			'checkout_payments_advanced_description',
			'gateway_discount',
			'commission',
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
	 * Get fields NOT allow to show
	 *
	 * @return array
	 */
	public function get_fields_not_show() {
		return array(
			// Set up the payment experience in your store.
			'checkout_pix_options_title',
			'mp_statement_descriptor',
			'_mp_category_id',
			'_mp_store_identificator',
			'_mp_integrator_id',
			// Advanced settings.
			'checkout_advanced_settings',
			'_mp_debug_mode',
			'_mp_custom_domain',
			// Configure the personalized payment experience in your store.
			'checkout_pix_payments_title',
			'checkout_payments_subtitle',
			'checkout_pix_payments_description',
			'enabled',
			'checkout_pix_date_expiration',
			WC_WooMercadoPago_Helpers_CurrencyConverter::CONFIG_KEY,
			// About PIX.
			'checkout_about_pix',
			// Advanced configuration of the personalized payment experience.
			'checkout_pix_payments_advanced_title',
			'checkout_payments_advanced_description',
			'gateway_discount',
			'commission',
			// Everything ready for the takeoff of your sales?
			'checkout_ready_title',
			'checkout_ready_description',
			'checkout_ready_description_link',
		);
	}


	/**
	 * Field checkout steps
	 *
	 * @return array
	 */
	public function field_checkout_steps_pix() {
		$steps_content = wc_get_template_html(
			'checkout/credential/steps-pix.php',
			array(
				'title'                       => __( 'To activate Pix, you must have a key registered in Mercado Pago.', 'woocommerce-mercadopago' ),
				'step_one_text'               => __( 'Download the Mercado Pago app on your cell phone.', 'woocommerce-mercadopago' ),
				'step_two_text_one'           => __( 'Go to the ', 'woocommerce-mercadopago' ),
				'step_two_text_two'           => __( 'area and choose the ', 'woocommerce-mercadopago' ),
				'step_two_text_highlight_one' => __( 'Your Profile ', 'woocommerce-mercadopago' ),
				'step_two_text_highlight_two' => __( 'Your Pix Keys section.', 'woocommerce-mercadopago' ),
				'step_three_text'             => __( 'Choose which data to register as PIX keys. After registering, you can set up PIX in your checkout.', 'woocommerce-mercadopago' ),
				'observation_one'             => __( 'Remember that, for the time being, the Central Bank of Brazil is open Monday through Friday, from 9am to 6pm.', 'woocommerce-mercadopago' ),
				'observation_two'             => __( 'If you requested your registration outside these hours, we will confirm it within the next business day.', 'woocommerce-mercadopago' ),
				'button_about_pix'            => __( 'Learn more about PIX', 'woocommerce-mercadopago' ),
				'observation_three'           => __( 'If you have already registered a Pix key at Mercado Pago and cannot activate Pix in the checkout, ', 'woocommerce-mercadopago' ),
				'link_title_one'              => __( 'click here.', 'woocommerce-mercadopago' ),
				'link_url_one'                => 'https://www.mercadopago.com.br/pix/',
				'link_url_two'                => 'https://www.mercadopago.com.br/developers/pt/support/contact',
			),
			'woo/mercado/pago/steps/',
			WC_WooMercadoPago_Module::get_templates_path()
		);

		return array(
			'title' => $steps_content,
			'type'  => 'title',
			'class' => 'mp_title_checkout',
		);
	}

	/**
	 * Field checkout pix header
	 *
	 * @return array
	 */
	public function field_checkout_pix_header() {
		return array(
			'title' => sprintf(
				/* translators: %s checkout */
				__( 'Checkout of payments via PIX %s', 'woocommerce-mercadopago' ),
				'<div class="mp-row">
                <div class="mp-col-md-12 mp_subtitle_header">
                ' . __( 'Accept payments at any time of the day and expand your purchase options!', 'woocommerce-mercadopago' ) . '
                 </div>
              <div class="mp-col-md-12">
                <p class="mp-text-checkout-body mp-mb-0">
                  ' . __( 'Offer this new payment option to your customers.', 'woocommerce-mercadopago' ) . '
                </p>
              </div>
            </div>'
			),
			'type'  => 'title',
			'class' => 'mp_title_header',
		);
	}

	/**
	 * Field checkout pix options title
	 *
	 * @return array
	 */
	public function field_checkout_pix_options_title() {
		return array(
			'title' => __( 'Configure Mercado Pago for WooCommerce', 'woocommerce-mercadopago' ),
			'type'  => 'title',
			'class' => 'mp_title_bd',
		);
	}

	/**
	 * Field checkout pix options description
	 *
	 * @return array
	 */
	public function field_checkout_pix_options_description() {
		return array(
			'title' => __( 'Enable and set up Pix as a payment method for your customers in the Mercado Pago checkout.', 'woocommerce-mercadopago' ),
			'type'  => 'title',
			'class' => 'mp_small_text',
		);
	}

	/**
	 * Field checkout pix payments
	 *
	 * @return array
	 */
	public function field_checkout_pix_payments_title() {
		return array(
			'title' => __( 'Set up the payment via Pix experience', 'woocommerce-mercadopago' ),
			'type'  => 'title',
			'class' => 'mp_title_bd',
		);
	}

	/**
	 * Field checkout pix payments advanced title
	 *
	 * @return array
	 */
	public function field_checkout_pix_payments_advanced_title() {
		return array(
			'title' => __( 'Advanced configuration of the PIX experience', 'woocommerce-mercadopago' ),
			'type'  => 'title',
			'class' => 'mp_subtitle_bd',
		);
	}

	/**
	 * Field date expiration
	 *
	 * @return array
	 */
	public function field_pix_date_expiration() {
		$pix_days = array();
		for ( $i = 1; $i <= 7; ++$i ) {
			$pix_days[ $i ] = $i;
		}

		return array(
			'title'       => __( 'Pix Expiration', 'woocommerce-mercadopago' ),
			'type'        => 'select',
			'description' => __( 'Set the limit of days in which your customers can pay.', 'woocommerce-mercadopago' ),
			'default'     => $this->get_option_mp( 'checkout_pix_date_expiration', '1' ),
			'options'     => $pix_days,
		);
	}

	/**
	 * Field checkout about pix
	 *
	 * @return array
	 */
	public function field_checkout_about_pix() {
		$link_content = wc_get_template_html(
			'checkout/credential/about-pix.php',
			array(
				'title'       => __( 'Want to learn how Pix works?', 'woocommerce-mercadopago' ),
				'subtitle'    => __( 'We have created a page to explain how this new payment method works and its advantages.', 'woocommerce-mercadopago' ),
				'url_link'    => 'https://www.mercadopago.com.br/pix/',
				'button_text' => __( 'Learn more about PIX', 'woocommerce-mercadopago' ),
			),
			'woo/mercado/pago/about-pix/',
			WC_WooMercadoPago_Module::get_templates_path()
		);

		return array(
			'title' => $link_content,
			'type'  => 'title',
		);
	}

	/**
	 * Payment fields
	 */
	public function payment_fields() {
		// add css.
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'woocommerce-mercadopago-basic-checkout-styles',
			plugins_url( '../assets/css/basic_checkout_mercadopago' . $suffix . '.css', plugin_dir_path( __FILE__ ) ),
			array(),
			WC_WooMercadoPago_Constants::VERSION
		);

		$parameters = array(
			'image_pix' => plugins_url( '../assets/images/pix.png', plugin_dir_path( __FILE__ ) ),
		);

		wc_get_template( 'checkout/pix-checkout.php', $parameters, 'woo/mercado/pago/module/', WC_WooMercadoPago_Module::get_templates_path() );
	}

	/**
	 * Process payment
	 *
	 * @param int $order_id Order Id.
	 * @return array|string[]
	 */
	public function process_payment( $order_id ) {
		// @todo need fix Processing form data without nonce verification
		// @codingStandardsIgnoreLine
		$pix_checkout = $_POST;
		$this->log->write_log( __FUNCTION__, 'Payment via Pix POST: ' );
		$order = wc_get_order( $order_id );

		$amount = $this->get_order_total();
		if ( method_exists( $order, 'update_meta_data' ) ) {
			$order->update_meta_data( '_used_gateway', get_class( $this ) );
			if ( ! empty( $this->gateway_discount ) ) {
				$discount = $amount * ( $this->gateway_discount / 100 );
				$order->update_meta_data( 'Mercado Pago: discount', __( 'discount of', 'woocommerce-mercadopago' ) . ' ' . $this->gateway_discount . '% / ' . __( 'discount of', 'woocommerce-mercadopago' ) . ' = ' . $discount );
			}

			if ( ! empty( $this->commission ) ) {
				$comission = $amount * ( $this->commission / 100 );
				$order->update_meta_data( 'Mercado Pago: comission', __( 'fee of', 'woocommerce-mercadopago' ) . ' ' . $this->commission . '% / ' . __( 'fee of', 'woocommerce-mercadopago' ) . ' = ' . $comission );
			}
			$order->save();
		} else {
			update_post_meta( $order_id, '_used_gateway', get_class( $this ) );

			if ( ! empty( $this->gateway_discount ) ) {
				$discount = $amount * ( $this->gateway_discount / 100 );
				update_post_meta( $order_id, 'Mercado Pago: discount', __( 'discount of', 'woocommerce-mercadopago' ) . ' ' . $this->gateway_discount . '% / ' . __( 'discount of', 'woocommerce-mercadopago' ) . ' = ' . $discount );
			}

			if ( ! empty( $this->commission ) ) {
				$comission = $amount * ( $this->commission / 100 );
				update_post_meta( $order_id, 'Mercado Pago: comission', __( 'fee of', 'woocommerce-mercadopago' ) . ' ' . $this->commission . '% / ' . __( 'fee of', 'woocommerce-mercadopago' ) . ' = ' . $comission );
			}
		}

		if ( filter_var( $order->get_billing_email(), FILTER_VALIDATE_EMAIL ) ) {
			$response = $this->create_preference( $order, $pix_checkout );

			if ( is_array( $response ) && array_key_exists( 'status', $response ) ) {
				if ( 'pending' === $response['status'] ) {
					if ( 'pending_waiting_payment' === $response['status_detail'] || 'pending_waiting_transfer' === $response['status_detail'] ) {
						WC()->cart->empty_cart();
						// WooCommerce 3.0 or later.
						if ( method_exists( $order, 'update_meta_data' ) ) {
							$order->update_meta_data( 'mp_transaction_amount', $response['transaction_amount'] );
							$order->update_meta_data( 'mp_pix_qr_base64', $response['point_of_interaction']['transaction_data']['qr_code_base64'] );
							$order->update_meta_data( 'mp_pix_qr_code', $response['point_of_interaction']['transaction_data']['qr_code'] );
							$order->update_meta_data('checkout_pix_date_expiration', $this->get_option_mp( 'checkout_pix_date_expiration', '1' ) . ( 1 < $this->get_option_mp( 'checkout_pix_date_expiration', '1' ) ? __( ' days', 'woocommerce-mercadopago' ) : __( ' day', 'woocommerce-mercadopago' )));
							$order->update_meta_data( 'pix_on', 1);
							$order->save();
						} else {
							update_post_meta( $order->get_id(), 'mp_transaction_amount', $response['transaction_amount'] );
							update_post_meta( $order->get_id(), 'mp_pix_qr_base64', $response['point_of_interaction']['transaction_data']['qr_code_base64'] );
							update_post_meta( $order->get_id(), 'mp_pix_qr_code', $response['point_of_interaction']['transaction_data']['qr_code'] );
							update_post_meta( $order->get_id(),'checkout_pix_date_expiration', $this->get_option_mp( 'checkout_pix_date_expiration', '1' ) .( 1 < $this->get_option_mp( 'checkout_pix_date_expiration', '1' ) ? __( ' days', 'woocommerce-mercadopago' ) : __( ' day', 'woocommerce-mercadopago' )));
							update_post_meta( $order->get_id(),'pix_on', 1);
						}
						// Shows some info in checkout page.
						$order->add_order_note(
							'Mercado Pago: ' .
							'<small>'. __( 'The customer has not paid yet.', 'woocommerce-mercadopago' ) .'</small>'	
						);
						if ( 'pix' === $response['payment_method_id'] ) {
							$order->add_order_note(
								'<div style="text-align: justify;"><p>Mercado Pago: '. __( 'Now you just need to pay with PIX to finalize your purchase', 'woocommerce-mercadopago' ).
								__( 'Scan the QR code below or copy and paste the code into your bank\'s application.', 'woocommerce-mercadopago' ) . '</small></p>',
								1,
								false
							);
						}

						return array(
							'result'   => 'success',
							'redirect' => $order->get_checkout_order_received_url(),
						);
					}
				}
			} else {
				// Process when fields are imcomplete.
				wc_add_notice(
					'<p>' .
					__( 'A problem occurred when processing your payment. Are you sure you have correctly filled in all the information on the checkout form?', 'woocommerce-mercadopago' ) . ' MERCADO PAGO: ' .
					WC_WooMercadoPago_Module::get_common_error_messages( $response ) .
					'</p>',
					'error'
				);
				return array(
					'result'   => 'fail',
					'redirect' => '',
				);
			}
		} else {
			// Process when fields are incomplete.
			wc_add_notice(
				'<p>' .
				__( 'A problem occurred when processing your payment. Please try again.', 'woocommerce-mercadopago' ) .
				'</p>',
				'error'
			);
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
	}

	/**
	 * Create preference
	 *
	 * @param object $order Order.
	 * @param array  $pix_checkout Picket checkout.
	 * @return string|array
	 */
	public function create_preference( $order, $pix_checkout ) {
		$preferences_pix = new WC_WooMercadoPago_Preference_pix( $this, $order, $pix_checkout );
		$preferences     = $preferences_pix->get_preference();
		try {
			$checkout_info = $this->mp->post( '/v1/payments', wp_json_encode( $preferences ) );
			$this->log->write_log( __FUNCTION__, 'Created Preference: ' . wp_json_encode( $checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			if ( $checkout_info['status'] < 200 || $checkout_info['status'] >= 300 ) {
				$this->log->write_log( __FUNCTION__, 'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message'] );
				return $checkout_info['response']['message'];
			} elseif ( is_wp_error( $checkout_info ) ) {
				$this->log->write_log( __FUNCTION__, 'WordPress gave error, payment creation failed with error: ' . $checkout_info['response']['message'] );
				return $checkout_info['response']['message'];
			} else {
				$this->log->write_log( __FUNCTION__, 'payment link generated with success from mercado pago, with structure as follow: ' . wp_json_encode( $checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
				return $checkout_info['response'];
			}
		} catch ( WC_WooMercadoPago_Exception $ex ) {
			$this->log->write_log( __FUNCTION__, 'payment creation failed with exception: ' . wp_json_encode( $ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			return $ex->getMessage();
		}
	}

	/**
	 * Is available?
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( ! parent::is_available() ) {
			return false;
		}

		$payment_methods = $this->activated_payment;
		if ( empty( $payment_methods ) || ! is_array( $payment_methods ) || ! in_array( 'pix', $payment_methods['pix'], true ) ) {
			$this->log->write_log( __FUNCTION__, 'PIX key not found in payment_methods API, no active PIX payment method. ' );
			return false;
		}

		$_mp_access_token    = get_option( '_mp_access_token_prod' );
		$is_prod_credentials = false === WC_WooMercadoPago_Credentials::validate_credentials_test( $this->mp, $_mp_access_token, null );

		if ( ( empty( $_SERVER['HTTPS'] ) || 'off' === $_SERVER['HTTPS'] ) && $is_prod_credentials ) {
			$this->log->write_log( __FUNCTION__, 'NO HTTPS, PIX unavailable.' );
			return false;
		}

		return true;
	}

	/**
	 * Enable pix payment notice
	 *
	 * @return void
	 */
	public function enable_pix_payment_notice() {
		$type    = 'notice-warning';
		$message = wc_get_template_html(
			'checkout/credential/alert/alert-pix-not-registered.php',
			array(
				'message'   => __( 'Please note that to receive payments via Pix at our checkout, you must have a Pix key registered in your Mercado Pago account.', 'woocommerce-mercadopago' ),
				'text_link' => __( 'Register your Pix key at Mercado Pago.', 'woocommerce-mercadopago' ),
				'url_link'  => 'https://www.mercadopago.com.br/stop/pix?url=https%3A%2F%2Fwww.mercadopago.com.br%2Fadmin-pix-keys%2Fmy-keys&authentication_mode=required',
			),
			'woo/mercado/pago/alert-pix-not-registered.php/',
			WC_WooMercadoPago_Module::get_templates_path()
		);
		WC_WooMercadoPago_Notices::get_alert_frame( $message, $type );
	}

	/**
	 * Get Id
	 *
	 * @return string
	 */
	public static function get_id() {
		return self::ID;
	}

	/**
	 * Get pix template
	 *
	 * @return string
	 */
	public function get_pix_template ($order, $sent_to_admin, $plain_text, $email) {

		$pix_on = array_pop(get_post_meta( $order->get_id(), 'pix_on' ));

		if($pix_on == 1){
			
		$qr_code 			= 	array_pop(get_post_meta( $order->get_id(), 'mp_pix_qr_code' ));
		$qr_image 			= 	array_pop(get_post_meta( $order->get_id(), 'mp_pix_qr_base64' ));
		$src 				= 	'data:image/jpeg;base64';
		$expiration_date	= 	array_pop(get_post_meta( $order->get_id(),'checkout_pix_date_expiration'));

		$pix_template = wc_get_template(
			'pix/pix-image-template.php',
			array(
				'src'   						=> 		$src,
				'qr_image'						=> 		$qr_image,
				'qr_code' 						=> 		$qr_code,
				'expiration_date' 				=> 		$expiration_date,
				'text_expiration_date'			=>      __( 'Code valid for ', 'woocommerce-mercadopago' ),
			),
			'',
			WC_WooMercadoPago_Module::get_templates_path()
		);

		return $pix_template;

		} 

	}

}