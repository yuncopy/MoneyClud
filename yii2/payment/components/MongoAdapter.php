<?php

/**
 * mongodb 操作类
 * Class MongoAdapter
 */

namespace app\components;

use Yii;

class MongoAdapter
{
    /**
     * @var null 连接
     */
    private $mongodb;
    protected $database    = '';
    protected $connect = true;

    public function __call( $method = '', $args = array() )
    {
        $ret = false;
		try
		{
			$callMethod = "__" . $method;
            if(!$this->connect)  //异常返回false
            {
                if($method == 'insert'){//如果是写入日记则写入文件
                    $this->__log($args);
                }
                return $ret;
            }
            elseif( method_exists( $this, $callMethod ) )
			{
				$ret = call_user_func_array([$this,$callMethod], $args );
			}
			else
			{
				$table = $args[0];
				unset($args[0]);
				$ret = call_user_func_array( [$this->database->$table,$method], $args );
			}
		}
		catch( \Exception $e )
		{
			$this->_triggerError( $e );
		}
        return $ret;
    }
    
    protected function _triggerError( $e )
    {
        $this->connect = false;

        // 发送php告警
        $message = get_class($e) . ": " . $e->getMessage();
        \Yii::$app->monitor->saveErrorData('mongodb', 'mongodb_alert1', $message);

        var_dump($e->getMessage());
    }

    /**
     * 连接
     * @param config ['server'=>'mongodb://payment:3DtpLtoxITqQ@192.168.6.67:27017/payment_logs','database'=>'payment_logs']
     * @return null|\stdClass
     */
    public function __construct($group = [])
    {
        if (!empty($group) && isset(App()->params['mongoConfig'][$group])) 
        {
            $config = App()->params['mongoConfig'][$group];
        } 
        else 
        {
            $config = App()->params['mongoConfig']['default'];
        }
        if(!empty($this->mongodb))
        {
            $this->database = $this->mongodb->$config['database'];
            return  $this->mongodb;  
        }
        $this->mongodb = new \stdClass();
        if (!isset($config['server'])) 
        {
            return $this->mongodb;
        }
		if (!empty($config['server'])) 
        {
            try
            {
                $option = array("connectTimeoutMS" => 60000, "socketTimeoutMS" => 60000);
                $this->mongodb = new \MongoClient($config['server'], $option);
                
            }
            catch(\Exception $e)
            {
                $this->_triggerError( $e );
            }
        }
        $this->database = $this->mongodb->$config['database'];
    }
    /**
     * 查询表中所有数据
     * @param $table    varcher     集合
     * @param $where    array       ['key'=>'value']   where key = value
     * @param array $sort
     * @param string $limit
     * @param string $skip
     * @return array|int
     */
    private function __getAll($table, $where = array(), $sort = array(), $limit = '', $skip = '') 
    {
        if (!empty($where)) 
        {
            $data = $this->database->$table->find($where);
        }
        else
        {
            $data = $this->database->$table->find();
        }

        if (!empty($sort)) 
        {
            $data = $data->sort($sort);
        }

        if (!empty($limit)) 
        {
            $data = $data->limit($limit);
        }

        if (!empty($skip)) 
        {
            $data = $data->skip($skip);
        }

        $newData = array();
        while ($data->hasNext()) 
        {
            $newData[] = $data->getNext();
        }
        if (count($newData) == 0) 
        {
            return 0;
        }
        return $newData;
    }

    /**
     * 统计个数
     * @param $table    varcher     集合
     * @param $where    array       ['key'=>'value']   where key = value
     * @return mixed
     */
    private function __getTotal($table, $where = array()) 
    {
        if (!empty($where)) 
        {
            $data = $this->database->$table->find($where)->count();
        }
        else
        {
            $data = $this->database->$table->find()->count();
        }
        return $data;
    }

    /**
     * 直接执行mongo命令
     * @param $sql
     * @return array
     */
    private function __query($sql) 
    {
        $result = $this->database->execute($sql);
        return $result;
    }

    /**
     * 分组统计个数
     * @param $table    varcher     集合
     * @param $where    array       ['key'=>'value']   where key = value
     * @param $field
     */
    private function __groupCount($table, $where, $field) 
    {
        $cond = 
        [
            [
                '$match' => $where,
            ],
            [
                '$group' =>[
                    '_id' => '$' . $field,
                    'count' =>[
                        '$sum' => 1
                    ],
                ],
            ],
            [
                '$sort' => [
                    "count" => -1
                ],
            ],
        ];
        $this->database->$table->aggregate($cond);
    }

    /**
     * 更新数据
     * @param $table    varcher     集合
     * @param $where    array       ['key'=>'value']   where key = value
     * @param $data
     * @return bool
     */
    private function __update($table, $where, $data)
    {
        $re = $this->database->$table->update($where, ['$set' => $data],['multiple'=>true,'upsert'=>false]);
        return $re;
    }

    /**
     * 删除数据
     * @param $table    varcher     集合
     * @param $where    array       ['key'=>'value']   where key = value
     * @return bool
     */
    private function __remove($table, $where)
    {
        $re = $this->database->$table->remove($where, ['justOne' => false]);
        return $re;
    }


    /**
     * 获取唯一数据
     * @param $table
     * @param $key
     * @return array
     */
    private function __distinctData($table, $key, $query = array()) {
        if (!empty($query)) {
            $where = array('distinct' => $table, 'key' => $key, 'query' => $query);
        } else {
            $where = array('distinct' => $table, 'key' => $key);
        }

        $data = $this->database->command($where);
        return $data['values'];
    }

    /**
     * 获取db的所有集合名称
     * @return mixed
     */
    private function __getCollectionNames()
    {
        $collections = $this->database->getCollectionNames();
        return $collections;
    }

    //关闭链接
    public function close(){
        $this->mongodb->close();
    }

    public function getMongoDB()
    {
        return $this->mongodb;
    }
    /**
     * 记录程序日志
     *
     * @param  array $args
     *   [
     *      0=>  日志类型
     *      1=>  日志内容
     *   ]
     *
     * @return boolean
     */
    public function __log( $args)
    {
        if(!empty($args))
        {
            error_log(var_export( $args, true ) . ' ' . date('Y-m-d H:i:s') . "\r\n", 3, dirname(dirname(__FILE__)) . '/runtime/logs/mongoerror.log');
        }
        
    }
}