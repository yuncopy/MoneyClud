<?php
/**
 * Created by PhpStorm.
 * User: Mafengbiao
 * Date: 2017/7/17
 * Time: 16:36
 */
namespace app\components;

use Yii;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MqClient {

    public  $config = [];
    public $exchange = 'PaymentCheck_PAYCENTER';
    public $queue = 'PaymentCheck_PAYCENTER';
    public $consumerTag = 'consumer';

    private    $connection = null;
    private    $channel = null;
    public function __construct()
    {
       $this->config = app()->params['MqConfig'];
       $this->exchange = $this->config['exchange'];
       $this->queue = $this->config['queue'];
       $this->consumerTag = $this->config['consumerTag'];
       try {
            $this->connection = new AMQPStreamConnection($this->config['host'], $this->config['port'], $this->config['user'], $this->config['pass'], $this->config['vhost']);//创建连接
            if (!$this->connection) {
                die('cann\'t connection');
            }
            $this->channel = $this->connection->channel();//
            if (!$this->channel) {
                die('cann\'t channel');
            }
            $this->channel->queue_declare($this->queue, false, true, false, false);
//            $this->channel->exchange_declare($this->exchange, 'direct', false, true, false);
//            $this->channel->queue_bind($this->queue, $this->exchange);
        }catch (\Exception $e){
            die($e->getMessage());
        }
    }
    //添加到队列
    public function send($msgData){
        try {
            $msg = new AMQPMessage($msgData, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
            $this->channel->basic_publish($msg, $this->exchange);
            echo " [x] Sent 'Hello World!'\n";
            $this->shutdown($this->channel,$this->connection);
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }
    //消费队列
    public function accept($callback = null){
        try {
            if(!$callback){
                $callback = function($message) { //默认的回调，测试用
                    echo "\ntest--------test\n";
                    echo $message->body;
                    echo "\ntest--------test\n";
                    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                };
            }
            $this->channel->basic_consume($this->queue, $this->consumerTag, false, false, false, false, $callback);
            register_shutdown_function([$this,'shutdown'],$this->channel,$this->connection);
            $time = time();
            while (count($this->channel->callbacks) && time() < $time + 50){
                $this->channel->wait(null, true, 5);//设置超时
            }
        }catch (\Exception $e){
            echo  $e->getMessage();
        }
    }
    //关闭客户端
    public function shutdown($channel,$connection)
    {
        $channel->close();
        $connection->close();
    }
}
