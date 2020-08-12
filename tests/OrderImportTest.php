<?php
namespace Edg\ErpService\Test;

class OrderImportTest extends Client
{
    public function testImport()
    {
        $result = new \stdClass;
        $result->result = null;
        $result->v_status = 'OK';
        $result->v_orders = file_get_contents(__DIR__ .'/_files/orderstatusResponse1.xml');

        $this->soapMock->expects($this->any())
            ->method('orderstatus2')
            ->willReturn($result);

        $results = $this->client->pullOrderUpdates('dummy');
        $response = $results[0];

        self::assertEquals('OK', $response->getStatus(), "Unexpected api response status");
        $orders = $response->getOrders();

        self::assertEquals(1, count($orders), "Unexpected amount of orders in API response");

        $order = $orders[0];

        self::assertEquals("shipped", $order->getOrderStatus(), "Unexpected order status");
        self::assertEquals("100002945", $order->getOrderNumber(), "Unexpected order number");

        $orderlines = $order->getOrderRows();
        $trackingcodes = $order->getBarcodes();

        self::assertEquals(3, count($orderlines), "Unexpected amount of orderrows in order object");
        self::assertEquals(1, count($trackingcodes), "Unexpected amount of tracking codes in order object");

        self::assertEquals("1019as", $trackingcodes[0]['zipcode'], "Unexpected zipcode found in order's barcode");
        self::assertEquals("3SYSGZ195795201", $trackingcodes[0]['code'], "Unexpected tracking code found in order's barcode");

        self::assertEquals(1, $orderlines[0]['ordered'], "Unexpected amount of ordered on first order row");
        self::assertEquals(1, $orderlines[0]['invoiced'], "Unexpected amount of invoiced on first order row");
        self::assertEquals(1, $orderlines[0]['shipped'], "Unexpected amount of shipped on first order row");
        self::assertEquals(1000070, $orderlines[0]['sku'], "Unexpected sku on first order row");

        self::assertEquals(7, $orderlines[2]['ordered'], "Unexpected amount of ordered on third order row");
        self::assertEquals(7, $orderlines[2]['invoiced'], "Unexpected amount of invoiced on third order row");
        self::assertEquals(3, $orderlines[2]['shipped'], "Unexpected amount of shipped on third order row");
        self::assertEquals(1000080, $orderlines[2]['sku'], "Unexpected sku on third order row");
    }
}