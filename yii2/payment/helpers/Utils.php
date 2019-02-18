<?php
namespace app\helpers;

class Utils
{
    /**
     * 验证邮箱地址的正确性
     *
     * @param string $email
     * @return boolean
     */
    public static function isEmail( $email = '' )
    {
       $isValid = true;
       $atIndex = strrpos($email, "@");
       if (is_bool($atIndex) && !$atIndex)
       {
          $isValid = false;
       }
       else
       {
          $domain = substr($email, $atIndex+1);
          $local = substr($email, 0, $atIndex);
          $localLen = strlen($local);
          $domainLen = strlen($domain);
          if ($localLen < 1 || $localLen > 64)
          {
             // local part length exceeded
             $isValid = false;
          }
          else if ($domainLen < 1 || $domainLen > 255)
          {
             // domain part length exceeded
             $isValid = false;
          }
          else if ($local[0] == '.' || $local[$localLen-1] == '.')
          {
             // local part starts or ends with '.'
             $isValid = false;
          }
          else if (preg_match('/\\.\\./', $local))
          {
             // local part has two consecutive dots
             $isValid = false;
          }
          else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
          {
             // character not valid in domain part
             $isValid = false;
          }
          else if (preg_match('/\\.\\./', $domain))
          {
             // domain part has two consecutive dots
             $isValid = false;
          }
          else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
          {
             // character not valid in local part unless 
             // local part is quoted
             if (!preg_match('/^"(\\\\"|[^"])+"$/',
                 str_replace("\\\\","",$local)))
             {
                $isValid = false;
             }
          }
          if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
          {
             // domain not found in DNS
             $isValid = false;
          }
       }
       return $isValid;
    }
    
    /**
     * 判断位运算
     *
     * @param int $data
     * @param int $val
     *
     * @return boolean
     */
    public static function hasBit( $data = 0, $val = 0 )
    {
        $data = intval( $data );
        $val = intval( $val );
        
        if( $val === 0 || $data === 0 )
        {
            return false;
        }
        
        return ( $data & $val ) == $val;
    }
    
    /**
     * 判断当前页面是否在微信浏览器下面
     * 
     * @return boolean 
     */
    public static function isUnderWeiXin()
    {
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false )
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取客户端IP
     *
     * @return string
     */
    public static function getClientIP()
    {
        $ip = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        }
        elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;

    }
    
    /**
     * 创建一个CURL网络访问
     *
     * @param string $url  要访问的地址
     * @param array $data  要POST的值
     * @param array $headers  要附加的HTTP头信息
     * @param string $contentType  传输方式
     * 
     * @return string  返回服务器的响应, false请求失败
     */
    public static function request($url = '', $data = array(), $headers = array(), $contentType = '')
    {
        if( empty( $url ) )
            return false;

        $timestart = microtime(1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名

        $temp = array();
        if( $headers )
        {
            foreach( $headers as $key => $val )
                $temp[] = "{$key}: {$val}";
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $temp );
        }

        if( $data )
        {
            if( !empty( $contentType ) )
            {
                if( $contentType === 'json' )
                {
                    if( !is_string( $data ) )
                    {
                        $data = json_encode( $data );
                    }

                    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge( array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data),
                    ), $temp ) );

                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
                else
                {
                    curl_close($ch);
                    return 'Request params type error';
                }
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
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
        }

        $output = curl_exec($ch);
        $error = curl_error($ch);
        $aStatus = curl_getinfo ( $ch );
        curl_close($ch);

        $timepass = number_format( microtime(1) - $timestart, 2, ".", "" );
        $output = trim( $output );

        $startVar = substr( $output, 0, 1 );
        $endVar = substr( $output, strlen($output) - 1, 1 );
        if( $startVar == '{' && $endVar == '}' || $startVar == '[' && $endVar == ']' )
        {
            $temp = @json_decode( $output, true );
            if( $temp )
                $output = $temp;
        }

        if( empty( $output ) && !empty( $error ) )
        {
            $output = $error;
        }

        if( !is_array( $output ) || !empty( $output["errorCode"] ) )
        {
            self::log( "api_error", array(
                "url" => $url,
                "data" => $data,
                "header" => $headers,
                "cost" => $timepass,
                "response" => $output,
            ) );
        }
        else
        {
            self::log( "api_access", array(
                "url" => $url,
                "data" => $data,
                "header" => $headers,
                "cost" => $timepass,
                "response" => $output,
            ) );
        }

        return $output;
    }
    
    /**
     * 创建一个并发CURL网络访问
     *
     * @param array $urls  要访问的地址和参数
     *     array(
     *         array(
     *             "url" => "http://www.baidu.com",
     *             "data" => array(
     *                 "aa" => "bb"
     *             ),
     *             "header" => array(
     *                 "cc" => "dd"
     *             ),
     *         ),
     *         array(
     *             "url" => "http://www.baidu.com",
     *             "data" => array(
     *                 "aa" => "bb"
     *             ),
     *             "header" => array(
     *                 "cc" => "dd"
     *             ),
     *         ),
     *     )
     * 
     * @return array  返回服务器的响应, false请求失败
     */
    public static function multiRequest( $urls = array() )
    {
        if( !is_array( $urls ) || empty( $urls ) )
            return false;
        
        $total = count( $urls );
        if( $total == 1 )
        {
            $data = end( $urls );
            return self::request( self::get( $data, "url", "t" ), self::get( $data, "data", "a" ), self::get( $data, "header", "a" ) );
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
                $url = self::get( $data, "url", "t" );
                $post = self::get( $data, "data", "a" );
                $header = self::get( $data, "header", "a" );
            }
            if( empty( $url ) )
                return false;
            
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5秒超时
        
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

        $responses = array();
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
                curl_multi_select($queue, 0.5);
            }

        }
        while ($active);

        curl_multi_close($queue);
        return $ret;
    }
    
    /**
     * 获取相应数组中的键值
     *
     * @param array $data   数据源
     * @param string $key    键值
     * @param string $type   获取的类型, s(string)  i(int)  f(float)  a(array)  t(trim) b(boolean)  默认s   
     * 
     * @return mixed
     */
    public static function get( $data = array(), $key = '', $type = '' )
    {
        if( is_string( $data ) )
            return self::get( $_GET, $data, $key );
            
        if( is_array( $key ) )
        {
            $ret = array();
            foreach( $key as $k )
                $ret[$k] = self::get( $data, $k, $type );
            
            return $ret;
        }
        
        $ret = isset( $data[$key] ) ? $data[$key] : null;
        
        $type = strtolower( $type );
        switch( $type )
        {
            case 's':
                $ret = strval( $ret );
                break;
            case 't':
                $ret = trim( strval( $ret ) );
                break;
            case 'i':
                $ret = intval( $ret );
                break;
            case 'f':
                $ret = floatval( $ret );
                break;
            case 'a':
                $ret = (array) $ret;
                break;
            case 'b':
                $ret = !!$ret;
                break;
        }
        
        return $ret;
    }
    
    /**
     * 获取相应数组中的键值
     *
     * @param string $key    键值
     * @param string $type   获取的类型, s(string)  i(int)  f(float)  a(array)  t(trim) b(boolean)  默认s   
     * 
     * @return mixed
     */
    public static function post( $key = '', $type = '' )
    {
        return self::get( $_POST, $key, $type );
    }
    
    /**
     * 获取HTTP头信息
     *
     * @param mix $key  要获取的头信息，获取多个用数组
     * @return mix
     */
    public static function header( $key = '' )
    {
        $headers = getallheaders();
        
        if( empty( $key ) )
            return $headers;
        
        if( is_string( $key ) )
            return isset( $headers[$key] ) ? $headers[$key] : null;
        
        $ret = array();
        if( is_array( $key ) )
        {
            foreach( $key as $k )
            {
                $ret[$k] = isset( $headers[$k] ) ? $headers[$k] : null;
            }
            
            return $ret;
        }
        
        return null;
    }
    
    /**
     * 获取路径，当路径不存在时，尝试创建路径
     *
     * @param string $path
     * @return string
     */
    public static function path( $path = '' )
    {
        if( empty( $path ) )
            return false;
        
        $parentDir = dirname( $path );
        if( !is_dir( $parentDir ) && !self::path( $parentDir ) )
        {
            return false;
        }
        
        $path = trim( $path );
        if( is_dir( $path ) )
            return $path;
        
        if( @mkdir( $path ) )
            return $path;
        
        return false;
    }
    
    /**
     * 记录一个日志文件，如果有就追加
     *
     * @param string $type       日志类型
     * @param mixed $data        日志数据内容
     *
     * @return int 返回写入的字节数
     */
    public static function log( $type = '', $data = array() )
    {
        $logPath = ROOT_PATH . "runtime/logs/{$type}/" . date("Ymd") . ".log";

        // 创建日志目录
        if( !self::path( dirname( $logPath ) ) )
        {
            return false;
        }

        $logData = $data;
        !is_array( $logData ) && $logData = array( "log" => $logData );
        $logData = array_merge( array(
            "logType" => $type,
            "log_time" => '中国时间：' . date( "Y-m-d H:i:s", gmtime() + ( 8 * 3600 ) ),
            "cur_url" => self::pageURL(),
            "cur_json" => file_get_contents("php://input"),
        ), $logData );

        $logContent = var_export( $logData, true );

        return !!file_put_contents( $logPath, $logContent . "\n\n\n=====================================================\n\n\n", FILE_APPEND );
    }
    
    public static function pageURL()
    {
        $pageURL = 'http://';

        if ($_SERVER["SERVER_PORT"] != "80")
        {
            if( $_SERVER["SERVER_PORT"] == 443 )
            {
                $pageURL = substr( $pageURL, 0, 4 ) . "s" . substr( $pageURL, 4 );
                $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            }
            else
            {
                $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
            }
        }
        else
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }
    
    /**
     * 获取客户端真实IP
     * 
     * @return string
     */
    public static function getRealIP()
    {
        if (isset($_SERVER))
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
                foreach ($arr AS $ip)
                {
                    $ip = trim($ip);

                    if ($ip != 'unknown')
                    {
                        $realip = $ip;

                        break;
                    }
                }
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else
            {
                if (isset($_SERVER['REMOTE_ADDR']))
                {
                    $realip = $_SERVER['REMOTE_ADDR'];
                }
                else
                {
                    $realip = '0.0.0.0';
                }
            }
        }
        else
        {
            if (getenv('HTTP_X_FORWARDED_FOR'))
            {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            }
            elseif (getenv('HTTP_CLIENT_IP'))
            {
                $realip = getenv('HTTP_CLIENT_IP');
            }
            else
            {
                $realip = getenv('REMOTE_ADDR');
            }
        }

        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        
        return $realip;
    }
}