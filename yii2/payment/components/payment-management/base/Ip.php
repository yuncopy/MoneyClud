<?php
namespace app\base;

use yii\base\Component;

/**
 * ip处理类
 *
 * ```php
 *    app()->ip->get(); // 返回ip字符串
 *    app()->ip->get(1); // 返回ip2long
 * ```
 */
class Ip extends Component
{
    /**
     * @var string|array $searches 搜索`$_SERVER`的键名
     */
    public $searches = 'HTTP_TRUE_CLIENT_IP,REMOTE_ADDR';

    /**
     * @var array ip信息，**['ip地址', 'ip2long']**
     */
    protected $ip = null;

    /**
     * 获取ip地址
     *
    ```
     * @param bool $ip2long 设为**true**时返回非负的ip2long
     * @return string|int
     *
     * - 获取成功时，返回ip地址（`$ip2long`为**true**时返回非负的ip2long）
     * - 获取失败，返回**0.0.0.0**（`$ip2long`为**true**时返回**0**）
     * @example
     * ```php
     *      app()->debug->dump(app()->ip->get());
     *
     *      // ip2long
     *      app()->debug->dump(app()->ip->get(1));
     * ```
     *
     * 以上例子输出类似：
     *
     * ```php
     *      string(9) "127.0.0.1"
     *      string(10) "2130706433"
     * ```
     */
    public function get($ip2long = false)
    {
        $index = $ip2long ? 1 : 0;
        if (null !== $this->ip) {
            return $this->ip[$index];
        }

        $ip = null;
        foreach (app()->helper->arr->trim($this->searches) as $item) {
            $ip = isset($_SERVER[$item]) ? $_SERVER[$item] : null;
            if ($ip) {
                $ip = strstr($ip, ',', true) ?: $ip;
                break;
            }
        }

        if ($ip2long = ip2long($ip)) {
            $this->ip = [$ip, sprintf('%u', $ip2long)];
        } else {
            $this->ip = ['0.0.0.0', 0];
        }

        return $this->ip[$index];
    } // end get
}
