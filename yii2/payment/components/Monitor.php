<?php
/**
 * 接入监控系统相关
 * @author kangpengfei 2016.09.23
 */
namespace app\components;

use Yii;

class Monitor {
    
    const MONITOR_REPORT_TRIGGLE	= 1; // 发送开关，是否发送信息到监控系统
    const ASYNCHRONY_SEND			= 1; // 异步发送开关, 目前只支持, 文件存储拟队列
    const QUEUE_FILE_NAME			= 'error_reporting_queue.txt'; // 错误报告文件队列文件名
    
    const METADATA_FILE = '/tmp/metadata_tags';//该文件内容记录了该项目标识名(由运维人员维护)
    
    static public $errorKey			= [];    // 错误缓存KEY-报错MD5错误信息，同一错误不再重复发送
    
    static public $responseCodeMap = [
        '0'		=> 'Success-成功',
        '10001' => 'Token error-令牌错误',
        '10002' => 'Token expired-令牌过期',
        '10003' => 'Incomplete data-数据不完整',
        '10004' => 'Data format error-数据格式错误',
        '10005' => 'Server Error-服务器错误',
        '10006' => 'No data-无相关数据',
        '10007' => 'Custom error-自定义错误',
    ];
    
    /**
     * 配置信息
     */
    private $monitorConfig = [
        'is_open' => true, //是否开启监控
        'point_list' => [
            //php监控点
            'php' => [
                'point_code' => 'WFZ25228',
                //yii框架中php错误对应我们的错误码
                'yii_code' => [
                    '1'   => '100001', '2'   => '100002', '4'   => '100005', '8'   => '100010', '16'  => '100005',
                    '32'  => '100006', '64'  => '100005', '128' => '100006', '256' => '100009', '512' => '100010'
                ],
                //错误码对应级别
                'error_code_lev' => [
                    '100001' => 2, '100002' => 5, '100003' => 2, '100005' => 2, '100006' => 5, '100009' => 2, '100010' => 5
                ],
                //默认错误码
                'def_error_code' => '100010',
            ],
            //mysql监控点
            'mysql' => [
                'point_code' => 'WFZ24418',
                'yii_code' => [
                    //数据库连接失败
                    '2000' => '100101', '2001' => '100101', '2002' => '100101', '2003' => '100101', '2004' => '100101',
                    '2005' => '100101', '2006' => '100101', '2007' => '100101', '2008' => '100101', '2009' => '100101',
                    //数据表不存在
                    '1051' => '100102',
                    //SQL命令无权限执行
                    '1147' => '100103', '1211' => '100103', '1227' => '100103',
                    //表字段不存在
                    '1054' => '100104',
                    //SQL语法错误
                    '1149' => '100105',
                    //数据库连接超时
                    '1158' => '100106', '1159' => '100106', '1160' => '100106', '1161' => '100106',
                ],
                'error_code_lev' => [
                    '100100' => 2, //默认语法错误
                    '100101' => 2, //数据库连接失败, 包涵 数据库账号密码错误情况
                    '100102' => 1, //数据表不存在
                    '100103' => 2, //SQL命令无权限
                    '100104' => 1, //表字段不存在
                    '100105' => 2, //SQL语法错误
                    '100106' => 2, //数据库连接超时
                    '100107' => 2, //MySQL数据插入，键冲突，插入失败
                    '100108' => 2, //MySQL查询超时，PHP程序主动停止MySQL查询进程
                    '100109' => 2, //MySQL查询进程，被系统停止
                    '100110' => 0, //MySQL数据库连接，账号无权限
                    '100111' => 2, //MySQL数据表锁定，会自动尝试恢复
                    '100112' => 0, //MySQL未选择数据库，常见伴随着 数据库账号无权限看不到数据库
                ],
                //默认错误码
                'def_error_code' => '100100',
            ],
            // redis错误
            'redis' => [
                'point_code' => 'BSE89510',
                'yii_code' => [
                    'redis_alert1'      => '120401',    // redis连接超时
                    'redis_alert2'      => '120402',    // redis连接错误
                ],
                'error_code_lev' => [
                    '120401' => 2,
                    '120402' => 2,
                ],
                'def_error_code' => '120402',
            ],
            // mongodb错误
            'mongodb' => [
                'point_code' => 'BSE97009',
                'yii_code' => [
                    'mongodb_alert1'      => '120501',    // redis连接错误
                ],
                'error_code_lev' => [
                    '120501' => 2,
                ],
                'def_error_code' => '120501',
            ],
            // 网站请求支付平台订单量监控
            'order_count' => [
                'point_code' => 'WPP41419',
                'yii_code' => [
                    'order_count_alert2'    => '112101',        // 网站请求支付订单量小于P2值
                    'order_count_alert3'    => '112102',     // 网站请求支付订单量小于P3值
                ],
                'error_code_lev' => [
                    '112101' => 2,
                    '112102' => 3,
                ],
                'def_error_code' => '112102',
            ],
            // 平台支付成功数监控
            'success_count' => [
                'point_code' => 'WPP58294',
                'yii_code' => [
                    'success_count_alert2'      => '112201',    // 平台支付成功数小于P2值
                    'success_count_alert3'      => '112202',    // 平台支付成功数小于P3值
                ],
                'error_code_lev' => [
                    '112201' => 2,
                    '112202' => 3,
                ],
                'def_error_code' => '112202',
            ],
            // 平台支付成功率监控
            'success_rate' => [
                'point_code' => 'WPP33235',
                'yii_code' => [
                    'success_rate_alert2'      => '112301',    // 平台支付成功率小于P2值
                    'success_rate_alert3'      => '112302',    // 平台支付成功率小于P3值
                ],
                'error_code_lev' => [
                    '112301' => 2,
                    '112302' => 3,
                ],
                'def_error_code' => '112302',
            ],
            // 平台支付错误码率监控
            'error_rate' => [
                'point_code' => 'WPP18511',
                'yii_code' => [
                    'error_rate_alert2'      => '112401',    // 平台支付结果错误代码率大于P2值
                    'error_rate_alert3'      => '112402',    // 平台支付结果错误代码率大于P3值
                ],
                'error_code_lev' => [
                    '112401' => 2,
                    '112402' => 3,
                ],
                'def_error_code' => '112402',
            ],
            // 平台支付严重错误监控
            'serious_error' => [
                'point_code' => 'WPP86821',
                'yii_code' => [
                    'order_count_alert1'        => '112501',    // 网站请求支付订单量小于P1值
                    'success_count_alert1'      => '112502',    // 平台支付成功数小于P1值
                    'success_rate_alert1'       => '112503',    // 平台支付成功率小于P1值
                    'error_rate_alert1'         => '112504',    // 平台支付错误代码率大于P1值
                ],
                'error_code_lev' => [
                    '112501' => 1,
                    '112502' => 1,
                    '112503' => 1,
                    '112504' => 1,
                ],
                'def_error_code' => '112504',
            ],
            // soa支付平台错误告警
            'soa_error' => [
                'point_code' => 'WPP47246',
                'yii_code' => [
                    'soa_alert1'    => '113501',    // soa支付平台严重错误
                    'soa_alert2'    => '113502',    // soa支付平台一般错误
                ],
                'error_code_lev' => [
                    '113501' => 1,
                    '113502' => 2,
                ],
                'def_error_code' => '113502',
            ],
            // 脚本任务数据堆积
            'data_overstock' => [
                'point_code' => 'WPP88117',
                'yii_code' => [
                    'kafka_alert2'        => '113401',    // kafka信息堆积
                    'redis_gather_alert2'      => '113402',    // 支付前端redis信息堆积
                    'kafka_consumer_alert2'   => '113403',//kafka消费队列堆积
                ],
                'error_code_lev' => [
                    '113401' => 2,
                    '113402' => 2,
                    '113403' => 2,
                ],
                'def_error_code' => '113401',
            ],
            //soa汇率监控
            'soa_rate'=>[
                'point_code' => 'WFZ93739',
                'yii_code' => [
                    'p1'        => '101701',    // P1
                    'p2'      => '101702',    // p2
                ],
                'error_code_lev' => [
                    '101701' => 2,
                    '101702' => 2,
                ],
                'def_error_code' => '101701',
            ],
        ]
    ];
    
    /**
     * @brief:  获取北京时区时间
     * @return:
     */
    static public function getBeiJingDateTime()
    {
        $timeZone = "Asia/Shanghai";
        $time = time();
        $dateTime = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone($timeZone));
        $dateTime->setTimestamp($time);
        return $dateTime->format('Y-m-d H:i:s');
    }
    
    /**
     * 保存错误日志
     * @param string $monitorName 监控点名称
     * @param string $yiiCode     系统错误码
     * @param string $info        错误信息
     * @param boolean $isTest      是否为自检信息
     * @return array|mixed
     */
    function saveErrorData($monitorName = '', $yiiCode = '', $info = '', $isTest = false)
    {
        if ($monitorName && $info) {
            $monitorConfig = $this->monitorConfig;
            if ($monitorConfig && isset($monitorConfig['is_open']) && $monitorConfig['is_open']) {
                $request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                $domain = "";
                if (in_array($monitorName, ["php", "mysql", "redis", "mongodb"])) {     // php、mysql告警设置domain信息
                    $domain = 'Url:[' . Yii::$app->params['url'] . $request . '] - ';
                }
                
                if ($monitorName == 'mysql') {
                    //mysql监控点通过匹配错误内容的关键字来取错误码
                    $errorCode = self::getMySqlErrorCode($info);
                } else {
                    //非mysql监控点则根据定义好的code来取错误码
                    $errorCodeList = $monitorConfig['point_list'][$monitorName]['yii_code'];
                    $errorCode  = isset($errorCodeList[$yiiCode]) ? $errorCodeList[$yiiCode] : $monitorConfig['point_list'][$monitorName]['def_error_code'];
                }
                
                $errorCodeLev  = $monitorConfig['point_list'][$monitorName]['error_code_lev'];
                $level		   = $errorCodeLev[$errorCode];
                
                $errorData  = [
                    'point_code'  => $monitorConfig['point_list'][$monitorName]['point_code'],
                    'error_code'  => $errorCode,
                    'level'		  => $level,
                    'content'     => ['info' => $domain . $info],
                    'is_test'     => 0,
                    'server_ip'   => self::getServerIp(),
                    'server_name' => self::getServerName()
                ];
                
                //判断自检（php错误监控点会包含'kpf_table', mysql错误监控也会包含'kpf_table'）
                if ($isTest || strpos($info, 'kpf_table') !== false) {
                    $errorData['is_test']	= 1;
                    $errorData['content']	= ['info' =>$domain . $monitorName . '自检数据. ' . (in_array($monitorName, ['php','mysql']) ? '' : $info)];
                }
                
                $response = self::report( $errorData);
                return $response;
            }
        }
    }
    
    /**
     * @brief:  发送报告JSON请求
     * @param:  array $data
     * @param:  bool  $is_asynchrony_send  是否采用异步发送, 默认不是
     * @return:
     */
    static public function report( $data, $is_asynchrony_send = false )
    {
        if ( !self::MONITOR_REPORT_TRIGGLE ) { 	// 关闭发送的小开关，如有情况先打开。
            return ['code'=>0, 'msg'=>"已关闭发送"];
        }
        // 限制报错信息长度
        $data['content']['info'] = substr( $data['content']['info'], 0, 1024 );
        // 相同的错误信息，只发送一次
        if ( !empty($data['content']['info']) && is_string($data['content']['info']) ) {
            $key = self::getErrKey( $data );
            if ( isset(self::$errorKey[$key]) ) {
                self::$errorKey[$key]++;
                return ['code'=>0, 'msg'=>"相同错误已发送".self::$errorKey[$key]];
            }
            self::$errorKey[$key] = 1;
        }
    
        $data['notice_time'] = self::getBeiJingDateTime();
    
        // 是否异步发送
        if ( self::ASYNCHRONY_SEND			// 异步发送总开关
            && $data['is_test'] != 1		// 监控自检的不需要异步
            && $is_asynchrony_send === true // 主动采用异步发送, 默认是同步时时发送
        ) {
            return self::asynchronyReport($data);
        }
        $config = Yii::$app->params['rms110_api'];
        $apiUrl		= $config['url'];
        $ssl		= substr($apiUrl, 0, 8) == "https://" ? TRUE : FALSE;
        $post		= self::signData( $data );
    
        $header = array(
            "Cache-Control: no-cache, no-store, max-age=0, must-revalidate",
            "Expires: Mon, 26 Jul 1997 05:00:00 GMT"
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if ( $ssl ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        if ( empty($response) ) {
            // 时时发送失败，转异步发送
            return self::asynchronyReport( $data );
            //return ['code'=>-1, 'msg'=>"{$apiUrl}接口访问失败!"];
        }
        $response = json_decode( $response, true );
        $response['msg'] = isset(self::$responseCodeMap[$response['code']]) ? self::$responseCodeMap[$response['code']] : $response['code'];
        $response['requestData'] = $data;
        return $response;
    }
    
    /**
     * @brief:  获取错误信息MD5生成的KEY
     * @param:  $data
     * @return:
     */
    static public function getErrKey( $data )
    {
        if ( !empty($data['content']['info']) && is_string($data['content']['info']) )
        {
            $MysqlConnectFailMsg1 = '/connect\([^\)]*\)\s*connect\s*error!/i';
            if ( preg_match( $MysqlConnectFailMsg1, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
            $MysqlConnectFailMsg2 = '/Sorry,\s*due\s*to\s*line\s*fault,\s*temporarily\s*unable\s*to\s*browse,\s*we\s*are\s*dealing\s*with./i';
            if ( preg_match( $MysqlConnectFailMsg2, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
            $TableDoesnExistMsg = "/table\s*'[^']*'\s*doesn't\s*exist/i";
            if ( preg_match( $TableDoesnExistMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
            $CommandDeniedMsg = "/command\s*denied\s*to\s*user\s*'[^']*'@'[^']*'\s*for\s*table\s*'[^']*'/i";
            if ( preg_match( $CommandDeniedMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
            $UnknownColumnMsg = "/unknown\s*column\s*'[^']*'\s*in\s*'[^']*'/i";
            if ( preg_match( $UnknownColumnMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
            //MySQL server has gone away
            $ServerGoneAwayMsg = "/MySQL\s*server\s*has\s*gone\s*away/i";
            if ( preg_match( $ServerGoneAwayMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
    
            //MySQL数据插入，键冲突，插入失败  错误级别2
            $DuplicateEntryMsg = "/Duplicate\s*entry\s*'[^']*'\s*for\s*key/i";
            if ( preg_match( $DuplicateEntryMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
    
            //MySQL查询超时，PHP程序主动停止MySQL查询进程
            $KillMsg = "/KILL\s*\d+|Query\s*execution\s*was\s*interrupted/i";
            if ( preg_match( $KillMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
    
            //MySQL查询进程，被系统停止
            $LostConnectionMsg = "/Lost\s*connection\s*to\s*MySQL\s*server\s*during\s*query/i";
            if ( preg_match( $LostConnectionMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
    
            //MySQL数据库连接，账号无权限
            $AccessDeniedMsg = "/Access\s*denied\s*for\s*user\s*'[^']+'@'[^']+'/i";
            if ( preg_match( $AccessDeniedMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
    
            //MySQL数据表锁定,会自动尝试回复
            $DeadlockMsg = "/Deadlock\s*found\s*when\s*trying\s*to\s*get\s*lock/i";
            if ( preg_match( $DeadlockMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
    
            //MySQL未选择数据库，常见伴随着 数据库账号无权限看不到数据库
            $NoDatabaseMsg = "/No\s*database\s*selected/i";
            if ( preg_match( $NoDatabaseMsg, $data['content']['info'], $match ) ) {
                return md5( $match[0] );
            }
            return md5( substr( $data['content']['info'], strpos($data['content']['info'],'/') ) );
        }
        return md5('');
    }
    
    /**
     * 通过mysql报错内容,获取错误码
     * @param $error string 错误内容
     * @return number
     */
    static public function getMySqlErrorCode( $error )
    {
        $error_code = 100100;  //默认语法错误
    
        //数据库连接失败, 包涵 数据库账号密码错误情况
        $MysqlConnectFailMsg1 = '/connect\([^\)]*\)\s*connect\s*error!/i';
        if ( preg_match( $MysqlConnectFailMsg1, $error, $match ) ) {
            $error_code = 100101;
        }
    
        //数据库连接失败
        $MysqlConnectFailMsg2 = '/Sorry,\s*due\s*to\s*line\s*fault,\s*temporarily\s*unable\s*to\s*browse,\s*we\s*are\s*dealing\s*with./i';
        if ( preg_match( $MysqlConnectFailMsg2, $error, $match ) ) {
            $error_code = 100101;
        }
    
        //数据表不存在
        $TableDoesnExistMsg = "/table\s*'[^']*'\s*doesn't\s*exist/i";
        if ( preg_match( $TableDoesnExistMsg, $error, $match ) ) {
            $error_code = 100102;
        }
    
        //SQL命令无权限
        $CommandDeniedMsg = "/command\s*denied\s*to\s*user\s*'[^']*'@'[^']*'\s*for\s*table\s*'[^']*'/i";
        if ( preg_match( $CommandDeniedMsg, $error, $match ) ) {
            $error_code = 100103;
        }
    
        //表字段不存在
        $UnknownColumnMsg = "/unknown\s*column\s*'[^']*'\s*in\s*'[^']*'/i";
        if ( preg_match( $UnknownColumnMsg, $error, $match ) ) {
            $error_code = 100104;
        }
    
        //SQL语法错误
        $SqlSyntaxMsg = "/You\s*have\s*an\s*error\s*in\s*your\s*SQL\s*syntax/i";
        if ( preg_match( $SqlSyntaxMsg, $error, $match ) ) {
            $error_code = 100105;
        }
    
        //数据库连接超时
        $ServerGoneAwayMsg = "/MySQL\s*server\s*has\s*gone\s*away/i";
        if ( preg_match( $ServerGoneAwayMsg, $error, $match ) ) {
            $error_code = 100106;
        }
    
        //MySQL数据插入，键冲突，插入失败  错误级别2
        $DuplicateEntryMsg = "/Duplicate\s*entry\s*'[^']*'\s*for\s*key/i";
        if ( preg_match( $DuplicateEntryMsg, $error, $match ) ) {
            $error_code = 100107;
        }
    
        //MySQL查询超时，PHP程序主动停止MySQL查询进程
        $KillMsg = "/KILL\s*\d+|Query\s*execution\s*was\s*interrupted/i";
        if ( preg_match( $KillMsg, $error, $match ) ) {
            $error_code = 100108;
        }
    
        //MySQL查询进程，被系统停止
        $LostConnectionMsg = "/Lost\s*connection\s*to\s*MySQL\s*server\s*during\s*query/i";
        if ( preg_match( $LostConnectionMsg, $error, $match ) ) {
            $error_code = 100109;
        }
    
        //MySQL数据库连接，账号无权限
        $AccessDeniedMsg = "/Access\s*denied\s*for\s*user\s*'[^']+'@'[^']+'/i";
        if ( preg_match( $AccessDeniedMsg, $error, $match ) ) {
            $error_code = 100110;
        }
    
        //MySQL数据表锁定,会自动尝试回复
        $DeadlockMsg = "/Deadlock\s*found\s*when\s*trying\s*to\s*get\s*lock/i";
        if ( preg_match( $DeadlockMsg, $error, $match ) ) {
            $error_code = 100111;
        }
    
        //MySQL未选择数据库，常见伴随着 数据库账号无权限看不到数据库
        $NoDatabaseMsg = "/No\s*database\s*selected/i";
        if ( preg_match( $NoDatabaseMsg, $error, $match ) ) {
            $error_code = 100112;
        }
    
        return $error_code;
    }
    
    /**
     * @brief:  插入异步发送队列里，进行异步发送
     * @param:  $data
     * @return:
     */
    static public function asynchronyReport( $data, $type='file' )
    {
        switch( $type ) {
            case 'file':
                return self::pushToFilQueue( $data );
            default:
                break;
        }
    }
    
    /**
     * @brief:  将错误消息，压入消息文件队列
     * @param:  $data
     * @return:
     */
    static public function pushToFilQueue( $errorData )
    {
        $errKey = self::getErrKey( $errorData );
        $ErrorQueueFileName =  yii::$app->runtimePath . '/logs/' . self::QUEUE_FILE_NAME;
        $CurrentError = '';
        if ( is_readable($ErrorQueueFileName) ) {
            $CurrentError = file_get_contents( $ErrorQueueFileName );
        }
        if ( !empty($CurrentError) && false!==strpos( $CurrentError, $errKey ) ) {
            return ['code'=>0, 'msg'=>"错误已在文件中"];
        }
        // 文件不可写
        if ( !file_exists($ErrorQueueFileName) && !is_writeable(yii::$app->runtimePath . '/logs/') )
        {
            return ['code'=>-1, 'msg'=>"采用异步发送的形式，但{$ErrorQueueFileName}文件队列无权写入"];
        }
        // 文件大于 10MB, 不允许在写
        $MB = pow( 1024, 2 );
        if ( file_exists($ErrorQueueFileName) && filesize($ErrorQueueFileName) > $MB*10 )
        {
            return ['code'=>-2, 'msg'=>"{$ErrorQueueFileName}文件队列, 过大, 请检查异步发送定时脚本( MonitorAsynchronyReport.php )是否执行"];
        }
        // 序列化
        $data[$errKey] = $errorData;
        $serializeErrData = serialize( $data );
    
        // 加入文件队列
        $fp = fopen( $ErrorQueueFileName, "a" ); //
        if ( flock($fp, LOCK_EX) ) {
            fputs($fp,"{$serializeErrData}||");
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        return ['code'=>0, 'msg'=>"成功加入异步发送文件队列"];
    }
    
    /**
     * @brief:  数据完整性签名
     * @param:  $data
     * @return:
     */
    static public function signData( $data )
    {
        $config = Yii::$app->params['rms110_api'];
        $token	=$config['token'];
        return 'token=' . md5($token . json_encode($data)) . '&encode=1&data=' . base64_encode(json_encode($data));
    }
    
    /**
     * 获取服务器标识(格式如:affapi_web_product_127.0.0.1)
     */
    static public function getServerName()
    {
        $serverName = '';
        if (is_readable(self::METADATA_FILE)) {
            $tempName = file_get_contents(self::METADATA_FILE);
            $tempName = trim($tempName);
            $serverName = str_replace('name:', '', $tempName);
        }
        return strtolower($serverName);
    }
    
    /**
     * 获取服务器IP(内网IP)
     */
    static public function getServerIp()
    {
        $serverName = self::getServerName();
        $tempArr = explode('_', $serverName);
        return $tempArr ? end($tempArr) : '127.0.0.1';
    }
}