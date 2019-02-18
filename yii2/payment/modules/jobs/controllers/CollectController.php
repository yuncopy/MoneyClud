<?php
/**
 * SOA数据收集
 * author chenll
 * date 	2017/08/30
 */
namespace app\modules\jobs\controllers;
use Yii;
use app\modules\admin\models\DataCollect;
use app\helpers\Utils;

class CollectController extends \yii\web\Controller
{
	/**
     * 从mysql拿出元数据处理
     */
	public function actionIndex()
	{
	    
        $buildByDayUrl = "http://" . $_SERVER["HTTP_HOST"].'/jobs/collect/handle';
        for( $i = 1; $i < 20; $i ++ )
        {
            $buildUrls[] = $buildByDayUrl;
        }
        
        $result = Utils::multiRequest( $buildUrls );
	}



    /**
     * 处理redis到MongoDB元数据
     */
    public function actionHandle(){


        //开始时间
        $time_start = microtime(true);

        set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);

        $key = 'soa_collect_handle';
        $redis = Yii::$app->cache->redis;
        $times = $redis->incr($key);
        $maxProcessNum = 10000000;    // 同时执行的最多进程数
        $startTime = microtime(true);

        // key没有设置过期时间，那么就在这里设置过期时间
        $ttl = $redis->ttl($key);
        if (-1 == $ttl) {
            $redis->expire($key,120);
        }

        if($times > $maxProcessNum){
            $redis->decr($key);
            exit("已经有{$maxProcessNum}个进程在处理");
        }

        $redis->expire($key,120);
        // 程序结束 减 1
        register_shutdown_function(function()use($key,$redis){
            $redis->get($key) > 0 &&  $redis->decr($key);
        });

        $collect = new DataCollect();
        $collect->handle();

        $diffTime = number_format(microtime(true) - $startTime, 2);
        echo "Done, cost time[{$diffTime}]\n\n\n";

        //结束时间
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        if(IS_LOCAL) die("time {$time} seconds\n");
    }


    /**
     * 处理mongodb未处理数据
     */
	public function actionProcess()
	{
		set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);
		$key = 'soa_collect_process';
		$redis = Yii::$app->cache->redis;
	    $times = $redis->incr($key);

        // key没有设置过期时间，那么就在这里设置过期时间
        $ttl = $redis->ttl($key);
        if (-1 == $ttl) {
            $redis->expire($key,120);
        }

        if($times > 3){
            $redis->decr($key);
            exit('已经有3个进程在处理');
        }
        $redis->expire($key,120);
        // 程序结束 减 1
        register_shutdown_function(function()use($key,$redis){
            $redis->get($key) > 0 &&  $redis->decr($key);
        });
		$collect = new DataCollect();
        $collect->processPendingData();
	}

    /**
     * 获取kafka元数据保存Redis  (灾备服务器)
     */
    public function actionGather(){
        set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);
        $key = 'soa_collect_gather';
        $redis = Yii::$app->cache->redis;
        $times = $redis->incr($key);

        // key没有设置过期时间，那么就在这里设置过期时间
        $ttl = $redis->ttl($key);
        if (-1 == $ttl) {
            $redis->expire($key,120);
        }

        if($times > 3){
            $redis->decr($key);
            exit('已经有3个进程在处理');
        }
        $redis->expire($key,120);
        // 程序结束 减 1
        register_shutdown_function(function()use($key,$redis){
            $redis->get($key) > 0 &&  $redis->decr($key);
        });
        $collect = new DataCollect();
        $collect->getData(); //将元数据放入redis
    }

    /**
     * 将灾备服务器redis数据保存到mysql
     */
    public function actionHandleRedisData()
    {
        $collect = new DataCollect();
        $collect->handleKafkaData(); //从redis把数据保存到mysql
    }
    /**
     * 将mysql待数据保存到redis
     */
    public function actionSaveData(){


        //开始时间
        $time_start = microtime(true);

        $collect = new DataCollect();
        $collect->saveRedisData();

        //结束时间
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        if(IS_LOCAL) die("time {$time} seconds\n");
    }

    /**
     * 处理原来mongodb pendding数据
     */ 
    public function actionPendding(){
        $data = Yii::$app->MongoAdapter->getAll('soa_payment_metadata_pendding',[],['handleTime'=>1],'1000');
        if(!empty($data)){
            $collect = new DataCollect();
            foreach($data as $row){
                unset($row['_id'],$row['handleTime']);
                $collect->savePenddingData($row);
                Yii::$app->MongoAdapter->remove('soa_payment_metadata_pendding',$row); //从未处理集合中删除
            } 
        }else{
            exit('没有需要处理的数据');
        }
        
    }
    /**
     * 获取redis待处理数据
     */
    public function actionGetRedisData(){
        $data['key'] = !empty($_GET['key']) ? trim($_GET['key']) : '';
        $data['method'] = !empty($_GET['method']) ?  trim($_GET['method']) : '';
        print_r(DataCollect::getPendingData($data));
    }

    /**
     * 获取set的长度
     */
    public function actionGetSetCard()
    {
        $key = !empty($_GET['key']) ? $_GET['key'] : '';
        if (empty($key)) {
            echo "please set key";
            exit();
        }

        $number = app()->cache->sCard($key);
        echo "$number";
    }

    /**
     * 获取处理进程数
     */
    public function actionGetHandleNum()
    {
        $key = "soa_collect_handle";
        $handleNum = app()->cache->raw_get($key);
        echo "{$handleNum}";
    }

    /**
     * 获取redis中的kafka记录数
     */
    public function actionGetKafkaNum()
    {
        $key = 'soa:kakfa_data';
        $kafkaNum = yii::$app->cache->llen($key);
        echo "{$kafkaNum}";
    }
}