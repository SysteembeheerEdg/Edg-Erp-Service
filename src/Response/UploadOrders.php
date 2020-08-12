<?php
/**
 * UploadOrders
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Bold\PIMService\Response;


class UploadOrders
{
    /**
     *
     */
    protected $result;

    /**
     *
     */
    protected $v_STATUS;

    /**
     *
     */
    private $_statuses = array(
        "OK" => true,
        "ERROR: MSGID ALREADY EXISTS" => false,
        "ERROR: INVALID XML. NO INCREMENTID FOND" => false,
        "ERROR: INVALID XML> NO TYPE FOUND" => false,
        "ERROR: MSGID MISMATCH. MSGID DIFFERS FROM XML" => false,
        "ERROR: TYPE MISMATCH. TYPE DIFFERS FROM XML" => false
    );

    public function __construct($result, $status)
    {
        $this->result = $result;
        $this->v_STATUS = $status;
    }

    /**
     *
     */
    public function isValid() {
        return key_exists($this->v_STATUS, $this->_statuses) && $this->_statuses[$this->v_STATUS];
    }

    /**
     *
     */
    public function getResult() {
        return $this->result;
    }

    /**
     *
     */
    public function getMessage() {
        $message = explode(":", $this->v_STATUS);
        return count($message) > 1 ? trim($message[1]) : $message[0];
    }

    /**
     *
     */
    public function getStatus() {
        return $this->v_STATUS;
    }
}