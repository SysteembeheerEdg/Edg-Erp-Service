<?php
/**
 * StockInfo
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Edg\ErpService\Sync\Pull;

use Edg\ErpService\DataModel\StockMutation;
use Edg\ErpService\Response\StockMutation as StockMutationResponse;
use Edg\ErpService\Sync\Request;

class StockMutations extends Request
{
    const NO_MUTATIONS = 'NO STOCK MUTATIONS FOUND';

    protected $environment;

    /**
     * StockMutations constructor.
     * @param \SoapClient $client
     * @param String $environment
     * @param array $settings
     * @throws \Exception
     */
    public function __construct(
        \SoapClient $client,
        $environment,
        $settings = []
    ) {
        if (!$environment) {
            throw new \Exception(__CLASS__ . ' requires an environment tag');
        }
        $this->environment = $environment;
        parent::__construct($client, $settings);
    }

    /**
     * @return StockMutationResponse[]
     */
    public function execute()
    {
        $count = 0;
        $responses = [];
        try {
            while ($count < 50) {
                $count++;
                $this->log(__METHOD__ . ': pulling stock data from environment "' . $this->environment . '". currently on loop count: ' . $count);
                $soapResult = $this->client->stockmutations2(['v_environment' => $this->environment]);
                $response = $this->processResponse($soapResult);
                if (!$response || $response->getStatus() == self::NO_MUTATIONS) {
                    $this->log(__METHOD__ . ': no more stock data found. Aborting.');
                    break;
                }

                $responses[] = $response;
                $response = null;
            }
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
     * @return StockMutationResponse|bool
     */
    protected function processResponse(\stdClass $data)
    {
        $response = new StockMutationResponse($data->result, $data->v_status);

        if (strlen($data->v_stockmutations) > 1) {
            try {
                $stockXml = new \SimpleXMLElement('<' . '?xml version="1.0"?' . '>' . "\r\n" . $data->v_stockmutations);
            } catch (\Exception $exception) {
                $this->log(__METHOD__ . ': Stockmutations failed, got invalid response from server:', true);
                $this->log(var_export($data, true), true);
                return false;
            }

            foreach ($stockXml->article as $article) {
                $mutation = new StockMutation($article);
                $response->addMutation($mutation);
            }
        }

        return $response;
    }
}