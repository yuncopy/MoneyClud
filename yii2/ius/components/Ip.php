<?php
namespace app\components;

use Yii;

/**
 * ip处理类
 *
 * ~~~
 *    yii::$app->ip->get(); // 返回ip字符串
 *    yii::$app->ip->get(1); // 返回ip2long
 * ~~~
 */
class Ip
{
    /**
     * @var array ip信息，**['ip地址', 'ip2long']**
     */
    protected $ip = null;

    /**
     * 获取ip地址，支持返回ip字符串和`ip2long`
     *
     * @param bool $ip2long **true**时返回`ip2long`（非负）
     * @return string|int
     */
    public function get($ip2long = false)
    {
        $index = $ip2long ? 1 : 0;

        if (null !== $this->ip) {
            return $this->ip[$index];
        } elseif (isset($_SERVER['HTTP_TRUE_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_TRUE_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = null;
        }

        if ($ip2long = ip2long($ip)) {
            $this->ip = [$ip, sprintf('%u', $ip2long)];
        } else {
            $this->ip = ['0.0.0.0', 0];
        }

        return $this->ip[$index];
    }
}
