<?php
namespace Edg\ErpService\Test;

abstract class Client extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $soapMock;
    /**
     * @var \Edg\ErpService\Client
     */
    protected $client;

    protected function setUp()
    {
        parent::setUp();

        $this->soapMock = $this->getMockFromWsdl(__DIR__ . '/_files/edg.wsdl');

        $client = new \Edg\ErpService\Client(false);
        $client->setSoapClient($this->soapMock);
        
        $this->client = $client;
    }
}