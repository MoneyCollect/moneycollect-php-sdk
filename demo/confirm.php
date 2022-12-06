<?php

include __DIR__."/main.php";

use \MoneyCollect\Classes\MoneyCollectIntegration;

$moneycollect = new MoneyCollectIntegration($private_key);
$moneycollect->startLogger();

$customersId = '';

/**
 *  先存后付 先保存paymentMethodsId然后进行扣款
 *  1、有PCI认证，商户可以收集用户卡信息，从服务器端调用paymentMethods获取生产paymentMethodsId
 *  2、无PCI认证，商户通过API key调用jsSDK获取paymentMethodsId
 */

if( $customersId ){
    /**
     * 查询客户的customerPaymentMethodId
     */
    $paymentMethods_list = $moneycollect->request('paymentMethods_list',['path' => [
        'customerId' => $customersId
    ]]);
}


$pci = true;
$customerPaymentMethodId = null;

if( $pci ){
    /**
     *
     * 创建支付方法拿到pm_id
     * 然后创建付款 拿到
     * 然后确认付款
     */
    $paymentMethods_result = $moneycollect->request('paymentMethods',[
        'body'=>[
            "billingDetails" => [
                "address" => [
                    "city" => "Chengdu",
                    "country" => "US",
                    "line1" => "395 yignxiong rod",
                    "line2" => "",
                    "postalCode" => "455550",
                    "state" => "North Carolina"
                ],
                "email" => "phpsdkYanFa@mc.com",
                "firstName" => "Niko",
                "lastName" => "Ni",
                "phone" => "18537651592"
            ],
            "card" => [
                "cardNo" => "4242424242424242",
                "expMonth" => "03",
                "expYear" => "2025",
                "securityCode" => "123",
            ],
            "type" => "card"
    ]]);

    //var_dump($paymentMethods_result);

    if( $paymentMethods_result['code'] == 'success' ){
        $customerPaymentMethodId = $paymentMethods_result['data']['id'];
    }else{
        echo $paymentMethods_result['code']." : ".$paymentMethods_result['msg'];
    }
}else{
    /**
     * 网站无PCI认证
     * 获取sessiontoken，用于前端调用jsSDK
     * 通过前端jsSDK获取customerPaymentMethodId
     */

     /* js sdk */
    $customerPaymentMethodId = null ;

}

if($customerPaymentMethodId){
    //创建付款
    $payment_result = $moneycollect->request('payment_create',[
        'body' => [
            "amount" => 1000000,
            //"automaticPaymentMethods" => ["enabled"=>false],
            "confirm" => true,
            "confirmationMethod" => "manual",
            "currency" => "USD",
            "customerId" => "",
            "description" => "This charge for gifs",
            "fromChannel" => "WEB",
            "ip" => $_SERVER['SERVER_ADDR'],
            "lineItems" => [
                [
                    "amount" => 10000,
                    "currency" => "USD",
                    "description" => "Glasses",
                    "images" => ["{$http_type}://{$_SERVER['HTTP_HOST']}/moneycollect_php_sdk/demo/2022-6-7.jpg"],
                    "name" => "Glasses",
                    "quantity" => 1
                ],
                [
                    "amount" => 990000,
                    "currency" => "USD",
                    "description" => "Diamond",
                    "images" => ["{$http_type}://{$_SERVER['HTTP_HOST']}/moneycollect_php_sdk/demo/2022-6-7.jpg"],
                    "name" => "Diamond",
                    "quantity" => 1
                ]
            ],
            "notifyUrl" => $http_type.'://'.$_SERVER['HTTP_HOST']."/moneycollect_php_sdk/demo/notify.php",
            "orderNo" => getOrderNo(),
            "paymentMethod" => $customerPaymentMethodId,
            "paymentMethodTypes" => ["card"],
            "preAuth" => "n",
            "receiptEmail" => "phpsdkYanFa@mc.com",
            "recurring" => [
                "initial" => false,
                "relationPaymentId" => "",
            ],
            "returnUrl" => $http_type.'://'.$_SERVER['HTTP_HOST']."/moneycollect_php_sdk/demo/return.php",
            "setupFutureUsage" => "on",
            "shipping" => [
                "address" => [
                    "city" => "Chengdu",
                    "country" => "US",
                    "line1" => "395 yignxiong rod",
                    "line2" => "",
                    "postalCode" => "455550",
                    "state" => "North Carolina"
                    ],
                "firstName" => "Niko",
                "lastName" => "Ni",
                "phone" => "18537651592"
            ],
            "statementDescriptor" => "MYSHOP",
            "statementDescriptorSuffix" => "MC",
            "userAgent" => "",
            "website" => $http_type.'://'.$_SERVER['HTTP_HOST'],
        ]
    ]);

    var_dump($payment_result);

    if( $payment_result['code'] == 'success' ){
        /* Your business code */
        echo "success";
    }
}


