<?php
namespace app\base;

use yii;
use yii\base\Component;
use yii\base\InvalidCallException;

/**
 * 调试类，在调试开发时输出调试信息
 */
class Debug extends Component
{
    protected $methods = [
        'dump'      => 'var_dump',
        'export'    => 'var_export',
    ];

    protected $debug = false;

    /**
     * @param array $params
     * @return bool
     */
    public function dump(...$params)
    {
        return $this->debug('dump', $params);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function export(...$params)
    {
        return $this->debug('export', $params);
    }

    /**
     * @param null $string
     * @return bool
     */
    public function end($string = null)
    {
        return $this->debug('end', [$string]);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->debug = app()->env->isDev() || app()->env->isPhpunit();
    }

    /**
     * @param $method
     * @param array $params
     * @return bool
     */
    protected function debug($method, array $params)
    {
        if (!$this->debug) {
            return false;
        } elseif ('end' != $method && !array_key_exists(0, $params)) {
            throw new InvalidCallException(
                static::class . ':' . $method . '参数不能为空'
            );
        }
        echo '<pre>';

        // 调用所在文件和所在行数
        $backtrace  = app()->helper->getDebugBacktrace(2);
        $fileLine   = str_replace(yii::getAlias('@app'), '', $backtrace['file']);
        $fileLine  .= ' : ' . $backtrace['line'];
        echo $fileLine, "\n", str_repeat('-', strlen($fileLine)), "\n";

        foreach ($params as $value) {
            if ('exit' === $value || 'end' == $method) {
                if ('end' == $method) {
                    echo $value;
                }
                if (defined('PHPUNIT_TEST')) {
                    echo 'exit()';
                }
                app()->end();
                return false;
            } else {
                call_user_func($this->methods[$method], $value);
            }
        }

        echo '</pre>';
        return false;
    } // end debug
}
