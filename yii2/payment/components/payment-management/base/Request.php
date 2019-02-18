<?php

namespace app\base;

use yii\web\Request as YiiRequest;

/**
 * 基础Request，继承`yii\web\Request`
 */
class Request extends YiiRequest
{
    /**
     * {@inheritdoc}
     */
    public function getUserIp()
    {
        return app()->ip->get();
    }
}
