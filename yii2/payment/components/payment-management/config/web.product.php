<?php
/**
 * 站点配置项说明
 * ----------------------------------------------------------------
 * multPic      |  true or false  |  产品多维度展示图功能(默认不配置该项)
 * customBrand  |  true or false  |  自定义品牌词功能(默认不配置该项)
 * openLinkId   |  true or false  |  商品url带上推广id功能(默认不配置该项)
 * lang         |  es             |  从主站拉出单独国家作为一个站处理,如gearbest的俄语站和西语站都是单独出来的
 * ----------------------------------------------------------------
 */
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=pay-verify.cojn00n8zy2d.us-east-1.rds.amazonaws.com;dbname=payment_management_db',
            'username' => 'pay_m_user',
            'password' => '2VJjdkRbq5',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
            'schemaCacheDuration' => 86400,
            'schemaCache' => YII_ENV_PROD ? 'cache' : 'arrayCache',
            // 配置从服务器
            'slaveConfig' => [
                'username' => 'pay_s_user',
                'password' => '8v50mTSV37',
                'charset' => 'utf8',
                'attributes' => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
            ],
            // 配置从服务器组
            'slaves' => [
                ['dsn' => 'mysql:host=pay-verify-s.cojn00n8zy2d.us-east-1.rds.amazonaws.com;dbname=payment_management_db'],
            ],
        ],
        'checkoutDb' => [//checkout从库配置
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=checkout-new-slave.cojn00n8zy2d.us-east-1.rds.amazonaws.com;dbname=checkout_db',
            'username' => 'checkout_s_user',
            'password' => 'u94MisvmCrZt',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
            'schemaCacheDuration' => 86400,
            'schemaCache' => YII_ENV_PROD ? 'cache' : 'arrayCache',
        ],
        'checkoutDbWithMasterAndSlave' => [     // checkout带主从数据库的配置
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=checkout-new.cojn00n8zy2d.us-east-1.rds.amazonaws.com;dbname=checkout_db',
            'username' => 'checkout_m_user',
            'password' => 'CglvShNLte82',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
            'schemaCacheDuration' => 86400,
            'schemaCache' => YII_ENV_PROD ? 'cache' : 'arrayCache',
            // 配置从服务器
            'slaveConfig' => [
                'username' => 'checkout_s_user',
                'password' => 'u94MisvmCrZt',
                'charset' => 'utf8',
                'attributes' => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
            ],
            // 配置从服务器组
            'slaves' => [
                ['dsn' => 'mysql:host=checkout-new-slave.cojn00n8zy2d.us-east-1.rds.amazonaws.com;dbname=checkout_db'],
            ],
        ],
        'pciDb' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=pci-s.cojn00n8zy2d.us-east-1.rds.amazonaws.com;dbname=pci',
            'username' => 'pci_s_user',
            'password' => '9wP3CQB7MsNOEhAE',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
            'schemaCacheDuration' => 86400,
            'schemaCache' => YII_ENV_PROD ? 'cache' : 'arrayCache',
        ],
//        'cache' => [
//            'class' => 'yii\redis\Cache',
//            'keyPrefix' => 'pay_manage:',
//            'redis' => [
//                 'hostname' => 'payment-redis.mczgdb.clustercfg.use1.cache.amazonaws.com',
//                 'database' => 0,
//                 'port' => 6379,
//            ],
//        ],
        'checkoutRedis' => [
            'class' => 'app\components\checkoutRedis',
        ],
        'cache' => [
            'class' => 'app\components\Otherredis',
        ],
        'monitorCache' => [
            'class' => 'app\components\Otherredis',
            'prefix' => 'monitor:',
        ],
        'bjTime' => [
            'class' => 'app\components\NewSysTime',
            'timeZone' => 'Asia/Shanghai',
            'offset' => 0
        ],
    ],
    'params'    => [
        // 登陆地址
        'sso_server_url'    => 'http://user.gw-ec.com',
        // 项目url
        'url' => 'http://payment.dts114.com',
		//监控系统接口地址与密钥
        'rms110_api' => [
            'url' => 'http://www.rms110.com/api-source/index?project_code=GC_pay',
            'token' => 'NiYmKISZCHyhC76rjqfi',
        ],
        // 站点配置
        'sites' => [
            'buyinggoods' => [
                'name' => 'buyinggoods'
            ],
            'boynewyork' => [
                'name' => 'boynewyork'
            ],
            'chinabrands' => [
                'name' => 'chinabrands'
            ],
            'chinabrands_cn' => [
                'name' => 'chinabrands_cn'
            ],
            'dealsmachine' => [
                'name' => 'dealsmachine'
            ],
            'digbest' => [
                'name' => 'digbest'
            ],
            'dressfo' => [
                'name' => 'dressfo'
            ],
            'dezzal' => [
                'name' => 'dezzal'
            ],
            'dresslily' => [
                'name' => 'dresslily'
            ],
            'dizener' => [
                'name' => 'dizener'
            ],
            'everbuying' => [
                'name' => 'everbuying'
            ],
            'gamiss' => [
                'name' => 'gamiss'
            ],
            'gearbest' => [
                'name' => 'gearbest'
            ],
            'igogo' => [
                'name' => 'igogo',
                'url' => 'http://www.igogo.es',
                'apiUrl' => 'http://www.igogo.es:86',
            ],
            'nastydress' => [
                'name' => 'nastydress'
            ],
            'nextmia' => [
                'name' => 'nextmia'
            ],
            'oksells' => [
                'name' => 'oksells'
            ],
            'pasymoon' => [
                'name' => 'pasymoon'
            ],
            'rosewholesale' => [
                'name' => 'rosewholesale'
            ],
            'rosegal' => [
                'name' => 'rosegal'
            ],
            'sammydress' => [
                'name' => 'sammydress'
            ],
            'twinkledeals' => [
                'name' => 'twinkledeals'
            ],
            'trendsgal' => [
                'name' => 'trendsgal'
            ],
            'volumebest' => [
                'name' => 'volumebest'
            ],
            'yoshop' => [
                'name' => 'yoshop'
            ],
            'zaful' => [
                'name' => 'zaful'
            ],
            'zanstyle' => [
                'name' => 'zanstyle'
            ],
            'zanbase' => [
                'name' => 'zanbase'
            ],
            'sys_set' => [
                'name' => '系统管理',
                'url' => ''
            ],
        ],
        'redisConfig'=>[
            'default' => [
                'server'=>[
                    'tcp://172.31.19.51:6383',
                    'tcp://172.31.25.60:6383',
                    'tcp://172.31.49.45:6390',
                ],
                'options' => [
                    'replication' => 'sentinel',
                    'service' => 'sentinel-172.31.19.51-6382',
                    'parameters' => [
                        'password' => '6e1KWyC29w',
                        'database' => 0,
                    ]
                ],
                'prefix'=>'payment:',
            ],
            'monitor' => [
                'server'=>[
                    'tcp://172.31.19.51:6383',
                    'tcp://172.31.25.60:6383',
                    'tcp://172.31.49.45:6390',
                ],
                'options' => [
                    'replication' => 'sentinel',
                    'service' => 'sentinel-172.31.19.51-6382',
                    'parameters' => [
                        'password' => '6e1KWyC29w',
                        'database' => 0,
                    ]
                ],
                'prefix'=>'monitor:',
            ]
        ],
        //rabbit配置
        'MqConfig' => [
            'host' => 'conn.amazon.rabbitmq.hqygou.com',
            'port' => '5672',
            'user' => 'pay_center_pro',
            'pass' => 'KHTUEQux',
            'vhost' => 'OMS',
            'exchange'=>'PaymentCheck_PAYCENTER',
            'queue'=>'PaymentCheck_PAYCENTER',
            'consumerTag'=>'consumer',
        ],
        // oms配置
        'omsConfig' => [
            "base_url" => "http://oms.gw-ec.com",                        // oms的基础url
            "get_order_key" => "cd4120018520b76382b27520b55a24d5",       // 获取oms订单的密钥

            // 同步oms
            "sync_from_oms_url" => "http://omsdata.dts114.com",      // oms（支付）地址
            'sync_from_oms_key' => "cd4120018520b76382b27520b55a24d5",                        // 获取oms数据的密钥
        ],

        // sms配置
        'smsConfig' => [
            "key" => "cd4120018520b76382b27520b55a24d5",              // 获取sms数据的密钥
            "sync_url" => "http://omsdata.dts114.com",                // 同步url
        ],

	    'logMq'=> [
		    'base' => [
			    'host'      => 'conn.amazon.rabbitmq.hqygou.com',
			    'port'      => '5672',
			    'user'      => 'pay_center_pro',
			    'pwd'       => 'KHTUEQux',
			    'vhost'     => 'PAYCENTER',
		    ],
		    'queue' => [
			    'name'          => 'paypalLog_PAYCENTER',
			    'durable'       => true,
			    'auto_delete'   => false,
			    'routingkey'    => 'paypalLog_PAYCENTER',
		    ],
		    'exchange' => [
			    'name'          => 'amq.direct',
			    'durable'       => true,
			    'auto_delete'   => false,
			    'exchange_type' => 'direct'
		    ],
		    'delivery_mode'     => 2,   // 存入消息需设置的
		    'format'            => 'json'
	    ],
        'riskMq'=> [ //风控黑名单
            'base' => [
                'host'      => 'conn.amazon.rabbitmq.hqygou.com',
                'port'      => '5672',
                'user'      => 'pay_center_pro',
                'pwd'       => 'KHTUEQux',
                'vhost'     => 'OMS',
            ],
            'queue' => [
                'name'          => 'RiskBlacklist_PAYCENTER',
                'durable'       => true,
                'auto_delete'   => false,
                'routingkey'    => 'RiskBlacklist_PAYCENTER',
            ],
            'exchange' => [
                'name'          => 'amq.direct',
                'durable'       => true,
                'auto_delete'   => false,
                'exchange_type' => 'direct'
            ],
            'delivery_mode'     => 2,   // 存入消息需设置的
            'format'            => 'json'
        ],
        'orderPushMq'=> [ //推送订单至oms
            'base' => [
                'host'      => 'conn.amazon.rabbitmq.hqygou.com',
                'port'      => '5672',
                'user'      => 'pay_center_pro',
                'pwd'       => 'KHTUEQux',
                'vhost'     => 'PAYCENTER',
            ],
            'queue' => [
                'name'          => 'orderPush_OMS',
                'durable'       => true,
                'auto_delete'   => false,
                'routing_key'    => 'orderPaymentStatistics',
            ],
            'exchange' => [
                'name'          => 'amq.direct',
                'durable'       => true,
                'auto_delete'   => false,
                'exchange_type' => 'direct'
            ],
            'delivery_mode'     => 2,   // 存入消息需设置的
            'format'            => 'json'
        ],
        'soaWebsiteOrderMq'=> [     // 接收网站的订单（用于页面加载时长）
            'ZF' => [
                'base' => [
                    'host'      => 'conn.amazon.rabbitmq.hqygou.com',
                    'port'      => '5672',
                    'user'      => 'pay_center_pro',
                    'pwd'       => 'KHTUEQux',
                    'vhost'     => 'WEB_ZF',
                ],
                'queue' => [
                    'name'          => 'soaPayLog_PAYCENTER',
                    'durable'       => true,
                    'auto_delete'   => false,
                    'routing_key'    => 'soaPayLog_PAYCENTER',
                ],
                'exchange' => [
                    'name'          => 'amq.direct',
                    'durable'       => true,
                    'auto_delete'   => false,
                    'exchange_type' => 'direct'
                ],
                'delivery_mode'     => 2,   // 存入消息需设置的
                'format'            => 'json'
            ],
            'RG' => [
                'base' => [
                    'host'      => 'conn.amazon.rabbitmq.hqygou.com',
                    'port'      => '5672',
                    'user'      => 'pay_center_pro',
                    'pwd'       => 'KHTUEQux',
                    'vhost'     => 'WEB_RG',
                ],
                'queue' => [
                    'name'          => 'soaPayLog_PAYCENTER',
                    'durable'       => true,
                    'auto_delete'   => false,
                    'routing_key'    => 'soaPayLog_PAYCENTER',
                ],
                'exchange' => [
                    'name'          => 'amq.direct',
                    'durable'       => true,
                    'auto_delete'   => false,
                    'exchange_type' => 'direct'
                ],
                'delivery_mode'     => 2,   // 存入消息需设置的
                'format'            => 'json'
            ],
            'RW' => [
                'base' => [
                    'host'      => 'conn.amazon.rabbitmq.hqygou.com',
                    'port'      => '5672',
                    'user'      => 'pay_center_pro',
                    'pwd'       => 'KHTUEQux',
                    'vhost'     => 'WEB_RW',
                ],
                'queue' => [
                    'name'          => 'soaPayLog_PAYCENTER',
                    'durable'       => true,
                    'auto_delete'   => false,
                    'routing_key'    => 'soaPayLog_PAYCENTER',
                ],
                'exchange' => [
                    'name'          => 'amq.direct',
                    'durable'       => true,
                    'auto_delete'   => false,
                    'exchange_type' => 'direct'
                ],
                'delivery_mode'     => 2,   // 存入消息需设置的
                'format'            => 'json'
            ],

        ],
        'cdpMq' => [        // CDP MQ队列
            'mainConfig' => [
                'base' => [
                    'host'      => 'conn.amazon.rabbitmq.hqygou.com',
                    'port'      => '5672',
                    'user'      => 'pay_center_pro',
                    'pwd'       => 'KHTUEQux',
                    'vhost'     => 'CDP',
                ],
                'exchange' => [
                    'name'          => 'amq.direct',
                    'durable'       => true,
                    'auto_delete'   => false,
                    'exchange_type' => 'direct'
                ],
                'delivery_mode'     => 2,   // 存入消息需设置的
                'format'            => 'json'
            ],
            'queueConfig' => [
                'salesPlatform' => [        // 销售平台：站点、国家站数据
                    'queue' => [
                        'name'          => 'salesplatform_PAYCENTER',
                        'durable'       => true,
                        'auto_delete'   => false,
                        'routing_key'    => 'salesplatform_ITMS',
                    ],
                ],
                'payment' => [          // 支付方式
                    'queue' => [
                        'name'          => 'payment_PAYCENTER',
                        'durable'       => true,
                        'auto_delete'   => false,
                        'routing_key'    => 'payment_SOA',
                    ],
                ]
            ]
        ],
        'kafkaConfig'=>[
            'default' => [
                'broker'=>'169.60.21.198:9092,169.60.21.198:9093,169.60.21.198:9094',
                'topic'=>'pay_center_trace_topic',
                'group_id'=>'PaymentTS_Group',
            ],
            'configure' => [
                'broker'=>'169.60.21.198:9092,169.60.21.198:9093,169.60.21.198:9094',
                'topic'=>'currency_rate_topic',
                'group_id'=>'PaymentTS_Group',
            ],
         ],
         'mongoConfig'=>[
            'default' => [
                'server' => 'mongodb://pay_mongo_m:rwRqbvwnkbB5@172.31.44.174:27017/payment_logs',
                'database' => 'payment_logs',
            ],
         ],
	    'emailEncryptParam'=>[
		    'key' => 'srdZFHi28NvU3kAaezBC6chWn47mDg9j',
		    'iv'  => 's7eiExjhDdky92J8',
	    ],

        //配置查询的方式 ,先写到文件中，后面集成过来
        'payConfig' => [
            'gc'=>[
                'baseUrl'=>'https://payment.gearbest.com/index.php?m=index&a=getRawOstatus&code=%s&oid=%s&site_name=%s&mid=%s',
                'allowSite'=>['sammydress','rosegal','rosewholesale','bridalpure','everbuying','dresslily',
                    'igogo','gearbest','nastydress','trendsgal','digbest','yoshop'],
            ],
            'worldpay' =>[
                'baseUrl'=>'https://checkout.gearbest.com/worldpay/getOrderStatus',
                'allowSite'=>['buyinggoods','boynewyork',
                    'chinabrands',
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
                    'zaful','zanstyle','zanbase'],
            ],
            'WP_QIWI' =>[
                'baseUrl'=>'https://checkout.gearbest.com/qiwi/getOrderStatus',
                'allowSite'=>['buyinggoods','boynewyork',
                    'chinabrands',
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
                    'zaful','zanstyle','zanbase'],
            ],
            'checkout_credit' =>[
                'baseUrl'=>'https://checkout.gearbest.com/credit/getOrderStatus',
                'allowSite'=>['buyinggoods','boynewyork',
                    'chinabrands',
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
                    'zaful','zanstyle','zanbase'],
            ],
            'instalments' => [
                'baseUrl'   => 'https://checkout.gearbest.com/instalments/getOrderStatus',
                'allowSite' => [
                    'buyinggoods','boynewyork',
                    'chinabrands',
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
            ],
            'ebanxinstalment' => [
                'baseUrl'   => 'https://checkout.gearbest.com/instalments/getOrderStatus',
                'allowSite' => [
                    'buyinggoods','boynewyork',
                    'chinabrands',
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
            ],
            'ideal' => [
                'baseUrl'   => 'https://checkout.gearbest.com/ideal/getOrderStatus',
                'allowSite' => [
                    'buyinggoods','boynewyork',
                    'chinabrands',
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
            ],
            'boleto' => [
                'baseUrl'   => 'https://checkout.gearbest.com/boleto/getOrderStatus',
                'allowSite' => [
                    'buyinggoods','boynewyork',
                    'chinabrands',
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
            ],
            'przelewy24' => [
                'baseUrl'   => 'https://checkout.gearbest.com/przelewy24/get-order-status',
                'allowSite' => [
                    'buyinggoods','boynewyork',
                    'chinabrands',
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
            ],
        ],
    ]
];
