<?php
/**
 * 订单数据收集
 * author chenll
 * date 	2017/08/30
 */
namespace app\modules\jobs\controllers;

use Yii;
use app\modules\admin\models\Gather;

class GatherController extends \yii\web\Controller
{
	//获取redis队列数据保存到数据库
	public function actionIndex(){
	    set_time_limit(600);    // 允许运行10分钟

		$Gather = new Gather();
 		$Gather->Handle();
	}

    /**
     * 处理redis队列中没有同步到数据库的数据
     */
	public function actionHandleNotDealData()
    {
        set_time_limit(600);

        $gather = new Gather();
        $gather->HandleNotDealData();
    }

    /**
     * 处理利率分期数据收集
     */
	public function actionInstalment()
    {
        set_time_limit(600);    // 允许支行10分钟
        $gather = new Gather();
        $gather->HandleInstalment();
    }

    /*
     * 收集支付中心支付加载和页面加载时间
     */
    public function actionHandlePayTime()
    {
        $gather = new Gather();
        $gather->HandlePayTime();
    }
}