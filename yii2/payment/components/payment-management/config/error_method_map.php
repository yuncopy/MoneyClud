<?php
/**
 * Created by PhpStorm.
 * User: liujianlin
 * Date: 2018/10/19
 * Time: 10:13
 */

$gatewayMap = include(__DIR__ . DIRECTORY_SEPARATOR . "gateway_map.php");
$errorMethodMap = [
    'cybersource' => [
        'ADN_CC',
        'worldpay',
        'checkout_credit',
        'GC',
        'ebanxinstalment',
        'oceanpayment',
    ]
];

$errorMethodMap = array_merge($errorMethodMap, $gatewayMap);
uksort($errorMethodMap, function($left, $right) {
    $left = strtolower($left);
    $right = strtolower($right);
    if ($left === $right) {
        return 0;
    } elseif ($left > $right) {
        return 1;
    } else {
        return -1;
    }
});
return $errorMethodMap;
