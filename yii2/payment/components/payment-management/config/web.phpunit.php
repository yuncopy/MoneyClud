<?php
return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/web.dev.php'),
    [
        'params' => [
            'site' => 'test',
            'phpunit' => true,
        ],
    ]
);
