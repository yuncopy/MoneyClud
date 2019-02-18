<?php
//kakfa数据收集
namespace app\modules\admin\models;

use app\helpers\Helper;
use app\helpers\ReportHelper;
use Yii;
use app\base\AdminActiveRecord;
use yii\db\MigrationInterface;
use yii\helpers\Url;
use app\helpers\Platform;
use app\helpers\MongodbHelper;
use app\components\KafkaAdapter;

class KafkaConsumer extends AdminActiveRecord
{   
    //汇率集合
    const COLLECTION_RATE = 'soa_payment_rate';

    public static function tableName()
    {
        return 'kafka_source_data';
    }

    /**
     * 获取kafka数据保存mysql
     * @return      array
    */
    public static function get()
    {
        $KafkaAdapter = new KafkaAdapter('configure');
        $data = $KafkaAdapter->consumer();
        if(!empty($data)){   
            foreach($data as $key=>$row){
                if($row['status'] == 1){
                    $detail = json_decode($row['data'],true);
                    $save = json_decode($detail['data'],true);
                    $save['method'] = $detail['method'];
                    $saves[] = ['data'=>json_encode($save)];
                    
                }   
            }
            if(!empty($saves)){
                yii::$app->db->enableSlaves = false;        // 禁用从库查询，从主库查询数据
                $status = Yii::$app->db->createCommand()->batchInsert(self::tableName(), ['data'], $saves)->execute();//执行批量添加
                if(empty($status)){
                    app()->recordLog->log('kafka-fail', $saves); //记录kafka失败保存记录
                }
            }    
        }else{
            exit('没有kafka消息');
        }
    }

    /**
     * 处理mysql中的数据
     */
    public static function handle()
    {
        $data = self::find()->select('*')->where(['status'=>0])->orderBy('id asc')->asArray()->limit(100)->all(); //从mysql拿数据
        if(!empty($data)){
            foreach($data as $row){
                $detail = json_decode($row['data'],true);
                switch ($detail['method']) {
                    case 'currencyRateInfo': //处理汇率
                        $detail['date'] = intval(gmToChinaDate('Ymd',$detail['date']));
                        $detail['rateSource'] = strtolower($detail['rateSource']);
                        $list = Yii::$app->MongoAdapter->findOne(self::COLLECTION_RATE,['date'=>$detail['date'],'rateSource'=>$detail['rateSource'],'rateType'=>$detail['rateType']]);
                        if(!empty($list)){
                            $saveData = json_decode($list['rateData'],true);
                            $rateData = json_decode($detail['rateData'],true);
                            foreach($rateData as $key=>$val){
                                $saveData[$key] = $val;
                            }
                            if(Yii::$app->MongoAdapter->update(self::COLLECTION_RATE,['date'=>$detail['date'],'rateSource'=>$detail['rateSource'],'rateType'=>$detail['rateType']],['rateData'=>json_encode($saveData)])){
                                $update[] = $row['id'];
                            }

                        }else{
                           if(Yii::$app->MongoAdapter->insert(self::COLLECTION_RATE,$detail)){
                                $update[] = $row['id'];
                            } 
                        }
                        break;
                      
                    default:
                         
                        break;
                }  
            }
            if(!empty($update)){
                yii::$app->db->enableSlaves = false; // 禁用从库查询，从主库查询数据.
                yii::$app->db->createCommand('update '.self::tableName().' set status = 1 where id in ('.implode(',', $update).')')->execute();
            }
        }
    }
}
