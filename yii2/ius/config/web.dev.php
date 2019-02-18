<?php
//define('DOMAIN','www.ius.local');
define('DOMAIN',$_SERVER['HTTP_HOST']);
//时区-中国上海
date_default_timezone_set("Asia/Shanghai");
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 's3imageupload',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
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
        'db' => [
            'class'                 => 'yii\db\Connection',
            //'dsn'                   => 'mysql:host=192.168.6.71;dbname=s3imageupload_db',
            'dsn'                   => 'mysql:host=10.40.6.148;dbname=s3imageupload_db',
            'username'              => 'root',
            'password'              => 'NvGHHsQvo3!90YS@',
            'charset'               => 'utf8',
            'enableSchemaCache'     => 0,
            'schemaCacheDuration'   => 3600,
            'schemaCache'           => 'cache',
            
            // 配置从服务器
            'slaveConfig'       => [
                'username'      => 'root',
                'password'      => 'NvGHHsQvo3!90YS@',
                'charset'       => 'utf8',
                'attributes'    => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
            ],
                
            // 配置从服务器组
            'slaves' => [
                ['dsn' => 'mysql:host=10.40.6.148;dbname=s3imageupload_db'],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        
    ],
    'params' => [
        'sso_server_url'        => 'http://user.hqygou.com',
        'admin_url'             => 'http://'.DOMAIN,
        's3_domain_url'         => 'https://s3.amazonaws.com/tests3imageupload/',
        's3_domain_uuu'         => 'https://s3.amazonaws.com/tests3imageupload/',
        's3' => [ // s3
            'region'          => 'us-east-1',
            'bucket'          => 'tests3imageupload',//bucket为储存服务器组的key，由运维解析域名到该服务器组
            'key'             => 'AKIAIPMRA37S5NXAMKIA',
            'secret'          => '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',
        ],
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
