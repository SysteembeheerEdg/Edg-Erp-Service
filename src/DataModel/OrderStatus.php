<?php
/**
 * OrderStatus
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Edg\ErpService\DataModel;


class OrderStatus
{
    const STATUS_NOT_SHIPPED = 'not-shipped';

    protected $simpleXML;
    protected $orderrows = [];
    protected $barcodes = [];

    public function __construct(\SimpleXMLElement $element)
    {
        $this->simpleXML = $element;
        
        if($element->orderrows){
            foreach($element->orderrows->orderrow as $row){
                $this->orderrows[] = [
                    'sku' => $row->sku->__toString(),
                    'ordered' => $row->ordered->__toString(),
                    'invoiced' => $row->invoiced->__toString(),
                    'shipped' => $row->shipped->__toString()
                ];
            }
        }
        
        if($element->barcodes){
            foreach($element->barcodes->barcode as $barcode){
                $this->barcodes[] = [
                    'code' => $barcode->code->__toString(),
                    'zipcode' => $barcode->zipcode->__toString()
                ];
            }
        }
        
    }
    
    public function getBarcodes()
    {
        return $this->barcodes;
    }

    /**
     * @return int
     */
    public function getOrderNumber()
    {
        return (int) $this->simpleXML->orderId->__toString();
    }

    public function getOrderStatus()
    {
        return $this->simpleXML->status->__toString();
    }

    /**
     * @return array
     */
    public function getOrderRows()
    {
        return $this->orderrows;
    }

    public function getAllAsSimpleXml()
    {
        return $this->simpleXML;
    }
}