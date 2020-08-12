<?php
namespace Bold\PIMService\Test;

abstract class Client extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $soapMock;
    /**
     * @var \Bold\PIMService\Client
     */
    protected $client;

    protected function setUp()
    {
        parent::setUp();

        $this->soapMock = $this->getMockFromWsdl(__DIR__ . '/_files/edg.wsdl');

        $client = new \Bold\PIMService\Client(false);
        $client->setSoapClient($this->soapMock);
        
        $this->client = $client;
    }
}