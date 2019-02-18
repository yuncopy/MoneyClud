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

    /**
     * 获取子类
     *
     * @param array $data 所有数据
     * @param int $id 分id
     * @param bool $includeSelf `true`包含本身
     * @param string $idName 主键字段名称
     * @return string 所有子类id，如果没有子类，返回空字符串或空数组
     */
    public function getSubItems(array $data, $id, $includeSelf = true, $idName = 'id')
    {
        $result = [];
        if (!isset($data[$id])) {
            return [];
        } elseif ($includeSelf) {
            $result[$id] = $data[$id];
        }

        $item   = $data[$id];
        $node   = $item['node'];
        $level  = $item['level'];
        foreach ($data as $k => $v) {
            if (0 === strpos($v['node'], $node . ',')
                && $v['level'] > $level
                && $k != $id
            ) {
                $result[$v[$idName]] = $v;
            }
        }
        return $result;
    }

    /**
     * @return Arr
     */
    protected function getArr()
    {
        if (null === $this->arrHelper) {
            $this->arrHelper = yii::createObject(__NAMESPACE__ . '\Arr');
        }
        return $this->arrHelper;
    }

    /**
     * @return Str
     */
    protected function getStr()
    {
        if (null === $this->strHelper) {
            $this->strHelper = yii::createObject(__NAMESPACE__ . '\Str');
        }
        return $this->strHelper;
    }
}
