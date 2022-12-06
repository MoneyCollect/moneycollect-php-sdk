<?php
include __DIR__."/main.php";

use \MoneyCollect\Classes\MoneyCollectIntegration;

$moneycollect = new MoneyCollectIntegration($private_key);
$moneycollect->startLogger();

//查询余额
$rows = $moneycollect->request('balance');
echo "<pre>";
var_dump($rows);
if($rows['code'] == 'success'){
    /* Your business code */
}
