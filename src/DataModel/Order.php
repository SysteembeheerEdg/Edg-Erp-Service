<?php

namespace Edg\ErpService\DataModel;


class Order
{
    private $data;
    private $fields = [
        'incrementId',
        'shipping_method',
        'shipping_amount',
        'shipping_incl_tax',
        'payment_method_title',
        'payment_method',
        'payment_transactionid',
        'total_items',
        'discount',
        'currency',
        'subtotal',
        'subtotal_incl_tax',
        'grandtotal',
        'total_paid',
        'total_refunded',
        'total_due',
        'coupon_code',
        'coupon_rule_name',
        'order_customer_group_id',
        'order_customer_group_name',
        'order_brinnummer',
        'order_school_naam',
        'order_functie_besteller',
        'order_reference',
        'order_ip',
        'additional',
        'vat_number',
        'environment_tag'
    ];

    private $compositeFields = [
        'meta' => [
            'type',
            'store',
            'datetime',
            'time_offset'
        ],
        'addresses' => [
            'city',
            'company',
            'country_id',
            'email',
            'fax',
            'telephone',
            'firstname',
            'lastname',
            'middlename',
            'postcode',
            'prefix',
            'region',
            'street',
            'vat_id'
        ],
        'items' => [
            'item_id',
            'name',
            'sku',
            'qty',
            'price',
            'price_incl_tax',
            'tax_percent',
            'discount_amount',
            'row_total',
            'original_price',
            'row_total_incl_tax',
            'product_type',

            'parent_item_id'
        ],
        'customer' => [
            'id',
            'email',
            'customer_group',
            'progress_id',
            'school_naam',
            'brinnummer',
            'functie_besteller',
            'school_bool'
        ]
    ];

    public function __construct($data = [])
    {
        $this->init($data);

        if(!$this->validate()){
            throw new \Exception(sprintf("Invalid order data found:\n%s", print_r($data, true)));
        }
    }

    public function getOrderNumber()
    {
        return $this->data['incrementId'];
    }

    public function getData()
    {
        return $this->data;
    }

    private function validate()
    {
        return true;
    }

    private function init($input)
    {
        foreach($this->fields as $key){
            $this->data[$key] = $input[$key] ?? null;
        }

        if(isset($input['addresses'])){
            foreach($input['addresses'] as $type => $values){
                foreach($this->compositeFields['addresses'] as $key){
                    $this->data['addresses'][$type][$key] = $values[$key] ?? null;
                }
            }
        }

        if(isset($input['customer'])){
            foreach($this->compositeFields['customer'] as $key){
                $this->data['customer'][$key] = $input['customer'][$key] ?? null;
            }
        }

        if(isset($input['meta'])){
            foreach($this->compositeFields['meta'] as $key){
                $this->data['meta'][$key] = $input['meta'][$key] ?? null;
            }
        }

        if(isset($input['items'])){
            foreach($input['items'] as $values){
                $temp = [];
                foreach($this->compositeFields['items'] as $key){
                    $temp[$key] = $values[$key] ?? null;
                }

                $parentId = $temp['parent_item_id'];
                unset($temp['parent_item_id']);

                if($parentId){
                    $this->data['items'][$parentId]['configurables'][] = $temp;
                }else{
                    $this->data['items'][$temp['item_id']] = $temp;
                }
            }
        }

        if(isset($input['order_remarks'])){
            if(is_array($input['order_remarks'])){
                $this->data['order_remarks'] = implode("\n\n---\n\n", $input['order_remarks']);
            }else{
                $this->data['order_remarks'] = $input['order_remarks'];
            }
        }
    }
}
