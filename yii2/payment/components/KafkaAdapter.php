<?php
namespace app\components;

use Yii;

class KafkaAdapter
{
    public $broker = '';//kafka的地址
    public $topic = '';
    public $partition = 0;
    public $config = [];

    protected $producer = null;
    protected $consumer = null;
    static $conf = null;

    public function __construct($group='')
    {   
        if (!empty($group) && isset(App()->params['kafkaConfig'][$group])) {
            $this->config = App()->params['kafkaConfig'][$group];
        } else {
            $this->config = App()->params['kafkaConfig']['default'];
        }
        $this->broker = $this->config['broker'];
        $this->topic = $this->config['topic'];
        if (empty($this->broker)) {
            $this->error("broker not config");
        }
        $rk = new \RdKafka\Producer();

        if (empty($rk)) {
            $this->error("producer error");
        }
        $rk->setLogLevel(LOG_DEBUG);

        if (!$rk->addBrokers($this->broker)) {
            $this->error("producer error");
        }
        $this->producer = $rk;
    }

    private function getConf(){
        self::$conf = new \RdKafka\Conf();
        // 当有新的消费进程加入或者退出消费组时，kafka 会自动重新分配分区给消费者进程，这里注册了一个回调函数，当分区被重新分配时触发
        self::$conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $kafka->assign(NULL);
                    break;

                default:
                    throw new \Exception($err);
            }
        });

        self::$conf->set('group.id', $this->config['group_id']);//默认为0

        self::$conf->set('metadata.broker.list', $this->broker);

        $topicConf = new \RdKafka\TopicConf();
        $topicConf->set('auto.offset.reset', 'smallest');//从开头消费消息
        self::$conf->setDefaultTopicConf($topicConf);
        return true;
    }

    /**
    * 往kafka发送消息
    * @param    message   array 发送消息内容
    * @return bool
    */
    public function send($messages = [])
    {
        $topic = $this->producer->newTopic($this->topic);
        $topic->produce(RD_KAFKA_PARTITION_UA, $this->partition, json_encode($messages));
        return $this->producer->poll($this->partition);
    }
    /**
    * 获取一条消息
    * @return ['status','msg','data']  
    */
    public function consumer()//自定义如何消费
    {
        if(empty(self::$conf)){
            $this->getConf();
        }
        $consumer = new \RdKafka\KafkaConsumer(self::$conf);

        $consumer->subscribe([$this->topic]); // 让消费者订阅pay_center_trace_topic主题
		$total = IS_LOCAL ?  45 :800;
        $total = !empty($this->config['total']) ? $this->config['total'] : $total;
        for($i = 0; $i < $total;$i++){

            //当获取不到数据时会阻塞默认10秒 ，120*1000  (单位是毫秒)
            $message = $consumer->consume(120*1000);

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $data[] = ['status'=>1,'data'=>$message->payload];
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF://没有消息 -191
                    $data[] = ['msg'=>'No more messages; will wait for more','status'=>0];
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT://超时 -185
                    $data[] = ['msg'=>'Timed out','status'=>0];
                    break;
                default:
                    $data[] = ['msg'=>$message->errstr(),'status'=>0];
                    break;  
            }
        }

        app()->recordLog->log('kafka', $data);
        return $data;
    }

    private function error($msg = '')
    {
        if ($msg) {
            error_log($msg . ' ' . date('Y-m-d H:i:s') . "\r\n", 3, dirname(dirname(__FILE__)) . '/runtime/logs/kafkaerror.log');
        }

        return false;
    }
}