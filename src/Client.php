<?php

namespace Edg\ErpService;

use Psr\Log\LoggerInterface;

/**
 * Main class to use, for sending commands to the edg server
 *
 * @package Edg\ErpService
 */
class Client
{
    /**
     * @var \SoapClient $soapClient     preconfigured soap client
     */
    protected $soapClient;

    /**
     * @var array $clientSettings       additional settings to pass on to the commands
     * @see \Edg\ErpService\Sync\Request::$settings
     */
    protected $clientSettings;

    /**
     * Client constructor.
     *
     * constructor for the client that interacts with the API. if you pass the wsdl URI and config settings, then the
     * soapclient will be created in this constructor. If you dont do this, then you must manually set the SoapClient
     * with the setSoapClient method before calling one of the API methods.
     *
     * @param bool|string $wsdl URI of the WSDL file or false
     * @param array $soapConfig
     */
    public function __construct($wsdl = false, $soapConfig = [])
    {
        if($wsdl !== false){
            $this->soapClient = new \SoapClient($wsdl, $soapConfig);
        }

        $this->clientSettings = $this->setDefaultSettings([]);
    }

    /**
     * setter to set a preconfigured soapClient instance that will connect with the Progress Soap server. This method is not
     * needed if you already passed the data with the constructor
     *
     * @param \SoapClient $client
     * @return $this
     */
    public function setSoapClient(\SoapClient $client)
    {
        $this->soapClient = $client;
        return $this;
    }


    /**
     * retrieve product stock updates for the specified environment
     *
     * @param string $environment   Specifies which environment on the Progress server needs to be checked for stock updates
     * @return Response\StockMutation[]
     * 
     * @uses \Edg\ErpService\Sync\Pull\StockMutations::execute
     */
    public function pullStockUpdates($environment)
    {
        $pullStockCommand = new Sync\Pull\StockMutations($this->soapClient, $environment, $this->clientSettings);
        return $pullStockCommand->execute();
    }

    /**
     * retrieve all product data for the products that are specified with the skus parameter
     *
     * @param array $skus   list of skus that need to be synced with data from Progress
     * @return Response\ArticleInfo[]
     * 
     * @uses \Edg\ErpService\Sync\Pull\ArticleInfo::execute
     */
    public function pullArticleInfo($skus)
    {
        $pullArticleInfoCommand = new Sync\Pull\ArticleInfo($this->soapClient, $skus, $this->clientSettings);
        return $pullArticleInfoCommand->execute();
    }

    /**
     * Sends a new order to Progress
     * 
     * @param DataModel\Order $order
     * @param array $args
     * @return Response\UploadOrders[]
     * 
     * @uses \Edg\ErpService\Sync\Push\OrderExport::execute
     */
    public function pushNewOrder(DataModel\Order $order, $args = [])
    {
        $pushOrdersCommand = new Sync\Push\OrderExport($this->soapClient, $order, $args, $this->clientSettings);
        return $pushOrdersCommand->execute();
    }

    /**
     * fetch order status updates
     *
     * @uses \Edg\ErpService\Sync\Pull\OrderImport::execute
     *
     * @param $environment
     * @return Response\OrderStatus[]
     */
    public function pullOrderUpdates($environment)
    {
        $pullOrderUpdatesCommand = new Sync\Pull\OrderImport($this->soapClient, $environment, $this->clientSettings);
        return $pullOrderUpdatesCommand->execute();
    }

    /**
     * Set the Logger object for use with this library
     *
     * 
     * 
     * @param \Psr\Log\LoggerInterface $logger a preconfigured logger that can log data and follows the PSR-3 standard
     * @param bool $debug flag to specify whether or not only exceptions should be logged
     * @return $this
     * @throws \Exception
     */
    public function setLogger(LoggerInterface $logger, $debug = false)
    {
        if(! $logger instanceof \Psr\Log\LoggerInterface){
            throw new \Exception(__METHOD__ . ' ERROR - expected $logger to be an instance of PSR Logger interface');
        }
        $this->clientSettings['logging']['logger'] = $logger;
        $this->clientSettings['logging']['enable'] = true;
        $this->clientSettings['logging']['exception_only'] = !$debug;

        return $this;
    }

    /**
     * setting to write log messages to STDOUT. Usefull for debugging tests
     *
     * @return void 
     */
    public function enableEchoLogOutput()
    {
        $this->clientSettings['logging']['echo_output'] = true;
    }

    /**
     * appends the default data to the user specified settings.
     *
     * @param $settings
     * @return array
     */
    private function setDefaultSettings($settings)
    {
        return array_replace_recursive([
            'logging' => [
                'enable' => false,
                'exception_only' => false,
                'echo_output'   => false
            ]
        ], $settings);
    }
}