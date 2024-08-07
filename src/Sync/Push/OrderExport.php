<?php
/**
 * OrderExport
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Edg\ErpService\Sync\Push;


use Edg\ErpService\Response\UploadOrders;

class OrderExport extends \Edg\ErpService\Sync\Request
{
    /**
     * Amount of maximum order items in a single soap call
     */
    const MAX_ORDER_ITEM_COUNT_PER_PART = 20;

    /**
     *
     */
    const ORDER_TYPE_MULTIPART = 'multipart';

    /**
     *
     */
    const ORDER_TYPE_SINGLEPART = 'singlepart';

    /**
     *
     */
    const REQUEST_TYPE_UNEXPORTED = 'not yet exported';

    /**
     *
     */
    const REQUEST_TYPE_EXPORT_SUCCESS = 'export succeeded';

    /**
     *
     */
    const REQUEST_TYPE_EXPORT_FAILED = 'export failed';

    /**
     * @var \Edg\ErpService\DataModel\Order
     */
    protected $order;
    protected $args;
    /**
     * @var \Edg\ErpService\Utils\Xml
     */
    protected $xmlUtils;

    /**
     * OrderExport constructor.
     * @param \SoapClient $client
     * @param $order
     * @param array $args
     * @throws \Exception
     */
    public function __construct(
        \SoapClient $client,
        $order,
        $args = [],
        $settings = []
    ) {
        if (!$order instanceof \Edg\ErpService\DataModel\Order) {
            throw new \Exception(__CLASS__ . ' requires an order of type \\Edg\\ErpService\\DataModel\\Order');
        }


        if (!isset($args['export_type'])) {
            throw new \Exception(__CLASS__ . ' requires an export_type in the args array');
        }

        if (!isset($args['environment'])) {
            throw new \Exception(__CLASS__ . ' requires an environment tag in the args array');
        }

        if (!isset($args['id_prefix'])) {
            $args['id_prefix'] = '';
        }

        $this->args = $args;
        $this->order = $order;

        $this->xmlUtils = new \Edg\ErpService\Utils\Xml();

        parent::__construct($client, $settings);
    }

    /**
     * @return \Edg\ErpService\Response\UploadOrders[]
     */
    public function execute()
    {
        $responses = [];
        try {
            $order = $this->order;
            $requests = $this->getRequestData($order->getData());

            foreach ($requests as $request) {

                $xmlString = $this->xmlUtils->arrayToXml($request['order_array'], 'order', false);

                $soapResult = $this->client->uploadOrders(
                    [
                        'v_XML' => mb_convert_encoding($xmlString, 'UTF-8', 'ISO-8859-1'),
                        'v_TYPE' => $this->args['export_type'],
                        'v_MSGID' => $this->appendMsgIdPrefix($order->getOrderNumber()),
                        'v_environment' => $this->args['environment']
                    ]
                );

                $response = $this->processResponse($soapResult);
                $responses[] = $response;
                if ($response->isValid()) {
                    $this->log(__METHOD__ . sprintf(
                        ' Succesfully exported order #%s of type %s (part %d of %d) with message "%s", status "%s"',
                        $order->getOrderNumber(), $request['order_array']['export_type'], $request['part'],
                        $request['parts'],
                        $response->getMessage(), $response->getStatus()
                    ));
                    $this->log($xmlString);
                } else {
                    $this->log(__METHOD__ . sprintf(
                        ' Error exporting order #%s of type %s (part %d of %d) with message "%s", status "%s"',
                        $order->getOrderNumber(), $request['order_array']['export_type'], $request['part'],
                        $request['parts'],
                        $response->getMessage(), $response->getStatus()
                    ), true);
                    $this->log($xmlString, true);
                    break;
                }
                $response = null;
            }

        } catch (\Exception $e) {
            $this->log(__METHOD__ . ': WARNING - Errors during call: ' . $e->getMessage(), true);
            $this->log(__METHOD__ . ': SOAP request:', true);
            $this->log($this->client->__getLastRequest(), true);
            if (isset($response)) {
                $this->log(__METHOD__ . ': ' . var_export($response, true), true);
            }else{
                $responses[] = new UploadOrders(false, 'php error');
            }
        }

        return $responses;
    }

    /**
     * @param array $orderData
     * @return array
     */
    private function getRequestData($orderData)
    {
        $requestData = [];
        $itemChunks = array_chunk($orderData['items'], self::MAX_ORDER_ITEM_COUNT_PER_PART);

        $partCount = count($itemChunks);
        $orderData['items'] = null;

        foreach ($itemChunks as $partCurrent => $part) {
            $orderData['export_type'] = ($partCount > 1) ? self::ORDER_TYPE_MULTIPART : self::ORDER_TYPE_SINGLEPART;
            $orderData['export_parts_count'] = $partCount;
            $orderData['export_parts_current'] = $partCurrent + 1;
            $orderData['items'] = $part;

            // Append request to stack
            $requestData[] = [
                'status' => self::REQUEST_TYPE_UNEXPORTED,
                'order_array' => $orderData,
                'part' => $partCurrent + 1,
                'parts' => $partCount,
            ];
        }

        return $requestData;
    }

    /**
     * @param $ordernumber
     * @return string
     */
    protected function appendMsgIdPrefix($ordernumber)
    {
        return $this->args['id_prefix'] . $ordernumber;
    }

    /**
     * @param \stdClass $data
     * @return UploadOrders
     */
    protected function processResponse(\stdClass $data)
    {
        $response = new UploadOrders($data->result, $data->v_STATUS);

        return $response;
    }
}