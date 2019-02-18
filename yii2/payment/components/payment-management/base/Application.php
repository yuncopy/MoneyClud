<?php
namespace app\base;

use yii\web\Application as YiiApplication;
use yii\helpers\ArrayHelper;
use yii\base\InvalidParamException;

/**
 * 基础Application，继承`yii\web\Application`
 *
 * @property \app\base\Debug $debug
 * @property \app\base\Env $env
 * @property \app\base\Ip $ip
 * @property \app\helpers\Helper $helper
 * @property \yii\redis\Cache $cache
 * @property \yii\caching\ArrayCache $arrayCache
 * @property \yii\caching\FileCache $fileCache
 * @property \yii\db\Connection $oldDb
 * @property \app\components\Systime $systime
 * @property \app\components\cache\Category $categoryCache 分类缓存组件
 * @property \app\components\cache\Goods $goodsCache 商品缓存组件
 * @property \app\components\cache\Site $siteCache 站点缓存组件
 * @property \app\modules\gsc\components\Queue $queue 同步队列
 * @property \app\modules\gsc\components\Exchange $exchange 汇率
 */
class Application extends YiiApplication
{
    /**
     * {@inheritdoc}
     */
    public function end($status = 0, $response = null)
    {
        if (!defined('PHPUNIT_TEST')) {
            parent::end($status, $response);
        }
    }

    /**
     * 加载配置
     *
     * 比如配置文件为：<path>/config/config.php，则加载以下文件
     *
     * - <path>/config/config.php
     * - <path>/config/config.<env>.php
     *
     * @param string $configFile
     * @param string|null $env
     * @return array
     */
    public static function loadConfig($configFile, $env = null)
    {
        if (!is_file($configFile)) {
            throw new InvalidParamException("配置文件“{$configFile}”不存在");
        } else {
            $config = require($configFile);
        }

        // pathinfo('<path>/config/cofig.php')
        // [
        //      'dirname' => '<path>/config'
        //      'basename' => 'config.php'
        //      'extension' => 'php',
        //      'filename' => 'config'
        // ]
        $pathinfo = pathinfo($configFile);
        /**
         * @var string $dirname
         * @var string $filename
         * @var string $extension
         */
        extract($pathinfo);

        if (null === $env) {
            $env = YII_ENV;
        }
        // <path>/config/config.<env>.php
        if (is_file($file = "{$dirname}/{$filename}.{$env}.{$extension}")) {
            $config = ArrayHelper::merge($config, require($file));
        }

        return $config;
    } // end loadConfig
}
