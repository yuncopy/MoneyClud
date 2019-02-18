<?php
/**
 * SOA kafka 配置数据收集（汇率）
 * author chenll
 * date 	2018/06/14
 */
namespace app\modules\jobs\controllers;

use Yii;
use app\modules\admin\models\KafkaConsumer;

class KafkaConsumerController extends \yii\web\Controller
{

    /**
     * 获取kafka元数据保存mysql  (灾备服务器)
     */
    public function actionGather(){
        KafkaConsumer::get();
    }

    /**
     * 处理kafka元数据
     */
    public function actionHandle(){
       KafkaConsumer::handle();
    }
}