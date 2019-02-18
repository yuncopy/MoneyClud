<?php
namespace app\components;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 创建人: xzp
 * 创建时间: 2017-03-07
 */

class RabbitMq{
    // 基本配置：
    private $host;
    private $port;
    private $user;
    private $pwd;
    private $vhost;

    // 队列配置：
    private $queue_name             = '';    // 队列名称
    private $queue_durable          = true;  // 队列持久化  服务重启 存活
    private $queue_auto_delete      = false; // 通道关闭 是否 自动删除队列
    private $queue_exclusive        = false;
    private $queue_passive          = false;
    private $routing_key;

    // 交换机设置
    private $exchange_name          = '';    // 交换机名称
    private $exchange_type          = 'direct';  // 交换机类型：
    private $exchange_durable       = true;  // 交换机持久化
    private $exchange_auto_delete   = false; // 通道关闭 是否 自动删除交换机
    private $exchange_passive          = false;

    // 消息设置：
    private $delivery_mode          = AMQPMessage::DELIVERY_MODE_PERSISTENT;

	// 权限：
	private $writeAble;

    /**
     * @var callable
     */
    private $callback; // 消费回调

    private $checkReturn; // 是否需要检查回调

    /**
     * @var AMQPStreamConnection
     */
    private $mq;

    /**
     * @var AMQPChannel
     */
    public $channel;



    public function __construct($config){
        // 基本配置
        $base_config    = $config['base'];
        $this->host     = $base_config['host'];
        $this->port     = $base_config['port'];
        $this->user     = $base_config['user'];
        $this->pwd      = $base_config['pwd'];
        $this->vhost    = $base_config['vhost'];

        // 队列配置：
        $queue_config   = $config['queue'];
        if(isset($queue_config['name'])) $this->queue_name = $queue_config['name'];
        if(isset($queue_config['durable'])) $this->queue_durable = $queue_config['durable'];
        if(isset($queue_config['auto_delete'])) $this->queue_auto_delete = $queue_config['auto_delete'];
        if(isset($queue_config['routing_key'])) $this->routing_key = $queue_config['routing_key'];

        if(isset($queue_config['delivery_mode'])) $this->delivery_mode = $queue_config['delivery_mode'];

        // 交换机配置：
        $exchange_config = $config['exchange'];
        if(isset($exchange_config['name'])) $this->exchange_name = $exchange_config['name'];    // 交换机名称
        if(isset($exchange_config['type'])) $this->exchange_type = $exchange_config['type'];  // 交换机类型
        if(isset($exchange_config['durable'])) $this->exchange_durable = boolval($exchange_config['durable']);
        if(isset($exchange_config['auto_delete'])) $this->exchange_auto_delete = $exchange_config['auto_delete'];

	    // 是否有写权限：
		$this->writeAble = isset($config['write']) && $config['write'] ? true : false;
        $this->connect();
    }

    // 链接mq
    private function connect(){
        $this->mq = new AMQPStreamConnection($this->host,$this->port,$this->user,$this->pwd,$this->vhost);
        $this->channel = $this->mq->channel();
        $this->setChannel();
    }

    // 设置channel
    private function setChannel(){
        $this->channel->queue_declare($this->queue_name,$this->queue_passive,$this->queue_durable,$this->queue_exclusive,$this->queue_auto_delete);
	    if($this->writeAble){ // 可写权限进行绑定
		    $this->channel->exchange_declare($this->exchange_name,$this->exchange_type,$this->exchange_passive,$this->exchange_durable,$this->exchange_auto_delete);
	        $this->channel->queue_bind($this->queue_name,$this->exchange_name,$this->routing_key);
	    }
    }

    // 发布消息
    public function publish($msg,$contentType='text/plain'){
        if(!$this->mq || !$this->channel) $this->connect();
        $msgObj = new AMQPMessage($msg,['content_type'=>$contentType,'delivery_mode'=>$this->delivery_mode]);
        $this->channel->basic_publish($msgObj,$this->exchange_name,$this->routing_key);
    }

    // 进行消费队列处理
    public function consume($consumer_tag='',$callback,$checkReturn=false){
        $this->callback = $callback;
        $this->checkReturn = $checkReturn;
        $this->channel->basic_consume($this->queue_name,$consumer_tag,false,false,false,false,[$this,'process_message']);
        while (count($this->channel->callbacks)) {
	        $this->channel->wait();
        }
    }

	/**
	 * 获取一条消息 并确认消费
	 * @return mixed
	 */
	public function basic_get($no_ack=false){
		return $this->channel->basic_get($this->queue_name,$no_ack);
	}

	/**
	 * 确认一条消息消费
	 * @param $deliveryTag int 在channel中时递增的唯一的
	 * @param $multi bool 是否批量确认  true: <= $deliveryTag 都会确认消费  否则 只有 $deliveryTag 的消费
	 */
	public function basic_ok($deliveryTag,$multi=false){
		$deliveryTag && $this->channel->basic_ack($deliveryTag,$multi);
	}

    /**
     *  处理消息：
     * @param $msg AMQPMessage
     */
    public function process_message($msgObj){
        $result = call_user_func($this->callback,$msgObj->body);
        if(!$this->checkReturn || $result){
            // 消息确认消费：
            $msgObj->delivery_info['channel']->basic_ack($msgObj->delivery_info['delivery_tag']);
        }
    }

    // 关闭链接处理
    public function close(){
	    try{
		    if($this->channel) $this->channel = null;
		    if($this->mq) $this->mq->close();
		    $this->mq = null;
	    }catch (\Exception $e){
		    // ignore
	    }
    }

    // 析构函数
    public function __destruct(){
        $this->close();
    }
}