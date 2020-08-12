<?php
/**
 * ArticleInfo
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Bold\PIMService\Sync\Pull;


use Bold\PIMService\DataModel\ArticleInfo as ArticleInfoModel;
use Bold\PIMService\Response\ArticleInfo as ArticleInfoResponse;

class ArticleInfo extends \Bold\PIMService\Sync\Request
{
    const ARTICLE_INFO_SYNC_CHUNK_SIZE = 5;

    protected $skuList;

    /**
     * StockMutations constructor.
     * @param \SoapClient $soap
     * @param String[] $skuList
     * @param array $clientSettings
     * @throws \Exception
     */
    public function __construct(
        \SoapClient $soap,
        $skuList = [],
        $clientSettings = []
    ) {
        if (!is_array($skuList) || count($skuList) < 1) {
            throw new \Exception(__CLASS__ . ' requires an array of article SKUs');
        }
        $this->skuList = $skuList;

        parent::__construct($soap, $clientSettings);
    }

    /**
     * @return \Bold\PIMService\Response\ArticleInfo[]
     */
    public function execute()
    {
        $chunkCount = 0;
        $chunks = array_chunk($this->skuList, self::ARTICLE_INFO_SYNC_CHUNK_SIZE);
        $responses = [];

        try {
            foreach ($chunks as $chunk) {
                $chunkCount++;

                // Soap call
                $soapResult = $this->client->articleinfo(
                    array('v_nummer' => implode(',', $chunk))
                );

                // Parse response
                $response = $this->processResponse($soapResult);

                if ($response) {
                    $responses[] = $response;
                }
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
     * @return ArticleInfoResponse|bool
     */
    protected function processResponse(\stdClass $data)
    {
        $response = new ArticleInfoResponse($data->result);

        $articleXml = new \SimpleXMLElement($data->v_info);
        foreach ($articleXml->xpath('//info') as $product) {
            $response->addArticle(new ArticleInfoModel($product));
        }

        return $response;
    }
}