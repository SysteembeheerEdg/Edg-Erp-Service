<?php
/**
 * Request
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Edg\ErpService\Sync;

/**
 * Class Request
 *
 * abstract class for all classes that implement a connection with the EDG Progress server
 *
 * @package Edg\ErpService\Sync
 */
abstract class Request implements RequestInterface
{
    /**
     * List of settings for this webservice api. Currently only used for logging settings
     *
     * @var array
     */
    protected $settings;

    /**
     * soap client instance for communication with the soap server
     *
     * @var \SoapClient
     */
    protected $client;

    /**
     * Request constructor.
     * 
     * @param \SoapClient $soap fully configured soap client object
     * @param array $settings   additional settings for the api
     *
     * @see \Edg\ErpService\Sync\Request::$settings
     * @see \Edg\ErpService\Sync\Request::$client
     */
    public function __construct(
        \SoapClient $soap,
        $settings = []
    ) {
        $this->settings = $settings;
        $this->client = $soap;
    }

    /**
     * logs messages using a logger compatible with PSR-3
     * 
     * @param string $msg
     * @param bool $isException
     * @return bool
     */
    protected function log($msg, $isException = false)
    {
        if (!isset($this->settings['logging'])) {
            return false;
        }

        if($this->settings['logging']['echo_output']){
            echo $msg . PHP_EOL;
        }

        if ($this->settings['logging']['enable']) {
            /** @var \Psr\Log\LoggerInterface $logger */
            $logger = $this->settings['logging']['logger'];

            if (!$isException && !$this->settings['logging']['exception_only']) {
                $logger->debug($msg);
            } elseif ($isException) {
                $logger->error($msg);
            }
        }

        return true;
    }

}