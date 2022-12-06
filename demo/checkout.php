<?php
include __DIR__."/main.php";

use \MoneyCollect\Classes\MoneyCollectIntegration;

$moneycollect = new MoneyCollectIntegration($private_key);
$moneycollect->startLogger();
//创建会话支付方法
$data = array(
        "amountTotal" => 990,
        "billingDetails" => [
            "address" => [
                "city" => "Asheville",
                "country" => "US",
                "line1" => "3968 Fidler Drive",
                "line2" => "",
                "postalCode" => "28806",
                "state" => "North Carolina"
            ],
            "email" => "funny@moneycollect.com",
            "firstName" => "Mark",
            "lastName" => "Merrill",
            "phone" => "18537651592"
        ],
        "cancelUrl" => $http_type.'://'.$_SERVER['HTTP_HOST']."/moneycollect_php_sdk/demo/cancel.php",
        "clientReferenceId" => "OC10000001",
        "currency" => "USD",
        "customer" => "",
        "lineItems" => [[
            "amount" => 80000,
            "currency" => "USD",
            "description" => " ",
            "images" => ["{$http_type}://{$_SERVER['HTTP_HOST']}/moneycollect_php_sdk/demo/2022-6-7.jpg"],
            "name" => "iPhone13",
            "quantity" => 2
        ]],
        "notifyUrl" => $http_type.'://'.$_SERVER['HTTP_HOST']."/moneycollect_php_sdk/demo/notify.php",
        "orderNo" => getOrderNo(),
        "paymentMethodTypes" => ["card"],
        "preAuth" => "n",
        "returnUrl" => $http_type.'://'.$_SERVER['HTTP_HOST']."/moneycollect_php_sdk/demo/return.php",
        "shipping" => [
            "address" => [
                "city" => "Asheville",
                "country" => "US",
                "line1" => "3968 Fidler Drive",
                "line2" => "",
                "postalCode" => "28806",
                "state" => "North Carolina"
            ],
            "firstName" => "Mark",
            "lastName" => "Merrill",
            "phone" => "210-627-6464"
        ],
        "statementDescriptor" => "",
        "statementDescriptorSuffix" => "",
        "submitType" => "Pay",
        "website" => $http_type.'://'.$_SERVER['HTTP_HOST']
);


$response = $moneycollect->request('checkout_create',['body'=>$data]);

echo "<pre>";
var_dump($response);

if($response['code'] == 'success' ){
    /* Your business code */
    //header("location:".$response['data']['url']);
}
