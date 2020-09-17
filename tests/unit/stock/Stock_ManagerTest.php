<?php
require_once '../../../includes/stock/WC_WooMercadoPago_Stock_Manager.php';

class WC_WooMercadoPago_Stock_ManagerTest extends WP_UnitTestCase {

    public $class_instance;

    public function setUp() {
        parent::setUp();
        $this->class_instance = new WC_WooMercadoPago_Stock_Manager();
    }

    /**
     * @param $method
     * @return mixed
     * @throws ReflectionException
     */
    private function makeReflection($method) {

        $reflection = new ReflectionClass('WC_WooMercadoPago_Stock_Manager');
        $protectedMethod = $reflection->getMethod($method);
        $protectedMethod->setAccessible(true);

        return $protectedMethod->invokeArgs($this->class_instance, ['']);
    }
    public function test_check_wc_version() {

        $result = $this->makeReflection('check_wc_version');
        $this->assertTrue($result);
    }
}
