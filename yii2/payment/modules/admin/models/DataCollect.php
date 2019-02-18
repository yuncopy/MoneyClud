<?php
//
namespace app\modules\admin\models;

use app\helpers\Helper;
use app\helpers\ReportHelper;
use app\helpers\SoaHelper;
use Yii;
use app\base\AdminActiveRecord;
use yii\db\MigrationInterface;
use yii\helpers\Url;
use app\helpers\Platform;
use app\helpers\MongodbHelper;
use app\models\SoaPaymentReport;

class DataCollect extends AdminActiveRecord
{   
    const LOGTYPE_SOA_PAYMENT = 1; //SOA
    const LOGTYPE_UNKNOWN = 0; //未定义
    const LOGSUBTYPE_UNKNOWN = 0; //0： 未定义
    const LOGSUBTYPE_TOPAYPAGE = 1; //1： toPayPage（进入支付页面）
    const LOGSUBTYPE_TO_PAY = 2; //2： toPay（客户提交支付请求）
    const LOGSUBTYPE_PAYINFO = 3; //3： payInfo（解析客户支付信息）
    const LOGSUBTYPE_RISK = 4; //4： riskControl（支付风控结果信息）
    const LOGSUBTYPE_PAY_RESULT = 5; //5： payResult（支付网关结果信息）
    const LOGSUBTYPE_THREES_RESULT = 6; //6： threedsResult（3DS或本地支付跳回页面）
    const LOGSUBTYPE_NOTIFY = 7; //7： notify（支付网关异步通知）
    const LOGSUBTYPE_SYNC_STATUS = 8; //8： syncStatus（支付信息同步到系统结果）
    const LOGSUBTYPE_INTERNAL_RISK_CONTROL = 3.5; //内部风控
    const LOGSUBTYPE_EXPRESS_TO_PAY = 10; //快速付款客户提交支付请求
    const LOGSUBTYPE_EXPRESS_PAYINFO = 11; //快速付款解析客户支付信息
    const LOGSUBTYPE_EXPRESS_PAY_RESULT = 12; //快速付款支付网关结果信息

    //内部风控
    const RISK_TYPE_CHEAT = 1; //欺诈
    const RISK_TYPE_LAUNDERING = 2; //洗钱
    const RISK_TYPE_MALICE = 3; //恶意
    const RISK_TYPE_FISHING = 4; //钓鱼
    //mongodb 集合
    const COLLECTION_UNIT_DATA = 'soa_payment_metadata'; //集合名称
    const COLLECTION_PENDING = 'soa_payment_metadata_pendding'; //未处理集合
    const COLLECTION_ORDER_LAST_DATA = 'soa_payment_metadata_order_last_payinfo'; //订单数据集合
    const COLLECTION_ORDER_LAST_DATA_BY_ORDERSN = 'soa_payment_metadata_last_payinfo_by_order'; //订单数据集合

    //按月统计
    const COLLECTION_SUBMITDATE_LAST_MONTH = 'submitDate_last_month'; //提交日期最近一个月集合
    const COLLECTION_SUBMITDATE_LAST_PAYINFO_LAST_MONTH = 'submitDate_last_payinfo_last_month';
    const COLLECTION_ORDERDATE_LAST_MONTH = 'orderDate_last_month'; //订单日期最近一个月集合
    const COLLECTION_ORDERDATE_LAST_PAYINFO_LAST_MONTH = 'orderDate_last_payinfo_last_month';
    //按天统计
    const COLLECTION_ORDERDATE = 'orderDate_';
    const COLLECTION_ORDERDATE_LAST_PAYINFO = 'orderDate_last_payinfo_';
    const COLLECTION_SUBMITDATE = 'submitDate_';
    const COLLECTION_SUBMITDATE_LAST_PAYINFO = 'submitDate_last_payinfo_';
    //快捷支付集合
    const COLLECTION_EXPRESS_DATA = 'soa_payment_metadata_express';
    const COLLECTION_EXPRESS_PENDDING_DATA = 'soa_payment_metadata_express_pendding';
    const COLLECTION_TOKEN_DATA = 'soa_payment_metadata_token';
    //汇率集合
    const COLLECTION_RATE = 'soa_payment_rate';

    //用户行为集合
    const COLLECTION_BEHAVIOR = 'soa_behavior_metadata_';
    const BEHAVIOR_TYPE_CLICK = 'click'; //点击
    const BEHAVIOR_TYPE_VALIDATE = 'validate'; //验证
    const BEHAVIOR_TYPE_LOAD = 'load'; //加载
    const BEHAVIOR_TYPE_PAYINIT = 'payInit';//actionValue的值 1 channels和order接口都请求成功 0channels或order有一个失败

    const BEHAVIOR_ACTION_LOGO = 'logo'; //点击 pc logo
    const BEHAVIOR_ACTION_CHECKWALLET = 'checkWallet'; // 勾选电子钱包
    const BEHAVIOR_ACTION_UNCHECKWALLET = 'uncheckWallet';// 取消勾选电子钱包
    const BEHAVIOR_ACTION_MMORE = 'm_more'; // 点击m版more按钮
    const BEHAVIOR_ACTION_CHANNEL = 'channel'; //勾选支付方式（包含卡种）
    const BEHAVIOR_ACTION_MPAY0 = 'm_pay_0'; // m首页支付
    const BEHAVIOR_ACTION_MPAY1 = 'm_pay_1'; //表单支付
    const BEHAVIOR_ACTION_PCPAY = 'pc_pay'; // pc支付
    const BEHAVIOR_ACTION_MBAK1 = 'm_bak_1'; // 表单返回（只有移动，不含app）
    const BEHAVIOR_ACTION_MBAK0 = 'm_bak_0'; // 首页返回（只有移动，不含app）
    const BEHAVIOR_ACTION_SETPW = 'setPw'; //设置密码
    const BEHAVIOR_ACTION_FORGETPW = 'forgetPw'; //忘记密码

    // 事前风控
    const COLLECTION_COD_RISK = "soa_cod_risk_metadata_";
    const COD_TYPE_INTERNAL_RISK_CONTROL = 1; // 1： 事前内部风控
    const COD_TYPE_RISK_CONTROL = 2; // 2： 事前外部风控

    const COLLECTION_TO_CHECKOUT = "soa_to_checkout_metadata_";

    //redis keys
    const META_KAFKA_KEY = 'soa:source_data';//soa kafka数据
    const META_PENDING_KEY = 'soa:pendding:';//soa处理失败数据
    const META_KAFKA_DATA = 'soa:kakfa_data'; //kafka待处理队列
    const META_KAFKA_PENDING_KEY  = 'soa:pendding:keys';//soa处理失败数据keys集合
    const META_CHANNEL_CODE  = 'soa:channel_code';//存储支付方式


    public $status = 0;
    public static $behaviorAction = [
        self::BEHAVIOR_ACTION_LOGO => 1, 
        self::BEHAVIOR_ACTION_CHECKWALLET => 2,
        self::BEHAVIOR_ACTION_UNCHECKWALLET => 3,
        self::BEHAVIOR_ACTION_MMORE => 4,
        self::BEHAVIOR_ACTION_CHANNEL => 5,
        self::BEHAVIOR_ACTION_MPAY0 => 6,
        self::BEHAVIOR_ACTION_MPAY1 => 7,
        self::BEHAVIOR_ACTION_PCPAY => 8,
        self::BEHAVIOR_ACTION_MBAK1 => 9,
        self::BEHAVIOR_ACTION_MBAK0 => 10,
        self::BEHAVIOR_ACTION_SETPW => 11,
        self::BEHAVIOR_ACTION_FORGETPW => 12,
    ];


    //是否使用定金膨胀订单功能--为了是否执行定金膨胀订单代码
    const IS_OPEN_SWELL_ORDER = true;

    public static function tableName()
    {
        return 'kafka_meta_data';
    }

    /**
     * 获取kafka数据保存reids
     * @return      array
    */
    public function getData()
    {   

        $data = Yii::$app->KafkaAdapter->consumer();
        $fails = [];
        if(!empty($data)){
            foreach($data as $key=>$row){
                if($row['status'] == 1){
                    $detail = json_decode($row['data'],true);
                    $save = json_decode($detail['data'],true);
                    $save['method'] = $detail['method'];
                    $save['traceID'] = $save['method'] == 'webInfo' ?  $save['traceId'] : $save['traceID'];
                    $save['requestTime'] = $save['method'] == 'webInfo' ? intval(substr($save['actionTime'],0,strlen($save['actionTime'])-3))  : intval(substr($save['requestTime'],0,strlen($save['requestTime'])-3));

                    // 针对toPayPage，添加其到set中，用来后面检查数据
                    if ('toPayPage' == $save['method'] && !empty($save['parentOrderSn'])) {
                        $save['gatherTime'] = time();
                        $save['gatherDate'] = app()->bjTime->date('Ymd', $save['gatherTime']);
                        $gatherKey = "gather_{$save['siteCode']}_{$save['gatherDate']}";
                        yii::$app->cache->sAdd($gatherKey, $save['parentOrderSn']);
                    }

                    if(yii::$app->cache->push(self::META_KAFKA_KEY,$save))
                    {
                        continue;
                    }else{//保存不成功

                        $orderKey = [];
                        if (isset($save['parentOrderSn'])) {
                            $orderKey = ["parentOrderSn" => $save['parentOrderSn']];
                        } elseif (isset($save['traceID'])) {
                            $orderKey = ['traceID' => $save['traceID']];
                        } elseif (isset($save['paySn'])) {
                            $orderKey = ['paySn' => $save['paySn']];
                        } else {
                            $orderKey = ['method' => isset($save['method']) ? $save['method'] : ''];
                        }
                        $fails[] = [
                            'data'=>json_encode($save),
                            'request_time'=>$save['requestTime'],
                            'log_store_time'=>time(),
                            'order_key'=> json_encode($orderKey),
                        ];  // 保存数据
                    }
                }   
            }
            if(!empty($fails)){
                yii::$app->db->enableSlaves = false;        // 禁用从库查询，从主库查询数据
                $status = Yii::$app->db->createCommand()->batchInsert(self::tableName(), ['data','request_time','log_store_time','order_key'], $fails)->execute();//执行批量添加
                if(empty($status)){
                    app()->recordLog->log('kafka-fail', $fails); //记录kafka失败保存记录
                }
            } 
        }else{
            exit('没有kafka消息');
        }
        
    }

    /**
     * 从Redis获取保存到mysql数据库
     * @return bool
     */
    public function handleKafkaData()
    {
        yii::$app->db->enableSlaves = false;        // 禁用从库查询，从主库查询数据。
        $count = 0;
        while (true) {
            if ($count >= 1000){
                break;
            }
            $data[] = (array)yii::$app->cache->pop(self::META_KAFKA_KEY);
            $count++;
        }

        if(empty($data)){
            exit('没有需要处理的数据');
        }
        app()->recordLog->log('kafka-save', $data); //记录kafka redis记录
        $values = [];
        foreach($data as $row){
            if(!empty($row)){

                $orderKey = [];
                if (isset($row['parentOrderSn'])) {
                    $orderKey = ["parentOrderSn" => $row['parentOrderSn']];
                } elseif (isset($row['traceID'])) {
                    $orderKey = ['traceID' => $row['traceID']];
                } elseif (isset($row['paySn'])) {
                    $orderKey = ['paySn' => $row['paySn']];
                } else {
                    $orderKey = ['method' => isset($row['method']) ? $row['method'] : ''];
                }
                $values[] = [
                    'data'=>json_encode($row),
                    'request_time'=>$row['requestTime'],
                    'log_store_time'=>time(),
                    'order_key'=> json_encode($orderKey),
                ];  // 保存数据
            } 
        }
        if(empty($values)){
            exit('没有需要处理的数据');
        }

        $status = Yii::$app->db->createCommand()->batchInsert(self::tableName(), ['data','request_time','log_store_time','order_key'], $values)->execute();//执行批量添加

        //保存失败再添加还原数据
        if(empty($status)){
            foreach($data as $row){
               Yii::$app->cache->push(self::META_KAFKA_KEY,$row); 
            } 
        }   
        return true;
    }

    /**
     * 获取数据库未处理数据保存reids
     * @return none
     */
    public function saveRedisData()
    {

        // 从AWS云本地数据库查询数据保存到Redis中

        $data = $this->find()->select('*')->where(['status'=>0])->orderBy('id asc')->asArray()->limit(1000)->all(); //从mysql拿数据
        if(!empty($data)){
            foreach($data as $row){
                $row['data'] = json_decode($row['data'],true);
                if(Yii::$app->cache->push(self::META_KAFKA_DATA,$row['data'],false)){
                    $update[] = $row['id'];  
                }  
            }
            if(!empty($update)){
                yii::$app->db->enableSlaves = false; // 禁用从库查询，从主库查询数据.
                yii::$app->db->createCommand('update '.self::tableName().' set status = 1 where id in ('.implode(',', $update).')')->execute();
            }
        }

    }

    /**
     * 处理kafka数据  
     * @return bool
     */   
    public function handle()
    {


        $maxProcessCount = 1000;
        $this->status = 0;
        $count = 1;
        $keyList = []; 
        // 程序结束 重复键 减 1
        register_shutdown_function(function()use($keyList){
            if(!empty($keyList)){
                foreach ($keyList as  $value) {
                    yii::$app->cache->delete($value);
                }
            }  
        });

        $total = yii::$app->cache->llen(self::META_KAFKA_DATA);
        $validCount = $doneCount = $penddingCount = $mogoFailedCount = 0;

        while (  $total >= $count) {
            $count++;
            $data = yii::$app->cache->pop(self::META_KAFKA_DATA); //从redis列表抛出一条数据

            $row = is_object($data) ? (array)$data : $data;
            if(empty($row)){
                exit('没有需要处理的数据');
            }
            if(!empty($row['traceID'])){
                $key = $row['traceID'];
            }elseif(!empty($row['token'])){
                $key = $row['token'];
            } elseif (!empty($row['sourceId'])) {            // 事前风控
                $key = $row['sourceId'];
            } else{
                $key = $row['paySn'];
            }


            $times = yii::$app->cache->increment($key);

            if($times > 1){
                yii::$app->cache->decrement($key);
                yii::$app->cache->push(self::META_KAFKA_DATA,$data);
                continue;
            }else{
                yii::$app->cache->expire($key,60);
                $keyList[] = $key; 
            }
            
            $method = $row['method'];

            if(method_exists( $this, $method)){
                $row = $this->$method($row); //处理数据格式
            }

            if(empty($row) || (empty($row['parentOrderSn']) && empty($row['paySn']) && empty($row['traceID']))){
                yii::$app->cache->decrement($key);
                continue;
            }

            $validCount++;
            $base =  $this->base($row);


            if(!empty($base)){//处理基础数据
                $saveData = array_merge($row,$base);

                if($saveData['method'] == 'webInfo'){
                    $collects = [
                        self::COLLECTION_BEHAVIOR.$saveData['logDate'],
                    ];  
                } elseif (in_array($saveData['method'], ["codRiskControl", "codInternalRiskControl"])) {
                    $collects = [
                        self::COLLECTION_COD_RISK . $saveData['logDate'],
                    ];
                }
                elseif($saveData['logSubType'] == self::LOGSUBTYPE_TOPAYPAGE){
                    $collects = [
                        self::COLLECTION_SUBMITDATE_LAST_MONTH,
                        self::COLLECTION_ORDERDATE_LAST_MONTH,
                        self::COLLECTION_ORDERDATE.$saveData['orderDate'],
                    ];
                } elseif ($saveData['method'] == 'toCheckout') {
                    $collects = [
                        self::COLLECTION_TO_CHECKOUT.$saveData['logDate'],
                    ];
                } else{
                    $collects = [
                        self::COLLECTION_ORDERDATE.$saveData['orderDate'],
                        self::COLLECTION_SUBMITDATE.$saveData['submitDate'],
                        self::COLLECTION_SUBMITDATE_LAST_MONTH,
                        self::COLLECTION_ORDERDATE_LAST_MONTH
                    ];
                }
                /**
                 *
                 *
                 * 数据入基础数据聚合 $this->saveData()
                 * orderDate_日期
                 * submitDate_日期
                 * submitDate_last_month 历史日志
                 * orderDate_last_month 历史日志
                 *
                 * 备注：submitData 不包含 toPayPage 的埋点数据
                 *
                 *
                 * 数据入消前表数据聚合 $this->saveOrder();
                 *
                 * orderDate_last_payinfo_日期
                 * submitDate_last_payinfo_日期
                 * orderDate_last_payinfo_last_month
                 * submitDate_last_payinfo_last_month
                 *
                 *
                 */
                $status =$this->saveData($collects,$saveData);
                if(!empty($status)){//保存成功查询待处理队列
                    if($saveData['method'] == 'riskControl'){
                        $this->handleRiskMax($base['parentOrderSn']);
                        $this->AddRiskRecordToMysql($saveData);
                    }
                    $this->handlePendingData($row);

                    // 把toPayPage写入到set中，用来与gather对比数据
                    if ('toPayPage' == $saveData['method'] && !empty($saveData['parentOrderSn']) && !empty($saveData['gatherDate'])) {
                        $gatherKey = "handle_{$saveData['siteCode']}_{$saveData['gatherDate']}";
                        yii::$app->cache->sAdd($gatherKey, $saveData['parentOrderSn']);
                    }


                    $doneCount++;
                }else{//插入失败 push回队列
                    $mogoFailedCount ++;
                    yii::$app->cache->push(self::META_KAFKA_DATA,$data);
                }

            }
            else{
                $penddingCount ++;
                yii::$app->cache->decrement($key);
                if ($validCount > $maxProcessCount) {
                    break;
                }
                continue;
            }
            yii::$app->cache->decrement($key);
            if ($validCount > $maxProcessCount) {
                break;
            }
        }
        echo "totalCount => {$count}\nvalidCount => {$validCount}\ndoneCount => {$doneCount}\n";
        echo "penddingCount => {$penddingCount}\nmogoFailedCount => {$mogoFailedCount}\n";
    }

    /**
     * 保存待处理数据到redis
     * @param  array  待处理数据
     * @return  bool   
     */
    public function savePenddingData($data){
        switch ($data['method']) {
            case 'toPay':       // fall through
            case 'webInfo':
                $key = self::META_PENDING_KEY.'token:'.$data['token'];
                break;
            case 'payInfo': case 'internalRiskControl':
                $key = self::META_PENDING_KEY.'traceID:'.$data['traceID'];
                break;
            case 'payResult':
                $key = self::META_PENDING_KEY.'traceID:payResult:'.$data['traceID'];
                break;
            case 'riskControl':
                $key = self::META_PENDING_KEY.'traceID:riskControl:'.$data['traceID'];
                break;
            default:
                $key = self::META_PENDING_KEY.'paySn:'.$data['paySn'];
                break;
        }
        yii::$app->cache->sAdd($key,json_encode($data)); // 保存Redis无序集合,集合成员是唯一
        yii::$app->cache->zAdd(self::META_KAFKA_PENDING_KEY,time(),$key);  //有序集合
    }

    /**
     * 处理待处理数据
     * @param  array  处理成功数据
     * @return  bool   
     */
    private function handlePendingData($data)
    {
        switch ($data['method']) {
            case 'toPayPage':
                $key = self::META_PENDING_KEY.'token:'.$data['token'];
                $pending = yii::$app->cache->sMembers($key);
                break;
            case 'toPay':
                $key = self::META_PENDING_KEY.'traceID:'.$data['traceID'];
                $pending = yii::$app->cache->sMembers($key);
                break;    
            case 'payInfo':
                // 风控
                $key = [];
                $keyRisk = self::META_PENDING_KEY.'traceID:riskControl:'.$data['traceID'];
                $pendingRisk = yii::$app->cache->sMembers($keyRisk);
                if (!empty($pendingRisk)) {
                    $key[] = $keyRisk;
                }
                // payResult
                $keyPayResult = self::META_PENDING_KEY.'traceID:payResult:'.$data['traceID'];
                $pendingPayResult = yii::$app->cache->sMembers($keyPayResult);
                if (!empty($pendingPayResult)) {
                    $key[] = $keyPayResult;
                }
                // 具有paySn的数据
                $keyPay = self::META_PENDING_KEY.'paySn:'.$data['paySn'];
                $pendingPay = yii::$app->cache->sMembers($keyPay);
                if (!empty($pendingPay)) {
                    $key[] = $keyPay;
                }
                $pending = array_merge($pendingRisk, $pendingPayResult, $pendingPay);
                break;
        }
        $fails = [];
        if(!empty($pending)){
            foreach ($pending as $value) {
                $row =  json_decode((is_object($value) ? (array)$value : $value),true);
                if(empty($row)){
                    exit('没有需要处理的数据');
                }
                if($row['method'] == $data['method']){
                    continue;
                }
                $method = $row['method'];
                if(method_exists( $this, $method)){
                    $row = $this->$method($row); //处理数据格式
                }
                if(empty($row) || (empty($row['parentOrderSn']) && empty($row['paySn']) && empty($row['traceID']))){
                    continue;
                }
                $base =  $this->base($row);
                if(!empty($base)){//处理基础数据
                    $saveData = array_merge($row,$base);
                    if($saveData['method'] == 'webInfo'){
                        $collects = [
                            self::COLLECTION_BEHAVIOR.$saveData['logDate'],
                        ];
                    }
                    elseif($saveData['logSubType'] == self::LOGSUBTYPE_TOPAYPAGE){  //处理 toPayPage 页面埋点数据
                        $collects = [
                            self::COLLECTION_SUBMITDATE_LAST_MONTH,
                            self::COLLECTION_ORDERDATE_LAST_MONTH,
                            self::COLLECTION_ORDERDATE.$saveData['orderDate'],
                        ];
                    }else{
                        $collects = [
                            self::COLLECTION_ORDERDATE.$saveData['orderDate'],
                            self::COLLECTION_SUBMITDATE.$saveData['submitDate'], //处理toPayPage 页面埋点数据无需添加入submitDate 数据
                            self::COLLECTION_SUBMITDATE_LAST_MONTH,
                            self::COLLECTION_ORDERDATE_LAST_MONTH
                        ];
                    }
                    $status =$this->saveData($collects,$saveData);
                    if(!empty($status)){//保存成功查询待处理队列
                        if($saveData['method'] == 'riskControl'){
                            $this->handleRiskMax($base['parentOrderSn']);
                            $this->AddRiskRecordToMysql($saveData);
                        }
                        if(in_array($row['method'], ['toPay','payInfo'])){
                            $this->handlePendingData($row);
                        }
                    }else{//插入失败 push回队列
                        $fails[] = $value;
                    }

                }
                else{
                    $fails[] = $value;
                }
            }
            if (!is_array($key)) {
                $key = [$key];
            }
            foreach ($key as $item) {
                yii::$app->cache->delete($item); //删除原集合
            }

            yii::$app->cache->zrem(self::META_KAFKA_PENDING_KEY,$key);
            if(!empty($fails)){//将失败的插入redis集合
                foreach ($fails as $val) {
                    $value =  json_decode((is_object($val) ? (array)$val : $val),true);
                    $requestTime = strlen($value['requestTime'])>11 ? $value['requestTime']/1000 : $value['requestTime']; 
                    if($requestTime < (time() - 3*24*3600) ){//3天前的数据不要
                        yii::$app->cache->sAdd($key,$val);
                        yii::$app->cache->zAdd(self::META_KAFKA_PENDING_KEY,time(),$key);
                    }
                }
            }
        }
    }

    /**
     * 处理待处理数据
     *
     */
    public function processPendingData(){
        $this->status = 1;
        $keys = yii::$app->cache->zRangeByScore(self::META_KAFKA_PENDING_KEY,0,time(),['limit'=>[1,50]]);
        
        if(!empty($keys)){
            foreach ($keys as $key => $val) {
                $data = yii::$app->cache->sMembers($val);
                $fails = [];
                if(!empty($data)){
                    foreach ($data as $value) {
                        $row =  json_decode((is_object($value) ? (array)$value : $value),true);

                        if(empty($row)){
                            exit('没有需要处理的数据');
                        }
                        $method = $row['method'];
                        if(method_exists( $this, $method)){
                            $row = $this->$method($row); //处理数据格式
                        }
                        if(empty($row) || (empty($row['parentOrderSn']) && empty($row['paySn']) && empty($row['traceID']))){
                            continue;
                        }
                        $base =  $this->base($row);
                        if(!empty($base)){//处理基础数据
                            $saveData = array_merge($row,$base);
                            if($saveData['method'] == 'webInfo'){
                                $collects = [
                                    self::COLLECTION_BEHAVIOR.$saveData['logDate'],
                                ];
                            }
                            elseif($saveData['logSubType'] == self::LOGSUBTYPE_TOPAYPAGE){
                                $collects = [
                                    self::COLLECTION_SUBMITDATE_LAST_MONTH,
                                    self::COLLECTION_ORDERDATE_LAST_MONTH,
                                    self::COLLECTION_ORDERDATE.$saveData['orderDate'],
                                ];
                            }else{
                                $collects = [
                                    self::COLLECTION_ORDERDATE.$saveData['orderDate'],
                                    self::COLLECTION_SUBMITDATE.$saveData['submitDate'],
                                    self::COLLECTION_SUBMITDATE_LAST_MONTH,
                                    self::COLLECTION_ORDERDATE_LAST_MONTH
                                ];
                            }
                            $status =$this->saveData($collects,$saveData);
                            if(!empty($status)){//保存成功查询待处理队列
                                if($saveData['method'] == 'riskControl'){
                                    $this->handleRiskMax($base['parentOrderSn']);
                                    $this->AddRiskRecordToMysql($saveData);
                                }
                                if(in_array($row['method'], ['toPay','payInfo'])){
                                    $this->handlePendingData($row);
                                }
                            }else{//插入失败 push回队列
                                $fails[] = $value;
                            }
                        }
                        else{
                            $fails[] = $value;
                        }  
                    }
                    yii::$app->cache->delete($val); //删除原集合
                    yii::$app->cache->zrem(self::META_KAFKA_PENDING_KEY,$val);
                    if(!empty($fails)){//将失败的插入redis集合
                        foreach ($fails as $row) {
                            $value =  json_decode((is_object($row) ? (array)$row : $row),true);
                            $requestTime = strlen($value['requestTime'])>11 ? $value['requestTime']/1000 : $value['requestTime']; 
                            if($requestTime < (time() - 3*24*3600) ){//3天前的数据不要
                                yii::$app->cache->sAdd($val,$row);
                                yii::$app->cache->zAdd(self::META_KAFKA_PENDING_KEY,time(),$val);
                            }
                            
                        }
                    }
                }else{
                    yii::$app->cache->zrem(self::META_KAFKA_PENDING_KEY,$val);
                    continue;
                }
            }
        }
    }

    /**
     * 处理失败的数据 (废弃)
     * @return void
     */
    public function process($data=[])
    {
        $this->status = 1;
        if(empty($data)){
            $data = Yii::$app->MongoAdapter->getAll(self::COLLECTION_PENDING,[],['handleTime'=>1],'100');
        }
        
        if(empty($data)){
            exit('没有需要处理的数据');
        }
        foreach($data as $row){
            if(empty($row)){
                break;
            }
            $id = $row['_id'];
            unset($row['_id'],$row['handleTime']);
            $method = $row['method'];
            if(method_exists( $this, $method )){
                $row = $this->$method($row); //处理数据格式
            }
            $order = $this->base($row);
            if(!empty($order)){//处理基础数据
                $saveData = array_merge($row,$order);  
            }
            else{
                Yii::$app->MongoAdapter->update(self::COLLECTION_PENDING,['_id'=>$id],['handleTime'=>time()]);
                continue;
            }

            $this->saveData($saveData);//保存数据
            Yii::$app->MongoAdapter->remove(self::COLLECTION_PENDING,$row); //从未处理集合中删除
        }      
    }

    /**
     * 处理公共的基础数据
     * @param   array  data  元数据
     * @return  array
     */

    private function base($data)
    {
        $retAddition = [];      // 额外的返回数据

        switch ($data['method']){
            case 'webInfo'://用户行为
                if(empty($data['token'])){
                    return false;
                }
                $list = $this->selectData($data,['$and'=>[['token'=>$data['token']],['method'=>'toPayPage']]]);
                $time = !empty($data['time']) ? (array)$data['time'] : [];
                if(!empty($list)){
                    if (is_string($data['validateResult'])) {
                        $data['validateResult'] = json_decode($data['validateResult'], true);
                        $isValidateFailed = false;      // “前端校验失败”是否有效，如果校验结果为空，我们不认为有效，其他情况才有效
                        if (is_array($data['validateResult'])) {
                            foreach ($data['validateResult'] as $validateItem) {
                                if (isset($validateItem['type']) && $validateItem['type'] != 'required') {
                                    $isValidateFailed = true;
                                }
                            }
                        }
                        if ($isValidateFailed) {
                            $order = $data;
                            $order['parentOrderSn'] = $list['parentOrderSn'];
                            $this->dealPageValidate($order);
                        }
                    }

                    if (is_string($data['nativePayEvent'])) {
                        $data['nativePayEvent'] = json_decode($data['nativePayEvent'], true);
                    }
                    if (is_array($data['nativePayEvent'])) {
                        foreach ($data['nativePayEvent'] as $idx => $item) {
                            if (strstr($idx,"Time") && strlen($item) > 11) {
                                $item = substr($item, 0, 11);
                            }
                            if (is_numeric($item)) {
                                $data['nativePayEvent'][$idx] = $item * 1;
                            }
                        }

                        if (isset($data['nativePayEvent']['Average_FPS'])) {        // 部分app的平均帧数命名可能不一致，在这里改为一致
                            $data['nativePayEvent']['averageFps'] = $data['nativePayEvent']['Average_FPS'];
                            unset($data['nativePayEvent']['Average_FPS']);
                        }
                    }

                    $ret = [
                        'token'=>$data['token'],
                        'traceID'=>$data['traceID'],
                        'actionCode'=> $data['actionCode'], //操作类型 点击：click, 验证：validate
                        'actionValue'=>$data['actionValue'],//操作详情
                        'actionValueCode'=>isset(self::$behaviorAction[$data['actionValue']]) ? self::$behaviorAction[$data['actionValue']] : 0,
                        'logTime' => $data['actionTime'],
                        'logStoredTime'=> time(),
                        'logDate'=> intval(gmToChinaDate('Ymd',$data['requestTime'])),
                        'logHour'=> intval(gmToChinaDate('H',$data['requestTime'])),
                        'siteCode'=>$list['siteCode'],
                        'parentOrderSn'=>$list['parentOrderSn'],
                        'userEmail'=>$list['userEmail'],
                        'orderAmount'=>$list['orderAmount'],
                        'platform'=>$list['platform'],
                        'shippingCountryCode'=>$list['shippingCountryCode'],
                        'channelCode'=>$data['channelCode'],
                        'pipelineCode'=>$list['pipelineCode'],
                        'validateResult'=>$data['validateResult'],
                        'actionTime'=>$data['actionTime'],
                        'url'=>$data['url'],
                        'userAgent'=>$data['userAgent'],
                        'time'=>$data['time'], //json格式
                        'cardType'=>$data['cardType'],//卡类型
                        'apiExecuteTime'=> !empty($time['total']) ? $time['total'] : 0,//总耗时
                    ];
                    if (!empty($data['nativePayEvent'])) {
                        $ret['nativePayEvent'] = $data['nativePayEvent'];
                    }

                    if ('load' == $ret['actionCode']) {
                        $loadBehavior = array_merge($data, $ret);
                        $this->saveBehaviorLoadLast($loadBehavior);
                    } elseif ('nativeEvent' == $ret['actionCode']) {
                        $nativeEventBehavior = array_merge($data, $ret);
                        $this->saveBehaviorNativeEventFirst($nativeEventBehavior);
                    } else {
                        // do nothing
                    }
                    return $ret;
                }else{
                    return false;
                } 
                break;
            case 'toCheckout':
                $ret = [
                    'logStoredTime'=> time(),
                    'logDate'=> intval(gmToChinaDate('Ymd',$data['requestTime'])),
                    'logHour'=> intval(gmToChinaDate('H',$data['requestTime'])),
                ];
                return $ret;
                break;
            case 'internalRiskControl'://内部风控
                if(empty($data['traceID'])){
                    return false;
                }
                $data = $this->selectData($data,['$and'=>[['traceID'=>$data['traceID']],['method'=>'toPay']]]);
                $retAddition = [
                    'internalRiskScore'=>$data['internalRiskScore'],
                    'internalRiskDecision'=>$data['internalRiskDecision'],
                    'ruleName'=>$data['ruleName'],
                    'ruleType'=>$data['ruleType'],
                    'ruleResult'=>$data['ruleResult'],
                    'ruleChannel'=>$data['ruleChannel']

                ];
                $logSubType = self::LOGSUBTYPE_INTERNAL_RISK_CONTROL;
                break;
            case 'codInternalRiskControl':      // 事前内部风控
                if(empty($data['sourceId'])){
                    return false;
                }

                $codType = self::COD_TYPE_INTERNAL_RISK_CONTROL;
                $logDate = app()->bjTime->date('Ymd', $data['requestTime']) * 1;
                $ret = [
                    'logDate' => $logDate,
                    'codType' => $codType,
                    'fromTestEmail'=> MongodbHelper::isTestEmail($data['userEmail']),
                    'pipelineCode' => array_key_exists('pipelineCode', $data) && empty($data['pipelineCode']) ? $data['siteCode'] : $data['pipelineCode'],
                ];
                if (isset($data['ip'])) {
                    $ret['ipCountryCode'] = Helper::getIpCountryCode($data['ip']);
                }
                return $ret;
                break;
            case 'codRiskControl':              // 事前外部风控
                if(empty($data['sourceId'])){
                    return false;
                }

                $codType = self::COD_TYPE_RISK_CONTROL;
                $logDate = app()->bjTime->date('Ymd', $data['requestTime']) * 1;
                $ret = [
                    'logDate' => $logDate,
                    'codType' => $codType,
                    'fromTestEmail'=> MongodbHelper::isTestEmail($data['userEmail']),
                    'pipelineCode' => array_key_exists('pipelineCode', $data) && empty($data['pipelineCode']) ? $data['siteCode'] : $data['pipelineCode'],
                ];
                if (isset($data['ip'])) {
                    $ret['ipCountryCode'] = Helper::getIpCountryCode($data['ip']);
                }
                $extraData = $this->exchangeRiskStatus($data);
                $ret = array_merge($ret, $extraData);
                return $ret;
                break;

            case 'expressToPay': //PP快捷支付 到达支付
                if(empty($data['traceID'])){
                    return false;
                }
                $list = yii::$app->MongoAdapter->findOne(self::COLLECTION_ORDERDATE_LAST_MONTH,['token'=>$data['traceID']]);
                if(!empty($list['orderAmount'])){
                    $data['orderAmount'] = $list['orderAmount'];
                }
                $data['ip'] = !empty($data['ip']) ? $data['ip'] : $list['ip'];
                $data = array_merge($data,[
                    'logType'=>self::LOGTYPE_SOA_PAYMENT,
                    'logSubType'=>self::LOGSUBTYPE_EXPRESS_TO_PAY,
                    'submitDate' =>  intval(gmToChinaDate('Ymd',$data['requestTime'])),
                    'submitHour' =>  intval(gmToChinaDate('H',$data['requestTime'])),
                    'orderDate' =>  intval(gmToChinaDate('Ymd',$data['requestTime'])),
                    'orderHour' =>  intval(gmToChinaDate('H',$data['requestTime'])),
                    'logTime' => $data['requestTime'],
                    'logStoredTime'=> gmtime(),
                    'logDate'=> intval(gmToChinaDate('Y-m-d',$data['requestTime'])),
                    'logHour'=> intval(gmToChinaDate('H',$data['requestTime'])),
                    'ipCountryCode'=>Helper::getIpCountryCode($data['ip']),
                    'priceRange'=>$this->getPriceRange($data['orderAmount']),
                ]);
                Yii::$app->MongoAdapter->insert(self::COLLECTION_EXPRESS_DATA,$data);
                $pendingData = yii::$app->MongoAdapter->getAll(self::COLLECTION_EXPRESS_PENDDING_DATA,['traceID'=>$data['traceID']]);
                if(!empty($pendingData)){
                    foreach ($pendingData as $value) {
                        unset($value['_id']);
                        $order = array_merge($value,[
                            'logType'=>self::LOGTYPE_SOA_PAYMENT,
                            'logSubType'=> $value['method'] == 'expressPayInfo' ? self::LOGSUBTYPE_EXPRESS_PAYINFO : self::LOGSUBTYPE_EXPRESS_PAY_RESULT,
                            'submitDate' =>  $data['submitDate'],
                            'submitHour' =>  $data['submitHour'],
                            'orderDate'=>$data['orderDate'],
                            'orderHour'=>$data['orderHour'],
                            'logTime' => $value['requestTime'],
                            'logStoredTime'=> gmtime(),
                            'logDate'=> intval(gmToChinaDate('Y-m-d',$value['requestTime'])),
                            'logHour'=> intval(gmToChinaDate('H',$value['requestTime'])),
                            'ipCountryCode'=> $data['ipCountryCode'],
                            'orderAmount'=>$data['orderAmount'],
                            'priceRange' =>$data['priceRange'],
                            'orderCurrency'=>$data['orderCurrency'],
                            'platform'=>$data['platform'],
                            'pipelineCode'=>$data['pipelineCode'],
                            'siteCode'=>$data['siteCode'],
                        ]);
                        Yii::$app->MongoAdapter->insert(self::COLLECTION_EXPRESS_DATA,$order);
                    }
                    yii::$app->MongoAdapter->remove(self::COLLECTION_EXPRESS_PENDDING_DATA,['traceID'=>$data['traceID']]);
                }
                
                return false;
                break;
            case 'expressPayInfo'://PP快捷支付 支付详情
                 if(empty($data['traceID'])){
                    return false;
                }
                $order = Yii::$app->MongoAdapter->findOne(self::COLLECTION_EXPRESS_DATA,['$and'=>[['traceID'=>$data['traceID']],['method'=>'expressToPay']]]);
                if(empty($order)){//保存到待处理集合
                    Yii::$app->MongoAdapter->insert(self::COLLECTION_EXPRESS_PENDDING_DATA,$data);
                    return false;
                }
                $data = array_merge($data,[
                    'logType'=>self::LOGTYPE_SOA_PAYMENT,
                    'logSubType'=>self::LOGSUBTYPE_EXPRESS_PAYINFO,
                    'submitDate' =>  $order['submitDate'],
                    'submitHour' =>  $order['submitHour'],
                    'orderDate'=>$order['orderDate'],
                    'orderHour'=>$order['orderHour'],
                    'logTime' => $data['requestTime'],
                    'logStoredTime'=> gmtime(),
                    'logDate'=> intval(gmToChinaDate('Y-m-d',$data['requestTime'])),
                    'logHour'=> intval(gmToChinaDate('H',$data['requestTime'])),
                    'ipCountryCode'=> $order['ipCountryCode'],
                    'orderAmount'=>$order['orderAmount'],
                    'priceRange' =>$data['priceRange'],
                    'orderCurrency'=>$order['orderCurrency'],
                    'platform'=>$order['platform'],
                    'pipelineCode'=>$order['pipelineCode'],
                    'siteCode'=>$order['siteCode'],
                ]);
                Yii::$app->MongoAdapter->insert(self::COLLECTION_EXPRESS_DATA,$data);
                return false;
                break;

            case 'expressPayResult': //PP快捷支付 支付响应
                if(empty($data['traceID'])){
                    return false;
                }
                $order = Yii::$app->MongoAdapter->findOne(self::COLLECTION_EXPRESS_DATA,['$and'=>[['traceID'=>$data['traceID']],['method'=>'expressToPay']]]);
                if(empty($order)){//保存到待处理集合
                    Yii::$app->MongoAdapter->insert(self::COLLECTION_EXPRESS_PENDDING_DATA,$data);
                    return false;
                }
                $data = array_merge($data,[
                    'logType'=>self::LOGTYPE_SOA_PAYMENT,
                    'logSubType'=>self::LOGSUBTYPE_EXPRESS_PAY_RESULT,
                    'submitDate' =>  $order['submitDate'],
                    'submitHour' =>  $order['submitHour'],
                    'orderDate'=>$order['orderDate'],
                    'orderHour'=>$order['orderHour'],
                    'logTime' => $data['requestTime'],
                    'logStoredTime'=> gmtime(),
                    'logDate'=> intval(gmToChinaDate('Y-m-d',$data['requestTime'])),
                    'logHour'=> intval(gmToChinaDate('H',$data['requestTime'])),
                    'ipCountryCode'=> $order['ipCountryCode'],
                    'orderAmount'=>$order['orderAmount'],
                    'priceRange' =>$data['priceRange'],
                    'orderCurrency'=>$order['orderCurrency'],
                    'platform'=>$order['platform'],
                    'pipelineCode'=>$order['pipelineCode'],
                    'siteCode'=>$order['siteCode'],
                ]);
                Yii::$app->MongoAdapter->insert(self::COLLECTION_EXPRESS_DATA,$data);
                return false;
                break;           
            case 'syncStatus':
                if(empty($data['paySn'])){
                    return false;
                }
                $data = $this->selectData($data,['$and'=>[['paySn'=>$data['paySn']],['method'=>'payInfo']]]);  //payInfo   //第一次路由

                //$data = $this->selectData($data,['$and'=>[['paySn'=>$data['paySn']],['method'=>'payResult']]]);  //payResult 第二次路由

                $logSubType = self::LOGSUBTYPE_SYNC_STATUS;
                if(!empty($data['payStatus']) && $data['payStatus'] == 2){

                    //第一次路由
                    $this->updateData('update',['paySn'=>$data['paySn']],['payStatus'=>$data['payStatus']]);//更新集合
                }
                $retAddition['traceID'] = $data['traceID'];
                break;
            case 'notify':
                if(empty($data['paySn'])){
                    return false;
                }
                $originData = $data;
                $data = $this->selectData($data,['$and'=>[['paySn'=>$data['paySn']],['method'=>'payInfo']]]);
                $updateData = ['hasNotify' => 1];
                if(!empty($data['payStatus']) && $data['payStatus'] == 2){
                    $updateData['payStatus'] = $data['payStatus'];
                }
                $this->updateData('update',['paySn'=>$data['paySn']], $updateData);//更新集合
                $this->handleInstalmentSubsidy($data);      // 处理分期贴息
                $retAddition['traceID'] = $data['traceID'];
                $logSubType = self::LOGSUBTYPE_NOTIFY;

                $threeDData = array_merge($originData, $data);
                $this->handleThreesResult($threeDData, $retAddition);     // 处理3D结果
                break;
            case 'threedsResult':
                if(empty($data['paySn'])){
                    return false;
                }
                $data = $this->selectData($data,['$and'=>[['paySn'=>$data['paySn']],['method'=>'toPay']]]);
                $logSubType = self::LOGSUBTYPE_THREES_RESULT;
                $this->doHandleThreedsResult($data, $retAddition);
                break;
            case 'payResult':
                if(empty($data['traceID'])){
                    return false;
                }
                $data = $this->selectData($data,['$and'=>[['traceID'=>$data['traceID']],['method'=>'payInfo']]]);

                $logSubType = self::LOGSUBTYPE_PAY_RESULT;
                if(isset($data['payStatus'])){
                    $updateData = ['cardType'=>$data['cardType'],'cardFundingSource'=>$data['cardFundingSource'],'payStatus'=>$data['payStatus']];
                    if (in_array($data['channelCode'], ["ebanxinstalment", 'EBX_MXCC']) && 2 == $data['payStatus']) {   // 更新分期贴息信息
                        $updateData['gatewayCurrencyRate'] = isset($data['gatewayCurrencyRate']) ? $data['gatewayCurrencyRate'] : "";
                        $updateData['gatewayRealAmount'] = isset($data['gatewayRealAmount']) ? $data['gatewayRealAmount'] : "";
                    }
                    $this->updateData('update', ['traceID'=>$data['traceID']], $updateData);//更新集合
                    if(!empty($data['riskChannel']) && $data['riskChannel'] !='none' && strtolower($data['riskChannel']) != 'cybersource' ){//内置风控
                        $this->handleRiskData($data); //处理网关内置风控   
                    }
                    $this->handleInstalmentSubsidy($data);      // 处理分期贴息
                }

                if (isset($data['need3ds']) && 1 == $data['need3ds']) {     // 针对3d数据，进行排重标记。
                    $threeDWhere = [
                        "parentOrderSn" => $data['parentOrderSn'],
                        "logSubType" => self::LOGSUBTYPE_PAY_RESULT,
                        "need3ds" => 1
                    ];
                    $threeDData = ["is3DUniqueId" => 0];
                    $this->updateData('update',$threeDWhere,$threeDData);
                    $retAddition['is3DUniqueId'] = 1;
                }
                break;
            case 'riskControl':
                if(empty($data['traceID'])){
                    return false;
                }
                $data = $this->selectData($data,['$and'=>[['traceID'=>$data['traceID']],['method'=>'payInfo']]]);   // 风控在payInfo之后才处理
                if(empty($data)){
                    return false;
                }
                $status = $this->exchangeRiskStatus($data);
                $retAddition = $status;
                $retAddition['is_max'] = 1;
                $data = array_merge($data,$status);
                $this->updateData('update',['traceID'=>$data['traceID']],['riskChannel'=>$data['riskChannel']]);
                $logSubType = self::LOGSUBTYPE_RISK;
                break;
            case 'payInfo':

                if(empty($data['traceID'])){
                    return false;
                }
                $data['currencyChannel'] = !empty($data['FX_rate_provider']) ? strtolower($data['FX_rate_provider']) : 'yahoo';
                $data = $this->selectData($data,['$and'=>[['traceID'=>$data['traceID']],['method'=>'toPay']]]);

                if(!empty($data)){
                    $this->updateData('update',['traceID'=>$data['traceID']],['channelCode'=>$data['channelCode'],'firstChannelCode'=>$data['firstChannelCode'],'paySn'=>$data['paySn'],'sameAsFirstChannel'=>$data['sameAsFirstChannel'],'isWallet'=>$data['isWallet'],'pipelineCode'=>$data['pipelineCode'],'walletAmount'=>$data['walletAmount']]);
                    $retAddition['currencyChannel'] = $data['currencyChannel'];
                    $retAddition['platformOrderAmount'] = $data['platformOrderAmount'];
                    $retAddition['marketOrderAmount'] = $data['marketOrderAmount'];
                    if($data['isWallet'] == 1){
                        $this->updateData('update',
                            [
                                'parentOrderSn'=>$data['parentOrderSn'],
                                'requestTime'=>['$gte'=>$data['requestTime']],
                                'logSubType' => ['$gte' => self::LOGSUBTYPE_PAYINFO],
                            ],
                            [
                                'isWallet'=>$data['isWallet'],
                                'walletAmount'=>$data['walletAmount'],
                            ]
                        );
                    }
                }
                $logSubType = self::LOGSUBTYPE_PAYINFO;
                break;
            case 'toPay':

                if(empty($data['token'])){
                    return false;
                }

                //获取toPayPage数据，第一步骤数据
                $data = $this->selectData($data,['$and'=>[['token'=>$data['token']],['method'=>'toPayPage']]]);

                if(!empty($data)){
                    $this->updateData('update',
                        ['token'=>$data['token']],
                        [
                            'submitDate'=>$data['submitDate'],
                            'submitHour'=>$data['submitHour'],
                        ]
                    );
                }
                $logSubType = self::LOGSUBTYPE_TO_PAY;
                break;
            case 'toPayPage':
                $logSubType = self::LOGSUBTYPE_TOPAYPAGE;
                // 进入支付页面时，根据订单号判断第一次进入支付页面时间
                $parentOrderSn = empty( $data['parentOrderSn'] ) ? "" : $data['parentOrderSn'];

                if( $parentOrderSn )
                {
                    if(empty($data['ip'])){
                        $express = yii::$app->MongoAdapter->findOne(self::COLLECTION_EXPRESS_DATA,['traceID'=>$data['token']]);
                        $data['ip'] = isset($express['ip']) ? $express['ip'] : '';
                    }
                    $orderTime = $this->getOrderTime( $parentOrderSn );
                    $domain = !empty($data['userEmail']) ? explode('@',strtolower($data['userEmail'])) : '';
                    $data = array_merge( $data, [
                        'orderDate'=> intval( empty( $orderTime ) ? gmToChinaDate('Ymd',$data['requestTime']) : gmToChinaDate('Ymd',$orderTime) ),
                        'orderHour'=> intval(empty( $orderTime ) ? gmToChinaDate('H',$data['requestTime']) :  gmToChinaDate('H',$orderTime)) ,
                        'ipCountryCode' => Helper::getIpCountryCode($data['ip']),
                        'fromTestEmail'=> MongodbHelper::isTestEmail($data['userEmail']),
                        'pipelineCode' => empty($data['pipelineCode']) ? $data['siteCode'] : $data['pipelineCode'],
                        'shippingCountryCode' => empty($data['shippingCountryCode']) ? '' : $data['shippingCountryCode'],
                        'swellOrderAmount' => empty($data['swellOrderAmount']) ? null : $data['swellOrderAmount'],
                        'domain' => empty($domain[1]) ? '' : $domain[1],
                    ] );
                }

                //更新快速付款订单金额
                $priceRange = $this->getPriceRange($data['orderAmount']);
                yii::$app->MongoAdapter->update(self::COLLECTION_EXPRESS_DATA,['traceID'=>$data['token']],['orderAmount'=>$data['orderAmount'],'priceRange'=>$priceRange]);
                break;
            default:
                $logSubType = self::LOGSUBTYPE_UNKNOWN;
                break;
        }
        if( empty($data['siteCode']) || empty($data['parentOrderSn']) ){
            return false;
        }
        
        $data['logSubType'] = $logSubType;
        $data['priceRange'] = empty($priceRange) ?  $this->getPriceRange($data['orderAmount']) : $priceRange;
        //保存数据至消前元数据表
        $this->saveOrder($data);
        $domain = !empty($data['userEmail']) ? explode('@',strtolower($data['userEmail'])) : '';
        $ret = [
            'logType'=> self::LOGTYPE_SOA_PAYMENT,
            'logSubType'=> $logSubType,
            'logTime' => $data['requestTime'],
            'logStoredTime'=> gmtime(),
            'logDate'=> gmToChinaDate('Y-m-d',$data['requestTime']),
            'logHour'=> intval(gmToChinaDate('H',$data['requestTime'])),
            'siteCode' => !empty($data['siteCode']) ? $data['siteCode'] : '',
            'parentOrderSn' => !empty($data['parentOrderSn']) ? $data['parentOrderSn'] : '',
            'orderAmount' => !empty($data['orderAmount']) ? $data['orderAmount'] : 0.00,
            'priceRange' => $data['priceRange'],
            'walletAmount' => !empty($data['walletAmount']) ? $data['walletAmount'] : 0.00,
            'platform' => !empty($data['platform']) ? $data['platform'] : 1,
            'shippingCountryCode' => !empty($data['shippingCountryCode']) ? $data['shippingCountryCode'] : '',
            'shippingCity' => !empty($data['shippingCity']) ? $data['shippingCity'] : '',
            'shippingState' => !empty($data['shippingState']) ? $data['shippingState'] : '',
            'billingCountryCode' => !empty($data['billingCountryCode']) ? $data['billingCountryCode'] : '',
            'billingState' => !empty($data['billingState']) ? $data['billingState'] : '',
            'billingCity' => !empty($data['billingCity']) ? $data['billingCity'] : '',
            'cardNo' => !empty($data['cardNo']) ? $data['cardNo'] : '',
            'cardNoCopy' => !empty($data['cardNoCopy']) ? $data['cardNoCopy'] : '',
            'channelCode'=> !empty($data['channelCode']) ? $data['channelCode'] : '',
            'isWallet' => !empty($data['isWallet']) ? $data['isWallet'] : 0,
            'orderDate'=> !empty($data['orderDate']) ? $data['orderDate'] : 0,
            'orderHour'=> !empty($data['orderHour']) ? $data['orderHour'] : 0,
            'submitDate' => !empty($data['submitDate']) ? $data['submitDate'] : 0,
            'submitHour' => !empty($data['submitHour']) ? $data['submitHour'] : 0,
            'firstChannelCode'=>!empty($data['firstChannelCode']) ? $data['firstChannelCode'] : '',
            'cardType' => !empty($data['cardType']) ? $data['cardType'] : 'none',
            'cardFundingSource' => !empty($data['cardFundingSource']) ? $data['cardFundingSource'] : 'none',
            'riskChannel' => !empty($data['riskChannel']) ? $data['riskChannel'] : '',
            'paySn'=> !empty($data['paySn']) ?  $data['paySn'] : '',
            'sameAsFirstChannel' => !empty($data['sameAsFirstChannel']) ? $data['sameAsFirstChannel'] : 0,
            'userEmail'=> !empty($data['userEmail']) ? strtolower($data['userEmail']) : '',
            'cardSubType'=> $data['cardSubType'],
            'pipelineCode'=> $data['pipelineCode'],
            'ip'=>$data['ip'],
            'ipCountryCode'=>$data['ipCountryCode'],
            'fromTestEmail'=>$data['fromTestEmail'],
            'payStatus'=>$data['payStatus'],
            'orderCurrency'=>$data['orderCurrency'],

            'checkoutType' => $data['checkoutType'],
            'swellOrderAmount' => $data['swellOrderAmount'],
            'domain' => empty($domain[1]) ? '' : $domain[1],
        ];
        $ret = array_merge($ret, $retAddition);
        return $ret;
    }


    /** ==================================定金膨胀订单===================start===============
     * 获取膨胀数据
     * @author chenhuian
     * @param array $rawData  原始数据
     * @return array $data    返回数据
     *
    */
    private  function addSwellData($rawData = []){

        //定金膨胀订单处理公共数据-2018年11月1日16:19:1
        $payStep = [
            'toPayPage', // 1 toPayPage（进入支付页面
            'toPay',  // 2 toPay（客户提交支付请求）
            'payInfo',  // 3 payInfo（解析客户支付信息）
            'internalRiskControl',  // 内部风控）
            'riskControl',  // 4 riskControl（支付风控结果信息）
            'payResult',  // 5 payResult（支付网关结果信息）
            'threedsResult',  // 6 threedsResult（3DS或本地支付跳回页面）
            'notify',  // 7 notify（支付网关异步通知）
            'syncStatus',  // 8 syncStatus（支付信息同步到系统结果）
        ];
        $data = [];

        if( true && in_array($rawData['method'],$payStep)){
            $data = [
                'checkoutType'      => isset($rawData['checkoutType']) ? $rawData['checkoutType'] : -1,
                'swellOrderAmount'  => isset($rawData['swellOrderAmount']) ? $rawData['swellOrderAmount'] : 0.00,
            ];
        }
        return $data;
    }


    /**
     *
     * 添加消前条件
     *
     * @author chenhuian
     * @param array $where 原始条件
     * @param array $ext 额外系统
     * @return array $where 组合条件
     *
     *
    */
    private function addCheckOutType($where,$ext){
        if(is_array($where) && $ext){
            return array_merge($where,$ext);
        }
        return $where;
    }
    /**
     * ==================================定金膨胀订单===================end===============
    */



    /**
     * 获取mongodb 数据
     * @param   array   查询条件 ['traceID'=>$data['traceID'],'method'=>'toPay']
     * @return bool or  array
     */
    private function selectData($data,$where)
    {
        $order = Yii::$app->MongoAdapter->findOne(self::COLLECTION_ORDERDATE_LAST_MONTH,$where);


        if(!empty($order)){   //获取到支付页数据
            $data['channelCode'] = !empty($data['channelCode']) ?  $data['channelCode'] : (!empty($data['paymentGateway']) ?  $data['paymentGateway'] : '');
            //电子钱包金额为空时查询之前订单记录 20180625
            if($data['method'] == 'payInfo' && empty($data['walletAmount'])){//电子钱包金额为空时查询之前订单记录
                $walletAmount = Yii::$app->MongoAdapter->findOne(self::COLLECTION_ORDERDATE_LAST_MONTH,['parentOrderSn'=>$order['parentOrderSn'],'walletAmount'=>['$gt'=>0]]);
                $data['walletAmount'] = !empty($walletAmount['walletAmount']) ? $walletAmount['walletAmount'] : 0;
            }
            if(!empty($data['channelCode']) && $data['channelCode'] == 'WALLET'){
                $data['walletAmount'] = !empty($order['orderAmount']) ? $order['orderAmount'] : $data['walletAmount'];
            }
            if($data['method'] == 'payInfo'){
                $rate = self::getRate();
                $platform = !empty($rate['new'][SoaPaymentReport::RATE_PLATFORM][strtolower($order['siteCode'])][$data['currencyCode']]) ? $rate['new'][SoaPaymentReport::RATE_PLATFORM][strtolower($order['siteCode'])][$data['currencyCode']] : 1;
                $market = !empty($rate['new'][SoaPaymentReport::RATE_EXTERNAL][$data['currencyChannel']][$data['currencyCode']]) ? $rate['new'][SoaPaymentReport::RATE_EXTERNAL][$data['currencyChannel']][$data['currencyCode']] : 1;
                $data['platformOrderAmount'] = floatval($platform * ($data['payAmount']+$data['walletAmount']));
                $data['marketOrderAmount'] = floatval($market * ($data['payAmount']+$data['walletAmount']));

            }
            $data['siteCode'] = $order['siteCode'];
            $data['orderAmount'] = $order['orderAmount'];
            $data['parentOrderSn'] = $order['parentOrderSn'];
            $data['paySn'] = !empty($data['paySn']) ? $data['paySn'] : (!empty($order['paySn']) ? $order['paySn'] : '');
            $data['platform'] = $order['platform'];
            $data['requestTime'] = !empty($data['requestTime']) ? $data['requestTime'] : time();
            $data['shippingCountryCode'] = !empty($order['shippingCountryCode']) ? $order['shippingCountryCode'] : '' ;
            $data['shippingState'] = !empty($order['shippingState']) ? $order['shippingState'] : '' ;
            $data['shippingCity'] = !empty($order['shippingCity']) ? $order['shippingCity'] : '' ;
            if($data['method']  == 'payResult'){
                $data['channelCode'] =  $data['channelCode'];
            }else{
                $data['channelCode'] = $data['method']  == 'payInfo' ? $data['channelCode'] : (!empty($order['channelCode']) ? $order['channelCode'] : '');
            }
            $data['isWallet'] = !empty($data['walletAmount']) ? 1 : ($order['isWallet'] > 0 ?  1 : 0);
            $data['walletAmount'] = !empty($data['walletAmount']) ? $data['walletAmount'] : (!empty($order['walletAmount']) ? $order['walletAmount'] : 0.00);
            $data['orderDate'] = intval(!empty($order['orderDate']) ? $order['orderDate'] : 0);
            $data['orderHour'] = intval(!empty($order['orderHour']) ? $order['orderHour'] : 0);
            $data['submitDate'] = intval($data['method']  == 'toPay' ? gmToChinaDate('Ymd',$data['requestTime']) : (!empty($order['submitDate']) ? $order['submitDate'] : 0));
            $data['submitHour'] = intval($data['method']  == 'toPay' ? gmToChinaDate('H',$data['requestTime']) : (!empty($order['submitHour']) ? $order['submitHour'] : 0));
            $data['firstChannelCode'] = $data['method'] == 'payInfo' ? $this->getChannelCode($data) : (!empty($order['firstChannelCode']) ? $order['firstChannelCode'] : '');
            $data['pipelineCode'] =  !empty($order['pipelineCode']) ? $order['pipelineCode'] : (array_key_exists('pipelineCode', $data) && empty($data['pipelineCode']) ? $data['siteCode'] : $data['pipelineCode']);
            $data['cardType'] = $data['method'] == 'toPay' ? (!empty($data['cardType']) ? $data['cardType'] : 'none') : (!empty($order['cardType']) ?  $order['cardType'] : 'none');
            $cardType = $this->getCardType($data['cardType']);
            $data['cardType'] = $cardType['cardType'];
            $data['cardSubType'] = !empty($order['cardSubType']) ? $order['cardSubType'] : $cardType['cardSubType'];
            $data['cardFundingSource'] = $data['method'] == 'payResult' ? (!empty($data['cardFundingSource']) ? $data['cardFundingSource'] : 'none') : (!empty($order['cardFundingSource']) ?  $order['cardFundingSource'] : 'none');
            $data['cardFundingSource'] = $data['cardFundingSource'] == 'none' || empty($data['cardFundingSource']) ? $data['cardSubType'] : $data['cardFundingSource'];
            $data['sameAsFirstChannel'] = $data['channelCode'] == $data['firstChannelCode'] ? 1 : 0;
            $data['userEmail'] = !empty($order['userEmail']) ? $order['userEmail'] : (!empty($data['userEmail']) ? $data['userEmail'] : '');
            $data['ip'] = !empty($order['ip']) ? $order['ip'] : (!empty($data['ip']) ? $data['ip'] : '' );
            $data['ipCountryCode'] = !empty($order['ipCountryCode']) ? $order['ipCountryCode'] : Helper::getIpCountryCode($data['ip']);
            $data['fromTestEmail'] = !empty($order['fromTestEmail']) ? $order['fromTestEmail'] : MongodbHelper::isTestEmail($data['userEmail']);
            $data['payStatus'] = !empty($order['payStatus']) && $order['payStatus'] > $data['payStatus'] ? $order['payStatus'] : (empty($data['payStatus']) ? 1 : $data['payStatus']);
            $data['traceID'] = !empty($data['traceID']) ? $data['traceID'] : (!empty($order['traceID']) ? $order['traceID'] : '');
            $data['orderCurrency'] = !empty($data['orderCurrency']) ? $data['orderCurrency'] : (!empty($order['orderCurrency']) ? $order['orderCurrency'] : 'USD');

            $data['checkoutType'] = !empty($data['checkoutType']) ? $data['checkoutType'] : (!empty($order['checkoutType']) ? $order['checkoutType'] : 0);
            $data['swellOrderAmount'] = !empty($data['swellOrderAmount']) ? $data['swellOrderAmount'] : (!empty($order['swellOrderAmount']) ? $order['swellOrderAmount'] : null);

            if (empty($data['riskChannel'])) {      // 处理risk channel
                if($data['method'] == 'riskControl'){
					$data['riskChannel'] = !empty($data['riskChannel']) ? $data['riskChannel'] : 'cybersource';
				}
                if (empty($data['riskChannel'])) {
                    $data['riskChannel'] = (!empty($order['riskChannel'])) ? $order['riskChannel'] : 'none';
                }
            }
            if ($data['method'] != "toPay") {
                $data['billingCity'] = !empty($order['billingCity']) ? $order['billingCity'] : "";
                $data['billingCountryCode'] = !empty($order['billingCountryCode']) ? $order['billingCountryCode'] : "";
                $data['billingState'] = !empty($order['billingState']) ? $order['billingState'] : "";
                $data['cardNo'] = !empty($order['cardNo']) ? $order['cardNo'] : "";
                $data['cardNoCopy'] = !empty($order['cardNoCopy']) ? $order['cardNoCopy'] : "";
            }
            if (in_array($data['method'], ['payResult', 'notify'])) {
                $data['currencyCode'] = isset($order['currencyCode']) ? $order['currencyCode'] : "";
                $data['payCurrencyAmount'] = isset($order['payCurrencyAmount']) ? $order['payCurrencyAmount'] : "";
                if (isset($order['instalments'])) {
                    $data['instalments'] = $order['instalments'];
                }
                if (isset($order['baseInterestRate'])) {
                    $data['baseInterestRate'] = $order['baseInterestRate'];
                }
                if (isset($order['realInterestRate'])) {
                    $data['realInterestRate'] = $order['realInterestRate'];
                }
            }
            $domain = !empty($data['userEmail']) ? explode('@',strtolower($data['userEmail'])) : '';
            $data['domain'] = empty($domain[1]) ? '' : $domain[1];
            return $data;
        }else{
            if($this->status == 0){
                $this->savePenddingData($data);
            }
            
        }
        return [];
    }

    /**
    * 获取支付信息
    * @param $data   array 埋点信息
    * @return array
    */
    private function payResult($data)
    {
        $data['isPaid'] = !empty($data['payStatus']) && $data['payStatus'] == 2 ? 1 : 0;
        $data['isPending'] = !empty($data['payStatus']) && $data['payStatus'] == 1 ? 1 : 0;
        return $data;
    }

    /**
     * 保存订单数据（消前）
     *
     *  取值：
     *  0： 未定义
     *  1： toPayPage（进入支付页面）
     *  2： toPay（客户提交支付请求）
     *  3： payInfo（解析客户支付信息）
     *  4： riskControl（支付风控结果信息）
     *  5： payResult（支付网关结果信息）
     *  6： threedsResult（3DS或本地支付跳回页面）
     *  7： notify（支付网关异步通知）
     *  8： syncStatus（支付信息同步到系统结果）
     * @param   data  array
     */
    public function saveOrder($data = [])
    {
        


        if($data['logSubType'] != self::LOGSUBTYPE_TOPAYPAGE){//toPayPage没有submitDate
            $payinfoCollect = [
                self::COLLECTION_ORDERDATE_LAST_PAYINFO_LAST_MONTH,
                self::COLLECTION_SUBMITDATE_LAST_PAYINFO_LAST_MONTH,
                self::COLLECTION_SUBMITDATE_LAST_PAYINFO.$data['submitDate'],
                self::COLLECTION_ORDERDATE_LAST_PAYINFO.$data['orderDate']
            ];
        }else{
            $payinfoCollect = [
                self::COLLECTION_ORDERDATE_LAST_PAYINFO_LAST_MONTH,
                self::COLLECTION_SUBMITDATE_LAST_PAYINFO_LAST_MONTH,
                self::COLLECTION_ORDERDATE_LAST_PAYINFO.$data['orderDate']
            ];
        }


        //获取该条数据历史消前数据聚合
        $orderLogs = Yii::$app->MongoAdapter->getAll( self::COLLECTION_ORDERDATE_LAST_PAYINFO_LAST_MONTH, [
            'parentOrderSn' => $data['parentOrderSn'],
            'checkoutType'  => $data['checkoutType']  //区分订单类型
        ] );



        $orderLogs = !empty($orderLogs) ? $orderLogs : [];
        $doNothingMethods = [ self::LOGSUBTYPE_SYNC_STATUS ];

        // 处理前端校验失败标记  $data 一维数组
        $this->dealValidateOrder($data, $orderLogs, $payinfoCollect);

        /**
         * 描述场景：
         * 用户第一次点击PayNow产生toPayPage,toPay记录，但用户放弃支付；
         * 用户某一时间，单击未支付订单并且成功支付该笔订单。
         * first 第一次支付日志  current 当前支付日志
        */

        //当前数据
        if( $data['logSubType'] <= self::LOGSUBTYPE_TO_PAY )
        {
            $hasToPayPage = $hasPayInfo = false;

            //历史支付记录
            foreach( $orderLogs as $logItem )
            {
                if( $logItem["logSubType"] <= self::LOGSUBTYPE_TO_PAY )
                {
                    $hasToPayPage = true;
                }
                elseif( $logItem["logSubType"] > self::LOGSUBTYPE_TO_PAY )
                {
                    $hasPayInfo = true;
                }
                
                if( $hasToPayPage && $hasPayInfo )
                {
                    break;
                }
            }


            // 如果已经有toPayPage和其他支付动作，则先保存此次数据，但logSubType标记成负数
            if( $hasToPayPage && $hasPayInfo )
            {
                /**
                 * first保存1,2,3,4记录，当前没有payInfo时记录则保存当前记录并标记-1
                 * 如果有payInfo支付记录则会更新标示，见else分支
                 *
                 *  first current
                 *    1    1
                 *    2    2
                 *    3
                 *    4
                 */

                $data["logSubType"] *= -1;
            }
            // 然则代表没有支付动作，则删除所有历史记录
            elseif($hasToPayPage)
            {
                /**
                 * first保存1,2记录，当前如果没有payInfo记录则删除所有之前的记录
                 *
                 *  first current
                 *    1    1
                 *    2    2
                */
                $hasWhere =  [
                    'parentOrderSn' => $data['parentOrderSn'],
                    'requestTime' => [ '$lte' => $data['requestTime'] ],
                    'logSubType' =>['$gte'=>self::LOGSUBTYPE_TO_PAY],
                ];

                //定金订单不被消前处理
                DataCollect::IS_OPEN_SWELL_ORDER && $hasWhere  =  $this->addCheckOutType($hasWhere,['checkoutType'=>$data['checkoutType']]);

                $this->updateData('remove',$hasWhere,[],$payinfoCollect);
            }

            //保存当前数据
            $this->saveData($payinfoCollect,$data);
        }
        else
        {

            /**
             * 先判断当前数据是否最新的数据  $data 当前数据   $orderLogs 历史保存数据
             * numberOfTimes 当前TraceID中，第几次请求网关进行扣款
             *
             *  first current
             *    1
             *    2
             *    3    3
             *    4    4
             *         5
             */

            $isNewer = true;
            foreach( $orderLogs as $logItem )
            {
                if( $logItem["logSubType"] > self::LOGSUBTYPE_TO_PAY
                    && (empty($data['numberOfTimes']) || $data['numberOfTimes'] < 2)
                    && $logItem["logSubType"] <= $data["logSubType"]    // 支付动作： 3    <= 3
                    && $logItem["requestTime"] > $data["requestTime"]   // 请求时间：1:01  > 1:00  同时满足则不是新数据
                    && !empty($logItem['requestTime'])
                ){
                    $isNewer = false;
                    break;
                }
            }


            // 最新的数据处理，如果不是最新的数据，则直接丢弃
            if( $isNewer && !in_array( $data['logSubType'], $doNothingMethods ) )
            {

                // 支付网关异步通知的步骤   payStatus = 2 支付成功状态
                if( $data['logSubType'] != self::LOGSUBTYPE_NOTIFY || $data['payStatus'] == 2 )
                {

                    // 找出最新的toPayPage和toPay并更新first次logSubType负数为正数
                    $latestToPayPage = [];
                    $toPayPageAndToPayCount = 0;
                    foreach( $orderLogs as $logItem )
                    {
                        $toPayPageAndToPay = [ self::LOGSUBTYPE_TOPAYPAGE, -1 * self::LOGSUBTYPE_TOPAYPAGE,self::LOGSUBTYPE_TO_PAY,-1*self::LOGSUBTYPE_TO_PAY ];

                        if( in_array( $logItem["logSubType"], $toPayPageAndToPay ) )
                        {
                            $toPayPageAndToPayCount ++;
                            if( empty( $latestToPayPage[$logItem["logSubType"]] ) )
                            {
                                $latestToPayPage[$logItem["logSubType"]] = $logItem; //先保存
                            }
                            else
                            {
                                //获取历史中toPayPage,toPay最新记录
                                $logItem["requestTime"] > $latestToPayPage[$logItem["logSubType"]]["requestTime"] && $latestToPayPage[$logItem["logSubType"]] = $logItem;
                            }
                        }
                    }


                    // 只保留最新的那条toPayPage和toPay
                    if( $latestToPayPage )
                    {

                        if( $toPayPageAndToPayCount > 1 )
                        {
                            foreach($latestToPayPage as $row){

                                //原始条件
                                $where = [
                                    'parentOrderSn' => $data['parentOrderSn'],
                                    'logSubType' => [
                                        '$in' => [
                                            $row['logSubType'], -1 * $row['logSubType']
                                        ]
                                    ],
                                    'requestTime' => [
                                        '$lt' => $row['requestTime']  //删除小于最新日志的所有记录
                                    ],
                                ];

                                //定金订单不被消前处理，同一parentOrderSn且checkoutType订单结算类型区分
                                DataCollect::IS_OPEN_SWELL_ORDER && $where  = $this->addCheckOutType($where,['checkoutType'=>$row['checkoutType']]);

                                $this->updateData('remove', $where,[],$payinfoCollect);
                            }
                        }

                        /**
                         * 由于支付通知结果延迟或者kafka分支式，收集日志不按照顺序
                         * 把当前支付流程暂存的toPayPage,toPay支付logSubType更新。
                         *
                        */
                        foreach($latestToPayPage as $latest){

                            if( $latest["logSubType"] < 0 )
                            {
                                $whereLatest = [
                                    'logSubType' => intval( abs( $latest["logSubType"] ) ),
                                ];

                                //定金订单不被消前处理，同一parentOrderSn且checkoutType订单结算类型区分
                                DataCollect::IS_OPEN_SWELL_ORDER && $whereLatest = $this->addCheckOutType($whereLatest,['checkoutType'=>$latest['checkoutType']]);

                                $this->updateData('update',[
                                    'parentOrderSn' => $data['parentOrderSn'],
                                    'logSubType' => $latest["logSubType"],
                                ],$whereLatest,$payinfoCollect);

                                //当前流程已保存最新的toPayPage,toPay
                            }
                        }   
                    }




                    // 删除所有logSubType比当前大，但时间却小的数据
                    if(empty($data['numberOfTimes']) || $data['numberOfTimes'] < 2){ //2次路由不消前

                        /**
                         *  当前步骤 3
                         *  first current
                         *         1
                         *         2  见 if($toPayPageAndToPayCount > 1)分支已删除 first(1,2)
                         *    3    3
                         *    4    4
                         *
                         *  则删除first(3,4) 保留current(4)
                         *
                         *  当前步骤 4
                         *          1
                         *          2
                         *    3     3
                         *    4     4
                         *    5
                         *  则删除first(3,4,5) 保留current(3,4)
                         *
                        */
                        $whereRemove = [
                            'parentOrderSn' => $data['parentOrderSn'],
                            'logSubType' => [ '$gte' => $data['logSubType'] ],
                            'requestTime' => [ '$lt' => $data['requestTime'] ],
                        ];

                        //定金膨胀订单（业务方面支付定金和尾款才能算一笔成功订单）
                        DataCollect::IS_OPEN_SWELL_ORDER && $whereRemove = $this->addCheckOutType($whereRemove,['checkoutType'=>$data['checkoutType']]);

                        $this->updateData('remove',$whereRemove,[],$payinfoCollect);
                    }

                    //保存3,4当前支付流程
                    $this->saveData($payinfoCollect,$data);
                }
            }
        }
    }

    /**
     * 添加订单（消前）记录时，处理前端校验失败标记
     * @param $data
     * @param $orderLogs
     * @param $payinfoCollect
     */
    private function dealValidateOrder(&$data, $orderLogs, $payinfoCollect)
    {
        if (empty($orderLogs)) {
            return ;
        }

        // 获取旧数据的前端校验失败标记
        $validateData = ['validateFailed' => 0, 'validateTime' => 0];
        foreach ($orderLogs as $logItem) {
            if (!empty($logItem['validateFailed'])) {
                $validateData['validateFailed'] = 1;
                $validateData['validateTime'] = $logItem['validateTime'];
                break;
            }
        }

        // 获取支付状态
        $payStatus = 1;
        foreach ($orderLogs as $logItem) {
            if (isset($logItem['payStatus']) && 2 == $logItem['payStatus']) {
                $payStatus = $logItem['payStatus'];
                break;
            }
        }
        if (!empty($data['payStatus']) && 2 == $data['payStatus']) {
            $payStatus = 2;
        }

        // 如果之前的数据没有前端校验失败标记，那么直接返回
        if (0 == $validateData['validateFailed']) {
            return ;
        }

        // 如果当前数据在前端校验失败后生成 并且 没有支付成功，那么清空前端校验失败标记
        if ($data['requestTime'] > $validateData['validateTime'] && $payStatus != 2) {
            $this->updateData('update',[
                'parentOrderSn' => $data['parentOrderSn'],
            ],[
                'validateFailed' => 0,
                'validateTime' => 0
            ],$payinfoCollect);
            return ;
        }

        // 否则，对当前数据打上标记
        $data['validateFailed'] = 1;
        $data['validateTime'] = $validateData['validateTime'];
    }

    /**
     * 新的前端校验失败数据到达时，处理订单（消前）的前端校验失败标记
     * @param $data
     */
    private function dealPageValidate($data)
    {
        $orderLogs = Yii::$app->MongoAdapter->getAll( self::COLLECTION_ORDERDATE_LAST_PAYINFO_LAST_MONTH, [
            'parentOrderSn' => $data['parentOrderSn']
        ] );
        $orderLogs = !empty($orderLogs) ? $orderLogs : [];

        // 如果前端校验失败不是最新的数据，那么忽略它
        $maxRequestTime = 0;
        foreach ($orderLogs as $logItem) {
            if ($maxRequestTime < $logItem['requestTime']) {
                $maxRequestTime = $logItem['requestTime'];
            }
        }
        if ($data['requestTime'] <= $maxRequestTime) {
            return ;
        }

        // 否则，给所有相同的parentOrderSn打上标记
        $collects = [
            self::COLLECTION_ORDERDATE_LAST_PAYINFO_LAST_MONTH,
            self::COLLECTION_SUBMITDATE_LAST_PAYINFO_LAST_MONTH
        ];
        foreach ($orderLogs as  $value) {
            if(!empty($value['submitDate'])){
                $collects[] = self::COLLECTION_SUBMITDATE_LAST_PAYINFO.$value['submitDate'];
            }
            if(!empty($value['orderDate'])){
                $collects[] = self::COLLECTION_ORDERDATE_LAST_PAYINFO.$value['orderDate'];
            }
        }
        $collects = array_unique($collects);
        $this->updateData('update',[
            'parentOrderSn' => $data['parentOrderSn'],
        ],[
            'validateFailed' => 1,
            'validateTime' => $data['requestTime']
        ], $collects);
    }

    /**
     * 保存最后一次加载行为
     * @param $data
     */
    private function saveBehaviorLoadLast($data)
    {
        // 获取页面加载数据
        $collect = SoaPaymentReport::COLLECTION_BEHAVIOR_LOAD_LAST . $data['logDate'];
        $collectArr = [$collect];
        $orderLogs = Yii::$app->MongoAdapter->getAll($collect, [
            'parentOrderSn' => $data['parentOrderSn'],
            'token' => $data['token'],
            'actionCode' => 'load',
        ] );

        // 计算最新的页面加载数据
        $lastLoadData = [];
        $lastRequestTime = 0;
        if (!empty($orderLogs)) {
            foreach ($orderLogs as $orderItem) {
                if ($lastRequestTime < $orderItem['requestTime']) {
                    $lastRequestTime = $orderItem['requestTime'];
                    $lastLoadData = $orderItem;
                }
            }
        }

        if ($lastRequestTime < $data['requestTime']) {
            $lastLoadData = $data;
        }

        // 删除旧的数据
        $this->updateData('remove',[
            'parentOrderSn' => $data['parentOrderSn'],
            'token' => $data['token'],
            'actionCode' => 'load',
        ],[], $collectArr);

        // 添加新的数据
        $this->saveData($collectArr, $lastLoadData);
    }

    /**
     * 对于同一个token,保存最开始的app数据
     * @param $data
     */
    private function saveBehaviorNativeEventFirst($data)
    {
        // 获取页面加载数据
        $collect = SoaPaymentReport::COLLECTION_BEHAVIOR_NATIVE_EVENT_FIRST . $data['logDate'];
        $collectArr = [$collect];
        $orderLogs = Yii::$app->MongoAdapter->getAll($collect, [
            'parentOrderSn' => $data['parentOrderSn'],
            'token' => $data['token'],
            'actionCode' => 'nativeEvent',
        ] );

        // 计算最开始的页面加载数据
        $firstRequestTime = 0;
        if (!empty($orderLogs)) {
            foreach ($orderLogs as $orderItem) {
                if ($firstRequestTime > $orderItem['requestTime']) {
                    $firstRequestTime = $orderItem['requestTime'];
                }
            }
        }

        if (0 != $firstRequestTime && $firstRequestTime < $data['requestTime']) {   // 如果当前的数据比库里的更加新，那么就不用处理
            return ;
        }

        // 删除库里的数据，换上当前的数据
        $firstEventData = $data;
        $this->updateData('remove',[
            'parentOrderSn' => $data['parentOrderSn'],
            'token' => $data['token'],
            'actionCode' => 'nativeEvent',
        ],[], $collectArr);

        $this->saveData($collectArr, $firstEventData);
    }

    /**
     * 获取订单的第一次channel
     * @param   $data   array 
     * @return varcher
     */
    private function getChannelCode($data)
    {
        $oldData = Yii::$app->MongoAdapter->getAll(self::COLLECTION_ORDERDATE_LAST_MONTH,['parentOrderSn'=>$data['parentOrderSn'],'method'=>'payInfo'],['requestTime'=>1],1);
        if(!empty($oldData[0]['requestTime']) && $data['requestTime'] >  $oldData[0]['requestTime']){
            return !empty($oldData[0]['firstChannelCode']) ? $oldData[0]['firstChannelCode'] : $data['channelCode'];
        }elseif (!empty($oldData[0]['requestTime']) && $data['requestTime'] <  $oldData[0]['requestTime']) {
            $this->updateData('update',['parentOrderSn'=>$data['parentOrderSn']],['firstChannelCode'=>$data['channelCode'],'sameAsFirstChannel'=>0]);
            return $data['channelCode'];
        }
        else{
            return $data['channelCode'];
        }
        
    }

    /**
     * 获取订单的第一次channel
     * @param   $data   array 
     * @return varcher
     */
    private function getOrderTime($parentOrderSn = '')
    {
        $where = ['parentOrderSn'=>$parentOrderSn, 'logSubType' => self::LOGSUBTYPE_TOPAYPAGE, 'requestTime' => ['$gt' => 0]];
        $oldData = Yii::$app->MongoAdapter->getAll(self::COLLECTION_ORDERDATE_LAST_MONTH,$where,['requestTime'=>1],1);
        return !empty($oldData[0]['requestTime']) ? $oldData[0]['requestTime'] : 0;
    }

    /**
     * 区分信用卡类型
     *@param  $cardType   varchar
     *@return array ['cardType','cardSubType']
    */
    private function getCardType($cardType)
    {
        $data = ['cardType'=>'none','cardSubType'=>'none'];
        $cards = explode('_', $cardType);
        $type = !empty($cards[0]) ? strtolower($cards[0]) : strtolower($cardType);
        $subType = !empty($cards[1]) ? strtolower($cards[1]) : '';
        switch ($type) {
            case 'mastercard': case 'ecmc':
                $data['cardType'] = 'Mastercard';
                break;
            case 'american': case ' amex':
                $data['cardType'] = 'Amex';
                break;
            case 'jcb': case 'cb':
                $data['cardType'] = strtoupper($type);
                break;
            default:
                $data['cardType'] = $type != 'none' ? ucwords($type) : 'none';
                break;
        }
        if(strpos($subType,'credit') !== false)
        {
            $data['cardSubType'] = 'Credit';
        }elseif(strpos($subType,'debit') !== false){
            $data['cardSubType'] = 'Debit';
        }else{
            $data['cardSubType'] = 'none';
        }
        return $data;
    }

    //获取未处理的kafka数量
    public static function getKafkaNumber(){
        return self::find()->where(['status'=>0])->count(); //从mysql拿数据
    }

    /**
     * 处理网关内置风控 保存到kafka元数据集合
     * @param  array  data  网关数据
     * @return bool 
     */
    private function handleRiskData($data)
    {
        if(!empty($data['riskChannel'])){
            $data['cyberRiskScore'] = $data['riskScore'];
            $data['method'] = 'riskControl';
            $data['logSubType'] = self::LOGSUBTYPE_RISK;
            $data['is_max'] = 1;
            $status = $this->exchangeRiskStatus($data);
            $data = array_merge($data,$status);
            $this->updateData('update',['traceID'=>$data['traceID']],['riskChannel'=>$data['riskChannel']]);
            $this->saveData([self::COLLECTION_ORDERDATE.$data['orderDate'],self::COLLECTION_SUBMITDATE.$data['submitDate'],self::COLLECTION_ORDERDATE_LAST_PAYINFO.$data['orderDate'],self::COLLECTION_SUBMITDATE_LAST_PAYINFO.$data['submitDate'],self::COLLECTION_SUBMITDATE_LAST_MONTH,self::COLLECTION_ORDERDATE_LAST_MONTH],$data);
            $this->handleRiskMax($data['parentOrderSn']);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 处理notify的3D结果
     * @param $threeDData
     * @param $threeDAddition
     * @return bool
     */
    private function handleThreesResult($threeDData, $threeDAddition)
    {
        // 如果有responsibilityCode或者eci，那代表这个支付执行了3D。将3D数据写入到mongodb
        if (!empty($threeDData['responsibilityCode']) || !empty($threeDData['eci'])) {
            $this->doHandleThreedsResult($threeDData, $threeDAddition);
            $threeDData = array_merge($threeDData, $threeDAddition);
            $threeDData['logSubType'] = self::LOGSUBTYPE_THREES_RESULT;
            $threeDData['method'] = 'threedsResult';
            $collection = [
                self::COLLECTION_ORDERDATE . $threeDData['orderDate'],
                self::COLLECTION_SUBMITDATE . $threeDData['submitDate'],
                self::COLLECTION_SUBMITDATE_LAST_MONTH,
                self::COLLECTION_ORDERDATE_LAST_MONTH
            ];
            $this->saveData($collection, $threeDData);  // 写入orderDate和submitDate
            $this->saveOrder($threeDData);      // 写入到last_payinfo
            return true;
        }
        return false;
    }

    /**
     * 处理3D结果
     * @param $data
     * @param $retAddition
     * @return mixed
     */
    private function doHandleThreedsResult(&$data, &$retAddition)
    {
        $data['responsibilityStatus'] = ReportHelper::getThreeDResponsibilityStatus($data['channelCode'],
            isset($data['responsibilityCode']) ? $data['responsibilityCode'] : "",
            isset($data['errorCode']) ? $data['errorCode'] : "",
            isset($data['eci']) ? $data['eci'] : ''
        );
        $uniqueData = Yii::$app->MongoAdapter->findOne(self::COLLECTION_ORDERDATE_LAST_MONTH, ['parentOrderSn'=>$data['parentOrderSn'],'logSubType'=>self::LOGSUBTYPE_THREES_RESULT, "isUniqueId" => 1]);
        if (empty($uniqueData)) {       // 如果之前没有相同的数据，那么当前的数据即为排重数据
            $data['isUniqueId'] = 1;
        } else {
            if (isset($uniqueData['responsibilityStatus']) && 1 == $uniqueData['responsibilityStatus']) {     // 如果之前的排重数据是符合责任转移，那么之前数据保持原状，当前数据不为排重数据
                $data['isUniqueId'] = 0;
            } else {        // 如果之前的排重数据不符合责任转移，那么当前数据为排重数据
                $this->updateData('update', ['parentOrderSn'=>$data['parentOrderSn'],'logSubType'=>self::LOGSUBTYPE_THREES_RESULT], ["isUniqueId" => 0]);//更新集合
                $data['isUniqueId'] = 1;
            }
        }
        $retAddition['responsibilityStatus'] = $data['responsibilityStatus'];
        $retAddition['isUniqueId'] = $data['isUniqueId'];
        $retAddition['traceID'] = $data['traceID'];
        if(isset($data['payStatus'])){
            $this->updateData('update', ['paySn'=>$data['paySn']],['payStatus'=>$data['payStatus']]);//更新集合
        }
    }

    /**
     * 转换风控状态
     * @param $data
     * @return array
     */
    private function exchangeRiskStatus($data)
    {
        $report = new ReportHelper();
        $reasonCode = "";
        if (strtolower($data['riskChannel']) == "cybersource") {
            $riskScore = isset($data['cyberDecisionReplyName']) ? $data['cyberDecisionReplyName'] : "";
            $riskDecision = isset($data['cyberRiskDecision']) ? $data['cyberRiskDecision'] : "";
            $reasonCode = isset($data['cyberReasonCode']) ? $data['cyberReasonCode'] : "";
        } else {
            $riskScore = isset($data['riskScore']) ? $data['riskScore'] : "";
            $riskDecision = isset($data['riskDecision']) ? $data['riskDecision'] : "";
        }
        $data['riskChannel'] = strtoupper($data['riskChannel']) == 'RG' ? 'riskguardian' : $data['riskChannel'];
        $param = [
            'risk_decision' => $riskDecision,
            'risk_type' => isset($data['riskChannel']) ? $data['riskChannel'] : "",
            'risk_score' => $riskScore,
            "code" => $reasonCode,
        ];
        $status = $report->exchangeRiskStatus($param);
        return $status;
    }

    /**
     * 处理风控is_max
     * @param  parentOrderSn  varchar
     * @return void
     */
    private function handleRiskMax($parentOrderSn){
        $list = yii::$app->MongoAdapter->getAll(self::COLLECTION_ORDERDATE_LAST_MONTH,['parentOrderSn'=>$parentOrderSn, 'logSubType' => self::LOGSUBTYPE_RISK],['requestTime'=>-1]);
        if(count($list)>1){
            $collects = [self::COLLECTION_SUBMITDATE_LAST_MONTH,self::COLLECTION_ORDERDATE_LAST_MONTH];
            foreach ($list as  $value) {
                if(!in_array(self::COLLECTION_SUBMITDATE.$value['submitDate'], $collects)){
                    $collects[] = self::COLLECTION_SUBMITDATE.$value['submitDate'];
                    $collects[] = self::COLLECTION_ORDERDATE.$value['orderDate'];
                    $submitDate = $value['submitDate'];
                    $orderDate = $value['orderDate'];
                }  
            }
            $this->updateData('update',['parentOrderSn'=>$parentOrderSn, 'logSubType' => self::LOGSUBTYPE_RISK],['is_max'=>0],$collects);
        }
        yii::$app->MongoAdapter->update(self::COLLECTION_SUBMITDATE_LAST_MONTH,['_id'=>$list[0]['_id']],['is_max'=>1]);
        $orderMonthOrder = Yii::$app->MongoAdapter->findOne(self::COLLECTION_ORDERDATE_LAST_MONTH,['parentOrderSn'=>$parentOrderSn, 'logSubType' => self::LOGSUBTYPE_RISK],['requestTime'=>-1]);
        yii::$app->MongoAdapter->update(self::COLLECTION_ORDERDATE_LAST_MONTH,['_id'=>$orderMonthOrder['_id']],['is_max'=>1]);
        $submitDateOrder = Yii::$app->MongoAdapter->findOne(self::COLLECTION_SUBMITDATE.$submitDate,['parentOrderSn'=>$parentOrderSn, 'logSubType' => self::LOGSUBTYPE_RISK],['requestTime'=>-1]);
        yii::$app->MongoAdapter->update(self::COLLECTION_SUBMITDATE.$submitDate,['_id'=>$submitDateOrder['_id']],['is_max'=>1]);
        $orderDateOrder = Yii::$app->MongoAdapter->findOne(self::COLLECTION_ORDERDATE.$submitDate,['parentOrderSn'=>$parentOrderSn, 'logSubType' => self::LOGSUBTYPE_RISK],['requestTime'=>-1]);
        yii::$app->MongoAdapter->update(self::COLLECTION_ORDERDATE.$submitDate,['_id'=>$orderDateOrder['_id']],['is_max'=>1]);
    }

    /**
     * 处理分期贴息
     * @param $data
     */
    private function handleInstalmentSubsidy($data)
    {
        $instalmentChannelArr = ["ebanxinstalment", "EBX_MXCC", "PayU_TRCC", "PayU_BKM"];
        if (!isset($data['payStatus']) || 2 != $data['payStatus'] || !in_array($data['channelCode'], $instalmentChannelArr)) {
            return ;
        }
        $currencyMapArr = ["ebanxinstalment" => "BRL", "EBX_MXCC" => "MXN", "PayU_TRCC" => "TRY", "PayU_BKM" => "TRY"];
        $localCurrency = $currencyMapArr[$data['channelCode']];
        if ("PayU_TRCC" == $data['channelCode'] || "PayU_BKM" == $data['channelCode']) {
            $data['gatewayCurrencyRate'] = 1;
            $data['gatewayRealAmount'] = round($data['payCurrencyAmount'] * (1 + $data['realInterestRate']), 2);
        }

        if (!isset($data['currencyCode'], $data['payCurrencyAmount'], $data['instalments'],
                $data['baseInterestRate'], $data['realInterestRate'], $data['gatewayCurrencyRate'])
        ) {
            return ;
        }

        $instalmentData = [
            "channelCode" => $data['channelCode'],
            "siteCode" => $data['siteCode'],
            "pipelineCode" => $data['pipelineCode'],
            "parentOrderSn" => $data['parentOrderSn'],
            "platform" => $data['platform'],
            "requestTime" => $data['requestTime'],
            "currencyCode" => $data['currencyCode'],
            "payCurrencyAmount" => $data['payCurrencyAmount'],
            "instalments" => $data['instalments'],
            "localCurrency" => $localCurrency,
            "baseInterestRate" => $data['baseInterestRate'],    // 利率的单位不是百分比，所以利率不用除以100
            "realInterestRate" => $data['realInterestRate'],    // 利率的单位不是百分比，所以利率不用除以100
            "gatewayCurrencyRate" => $data['gatewayCurrencyRate'],
            "gatewayRealAmount" => $data['gatewayRealAmount'],
        ];

        $key = "soa_subsidy";
        app()->cache->rpush($key, json_encode($instalmentData));
    }

    /**
     * 向mysql添加风控记录，用于前置风控因子
     * @param $data
     * @return int
     */
    private function AddRiskRecordToMysql($data)
    {
        $bjTime = app()->bjTime;
        $soaHelper = new SoaHelper();
        $siteName = $soaHelper->transferSiteName($data['siteCode'], "");
        $payment = "";          // 支付方式
        if (isset($data['channelCode'])) {
            $payment = strtolower($data['channelCode']);
            $paymentMapArr = ["ebanxinstalment" => "instalments", "checkout_credit" => "checkoutcredit", 'gc' => 'webcollect'];
            if (isset($paymentMapArr[$payment])) {
                $payment = $paymentMapArr[$payment];
            }
        }
        $payTime = $bjTime->time();     // 付款时间，默认为当前时间
        if (isset($data['requestTime'])) {
            $payTime = strlen($data['requestTime'] . "") > 11 ? substr($data['requestTime'] . "", 0, 11) : $data['requestTime'];
        }
        if (1 == $data['platform']) {
            $platformName = "pc";
        } else if (2 == $data['platform']) {
            $platformName = "m";
        } else if ($data['platform'] >= 3) {
            $platformName = "app";
        } else {
            $platformName = "pc";
        }

        // 将风控数据写入数据库
        $insertData = [
            "payment" => $payment,
            "site" => $siteName,
            "order_sn" => $data['parentOrderSn'],
            "channel" => $platformName,
            "amount" => $data['orderAmount'],
            "email" => $data['userEmail'],
            "billing_email" => "",
            "pay_time" => $payTime,
            "currency_code" => $data['orderCurrency'],
            "shipping_country" => $data['shippingCountryCode'],
            "shipping_state" => $data['shippingState'],
            "shipping_city" => $data['shippingCity'],
            "shipping_street1" => "",
            "full_name" => "",
            "card_holder" => "",
            "card_type" => isset($data['cardType']) ? $data['cardType'] : "",
            "card_number" => isset($data['cardNo']) ? $data['cardNo'] : "",
            "issuing_bank" => isset($data['issuerBank']) ? $data['issuerBank'] : "",
            "bin_country" => isset($data['issuerCountryCode']) ? $data['issuerCountryCode'] : "",
            "ip" => isset($data['ip']) ? $data['ip'] : "",
            "ip_country" => isset($data['ipCountryCode']) ? $data['ipCountryCode'] : "",
            "billing_name" => "",
            "billing_country" => isset($data["billingCountryCode"]) ? $data['billingCountryCode'] : "",
            "billing_state" => isset($data['billingState']) ? $data['billingState'] : "",
            "billing_city" => isset($data['billingCity']) ? $data['billingCity'] : "",
            "billing_zipcode" => "",
            "billing_street1" => "",
            "copy_or_manual" => !empty($data['cardNoCopy']) ? $data['cardNoCopy'] : "",        // 卡号是否复制  0-否 1-是
            "instalments" => 1,
            "cybersource_afs_score" => isset($data['cyberRiskScore']) ? $data['cyberRiskScore'] : "",
            "cyber_times" => 1,
            "cyber_decision" => isset($data['cyberRiskDecision']) ? $data['cyberRiskDecision'] : "",
            "system_type" => 2,         // soa支付平台
            "create_time" => $bjTime->time(),
        ];

        // 将数据写入到redis，后续再处理其他相关业务
        if ("accept" == strtolower($insertData['cyber_decision'])) {
            $cardBrand = "";
            $cardType = "";
            if (!empty($insertData['card_type']) && strtolower($insertData['card_type']) != 'none') {
                $cardBrand = $insertData['card_type'];
            }
            if (!empty($data['cardFundingSource']) && strtolower($data['cardFundingSource']) != 'none') {
                $cardType = $data['cardFundingSource'];
            }

            app()->cache->rpush("risk_record", json_encode([
                "card_bin" => substr($insertData['card_number'], 0, 6),
                "issuer_country_code" => $insertData['bin_country'],
                "issuer_bank" => $insertData['issuing_bank'],
                "card_brand" => $cardBrand,
                "card_type" => $cardType,
                "card_number" => $insertData['card_number'],
                "system_type"   => 2,
            ]));
        }

        app()->cache->rpush("risk_factor", json_encode($insertData));
    }

    /** 
     * 保存到mongodb
     * @param  data     array  需要保存的数据
     * @param  collects array  需要保存的集合
     * @return bool 
     */
    private function saveData($collects,$data)
    {
        if(!empty($collects) && !empty($data)){
            unset($data['_id']);
            foreach($collects as $row){
               $status = yii::$app->MongoAdapter->insert($row,$data);
               if(empty($status)){
                    return false;
               }
            }
            return true;
        }
        return false;
    }

    /**
     * 更新mongodb 数据
     * @param   $type    varchar 更新或删除(update,remove)     
     * @param   $where   array   更新条件
     * @param   $data    array   需要更新的数据
     * @param   $collect array   需要更新的集合
     * @return  bool
     */
    private function updateData($type,$where,$data=[],$collects=[])
    {
        if(empty($type) || empty($where)){
            return false;
        }
        if(empty($collects)){
            $list = Yii::$app->MongoAdapter->getAll(self::COLLECTION_ORDERDATE_LAST_MONTH,$where);
            if(empty($list)){
                return false;
            }
            $collects = [
                self::COLLECTION_ORDERDATE_LAST_MONTH,
                self::COLLECTION_ORDERDATE_LAST_PAYINFO_LAST_MONTH,
                self::COLLECTION_SUBMITDATE_LAST_MONTH,
                self::COLLECTION_SUBMITDATE_LAST_PAYINFO_LAST_MONTH
            ];
            foreach ($list as  $value) {
                if(!in_array(self::COLLECTION_SUBMITDATE.$value['submitDate'], $collects) && $value['logSubType'] != self::LOGSUBTYPE_TOPAYPAGE ){
                    if(!empty($value['submitDate'])){
                        $collects[] = self::COLLECTION_SUBMITDATE.$value['submitDate'];
                        $collects[] = self::COLLECTION_SUBMITDATE_LAST_PAYINFO.$value['submitDate']; 
                    }
                    if(!empty($value['orderDate'])){
                        $collects[] = self::COLLECTION_ORDERDATE.$value['orderDate'];
                        $collects[] = self::COLLECTION_ORDERDATE_LAST_PAYINFO.$value['orderDate'];
                    }
                }  
            }
            $collects = array_unique($collects);
        }
        switch ($type) {
            case 'update':
                if(empty($data)){
                    return false;
                }
                foreach ($collects as $value) {
                    $status = Yii::$app->MongoAdapter->update($value,$where,$data);
                    if(empty($status)){
                        return false;
                    }
                }
                return true;
                break;
            
            case 'remove':
               foreach ($collects as $value) {
                    $status = Yii::$app->MongoAdapter->remove($value,$where);
                    if(empty($status)){
                        return false;
                    }
                }
                return true;
                break;
        }
        return false;

    }

    /**
     * 获取价格区间
     * @param   price    float  价格
     * @return  intval
     */
    private function getPriceRange($price)
    {
        $range = [1=>500,2=>1000,3=>2000,4=>5000,5=>10000,6=>20000,7=>50000];
        foreach($range as $key=>$row){
            if(($price*100) <= $row){
                return $key;
            }
        }
        return 8;
    }

    /**
     * 获取最新汇率
     */
    static public function getRate()
    {
        $rate = [];
        $rateData = Yii::$app->MongoAdapter->aggregate( DataCollect::COLLECTION_RATE, array(
            [
                '$match'=>[
                    'rateSource'=>[
                        '$ne'=>''
                    ]
                ],
            ],
            [
                '$group' => [
                    '_id' => ['rateType'=>'$rateType','rateSource'=>'$rateSource'],
                    'max' => ['$max'=>'$requestTime'],
                ],
            ],
        ));
        if(!empty($rateData['result'])){
            foreach($rateData['result'] as $row){
                if($row['_id']['rateType'] == 3){
                    $data = Yii::$app->MongoAdapter->getAll(DataCollect::COLLECTION_RATE,['requestTime'=>['$lte'=>$row['max']],'rateType'=>$row['_id']['rateType'],'rateSource'=>$row['_id']['rateSource']],['requestTime'=>-1],2);
                    $rate['new'][$row['_id']['rateType']][$row['_id']['rateSource']] = json_decode($data[0]['rateData'],true);//最新汇率
                    $rate['old'][$row['_id']['rateType']][$row['_id']['rateSource']] = json_decode($data[1]['rateData'],true);//次新汇率
                }else{
                    $data = Yii::$app->MongoAdapter->findOne(DataCollect::COLLECTION_RATE,['requestTime'=>['$gte'=>$row['max']],'rateType'=>$row['_id']['rateType'],'rateSource'=>$row['_id']['rateSource']]);
                    $rate['new'][$row['_id']['rateType']][$row['_id']['rateSource']] = json_decode($data['rateData'],true);//最新汇率
                }  
            }

        }
        return $rate;
    }

    /**
     * 获取待处理redis数据
     * @param  array  ['method',key]
     * @return  array    
     */
    public static function getPendingData($data)
    {
        switch ($data['method']) {
            case 'toPayPage':
                $key = self::META_PENDING_KEY.'token:'.$data['key'];
                $pending = yii::$app->cache->sMembers($key);
                break;
            case 'toPay':
                $key = self::META_PENDING_KEY.'traceID:'.$data['key'];
                $pending = yii::$app->cache->sMembers($key);
                break;    
            case 'payInfo':
                // 风控
                $key = [];
                $keyRisk = self::META_PENDING_KEY.'traceID:riskControl:'.$data['traceID'];
                $pendingRisk = yii::$app->cache->sMembers($keyRisk);
                if (!empty($pendingRisk)) {
                    $key[] = $keyRisk;
                }
                // payResult
                $keyPayResult = self::META_PENDING_KEY.'traceID:payResult:'.$data['traceID'];
                $pendingPayResult = yii::$app->cache->sMembers($keyPayResult);
                if (!empty($pendingPayResult)) {
                    $key[] = $keyPayResult;
                }
                // 具有paySn的数据
                $keyPay = self::META_PENDING_KEY.'paySn:'.$data['paySn'];
                $pendingPay = yii::$app->cache->sMembers($keyPay);
                if (!empty($pendingPay)) {
                    $key[] = $keyPay;
                }
                $pending = array_merge($pendingRisk, $pendingPayResult, $pendingPay);
                break;
        }
        return $pending;
    }
}
