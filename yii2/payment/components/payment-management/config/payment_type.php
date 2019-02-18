<?php
/**
 * Created by PhpStorm.
 * User: Liujianlin
 * Date: 2017/12/5
 * Time: 17:37
 */

// 支付方式
$paymentTypeArr = [
    "credit_card" => [
        "name" => '信用卡',
        "cdp_payment" => [
            'ADN_BEBC' => 'ADN_BEBC',
            'ADN_CC' => 'ADN_CC',
            'ebanxinstalment' => 'ebanxinstalment',
            'EBX_MXCC' => 'EBX_MXCC',
            'PayU_TRCC' => 'PayU_TRCC',
            'worldpay' => 'worldpay',
            'checkout_credit' => 'checkout_credit',
            'GC' => 'GC',
        ],
        "payment" => [
            "worldpay",
            "webcollect",
            'checkoutcredit',
            'adn_cc',
            'oceanpayment',
            "instalments",
            "ebx_mxcc",
            "payu_trcc",
        ]
    ],
    "local_online" => [
        "name" => "本地线上支付",
        "cdp_payment" => [
            'ADN_EPS' => 'ADN_EPS',
            'ADN_IDACS' => 'ADN_IDACS',
            'ADN_IDATM' => 'ADN_IDATM',
            'ADN_MYOB' => 'ADN_MYOB',
            'ADN_RUCT' => 'ADN_RUCT',
            'ADN_THOB' => 'ADN_THOB',
            'ADN_TRSP' => 'ADN_TRSP',
            // 'ALIPAY' => '支付宝',
            'BOLETO' => 'BOLETO',
            'BrainTree' => 'BrainTree',
            'CashU' => 'CashU',
            'EPS' => 'EPS',
            'Giropay' => 'Giropay',
            'ideal' => 'ideal',
            'LipaPay' => 'LipaPay',
            'P_oneer' => 'P_oneer',
            'PagoEfectivo' => 'Pago Efectivo',
            'PAYPAL' => 'PAYPAL',
            'PayU_BKM' => 'PayU_BKM',
            'poli' => 'poli',
            'Postepay' => 'WP_PSTP',
            'PP_CC' => 'PP_CC',
            'PP_Credit' => 'PP_Credit',
            'Przelewy24' => 'Przelewy24',
            'PSE' => 'PSE',
            'qiwi' => 'qiwi',
            // 'SERVIPAG' => 'Servipag',
            'SOFORT_SSL' => 'SOFORT',
            'WALLET' => 'WALLET',
            'webmoney' => 'webmoney',
            'yandex_money' => 'yandex_money',
            'ZYPaytm' => 'ZYPaytm',
            'Zero_OrderPayment' => 'Zero_OrderPayment',
        ],
        "payment" => [
            "ideal",
            "sofort",
            "giropay",
            "eps",
            "poli",
            "przelewy24",
            "webmoney",
            "yandex",
            "qiwi",
            "pagoefectivo",
            "pse",
            "lipapay",
            "postepay",
            "adn_myob",
            "adn_thob",
            "adn_idacs",
            "adn_idatm",
            'payu_bkm',
            'p_oneer'
        ],
    ],
    "local_offline" => [
        "name" => "线下支付",
        "cdp_payment" => [
            'BANK_TRANSFER' => '银行卡转账',
            'BankTransfer' => 'GC银行转账',
            'OXXO' => 'OXXO',
            'WESTERN' => '西联',            
        ],
        "payment" => [
            "banktransfer",
            "boletobancario",
            "oxxo",
        ],
    ]
];

// 按顺序重排支付方式
foreach ($paymentTypeArr as $key => $value) {
    sort($paymentTypeArr[$key]["payment"]);
}

return $paymentTypeArr;
