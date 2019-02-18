<?php
namespace app\helpers;

use yii\base\InvalidParamException;

/**
 * 字符串处理类
 */
class Str
{
    /**
     * @var int 大小写字母(a-zA-Z)
     */
    const LETTER = 1;

    /**
     * @var int 小写字母(a-z)
     */
    const LOWER = 2;

    /**
     * @var int 大写字母(A-Z)
     */
    const UPPER = 4;

    /**
     * @var int 数字(0-9)
     */
    const NUMERIC = 8;

    /**
     * @var int 字母与数字(a-xA-Z0-9)
     */
    const ALPHANUMERIC = 16;

    /**
     * @var int 字母与数字(a-xA-Z0-9)，排除容易混淆的字符oOLl和数字01
     */
    const EXTENDED = 32;

    /**
     * @var int ascii码33-126中，除了字母和数字外的特殊字符
     */
    const SPECIALCHARS = 64;

    /**
     * @var int 随机字符串模式xor最大值
     */
    const MAX_XOR = 127;

    /**
     * @var array 随机字符串模式对应字符
     */
    public $randomChars = [
        self::LETTER        => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::LOWER         => 'abcdefghijklmnopqrstuvwxyz',
        self::UPPER         => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::NUMERIC       => '0123456789',
        self::ALPHANUMERIC  => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        self::EXTENDED      => 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ23456789',
        self::SPECIALCHARS  => '!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~',
    ];

    /**
     * 实现`htmlspecialchars`
     *
     * @param string $string
     * @param int $flags
     * @return string
     */
    public function escape($string, $flags = null)
    {
        return htmlspecialchars(
            $string,
            null === $flags ? ENT_COMPAT | ENT_HTML5 : $flags
        );
    }

    /**
     * 判断某个字符串是否包含有指定字符串
     *
     * 判断时区分大小写
     *
     * @param string $string 原始字符串
     * @param string|array $contains 期望包含的字符串，数组时表示多个字符串，匹配任意一个或者多个取决于`$logic`
     * @param string $logic 当`$contains`为数组时，是匹配任意一个还是多个，“|”时任意一个，否则同时匹配多个
     * @return bool
     * @example
     *  ```php
     *      $string = 'app()->helper->str->has foreach ((array) $contains as $item)';
     *
     *      // true
     *      app()->helper->str->has($string, 'Str');
     *
     *      // false
     *      app()->helper->str->has($string, 'foo');
     *
     *      // true
     *      app()->helper->str->has($string, ['foo', 'foreach']);
     *
     *      // false
     *      app()->helper->str->has($string, ['foo', 'foreach'], '|');
     *  ```
     */
    public function has($string, $contains, $logic = '&')
    {
        $result = false;
        foreach ((array) $contains as $item) {
            $result = false !== strpos($string, $item);

            if ($result && '|' == $logic) {
                return true;
            } elseif (!$result && '|' != $logic) {
                return false;
            }
        }

        return $result;
    }

    /**
     * 判断某个字符串是否以指定字符串开始
     *
     * 判断时区分大小写
     *
     * @param string $string 原始字符串
     * @param string|array $starts 期望开始的字符串，数组时表示可以以任意一个开始
     * @return bool
     * @example
     *  ```php
     *      $string = 'app()->helper->str->startWith foreach ((array) $starts as $item)';
     *
     *      // true
     *      app()->helper->str->startWith($string, 'Str');
     *
     *      // false
     *      app()->helper->str->startWith($string, 'foo');
     *
     *      // true
     *      app()->helper->str->startWith($string, ['foo', 'Str']);
     *  ```
     */
    public function startWith($string, $starts)
    {
        foreach ((array) $starts as $item) {
            if (0 === strpos($string, $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 判断某个字符串是否以指定字符串结尾
     *
     * 判断时区分大小写
     *
     * @param string $string 原始字符串
     * @param string|array $ends 期望结尾的字符串，数组时表示可以以任意一个结尾
     * @return bool
     * @example
     *  ```php
     *      $string = 'app()->helper->str->endWith foreach ((array) $ends as $item';
     *
     *      // true
     *      app()->helper->str->endWith($string, '$item');
     *
     *      // false
     *      app()->helper->str->endWith($string, 'foo');
     *
     *      // true
     *      app()->helper->str->endWith($string, ['foo', '$item']);
     *  ```
     */
    public function endWith($string, $ends)
    {
        foreach ((array) $ends as $item) {
            if ($item == substr($string, -strlen($item))) {
                return true;
            }
        }

        return false;
    }

    /**
     * 随机产生指定长度的字符串
     *
     * @param int $len 生成字符串的长度
     * @param int $mode 字符串模式，可选值有：
     *
     *  - `static::LETTER`：大小写字母a-zA-Z
     *  - `static::UPPER`：大写字母A-Z
     *  - `static::LOWER`：小写字母a-z
     *  - `static::NUMERIC`：数字0-9
     *  - `static::ALPHANUMERIC`：字母与数字a-zA-z0-9
     *  - `static::EXTENDED`: （默认）去掉了容易混淆的字符oOLl和数字01，要添加请使用`$addChars`参数
     *
     * @param string $addChars 额外添加的字符
     * @return string 随机的字符串
     * @example
     *  ```php
     *      // 默认
     *      app()->debug->dump(app()->helper->str->random());
     *
     *      // 长度为6的数字
     *      app()->debug->dump(app()->helper->str->random(6, static:::NUMERIC));
     *
     *      // 大写，增加额外字符“{}()”
     *      app()->debug->dump(app()->helper->str->random(4, static:::UPPER, '{}()'));
     *
     *      // 去掉了容易混淆的字符oOLl和数字01，额外字符“{}()”
     *      app()->debug->dump(app()->helper->str->random(4, null, '{}()'));
     *  ```
     *
     *  以上例子输出类似：
     *
     *  ```php
     *      string(4) "Fiup"
     *      string(6) "164907"
     *      string(4) "DCFA"
     *      string(4) "xd}N"
     *  ```
     */
    public function random($len = 4, $mode = null, $addChars = '')
    {
        $randomChars = $this->getRandomChars($mode) . $addChars;
        $randomChars = str_shuffle($randomChars);
        return substr($randomChars, 0, $len);
    }

    /**
     * 获取指定模型的随机字符
     *
     * @param int $mode
     * @return string
     */
    public function getRandomChars($mode)
    {
        if (null === $mode) {
            $mode = static::EXTENDED;
        }

        $randomChars = '';
        if (isset($this->randomChars[$mode])) {
            $randomChars = $this->randomChars[$mode];
        } elseif (is_int($mode) && $mode > 0 && $mode <= static::MAX_XOR) {
            $loop = [
                static::LETTER,
                static::LOWER,
                static::UPPER,
                static::NUMERIC,
                static::ALPHANUMERIC,
                static::EXTENDED,
                static::SPECIALCHARS,
            ];
            foreach ($loop as $item) {
                if ($item == ($item & $mode)) {
                    $randomChars .= $this->randomChars[$item];
                }
            }
        }

        if ($randomChars) {
            return $randomChars;
        } else {
            throw new InvalidParamException('不支持的字符串模式：' . var_export($mode, true));
        }
    } // end getRandomChars

    /**
     * 将**abc-def-ghi**格式的字符转化成**AbcDefGhi**
     *
     * @param string $string
     * @param string $separator
     * @return string
     */
    public function ucwords($string, $separator = '-')
    {
        static $ucwords = [];
        $key = $string;

        if (!isset($ucwords[$key])) {
            $string = str_replace($separator, ' ', $string);   // abc def ghi
            $string = ucwords($string);                 // Abc Def Ghi
            $string = str_replace(' ', '', $string);    // AbcDefGhi
            $ucwords[$key] = $string;
        }

        return $ucwords[$key];
    }

    /**
     * 将**AbcDefGhi**格式的字符转化成**abc-def-ghi**
     *
     * @param string $string
     * @param string $separator
     * @return string
     */
    public function revertUcwords($string, $separator = '-')
    {
        static $ucwords = [];
        $key = $string . $separator;

        if (isset($ucwords[$key])) {
            return $ucwords[$key];
        } elseif (!ctype_lower($string)) {
            $string = preg_replace_callback(
                '/([A-Z])/',
                function ($matches) use ($separator) {
                    return $separator . strtolower($matches[0]);
                },
                $string
            );
            $string = trim($string, $separator);
        }

        return $ucwords[$key] = $string;
    }

    /**
     * 截取字符串
     *
     * 该方法与`mb_strcut`一样，但`app()->helper->str->substr`
     * 实现的是`mb_strcut($string, $length, 'utf-8')`且：
     *
     * - 截取的开始位置从0开始
     * - 先判断`$string`长度是否超出了`$length`，不超出时直接返回原始字符串
     * - 支持截取后追加字符串
     *
     * 如果你需要指定截取的开始位置，请使用`mb_strcut`函数
     *
     * @param string $string 待截取的字符串
     * @param int $length 截取字节数，不包括`$append`的长度
     * @param string $append 截取后追加在截取词后面的字符串
     * @return string 截取后的字符串
     */
    public function substr($string, $length, $append = '')
    {
        if ($length < 1 || !isset($string{$length})) {
            return $string;
        } else {
            return mb_strcut($string, 0, $length, 'utf-8') . $append;
        }
    }

    /**
     * 使用`strstr`格式化字符串
     *
     * @param string $string
     * @param array $replacePairs
     * @return string|string
     */
    public function format($string, array $replacePairs)
    {
        if ($replacePairs) {
            $replace = [];
            foreach ($replacePairs as $key => $value) {
                $replace['{' . $key . '}'] = $value;
            }
            return strtr($string, $replace);
        } else {
            return $string;
        }
    }
}
