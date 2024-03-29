<?php
/**
 * OrderImport
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Edg\ErpService\Sync\Pull;

use Edg\ErpService\DataModel\OrderStatus;
use Edg\ErpService\Response\OrderStatus as OrderStatusResponse;

class OrderImport extends \Edg\ErpService\Sync\Request
{
    protected $environment;

    /**
     * OrderImport constructor.
     *
     * @param \SoapClient $client
     * @param string $environment mandatory parameter, used to specify which environment on Progress needs to be checked for updates
     * @param array $clientSettings
     * @throws \Exception   throws exception if environment parameter is missing
     */
    public function __construct(
        \SoapClient $client,
                    $environment,
                    $clientSettings = []
    ) {
        if (!$environment) {
            throw new \Exception(__CLASS__ . ' requires an environment tag');
        }

        $this->environment = $environment;
        parent::__construct($client, $clientSettings);
    }


    /**
     * @return OrderStatusResponse[]
     */
    public function execute()
    {
        $responses = [];
        try {
            $soapResult = $this->client->orderstatus2(['v_environment' => $this->environment]);
            $response = $this->processResponse($soapResult);
            $responses[] = $response;
            $response = null;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . ': WARNING - Errors during call: ' . $e->getMessage(), true);
            $this->log(__METHOD__ . ': SOAP request:', true);
            $this->log($this->client->__getLastRequest(), true);
            if (isset($response)) {
                $this->log(__METHOD__ . ': ' . var_export($response, true), true);
            }
        }

        return $responses;
    }

    /**
     * @param \stdClass $data
     * @return OrderStatusResponse|bool
     */
    protected function processResponse(\stdClass $data)
    {
        $response = new OrderStatusResponse($data->result, $data->v_status);

        try {
            $orderXml = new \SimpleXMLElement('<' . '?xml version="1.0"?' . '>' . "\r\n" . $data->v_orders);
        } catch (\Exception $exception) {
            $this->log(__METHOD__ . ': ' . $exception->getMessage(), true);
            $this->log(__METHOD__ . 'processing orders from response failed; got invalid response from server:', true);
            $this->log(print_r($data, true), true);
            return false;
        }

        foreach ($orderXml->order as $order) {
            $orderstatus = new OrderStatus($order);
            $response->addOrder($orderstatus);
        }

        return $response;
    }
}
