<?php
namespace app\base;

use yii\base\Component;

/**
 * 运行环境类
 *
 * 在单元测试中，需要对每个环境进行测试，使用常量时，不能正常切换环境，比如：
 * ```php
 *      if (YII_ENV_PRODUCT) {
 *          // 生产环境代码
 *      }
 * ```
 * 此时，可以使用`app()->env->isProduct()`替代`YII_ENV_PRODUCT`,
 * 单元测试中，通过`$this->invokeProperty(app()->env, 'is.product', true)`切换为生产环境
 */
class Env extends Component
{
    /**
     * @var string 运行环境
     */
    protected $env = null;

    /**
     * @var array 包含以下键名的键值对数组，值为`bool`
     * - **product**
     * - **test**
     * - **dev**
     * - **phpunit**
     */
    protected $is = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        if (isset($config['env'])) {
            $this->env = $config['env'];
            unset($config['env']);
        } else {
            $this->env = YII_ENV;
        }
        parent::__construct($config);
    }

    /**
     * 魔术方法`__toString`
     *
     * @return string 当前运行环境
     */
    public function __toString()
    {
        return $this->env;
    }

    /**
     * 获取当前运行环境
     *
     * @return string 当前运行环境
     */
    public function get()
    {
        return $this->env;
    }

    /**
     * 开发环境？
     *
     * @return bool
     */
    public function isDev()
    {
        return $this->is('dev');
    }

    /**
     * 测试环境？
     *
     * @return bool
     */
    public function isTest()
    {
        return $this->is('test');
    }

    /**
     * 生产环境？
     *
     * @return bool
     */
    public function isProduct()
    {
        return $this->is('product');
    }

    /**
     * phpunit单元测试？
     *
     * @return bool
     */
    public function isPhpunit()
    {
        return $this->is('phpunit');
    }

    /**
     * 非生产环境？
     *
     * @return bool
     */
    public function isLocal()
    {
        return !$this->isProduct();
    }

    /**
     * 判断是否为指定环境
     *
     * @param string $env
     * @return bool
     */
    protected function is($env)
    {
        if (!isset($this->is[$env])) {
            $this->is[$env] = 0 === strpos($this->env, $env);
        }
        return $this->is[$env];
    }
}
