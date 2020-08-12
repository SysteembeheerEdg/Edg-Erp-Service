<?php
namespace Edg\ErpService\Test;

class StockMutationsTest extends Client
{
    public function testStockMutations()
    {
        $result = new \stdClass;
        $result->v_status = 'OK';
        $result->result = null;
        $result->v_stockmutations = "<articles><environment>dummy</environment><article><sku>06094567763</sku><stock>5</stock></article></articles>";

        $result2 = new \stdClass;
        $result2->v_status = 'OK';
        $result2->result = null;
        $result2->v_stockmutations = "<articles><environment>dummy</environment><article><sku>abcd-1234</sku><stock>2</stock></article><article><sku>06094567763</sku><stock>4</stock></article></articles>";

        $result3 = new \stdClass;
        $result3->v_status = \Edg\ErpService\Sync\Pull\StockMutations::NO_MUTATIONS;
        $result3->result = null;
        $result3->v_stockmutations = "";

        $this->soapMock->expects($this->any())
            ->method('stockmutations2')
            ->willReturnOnConsecutiveCalls($result, $result2, $result3);

        $results = $this->client->pullStockUpdates('dummy_tag');

        self::assertEquals(2, count($results), "Expected only two results, because the third had no mutations");

        $mutation = $results[0]->getMutations()[0];

        self::assertEquals("06094567763", $mutation->getSku(),
            "Unexpected sku found in stock mutation of first result set");
        self::assertEquals(5, $mutation->getStock(),
            "Unexpected stock qty found in stock mutation of first result set");

        $mutation2 = $results[1]->getMutations();

        self::assertEquals(2, count($mutation2), "Expected two mutations in result set");
        self::assertEquals("abcd-1234", $mutation2[0]->getSku(),
            "Unexpected Sku found in first mutation of second result set");
        self::assertEquals("06094567763", $mutation2[1]->getSku(),
            "Unexpected sku found in second mutation of second result set ");
        self::assertEquals(2, $mutation2[0]->getStock(),
            "Unexpected stock qty found in first mutation of second result set");
        self::assertEquals(4, $mutation2[1]->getStock(),
            "Unexpected stock qty found in second mutation of second result set");

    }

}