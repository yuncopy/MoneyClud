<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use Closure;

class Curl extends Component
{
    /**
     * @var int 同时并发请求数
     */
    public $batchSize = 10;

    /**
     * @var int 最大请求数
     */
    public $maxSize = 0;

    /**
     * @var float `curl_multi_select`超时时间
     */
    public $timeout = 1.0;

    /**
     * @var Closure 所有请求开始时的回调
     */
    public $onExecute = null;

    /**
     * @var Closure 每个请求开始时的回调
     */
    public $onRequest = null;

    /**
     * @var Closure 每个请求成功时的回调
     */
    public $onSuccess = null;

    /**
     * @var Closure 所有请求结束时的回调
     */
    public $onDone = null;

    /**
     * @var array 每个curl默认配置
     */
    protected $defaultCurlOptions = [
        CURLOPT_SSL_VERIFYPEER  => 0,
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_CONNECTTIMEOUT  => 10,
    ];

    /**
     * @var array 请求队列
     */
    protected $items = [];

    /**
     * @var array 请求映射关系
     */
    protected $itemMap = [];

    /**
     * 所有已经完成的请求
     *
     * 每个请求为以下键值对关联数组：
     *
     * - **response**：返回内容
     * - **info**：`curl_getinfo`信息
     * - **item**：请求的url信息
     * - **ch**：curl句柄
     *
     * @var array
     */
    protected $allRequestItems = [];



    /**
     * 创建并发CURL网络访问队列
     * @return [type] [description]
     */
    function multiRequest( $urls = array() )
    {
        if( !is_array( $urls ) || empty( $urls ) )
        {
            return false;
        }
        
        $queue = curl_multi_init();
        $map = array();
        $ret = array();

        $i = 0;
        foreach ($urls as $uriKey => $data)
        {
            if( is_string( $data ) )
            {
                $url = $data;
                $post = array();
                $header = array();
            }
            else
            {
                $url = empty( $data["url"] ) ? "" : trim( $data["url"] );
                $post = empty( $data["data"] ) ? array() : $data["data"];
                $header = empty( $data["header"] ) ? array() : $data["header"];
            }

            if( empty( $url ) )
            {
                return false;
            }
            
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名  
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3600); // 5秒超时
            curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
            
        
            if( $header )
            {
                $temp = array();
                foreach( $header as $key => $val )
                    $temp[] = "{$key}: {$val}";
                curl_setopt ( $ch, CURLOPT_HTTPHEADER, $temp );
            }
            
            if( $post )
            {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }

            curl_multi_add_handle($queue, $ch);
            $map[(string) $ch] = $uriKey;
            
            $i ++;
        }

        // $responses = array();
        $active = null;
        do
        {
            while ( ($code = curl_multi_exec($queue, $active) ) == CURLM_CALL_MULTI_PERFORM ) ;

            if ($code != CURLM_OK)
            {
                break;
            }

            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($queue))
            {
                // get the info and content returned on the request
                //$info = curl_getinfo($done['handle']);
                //$error = curl_error($done['handle']);
                
                $output = curl_multi_getcontent($done['handle']);
                $output = trim( $output );
                // echo "<pre>";print_r($output);die;
                
                $startVar = substr( $output, 0, 1 );
                $endVar = substr( $output, strlen($output) - 1, 1 );
                if( $startVar == '{' && $endVar == '}' || $startVar == '[' && $endVar == ']' )
                {
                    $temp = @json_decode( $output, true );
                    if( $temp !== false )
                        $output = $temp;
                }
                
                $ret[$map[(string) $done['handle']]] = $output;
                //$results = callback(curl_multi_getcontent($done['handle']), $delay);
                //$responses[$map[(string) $done['handle']]] = compact('info', 'error', 'results');
                // remove the curl handle that just completed
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);
            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active > 0) {
                curl_multi_select($queue, 2);
            }

        }
        while ($active);

        curl_multi_close($queue);
        return $ret;
    }

    /**
     * 创建一个CURL网络访问
     *
     * @param string $url  要访问的地址
     * @param array  $data  要POST的值
     * @param array  $headers  要附加的HTTP头信息
     * @param string $dataType  数据类型  json  xml  post
     * 
     * @return string  返回服务器的响应, false请求失败
     */
    public function request($url = '', $data = array(), $headers = array(), $dataType = 'xml')
    {
        // echo "<pre>";print_r($data);die;
        if( empty( $url ) )
            return false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3600); // 5秒超时
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名  
        
        if( $headers )
        {
            $temp = array();
            foreach( $headers as $key => $val )
                $temp[] = "{$key}: {$val}";
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $temp );
        }

        
        if( $data )
        {
            // echo 11;die;
            curl_setopt($ch, CURLOPT_POST, 1);
            if( $dataType == 'xml' || $dataType == 'json' )
            {
                // echo "<pre>";print_r($data);die;

                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            else
            {
                $postData = $data;
                if( is_array( $postData ) )
                {
                    $postData = array();
                    foreach( $data as $key => $val )
                    {
                        $postData[] = $key . "=" . urlencode( $val );
                    }
                    $postData = implode( "&", $postData );

                }
                // echo "<pre>";print_r($postData);die;
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }            
        }

        
        $output = curl_exec( $ch );
        $error = curl_error( $ch );
        $httpInfo = curl_getinfo( $ch );
        curl_close($ch);

        $output = trim( $output );
    
        // echo "<pre>";print_r($output);die;
       
        if( $error && empty( $output ) )
        {
            $output = $error;
        }
        else
        {
            $startVar = substr( $output, 0, 1 );
            $endVar = substr( $output, strlen($output) - 1, 1 );
            if( $startVar == '{' && $endVar == '}' || $startVar == '[' && $endVar == ']' )
            {
                $temp = @json_decode( $output, true );
                if( $temp !== false )
                    $output = $temp;
            }
        }


        
        return $output;
    }

    /**
     * 执行一个POST请求
     * @param  [type] $url  [description]
     * @param  [type] $post [description]
     * @return [type]       [description]
     */
    public function curl_post( $url, $postData )
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    

    /**
     * 执行请求
     *
     * @param string|array 可选值有：
     *
     * - **string**：请求url，只执行该url的请求
     * - **array**：当包含**url**键名时，为添加一个请求，然后执行，否则批量添加多个请求
     *
     * @throws Exception 请求url数为空时
     * @return mixed 当为单个请求时，返回`curl_exec`的结果，否则返回**null**
     */
    public function execute($url = null)
    {
        $this->items = [];

        if ($url) {
            $this->addItem($url);
        }
        if (!$this->items) {
            throw new InvalidArgumentException('请求url数不能为空');
        }
        if ($this->onExecute) {
            call_user_func($this->onExecute, $this->items);
        }

        $count = count($this->items);
        if ($this->maxSize > 0 && $count > $this->maxSize) {
            throw new InvalidArgumentException(
                "最大请求数超出“{$this->maxSize}”限制，当前为：{$count}"
            );
        } elseif (1 == $count) {
            $result = $this->single();
        } else {
            $this->multi();
            $result = null;
        }

        if ($this->onDone) {
            call_user_func($this->onDone, $this->allRequestItems);
        }

        return $result;
    } // end execute

    /**
     * 创建一个`app\components\Curl`实例
     *
     * ~~~
     *    yii::$app->curl->create()->execute('http://baidu.com');
     * ~~~
     *
     * @array $config 配置
     * @return app\components\Curl
     */
    public function create(array $config = [])
    {
        $config['class'] = __CLASS__;
        return yii::createObject($config);
    }

    public function onExecute(Closure $onExecute)
    {
        $this->onExecute = $onExecute;
        return $this;
    }

    public function onRequest(Closure $onRequest)
    {
        $this->onRequest = $onRequest;
        return $this;
    }

    public function onSuccess(Closure $onSuccess)
    {
        $this->onSuccess = $onSuccess;
        return $this;
    }

    public function onDone(Closure $onDone)
    {
        $this->onDone = $onDone;
        return $this;
    }

    /**
     * 执行批处理请求
     *
     * @return void
     */
    protected function multi()
    {
        $this->batchSize = min(count($this->items), $this->batchSize);
        $master = curl_multi_init();

        // 首批
        for ($i = 0; $i < $this->batchSize; $i++) {
            $this->addHandle($master, $i);
        }
        do {
            while (($mrc = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM);

            if (CURLM_OK != $mrc) {
                Yii::error(array_slice($this->items, $i), true);
                break;
            }
            // 一个请求执行完毕
            while ($done = curl_multi_info_read($master)) {
                $info       = curl_getinfo($done['handle']);
                $response   = curl_multi_getcontent($done['handle']);
                $resourceId = (int) $done['handle'];
                $item       = $this->items[$this->itemMap[$resourceId]];
                $this->allRequestItems[$resourceId] = [
                    'response'  => $response,
                    'info'      => $info,
                    'item'      => $item,
                    'ch'        => $done['handle'],
                ];
                if ('' !== ($error = curl_error($done['handle']))) {
                    Yii::error($error);
                }

                // 回调
                if ($this->onSuccess){
                    unset($this->itemMap[$resourceId]);
                    call_user_func($this->onSuccess, $response, $item, $info, $done['handle']);
                }
                // 开始新的请求
                if (isset($this->items[$i]) && $i < count($this->items)) {
                    $this->addHandle($master, $i);
                    $i++;
                }

                // 干掉完成的请求
                curl_multi_remove_handle($master, $done['handle']);
            } // end while

            // 等待所有curl批处理中的活动连接
            if ($running) {
                curl_multi_select($master, $this->timeout);
            }

        } while ($running);

        curl_multi_close($master);
    } // end multi

    /**
     * 执行单一请求
     *
     * @return mixed 如果提供回调，返回回调结果，否则返回`curl_exec`的结果
     */
    protected function single()
    {
        $ch         = curl_init();
        $item       = array_shift($this->items);
        $options    = $this->getOptions($item);
        curl_setopt_array($ch, $options);
        $resourceId = (int) $ch;
        $item['resourceId'] = $resourceId;

        if ($this->onRequest) {
            call_user_func_array($this->onRequest, [&$item]);
        }

        $response   = curl_exec($ch);
        $info       = curl_getinfo($ch);
        $this->allRequestItems = [
            $resourceId => [
                'response'  => $response,
                'info'      => $info,
                'item'      => $item,
                'ch'        => $ch,
            ]
        ];

        if ('' !== ($error = curl_error($ch))) {
             Yii::error($error);
        }
        if ($this->onSuccess) {
            call_user_func($this->onSuccess, $response, $item, $info, $ch);
        }
        return $response;
    } // end single

    /**
     * 添加一个请求请求队列中
     *
     *
     * @param string|array $item 请求url或者一个数组（可为字符串或者包含以下键值对的关联数组或者是一个数组）
     *
     * - **url**：请求url
     * - **method**：请求方法，可选值为：**get**或者**post**，默认为**get**，当设置**data**数据时自动转换为**post**
     * - **data**：POST请求的**field => value**关联数组，值不需要**urlencode**
     * - **options**：curl请求配置，其中头部使用**headers**替代`CURLOPT_HTTPHEADER`
     *
     * @return self
     */
    protected function addItem($item)
    {
        $default = [
            'method'    => 'get',
            'data'      => [],
            'headers'   => [],
            'options'   => [],
        ];

        if (is_string($item)) {
            return $this->addItem(['url' => $item]);
        }

        if (isset($item['url'])) {
            $this->items[] = array_merge($default, $item);
        } else {
            foreach ($item as $v) {
                $this->addItem($v);
            }
        }

        return $this;
    }

    /**
     * 添加一个curl处理
     *
     * @param object $master
     * @param int $i
     * @return void
     */
    protected function addHandle(&$master, $i)
    {
        $ch         = curl_init();
        $options    = $this->getOptions($this->items[$i]);

        curl_setopt_array($ch, $options);
        curl_multi_add_handle($master, $ch);

        // 映射
        $resourceId = (int) $ch;
        $this->itemMap[$resourceId] = $i;
        $this->items[$i]['resourceId'] = $resourceId;

        if ($this->onRequest) {
            call_user_func_array($this->onRequest, [&$this->items[$i]]);
        }
    }

    /**
     * 获取指定请求配置
     *
     * @param array $item 请求
     * @return array
     */
    protected function getOptions(array &$item)
    {
        $options = $this->defaultCurlOptions; // 默认配置
        $options[CURLOPT_URL] = $item['url'];

        if (!empty($item['options'])) {
            foreach ($item['options'] as $key => $value) {
                $options[$key] = $value;
            }
        }
        if (!empty($item['data'])) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($item['data']);
        }
        if (!empty($item['headers'])) {
            $options[CURLOPT_HEADER] = 0;
            $options[CURLOPT_HTTPHEADER] = $item['headers'];
        }

        unset($options['headers']);
        $item['curlOptions'] = $options;
        return $options;
    }
}
