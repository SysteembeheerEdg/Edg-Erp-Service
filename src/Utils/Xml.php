<?php
/**
 * Xml
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Edg\ErpService\Utils;


class Xml
{
    public function arrayToXml($data, $nodeName = "order", $asobject = false)
    {
        $xml = $this->_buildXml($data, $nodeName);
        return $asobject ? $xml : $xml->saveXML();
    }

    /**
     * @param array $data
     * @return \DOMDocument
     */
    protected function _buildXml($data, $nodeName = "order") {
        $dom = new \DOMDocument("1.0", "ISO-8859-1");
        $dom->formatOutput = true;
        $orderXml = new \DOMElement($nodeName);
        $dom->appendChild($orderXml);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (substr($nodeName, -1) == "s") {
                    $nodeName = substr($nodeName, 0, strlen($nodeName) - 1);
                } else {
                    $nodeName = "item";
                }
                $subDom = $this->_buildXml($value, is_numeric($key) ? $nodeName : $key);
                if ($subDom->documentElement->hasChildNodes()) {
                    $node = $dom->importNode($subDom->documentElement, true);
                    $orderXml->appendChild($node);
                }
            } else {
                $node = $orderXml->appendChild(new \DomElement($key));
                $node->appendChild(new \DOMCdataSection((string)$value));
            }
        }
        $dom->normalize();
        return $dom;
    }
}
