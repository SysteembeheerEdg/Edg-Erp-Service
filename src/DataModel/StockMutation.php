<?php
/**
 * StockMutation
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Edg\ErpService\DataModel;


class StockMutation
{
    protected $simpleXML;
    
    public function __construct(\SimpleXMLElement $element)
    {
        $this->simpleXML = $element;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->simpleXML->sku->__toString();
    }

    /**
     * get new stock qty
     * 
     * @return mixed
     */
    public function getStock()
    {
        return $this->simpleXML->stock->__toString();
    }

    /**
     * get raw data
     * 
     * @return \SimpleXMLElement
     */
    public function getAllAsSimpleXml()
    {
        return $this->simpleXML;
    }
}