<?php



/**
 *
 * 实例代码而已
 *
 *
*/

class cache
{




 /**
     * 设置缓存/读取
     *
     * @author chenhuian
     *
     * @param  string $key 键名
     * @param callable $callable 回调函数
     * @param int $duration 时间
     * @return  mixed $value 时间
     *
    */
    public function getOrSet($key, $callable, $duration = null){

        $value = $this->redis->get($key);
        if ($value !== false && $value !== null) {
            return unserialize($value);
        }
        $value = call_user_func($callable, $this);
        $setResult =  $this->redis->set($key, serialize($value));
        $this->redis->expire($key, $duration);
        if (!$setResult) {
            Yii::warning('Failed to set cache value for key ' . json_encode($key), __METHOD__);
        }
        return $value;
    }
    
    
     /**
     *
     * 设置、获取缓存数据
     *
     * @author chenhuian
     * @param  string $key 缓存键名
     * @param array $data 需要缓存数组
     * @param array $callback 回调函数
     * @return  mixed $cache_data 数组
     *
     */
    public function setGetRedisCache($key = '', $data = [], $callback = [])
    {

        try {
            $cache_data = [];
            $redis = PRedis::getInstance();

            //$redis->del($key);
            //$aa = $redis->get($key);

            //设置缓存
            $setCache = function ($key, $data) use ($redis) {
                $redis->set($key, json_encode($data));
                $redis->expire($key, self::CACHE_TIME);  // 设置缓存时间 秒 单位
                return $data;
            };

            //获取缓存
            $getCache = function ($key) use ($redis) {
                $redis_data  = json_decode($redis->get($key), true);
                return is_null($redis_data) ? false : $redis_data;
            };

            // 执行回调函数
            $execCallBack = function ($callback) {
                return call_user_func($callback);
            };


            if ($data && $key) {
                if ($res = $execCallBack($callback)) {  // 先添加入口再进行设置缓存数据
                    $cache_data = $setCache($key, $data);
                }
            } elseif ($key) {
                $cache_data = $getCache($key);   // 如果没有缓存则执行回调函数，查询数据库
                if (!$cache_data) {
                    $cache_data = $execCallBack($callback); // 查询有结果再缓存
                    $cache_data &&  $setCache($key, $cache_data);
                }
            }
            return $cache_data;
        } catch (\Exception $e) {

            return false;

        }
    }

}
