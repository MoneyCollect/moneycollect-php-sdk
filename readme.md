MoneyCollect PHP SDK
===
一个集成了MoneyCollect的api接口的组件，通过传递指定请求类型和对应的参数即可完成接口请求。使用前请先阅读MoneyCollect[接口文档](https://apireference.moneycollect.com/)

PHP要求
===
* php >= 7.0
* [curl](https://www.php.net/manual/en/book.curl.php)
* [json](https://www.php.net/manual/en/book.json.php)
* [openssl](https://www.php.net/manual/en/book.openssl.php)


使用PHP SDK
===
1、加载MoneyCollectIntegration.php文件
```php 
include_once "classes/MoneyCollectIntegration.php"; 
```
2、初始化对象
```php
use \MoneyCollect\Classes\MoneyCollectIntegration;
$moneycollect = new MoneyCollectIntegration($private_key);
```
3、开启日志，可以通过参数设置目录，如果不开启则跳过这一步
```php 
$moneycollect->startLogger($bool，$dir);
```
4、发起api请求
```php 
$moneycollect->request($type,$data);
```


参数说明
===
>type：请求类型：自定义字符串<br/>
>data：请求参数：数组参数，包含path，body，query三个部分


示例代码
===
创建交易：
---

```php
$result = $moneycollect->request('checkout_create',['body'=>[
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
        "cancelUrl" => $http_type.'://'.$_SERVER['HTTP_HOST']."/mc_php_sdk/demo/cancel.php",
        "clientReferenceId" => "OC10000001", //引用Checkout会话的唯一字符串
        "currency" => "USD",
        "customer" => "",// MoneyCollect创建的客户id，非网站用户id
        "lineItems" => [[
            "amount" => 80000,
            "currency" => "USD",
            "description" => " ",
            "images" => ["{$http_type}://{$_SERVER['HTTP_HOST']}/mc_php_sdk/2022-6-7.jpg"],
            "name" => "iPhone13",
            "quantity" => 2
        ]],
        "notifyUrl" => $http_type.'://'.$_SERVER['HTTP_HOST']."/mc_php_sdk/demo/notify.php",
        "orderNo" => 'order-id-20220710102933',
        "paymentMethodTypes" => ["card"],//其它支付方式请参考文档说明
        "preAuth" => "n",
        "returnUrl" => $http_type.'://'.$_SERVER['HTTP_HOST']."/mc_php_sdk/demo/return.php",
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
        "submitType" => "Pay", //例如:pay,book or donate
        "website" => $http_type.'://'.$_SERVER['HTTP_HOST']
]]);

if($result['code'] == 'success'){
    /* Your business code */
}
```

查询交易信息：
---
```php
$moneycollect->request('transactions_retrieve',['path' => [
    'id' => $pt_id
]]);
```

创建客户：
---
```php
$moneycollect->request('customers_create',['body' => [
    'address' => [
        'city' => 'Asheville',
        'country' => 'US',//必填
        'line1' => '3968 Fidler Drive',//必填
        'line2' => '',
        'postalCode' => '18806',
        'state' => 'North Carolina',
],
    'description' => 'test customer',
    'email' => '123451234@email.com',
    'firstName' => 'firstName',
    'lastName' => 'lastName',
    'phone' => '13800138000',
]]);
```

删除客户：
---
```php
$moneycollect->request('customers_delete',['path' => [
    'id' => $customer_id
]]);
```

签名校验
---
```php
$moneycollect->verification();
```

获取webhook数据
---
```php
$moneycollect->getWebhookData();
```


payment类型
===

| 类型                     | 说明                  | 接口                                   |
|-------------------------|----------------------|----------------------------------------|
| checkoutSession         | 列出所有支付会话       | /checkout/session                       |
| checkout_create         | 创建会话              | /checkout/session/create                |
| checkout_retrieve       | 检索会话              | /checkout/session/{id}                  |
| paymentMethods          | 创建支付方式           | /payment_methods/create                 |
| paymentMethods_list     | 根据客户获取所有支付方式 | /payment_methods/list/{customerId}      |
| paymentMethods_retrieve | 检索付款方法           | /payment_methods/{id}                   |
| paymentMethods_attach   | 客户附加支付方式        | /payment_methods/{id}/attach            |
| paymentMethods_detach   | 解绑支付方式           | /payment_methods/{id}/detach            |
| paymentMethods_update   | 更新paymentMethod信息 | /payment_methods/{id}/update            |
| payment                 | 列出所有支付会话       | /payment                                |
| payment_create          | 创建付款pt_id         | /payment/create                         |
| payment_retrieve        | 检索付款信息           | /payment/{id}                           |
| payment_cancel          | 取消付款              | /payment//{id}/cancel                   |
| payment_capture         | 捕获付款              | /payment/{id}/capture                   |
| confirmPayment          | 确认扣款              | /payment/{id}/confirm                   |
| payment_update          | 更新付款信息           | /payment/{id}/update                    |


customers类型
===

| 类型                | 说明        | 接口                   |
|--------------------|------------|------------------------|
| customers          | 列出所有客户 | /customers             |
| customers_create   | 创建客户    | /customers/create       |
| customers_retrieve | 查询客户    | /customers/{id}         |
| customers_delete   | 删除客户    | /customers/{id}         |
| customers_update   | 更新客户    | /customers/{id}/update  |


dispute类型
===

| 类型                    | 说明          | 接口                                  |
|------------------------|--------------|---------------------------------------|
| dispute                | 列出所有争议   | /disputes                             |
| dispute_retrieve       | 检索争议      | /dispute/{id}                         |
| dispute_accept         | 接受争议      | /dispute/{id}/accepted                 |
| accept_pre_arbitration | 接受之前的仲裁 | /dispute/{id}/accepted_pre_arbitration |


refund类型
===

| 类型                    | 说明          | 接口                 |
|------------------------|--------------|----------------------|
| refunds                | 列出所有退款   | /refunds             |
| refund_create          | 创建退款      | /refunds/create      |
| refund_retrieve        | 检索退款      | /refunds/{id}        |
| refund_update          | 更新退款      | /refunds/{id}/update |


openApi类型
===

| 类型                    | 说明            | 接口                   |
|------------------------|-----------------|----------------------|
| balance                | 查看余额         | /balance             |
| device                 | 装置            | /0                   |
| payout                 | 列出所有付款      | /payouts             |
| payout_retrieve        | 检索付款信息      | /payouts/{id}        |
| transactions           | 查看所有交易记录   | /transactions        |
| transactions_retrieve  | 检索交易         | /transactions/{id}   |


添加自定义类型
===
* $request_type 请求类型，自定义字符，与request第一个参数一致，已经存在的类型不能添加
* $request_path 接口路径，参考MoneyCollect接口文档
```php
$moneycollect->addRequest($request_type,$request_path)->request($request_type,$data);
$moneycollect->addRequest($request_type,$request_path)->request($request_type,$data);
```
