<?php
return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/web.test.php'), 
    [
      //  'bootstrap' => ['yii-debug', 'gii'],
        'components' => [

        ],
        'modules' => [
            'yii-debug' => [
                'class' => 'yii\debug\Module',
                'allowedIPs' => ['*']
            ],
            'gii' => [
                'class' => 'yii\gii\Module',
                'allowedIPs' => ['*'],
            ],
        ],
        'params' => [
            // 项目url
            'url' => 'http://payment-management.com.trunk.s1cg.egomsl.com',
        ]
    ]
);
