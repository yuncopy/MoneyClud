<?php

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'arrayCache' => [
            'class' => 'yii\caching\ArrayCache',
            'serializer' => false,
        ],
        'request' => [
            'class' => 'app\base\Request',
            //'enableCsrfValidation' => false,
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '9nfRpkQ9RZYk8TzAVMsVeThtLePM9HdR',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                '<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => '<module>/<controller>/<action>',
                '' => 'admin/index/index',
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'admin/site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'env' => [
            'class' => 'app\base\Env',
        ],
        'ip' => [
            'class' => 'app\base\Ip',
        ],
        'debug' => [
            'class' => 'app\base\Debug',
        ],
        'helper' => [
            'class' => 'app\helpers\Helper',
        ],
        'systime' => [
            'class' => 'app\components\Systime',
        ],
        'diff' => [
            'class' => 'app\components\Diff',
        ],
        'recordLog' => [
            'class' => 'app\components\RecordLog'
        ],
        // 同步队列
        'queue' => [
            'class' => 'app\modules\gsc\components\Queue'
        ],
		// 监控点组件
		'monitor' => [
            'class' => 'app\components\Monitor',
        ],
        //权限
        'powers'=>[
            'class' => 'app\components\Powers',
        ],
        // 站点缓存组件
        'siteCache' => [
            'class' => 'app\components\cache\Site',
        ],
        'curl' => [
            'class' => 'app\components\Curl',
        ],
        'KafkaAdapter'=>[//kafka
            'class'=>'app\components\KafkaAdapter',
        ],
        'MongoAdapter'=>[//mongo db
            'class'=>'app\components\MongoAdapter',
        ],
        'omsUtil' => [
            'class' => 'app\components\OmsUtil',
        ]
    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module'
        ],
        'api' => [
            'class' => 'app\modules\api\Module'
        ],
        'jobs' => [
            'class' => 'app\modules\jobs\Module'
        ],
//        'gii' => [
//            'class' => 'yii\gii\Module',
//            'allowedIPs' => ['192.168.2.163'] // 按需调整这里
//        ],
    ],
    'params' => [
        'now_addr'=>'',
        'webSites'=>[  //按abc排序
            'buyinggoods','boynewyork',
            'chinabrands', 'chinabrands_cn',
            'dealsmachine','digbest','dressfo','dezzal','dresslily','dizener',
            'everbuying',
            'gamiss','gearbest',
            'igogo',
            'nastydress','nextmia',
            'oksells',
            'pasymoon',
            'rosewholesale','rosegal',
            'sammydress',
            'twinkledeals','trendsgal',
            'volumebest',
            'yoshop',
            'zaful','zanstyle','zanbase'
        ],
        'webSitesCodes' => [
            'GB' => 'gearbest',
			'ZF' => 'zaful',
			 //'DL'=>'dresslily',
			'RG'=>'rosegal',
            'RW' => 'rosewholesale',
            'YS' => 'yoshop',
            'CB'=>'chinabrands',
        ],
        'allSiteCode' => [
            'ZB'=>'zanbase',
            'PM'=>'pasymoon',
            'YS'=>'yoshop',
            'BN'=>'boynewyork',
            'DN'=>'dizener',
            'GS'=>'gamiss',
            'DF'=>'dressfo',
            'NT'=>'nextmia',
            'DB'=>'digbest',
            'ZF'=>'zaful',
            'ND'=>'nastydress',
            'TG'=>'trendsgal',
            'TD'=>'twinkledeals',
            'GB'=>'gearbest',
            'CB'=>'chinabrands',
            'RG'=>'rosegal',
            'RW'=>'rosewholesale',
            'DL'=>'dresslily',
            'IC'=>'ischatzkiste',
            'SD'=>'sammydress',
        ],
        'pipeline' => [
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GB',
                'pipeline_name' => '全球站',
                'domain' => 'www.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBFR',
                'pipeline_name' => '法语站',
                'domain' => 'fr.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBES',
                'pipeline_name' => '西语站',
                'domain' => 'es.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBRU',
                'pipeline_name' => '俄语站',
                'domain' => 'ru.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBPT',
                'pipeline_name' => '葡萄牙站',
                'domain' => 'pt.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBIT',
                'pipeline_name' => '意大利站',
                'domain' => 'it.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBDE',
                'pipeline_name' => '德语站',
                'domain' => 'de.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBUK',
                'pipeline_name' => '英国站',
                'domain' => 'uk.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBUS',
                'pipeline_name' => '美国站',
                'domain' => 'us.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBBR',
                'pipeline_name' => '巴西葡萄牙站',
                'domain' => 'br.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBTR',
                'pipeline_name' => '土耳其站',
                'domain' => 'tr.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBPL',
                'pipeline_name' => '波兰站',
                'domain' => 'pl.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBNL',
                'pipeline_name' => '荷兰站',
                'domain' => 'nl.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBGR',
                'pipeline_name' => '希腊站',
                'domain' => 'gr.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBMX',
                'pipeline_name' => '墨西哥站',
                'domain' => 'mx.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBHU',
                'pipeline_name' => '匈牙利站',
                'domain' => 'hu.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBSK',
                'pipeline_name' => '斯洛伐克站',
                'domain' => 'sk.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBJP',
                'pipeline_name' => '日本站',
                'domain' => 'jp.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBRO',
                'pipeline_name' => '罗马尼亚站',
                'domain' => 'ro.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBMA',
                'pipeline_name' => '摩洛哥站',
                'domain' => 'ma.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBCZ',
                'pipeline_name' => '捷克站',
                'domain' => 'cz.gearbest.com',
            ],
            [
                'siteCode' => 'GB',
                'pipeline_code' => 'GBAU',
                'pipeline_name' => '澳大利亚站',
                'domain' => 'au.gearbest.com',
            ],
            [
                'siteCode' => 'ZF',
                'pipeline_code' => 'ZF',
                'pipeline_name' => '全球站',
                'domain' => 'www.zaful.com',
            ],
            [
                'siteCode' => 'DL',
                'pipeline_code' => 'DL',
                'pipeline_name' => '全球站',
                'domain' => 'www.dresslily.com',
            ],
            [
                'siteCode' => 'RG',
                'pipeline_code' => 'RGEN',
                'pipeline_name' => '全球站',
                'domain' => 'www.rosegal.com',
            ],
            [
                'siteCode' => 'RG',
                'pipeline_code' => 'RGAR',
                'pipeline_name' => '阿拉伯站',
                'domain' => 'ar.rosegal.com',
            ],
            [
                'siteCode' => 'RG',
                'pipeline_code' => 'RGFR',
                'pipeline_name' => '法国站',
                'domain' => 'fr.rosegal.com',
            ],
            [
                'siteCode' => 'RG',
                'pipeline_code' => 'RGRU',
                'pipeline_name' => '俄罗斯站',
                'domain' => 'ru.rosegal.com',
            ],
            [
                'siteCode' => 'RW',
                'pipeline_code' => 'RW',
                'pipeline_name' => '全球站',
                'domain' => 'www.rosewholesale.com',
            ],
            [
                'siteCode' => 'YS',
                'pipeline_code' => 'YS',
                'pipeline_name' => '全球站',
                'domain' => 'www.yoshop.com',
            ],
            [
                'siteCode' => 'ZF',
                'pipeline_code' => 'ZF',
                'pipeline_name' => '全球站',
                'domain' => 'www.zaful.com',
            ],
            [
                'siteCode' => 'ZF',
                'pipeline_code' => 'ZFIT',
                'pipeline_name' => '意大利站',
                'domain' => 'it.zaful.com',
            ],
            [
                'siteCode' => 'ZF',
                'pipeline_code' => 'ZFPT',
                'pipeline_name' => '葡萄牙站',
                'domain' => 'pt.zaful.com',
            ],
            [
                'siteCode' => 'ZF',
                'pipeline_code' => 'ZFAR',
                'pipeline_name' => '阿拉伯站',
                'domain' => 'ar.zaful.com',
            ],
            [
                'siteCode' => 'ZF',
                'pipeline_code' => 'ZFDE',
                'pipeline_name' => '德国站',
                'domain' => 'de.zaful.com',
            ],
            [
                'siteCode' => 'ZF',
                'pipeline_code' => 'ZFES',
                'pipeline_name' => '西语站',
                'domain' => 'es.zaful.com',
            ],
            [
                'siteCode' => 'ZF',
                'pipeline_code' => 'ZFFR',
                'pipeline_name' => '法国站',
                'domain' => 'fr.zaful.com',
            ],
        ],
        "gateWay" => [//网关的相关信息[queryApi：查询支付状态的API]
            "WorldPay" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=WorldPay" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=WorldPay",
                "payment_method" => ["worldpay"],
            ],
            "Checkout" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=Checkout" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=Checkout",
                "payment_method" => ["checkoutcredit"],
            ],
            "Ebanx" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=Ebanx" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=Ebanx",
                "payment_method" => ["instalments"],
            ],
            "Adyen" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=Adyen" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=Adyen",
                "payment_method" => ["adn_cc"],
            ],
            "Oceanpay" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=Oceanpay" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=Oceanpay",
                "payment_method" => ["przelewy24", "oceanpayment"],
            ],
            "Passion" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=Passion" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=Passion",
            ],
            "Lipapay" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=Lipapay" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=Lipapay",
            ],
            "PayU" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=PayU" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=PayU",
            ],
            "Webcollect" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=Webcollect" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=Webcollect",
                "payment_method" => ["webcollect"],
            ],
            "PayMaster24" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=PayMaster24" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=PayMaster24",
            ],
            "Payoneer" => [
                "queryApi" => IS_LOCAL ? "http://checkout.com.d.s1.egomsl.com/api/gatewayOrder?gateway=Payoneer" : "https://checkout.gearbest.com/api/gatewayOrder?gateway=Payoneer",
            ],
        ],
        'payMethod'=>[//支付方式
           'applepay','banktransfer','boletobancario','checkout_credit','gc'
            ,'ideal', 'instalments','oxxo','oceanpayment','payssion',
            'poli','przelewy24','worldpay','yandex_money','paypal','adn_cc',
            'adn_myob','adn_thob','adn_idacs','adn_idatm', 'ebx_mxcc', 'pagoefectivo', 'wp_qiwi'
        ],
        'paymentMethod' => [    // 新的支付方式数组，用于替代payMethod数组，备注*__field 其中*需要是OMS对应的字段            
            "payu_bkm" => [
                "img" => '/static/dist/img/payment_method/payu.png',
                "gateway" => 'PayU',//支付网关
                "gateway_field" => "transaction_id",//第三方网关查询付款单信息的字段
                'order_table' => 'payu_bkm_orders',//付款单表
                "success_status" => ['PAYMENT_AUTHORIZED', 'COMPLETE'],//支付成功状态
                //付款单字段映射
                'order_sn_field' => 'order_no',
                'transaction_id_field' => 'payment_id',
                'currency_field' => 'currency_code',
                'status_field' => 'payu_status',
                'amount_field' => 'amount',
                'usd_rate_field' => 'usd_rate',
                'customer_ip_field' => 'client_ip',
                'paid_time_field' => 'paid_time',
            ],
            "p_oneer" => [
                "img" => '/static/dist/img/payment_method/payoneer.png',
                "gateway" => 'Payoneer',//支付网关
                "gateway_field" => "transaction_id",//第三方网关查询付款单信息的字段
                'order_table' => 'p_oneer_orders',//付款单表
                "success_status" => ['Success'],//支付成功状态
                //付款单字段映射
                'order_sn_field' => 'order_no',
                'transaction_id_field' => 'audit_id',
                'currency_field' => 'currency_code',
                'status_field' => 'payoneer_status',
                'amount_field' => 'amount',
                'usd_rate_field' => 'usd_rate',
                'customer_ip_field' => 'client_ip',
                'paid_time_field' => 'paid_time',
            ],
            "payu_trcc" => [
                "img" => '/static/dist/img/payment_method/payu.png',
                "gateway" => 'PayU',//支付网关
                "gateway_field" => "transaction_id",//第三方网关查询付款单信息的字段
                'order_table' => 'payu_trcc_orders',//付款单表
                "success_status" => ['PAYMENT_AUTHORIZED', 'COMPLETE'],//支付成功状态
                //付款单字段映射
                'order_sn_field' => 'order_no',
                'transaction_id_field' => 'payment_id',
                'currency_field' => 'currency_code',
                'status_field' => 'payu_status',
                'amount_field' => 'amount',
                'usd_rate_field' => 'usd_rate',
                'customer_ip_field' => 'client_ip',
                'paid_time_field' => 'paid_time',
                //其他
                'getInstalmentsApiUrl' => 'https://secure.payu.com.tr/openpayu/v2/installment_payment.json/get_available_installments/',
                'PayU_MERCHANT' => IS_LOCAL ? 'TRTEST3D' : 'YSNGTNGC',
            ],
            "adn_cc" => [
                "img" => '/static/dist/img/payment_method/adyen_logo.jpg',
                "gateway" => 'Adyen',
                'order_table' => 'adn_cc_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'adyen_psp_reference',
            ],
            "adn_idacs" => [
                "img" => '/static/dist/img/payment_method/adyen_logo.jpg',
                "gateway" => 'Adyen',
                'order_table' => 'adn_idacs_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'adyen_psp_reference',
            ],
            "adn_idatm" => [
                "img" => '/static/dist/img/payment_method/adyen_logo.jpg',
                "gateway" => 'Adyen',
                'order_table' => 'adn_idatm_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'adyen_psp_reference',
            ],
            "adn_myob" => [
                "img" => '/static/dist/img/payment_method/adyen_logo.jpg',
                "gateway" => 'Adyen',
                'order_table' => 'adn_myob_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'adyen_psp_reference',
            ],
            "adn_thob" => [
                "img" => '/static/dist/img/payment_method/adyen_logo.jpg',
                "gateway" => 'Adyen',
                'order_table' => 'adn_thob_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'adyen_psp_reference',
            ],
            "adn_trsp" => [
                "img" => '/static/dist/img/payment_method/adyen_logo.jpg',
                "gateway" => 'Adyen',
                'order_table' => 'adn_trsp_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'adyen_psp_reference',
            ],
            "adn_ruct" => [
                "img" => '/static/dist/img/payment_method/adyen_logo.jpg',
                "gateway" => 'Adyen',
                "gateway_field" => "transaction_id",//第三方网关查询付款单信息的字段
                'order_table' => 'adn_ruct_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'adyen_psp_reference',
                "success_status" => ['SentForSettle', 'Authorised', 'Settled', 'SettledBulk'],//支付成功状态
                //付款单字段映射
                'order_sn_field' => 'order_no',
                'currency_field' => 'currency_code',
                'status_field' => 'adyen_status',
                'amount_field' => 'amount',
                'usd_rate_field' => 'usd_rate',
                'customer_ip_field' => 'client_ip',
                'paid_time_field' => 'paid_time',
            ],
            "adn_bebc" => [
                "img" => '/static/dist/img/payment_method/adyen_logo.jpg',
                "gateway" => 'Adyen',
                'order_table' => 'adn_bebc_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'adyen_psp_reference',
            ],
            "banktransfer" => [
                "img" => '/static/dist/img/payment_method/webcollect_logo.png',
                "gateway" => 'GC',
                'order_table' => 'bank_pay_info',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',
            ],
            'webcollect' => [
                "img" => '/static/dist/img/payment_method/webcollect_logo.png',
                "gateway" => 'GC',
                'order_table' => 'adn_bebc_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',
            ],
            "boletobancario" => [
                "img" => '/static/dist/img/payment_method/ebanx_logo.png',
                "gateway" => 'Ebanx',
                'order_table' => 'boleto_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'ebanx_hash',
            ],
            "ebx_mxcc" => [
                "img" => '/static/dist/img/payment_method/ebanx_logo.png',
                "gateway" => 'Ebanx',
                'order_table' => 'ebx_mxcc_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'ebanx_hash',
            ],
            'instalments' => [
                "img" => '/static/dist/img/payment_method/ebanx_logo.png',
                "gateway" => 'Ebanx',//支付网关
                "gateway_field" => "transaction_id",//第三方网关查询付款单信息的字段
                'order_table' => 'instalments_orders',//付款单表
                "success_status" => ['CO'],//支付成功状态
                //付款单字段映射
                'order_sn_field' => 'order_no',
                'transaction_id_field' => 'ebanx_hash',
                'currency_field' => 'currency_code',
                'status_field' => 'ebanx_status',
                'amount_field' => 'order_amount',
                'usd_rate_field' => 'usd_rate',
                'card_type_field' => 'payment_type_code',
                'fraud_result_field' => 'cyber_afs_result',
                'cybs_DM_rules_field' => 'cybs_dm_rules',
                'customer_ip_field' => 'client_ip',
                'paid_time_field' => 'paid_time',
                'real_pay_amount_field' => 'amount',
                'instalment_field' => 'instalments',
                'instalments_rate_field' => 'instalments_rate',
                'instalments_rate_amount_field' => 'instalment_rate_amount',
            ],
            "oxxo" => [
                "img" => '/static/dist/img/payment_method/ebanx_logo.png',
                "gateway" => 'Ebanx',
                'order_table' => 'oxxo_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'ebanx_hash',
            ],
            "pagoefectivo" => [
                "img" => '/static/dist/img/payment_method/ebanx_logo.png',
                "gateway" => 'Ebanx',
                'order_table' => 'pagoefectivo_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'ebanx_hash',
            ],
            "pse" => [
                "img" => '/static/dist/img/payment_method/ebanx_logo.png',
                "gateway" => 'Ebanx',  
                'order_table' => 'pse_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'ebanx_hash',                
            ],
            'checkoutcredit' => [
                "img" => '/static/dist/img/payment_method/checkout_logo.png',
                "gateway" => 'Checkout',
                'order_table' => 'credit_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'charge_id',
            ],
            "ideal" => [
                "img" => '/static/dist/img/payment_method/checkout_logo.png',
                "gateway" => 'Checkout',
                'order_table' => 'ideal_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'charge_id',
            ],
            "poli" => [
                "img" => '/static/dist/img/payment_method/checkout_logo.png',
                "gateway" => 'Checkout',
                'order_table' => 'poli_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'charge_id',
            ],
            "oceanpayment" => [
                "img" => '/static/dist/img/payment_method/oceanpayment_logo.jpg',
                "gateway" => 'Oceanpay',
                'order_table' => 'oceanpayment_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',
            ],
            "przelewy24" => [
                "img" => '/static/dist/img/payment_method/oceanpayment_logo.jpg',
                "gateway" => 'Oceanpay',
                'order_table' => 'przelewy24_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',
            ],
            'worldpay' => [
                "img" => '/static/dist/img/payment_method/worldpay_logo.png',
                "gateway" => 'Worldpay', 
                'order_table' => 'worldpay_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',               
            ],
            "giropay" => [
                "img" => '/static/dist/img/payment_method/passion_logo.png',
                "gateway" => 'Passion', 
                'order_table' => 'giropay_de_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',               
            ],
            "eps" => [
                "img" => '/static/dist/img/payment_method/passion_logo.png',
                "gateway" => 'Passion',
                'order_table' => 'eps_at_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',                
            ],
            "lipapay" => [
                "img" => '/static/dist/img/payment_method/lipapay_logo.png',
                "gateway" => 'Lipapay', 
                'order_table' => 'lipapay_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',               
            ],
            "postepay" => [
                "img" => '/static/dist/img/payment_method/worldpay_logo.png',
                "gateway" => 'Worldpay',  
                'order_table' => 'postepay_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',              
            ],
            "webmoney" => [
                "img" => '/static/dist/img/payment_method/paymaster24.png',
                "gateway" => 'PayMaster24', 
                'order_table' => 'webmoney_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => 'lmi_sys_payment_id',                       
            ],
            "yandex" => [
                "img" => '/static/dist/img/payment_method/yandexmoney.png',
                "gateway" => 'YandexMoney',  
                'order_table' => '',
                'order_no_field' => '',
                'transaction_id_field' => '',                      
            ],
            "payu_upi" => [
                "img" => '/static/dist/img/payment_method/payu.png',
                "gateway" => 'PayU',//支付网关
                "gateway_field" => "transaction_id",//第三方网关查询付款单信息的字段
                'order_table' => 'payu_upi_orders',//付款单表
                "success_status" => ['PAYMENT_AUTHORIZED', 'COMPLETE'],//支付成功状态
                //付款单字段映射
                'order_sn_field' => 'order_no',
                'transaction_id_field' => 'payment_id',
                'currency_field' => 'currency_code',
                'status_field' => 'payu_status',
                'amount_field' => 'amount',
                'usd_rate_field' => 'usd_rate',
                'customer_ip_field' => 'client_ip',
                'paid_time_field' => 'paid_time',
            ],
            "payu_innb" => [
                "img" => '/static/dist/img/payment_method/payu.png',
                "gateway" => 'PayU',//支付网关
                "gateway_field" => "transaction_id",//第三方网关查询付款单信息的字段
                'order_table' => 'payu_innb_orders',//付款单表
                "success_status" => ['PAYMENT_AUTHORIZED', 'COMPLETE'],//支付成功状态
                //付款单字段映射
                'order_sn_field' => 'order_no',
                'transaction_id_field' => 'payment_id',
                'currency_field' => 'currency_code',
                'status_field' => 'payu_status',
                'amount_field' => 'amount',
                'usd_rate_field' => 'usd_rate',
                'customer_ip_field' => 'client_ip',
                'paid_time_field' => 'paid_time',
            ],
            'wp_p24' => [
                "img" => '/static/dist/img/payment_method/worldpay_logo.png',
                "gateway" => 'Worldpay', 
                'order_table' => 'worldpay_przelewy24_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',               
            ],
            'qiwi' => [
                "img" => '/static/dist/img/payment_method/worldpay_logo.png',
                "gateway" => 'Worldpay', 
                'order_table' => 'qiwi_orders',
                'order_no_field' => 'order_no',
                'transaction_id_field' => '',               
            ],
        ],
        'pointCode'=>[ //监控点配置
            'order_number'=>'订单量',
            'error_code'=>'错误代码率',
            'success_rate'=>'支付成功率',
            'success_number'=>'支付成功量',
        ],
        'monitorType' => [
            "order_number" => "尝试支付订单量",
            "success_number" => "支付成功量",
        ],
        'platForms'=>[//平台
            'pc',
            'm',
            'app',
        ],
		'language'=>[
			'en',
			'ar',
			'de',
			'es',
			'fr',
			'it',
			'pt',
			'ru',
		],
        "payment_type" => include(__DIR__ . DIRECTORY_SEPARATOR . "payment_type.php"),
        "site_type" => include(__DIR__ . DIRECTORY_SEPARATOR . "site_type.php"),
        'risk_type' => ['Cybersource', 'ReD', 'RiskGuardian'],
        "region" => include(__DIR__ . DIRECTORY_SEPARATOR . "region.php"),
        "rate_type" => [//汇率监控
            1=>[
                'title'=>'站点汇率',
                'type'=> 1,
                'compareFormula'=>'(新生效站点汇率-上次生效站点汇率)/上次生效站点汇率'
            ],
            2=>[
                'title'=>'平台汇率',
                'type'=> 2,
                'compareFormula'=>'(新生效站点汇率-平台汇率)/平台汇率',
            ],
            3=>[
                'title'=>'外部汇率',
                'type'=> 3,
                'compareFormula'=> '(新生效站点汇率-外部汇率)/外部汇率',
            ],
            4=>[
                'title'=>'速卖通汇率',
                'type'=> 4,
                'compareFormula'=>'(新生效站点汇率-速卖通汇率)/速卖通汇率',
            ],
            5=>[
                'title'=>'棒谷汇率',
                'type'=>5,
                'compareFormula'=>'(新生效站点汇率-棒谷汇率)/棒谷汇率',
            ],
            6=>[
                'title'=>'通拓汇率',
                'type'=>6,
                'compareFormula'=>'(新生效站点汇率-通拓汇率波动)/通拓汇率波动'
            ],
            7=>[
                'title'=>'Shein汇率',
                'type'=>7,
                'compareFormula'=>'(新生效站点汇率-Shein汇率波动)/Shein汇率波动'
            ],
            
        ],
        "subsidy" => [      // 分期贴息
            "EBX-Instalment" => [
                "name" => '巴西分期付款',
                "settleCurrency" => 'BRL',        // 结算币种
                "paymentMethod" => 'instalments',
            ],
            "EBX-MXCC" => [
                "name" => '墨西哥分期付款',
                "settleCurrency" => 'MXN',
                'paymentMethod' => 'EBX_MXCC',
            ],
            "PayU_TRCC" => [
                "name" => '土耳其分期付款',
                'settleCurrency' => 'TRY',
                'paymentMethod' => 'PayU_TRCC',
            ],
            "PayU_BKM" => [
                "name" => 'PayU_BKM',
                'settleCurrency' => 'TRY',
                'paymentMethod' => 'PayU_BKM',
            ]
        ],
        'gatewayMap' => include(__DIR__ . DIRECTORY_SEPARATOR . "gateway_map.php"),
        'errorMethodMap' => include(__DIR__ . DIRECTORY_SEPARATOR . "error_method_map.php"),
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'yii-debug';
    $config['modules']['yii-debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*']
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}


return $config;
