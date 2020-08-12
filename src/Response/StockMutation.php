<?php
/**
 * StockMutation
 *
 * @copyright Copyright © 2017 Bold Commerce BV. All rights reserved.
 * @author    dev@boldcommerce.nl
 */

namespace Edg\ErpService\Response;


class StockMutation
{
    /**
     * @var \Edg\ErpService\DataModel\StockMutation[]
     */
    protected $mutations = [];
    protected $result;
    protected $status;
    
    public function __construct($result, $status)
    {
        $this->result = $result;
        $this->status = $status;
    }
    
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return \Edg\ErpService\DataModel\StockMutation[]
     */
    public function getMutations()
    {
        return $this->mutations;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function addMutation(\Edg\ErpService\DataModel\StockMutation $mutation)
    {
        $this->mutations[] = $mutation;
        return $this;
    }
}