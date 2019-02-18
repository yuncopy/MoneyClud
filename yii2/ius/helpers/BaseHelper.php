<?php
namespace app\helpers;

use yii;
use yii\base\Component;

/**
 * 基础助手类
 *
 * 这里的方法，适合所有项目
 *
 * @property Str $str
 * @property Arr $arr
 */
class BaseHelper extends Component
{
    protected $strHelper = null;
    protected $arrHelper = null;

    /**
     * 获取执行轨迹
     *
     * @param int $index 轨迹索引，从0开始
     * @param int $limit 获取轨迹条数
     * @param bool $ignoreArgs **true**时忽略调用方法的参数
     * @return array
     */
    public function getDebugBacktrace($index = 0, $limit = 0, $ignoreArgs = true)
    {
        $backtrace = debug_backtrace(
            $ignoreArgs ? DEBUG_BACKTRACE_IGNORE_ARGS : DEBUG_BACKTRACE_PROVIDE_OBJECT,
            $index < 0 ? $limit : $index + 1
        );

        return isset($backtrace[$index]) ? $backtrace[$index] : $backtrace;
    }


}
