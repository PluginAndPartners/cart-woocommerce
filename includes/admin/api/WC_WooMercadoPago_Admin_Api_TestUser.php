<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WC_WooMercadoPago_Admin_Api_TestUser
 */
class WC_WooMercadoPago_Admin_Api_TestUser
{

    /**
     * WC_WooMercadoPago_Admin_Api_TestUser constructor.
     */
    public function __construct()
    {
        add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'get_test_user'));
    }

    /**
     *
     */
    public function get_test_user()
    {
        $accessToken = $_REQUEST['accessToken'];
        $siteId = $_REQUEST['site_id'];
        $url = 'https://api.mercadopago.com/users/test_user?access_token=' . $accessToken;
        $postFields = "{\"site_id\":\"$siteId\"}";

        $headers = array();
        $headers[] = 'Content-Type: application/json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            status_header(400);
            die('Error:' . curl_error($ch));
        }
        curl_close($ch);

        status_header(200);
        die($result);
    }

}
