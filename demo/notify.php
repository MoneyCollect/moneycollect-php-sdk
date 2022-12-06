<?php

include __DIR__."/main.php";

use \MoneyCollect\Classes\MoneyCollectIntegration;

$moneycollect = new MoneyCollectIntegration($private_key);
$moneycollect->startLogger();


if( $moneycollect->verification($webhook_token) ){
    $data = $moneycollect->getWebhookData();
    /* Your business code */
    echo 'success';
}
echo 'success';
die();