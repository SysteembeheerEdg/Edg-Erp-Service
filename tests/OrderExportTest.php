<?php
/**
 * OrderExportTest
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Edg\ErpService\Test;


class OrderExportTest extends Client
{
    public function testExport()
    {
        $order = $this->createOrder();

        $result = new \stdClass;
        $result->result = null;
        $result->v_STATUS = "OK";
        
        $xml = file_get_contents(__DIR__ . '/_files/orderupload1.xml');

        $this->soapMock->expects($this->any())
            ->method('uploadOrders')
            ->with(
                $this->callback(function($input){
                    if(count($input) != 4){
                        return false;
                    }
                    $keys = ['v_XML', 'v_TYPE', 'v_MSGID', 'v_environment'];
                    foreach($keys as $key){
                        if(!array_key_exists($key, $input)){
                            throw new \PHPUnit_Framework_ExpectationFailedException('mandatory array keys not found');
                        }
                    }

                    $expected_content = file_get_contents(__DIR__ . '/_files/orderupload1.xml');
                    try{

                        $expected = dom_import_simplexml(new \SimpleXMLElement(file_get_contents(__DIR__ . '/_files/orderupload1.xml')));
                        $actual = dom_import_simplexml(new \SimpleXMLElement($input['v_XML']));
                        \PHPUnit_Framework_TestCase::assertEqualXMLStructure($expected, $actual);
                    } catch(\Exception $e){

                        throw new \PHPUnit_Framework_ExpectationFailedException('xml structure of v_XML does not match the expected structure of orderupload1.xml' .
                        PHP_EOL . 'actual: ' . $input['v_XML'] . PHP_EOL . 'expected: ' . $expected_content);
                    }

                    return true;
                })
            )
            ->willReturn($result);
        $results = $this->client->pushNewOrder($order, ['environment' => 'test env', 'export_type' => 'test type']);

        self::assertTrue($results[0]->isValid());
    }

    protected function createOrder()
    {
        return new \Edg\ErpService\DataModel\Order([
            'incrementId' => 1000001,
            'shipping_method' => 'postnl',
            'shipping_amount' => "4,20",
            'shipping_incl_tax' => "4,95",
            'payment_method_title' => "banktransfer",
            'payment_method' => "banktransfer",
            'payment_transactionid' => '3',
            'total_items' => 5,
            'currency' => 'EUR',
            'subtotal' => '100',
            'subtotal_incl_tax' => '120',
            'grandtotal' => '124,95',
            'total_paid' => '124,95',
            'total_due' => 0,
            'order_customer_group_id' => 1,
            'order_customer_group_name' => 'general',
            'order_school_naam' => 'OBS test',
            'order_functie_besteller' => 'directeur',
            'additional' => [
                'custom_attrib_1' => 'qwerty'
            ],
            'environment_tag' => 'dummy env',
            'customer' => [
                'id' => '1234',
                'email' => 'customer@example.com',
                'customer_group' => 'default',
                'progress_id' => '2',
                'school_naam' => 'OBS test',
                'brinnummer' => '5678',
                'functie_besteller' => 'directeur',
                'school_bool' => '1'
            ],
            'items' => [
                0 => [
                    'item_id' => 1,
                    'name' => 'simple 1',
                    'sku' => 'simple-1',
                    'qty' => 3,
                    'price' => 17,
                    'price_incl_tax'=> 20,
                    'tax_percent' => '21',
                    'discount_amount' => 0,
                    'row_total' =>  51,
                    'original_price' => 17,
                    'row_total_incl_tax' => 60,
                    'product_type' => 'simple'
                ],
                1 => [
                    'item_id' => 2,
                    'name' => 'bundle 1',
                    'sku' => 'bundle-1',
                    'qty' => 2,
                    'price' => 25,
                    'price_incl_tax' => 30,
                    'tax_percent' => 6,
                    'row_total' => 50,
                    'original_price' => 25,
                    'row_total_incl_tax' => 60,
                    'product_type' => 'bundle',
                ],
                2 => [
                    'item_id' => 3,
                    'name' => 'simple 2',
                    'sku' => 'simple-2',
                    'qty' => 2,
                    'price' => 0,
                    'price_incl_tax' => 0,
                    'tax_percent' => 0,
                    'discount_amount' => 0,
                    'row_total' => 0,
                    'original_price' => 0,
                    'row_total_incl_tax' => 0,
                    'product_type' => 'simple',

                    'parent_item_id' => 2
                ]
            ],
            'addresses' => [
                'shipping' => [
                    'city' => 'shipping city',
                    'country_id' => 'NL',
                    'email' => 'shipping@example.com',
                    'telephone' => '123456789',
                    'firstname' => 'ship',
                    'lastname' => 'ping',
                    'postcode' => '1234 AB',
                    'street' => 'shipping street',
                ],
                'billing' => [
                    'city' => 'billing city',
                    'company' => 'billing company',
                    'country_id' => 'NL',
                    'email' => 'billing@example.com',
                    'telephone' => '987654321',
                    'firstname' => 'bi',
                    'lastname' => 'ing',
                    'middlename' => 'll',
                    'postcode' => '9876 ZY',
                    'prefix' => 'Mr',
                    'region' => 'billing region',
                    'street' => 'billing street',
                ]
            ],
            'meta' => [
                'type' => 'meta type 1',
                'store' => 'Default Store',
                'datetime' => '2017-01-01 06:30:00',
                'time_offset' => 'GMT+2'
            ],
            'order_remarks' => [
                0 => 'order placed',
                1 => 'order invoiced'
            ]

        ]);
    }
}