<?php

include __DIR__."/main.php";

use \MoneyCollect\Classes\MoneyCollectIntegration;

$moneycollect = new MoneyCollectIntegration($private_key);
$moneycollect->startLogger();

$customer_id = $_COOKIE['mc_customerId'];
/**
 * 创建客户
 */

if( !$customer_id  ){
    $customers_result = $moneycollect->request('customers_create',['body' => [
        'address' => [
            "city" => "Asheville",
            "country" => "US",
            "line1" => "3968 Fidler Drive",
            "line2" => "",
            "postalCode" => "28806",
            "state" => "North Carolina"
        ],
        'description' => 'test customer',
        'email' => '123451234@email.com',
        'firstName' => 'firstName',
        'lastName' => 'lastName',
        'phone' => '13800138000',
        'shipping' => [
            'address' => [
                "city" => "Asheville",
                "country" => "US",
                "line1" => "3968 Fidler Drive",
                "line2" => "",
                "postalCode" => "28806",
                "state" => "North Carolina"
            ],
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'phone' => '13800138000'
        ]
    ]]);

    if( $customers_result['code'] == 'success' ){
        setcookie('mc_customerId',$customers_result['data']['customerId'],time()+1000);
    }
}


/**
 * 查询客户列表
 */
$customers_page = $moneycollect->request('customers',['query' => [
    'limit' => '10', //返回对象的数量限制。Limit的范围在1到100之间，默认值是10。
]]);

/**
 * 查询单个客户
 */
if( $customer_id ){
    $customer_info = $moneycollect->request('customers_retrieve',['path' => [
        'id' => $customer_id
    ]]);
}

/**
 * 删除客户
 */

if($customer_id ){
    $customer_delete = $moneycollect->request('customers_delete',['path' => [
        'id' => $customer_id
    ]]);

    if( $customer_delete['code'] == 'success' ){
        setcookie('ab_customerId',null,-1);
    }
}