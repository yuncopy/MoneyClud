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
            'dsn' => 'mysql:host=192.168.6.71;dbname=payment_management_db',
            'username' => 'root',
            'password' => 'NvGHHsQvo3!90YS@',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 86400,
            'schemaCache' => YII_ENV_PROD ? 'cache' : 'arrayCache',
        ],
        'checkoutDb' => [//checkout从库配置
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.6.71;dbname=checkout_db',
            'username' => 'root',
            'password' => 'NvGHHsQvo3!90YS@',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 86400,
            'schemaCache' => YII_ENV_PROD ? 'cache' : 'arrayCache',
        ],
        'checkoutDbWithMasterAndSlave' => [     // checkout带主从数据库的配置
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.6.71;dbname=checkout_db',
            'username' => 'root',
            'password' => 'NvGHHsQvo3!90YS@',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 86400,
            'schemaCache' => YII_ENV_PROD ? 'cache' : 'arrayCache',
        ],
        'pciDb' => [//checkout从库配置
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.6.71;dbname=gc_db',
            'username' => 'root',
            'password' => 'NvGHHsQvo3!90YS@',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 86400,
            'schemaCache' => YII_ENV_PROD ? 'cache' : 'arrayCache',
        ],
//        'cache' => [
//            'class' => 'yii\redis\Cache',
//            'keyPrefix' => 'pay_manage:',
//        'redis' => [
//            'hostname' => '192.168.6.176',
//            'database' => 0,
//            'port' => 26386,
//        ],
//    ],
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
        'assetManager' => [

            'linkAssets' => true,

        ],
        'bjTime' => [
            'class' => 'app\components\NewSysTime',
            'timeZone' => 'Asia/Shanghai',
            'offset' => 0
        ],
    ],
    'params' => [
        // 登陆地址
        'sso_server_url' => 'http://user.hqygou.com',
        //系统访问域名
        'www_url'				=> 'http://test.pm_a.cn',
        //监控系统接口地址与密钥
        'rms110_api' => [
            'url' => 'http://rms110.com.trunk.s1cg.egomsl.com/api-source/index?project_code=GC_pay',
            'token' => 'NiYmKISZCHyhC76rjqfi',
        ],
        //站点提供的获取数据接口地址

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
                'merchantId' => 11339972,
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
                'name' => 'yoshop',
            ],
            'zaful' => [
                'name' => 'zaful',
                'code' => 'ZF',
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
                    'tcp://10.60.46.195:6404'
                ],
                'prefix'=>'payment:'
            ],
            'monitor' => [
                'server'=>[
                    'tcp://10.60.46.195:6404'
                ],
                'prefix'=>'monitor:'
            ]
         ],
         'kafkaConfig'=>[
            'default' => [//支付埋点
                'broker'=>'10.40.6.151,10.40.6.152,10.40.6.153',
                'topic'=>'pay_center_trace_topic',
                'group_id'=>'PaymentTS_P',
            ],
            'configure' => [//配置信息
                'broker'=>'10.40.6.151,10.40.6.152,10.40.6.153',
                'topic'=>'currency_rate_topic',
                'group_id'=>'PaymentTS_P',
            ],
         ],
         'mongoConfig'=>[
            'default' => [
                'server' => 'mongodb://payment:3DtpLtoxITqQ@192.168.6.67:27017/payment_logs',
                'database' => 'payment_logs',
            ],
         ],
        //rabbit配置
        'MqConfig' => [
            'host' => '10.40.6.89',
            'port' => '5672',
            'user' => 'pay_center_test',
            'pass' => 'pay_center_test',
            'vhost' => 'OMS',
            'exchange'=>'PaymentCheck_PAYCENTER',
            'queue'=>'PaymentCheck_PAYCENTER',
            'consumerTag'=>'consumer',
        ],

	    'logMq'=> [
		    'base' => [
			    'host'      => '10.40.6.89',
			    'port'      => '5672',
			    'user'      => 'pay_center_test',
			    'pwd'       => 'pay_center_test',
			    'vhost'     => 'SYS_PAYCENTER',
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
                'host'      => '10.40.6.89',
                'port'      => '5672',
                'user'      => 'oms_test',
                'pwd'       => 'oms_test',
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
                'host'      => '10.40.6.89',
                'port'      => '5672',
                'user'      => 'pay_center_test',
                'pwd'       => 'pay_center_test',
                'vhost'     => 'SYS_PAYCENTER',
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
        'soaWebsiteOrderMq'=> [ // 接收网站的订单（用于页面加载时长）
            'ZF' => [
                'base' => [
                    'host'      => '10.40.6.89',
                    'port'      => '5672',
                    'user'      => 'web_zf_test',
                    'pwd'       => 'web_zf_test',
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
        ],
        'cdpMq' => [        // CDP MQ队列
            'mainConfig' => [
                'base' => [
                    'host'      => '10.40.6.89',
                    'port'      => '5672',
                    'user'      => 'pay_center_test',
                    'pwd'       => 'pay_center_test',
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

	    'emailEncryptParam'=>[
		    'key' => 'srdZFHi28NvU3kAaezBC6chWn47mDg9j',
		    'iv'  => 's7eiExjhDdky92J8',
	    ],
        // oms配置
        'omsConfig' => [
            "base_url" => "http://oms.hqygou.com",                              // oms的基础url
            "get_order_key" => "cd4120018520b76382b27520b55a24d5",              // 获取oms订单的密钥

            // 同步oms
            "sync_from_oms_url" => "http://omsdata.dts114.com.release-prod-20180320-v1.s1cg.egomsl.com",      // oms（支付）地址
            'sync_from_oms_key' => "cd4120018520b76382b27520b55a24d5",                        // 获取oms数据的密钥
        ],

        // sms配置
        'smsConfig' => [
            "key" => "cd4120018520b76382b27520b55a24d5",              // 获取sms数据的密钥
            "sync_url" => "http://omsdata.dts114.com.release-prod-20180320-v1.s1cg.egomsl.com",      // 同步url
            //"sync_url" => "http://oms-data-a.com"
        ],

        //配置查询的方式 ,先写到文件中，后面集成过来
        'payConfig' => [
            'gc'=>[
                'baseUrl'=>'http://test.gc.com.trunk.s1.egomsl.com/index.php?m=index&a=getRawOstatus&code=%s&oid=%s&site_name=%s&mid=%s',
                //支付方式支持的站点
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
                    'zaful','zanstyle','zanbase'
                ],
            ],
            'worldpay' =>[
                'baseUrl'=>'http://checkout.com.trunk.s1.egomsl.com/worldpay/getOrderStatus',
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
                    'zaful','zanstyle','zanbase'
                ],
            ],
            'WP_QIWI' =>[
                'baseUrl'=>'http://checkout.com.d.s1.egomsl.com/qiwi/getOrderStatus',
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
                'baseUrl'=>'http://checkout.com.d.s1.egomsl.com/credit/getOrderStatus',
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
                    'zaful','zanstyle','zanbase'
                ],
            ],
            'instalments' => [
                'baseUrl'   => 'http://checkout.com.d.s1.egomsl.com/instalments/getOrderStatus',
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
                'baseUrl'   => 'http://checkout.com.d.s1.egomsl.com/instalments/getOrderStatus',
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
                'baseUrl'   => 'http://checkout.com.d.s1.egomsl.com/ideal/getOrderStatus',
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
                'baseUrl'   => 'http://checkout.com.d.s1.egomsl.com/boleto/getOrderStatus',
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
                'baseUrl'   => 'http://checkout.com.d.s1.egomsl.com/przelewy24/get-order-status',
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
