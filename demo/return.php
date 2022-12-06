<?php

include __DIR__."/main.php";

use \MoneyCollect\Classes\MoneyCollectIntegration;

$moneycollect = new MoneyCollectIntegration($private_key);
$moneycollect->startLogger();

if( $_GET ){
    echo "GET:";
    var_dump($_GET);
}
echo 'success';
exit();



