<?php
namespace app\helpers;

use Closure;

/**
 * 数组处理类
 */
class Arr
{
    /**
     * 将多唯数组转化成一唯数组
     *
     * @param array $array 待转化数组
     * @return array 转化后的数组，数组的索引为数字，从0开始
     * @example
     *  ```php
     *    $allowMimeExtensions = [
     *        'image'       => ['jpg', 'jpeg', 'png'],
     *        'msoffice'    => [
     *            'excel'   => ['xls', 'xlsx'],
     *            'word'    => ['doc', 'docs']
     *        ],
     *    ];
     *    app()->debug->export(app()->helper->arr->flatten($allowMimeExtensions));
     *  ```
     *
     *  以上例子输出：
     *
     *  ```php
     *      array (
     *        0 => 'jpg',
     *        1 => 'jpeg',
     *        2 => 'png',
     *        3 => 'xls',
     *        4 => 'xlsx',
     *        5 => 'doc',
     *        6 => 'docs',
     *      )
     *  ```
     */
    public function flatten(array $array)
    {
        $return = [];
        array_walk_recursive($array, function($x) use (&$return) {
            $return[] = $x;
        });

        return $return;
    }

    /**
     * 返回一个值全部为整形的数组
     *
     * @param array|string $string 待转换的字符串或数组
     * @param bool $join **true**时返回数组
     * @param mixed $exclude 排除值，支持以下类型：
     *
     * - **false**：排除小于0的值（默认）
     * - **null**：不排除任何值
     * - `Closure`：一个自定义排除的闭包
     * - 其它：排除值（数组或“,”分隔的字符串）
     *
     * @return array|string 如果`$join`为**true**，返回数组，否则返回用`$join`分隔的字符串
     * @example
     *  ```php
     *      $array = [
     *          -10,
     *          10,
     *          '11',
     *          20,
     *          '3string',
     *          [100, 2, 3], // 转化为整形时为：1
     *      ];
     *
     *      // 不排除任何值
     *      app()->debug->export(app()->helper->arr->toint($array, false, null));
     *
     *      // 默认排除小于0的值
     *      app()->debug->export(app()->helper->arr->toint($array));
     *
     *      // 排除10,20，返回数组
     *      app()->debug->export(app()->helper->arr->toint($array, true, [10, 20]));
     *      // 等价 app()->helper->arr->toint($array, true, '10,20');
     *
     *
     *      // 自定义排除回调，排除小于0的值以及10,20，返回数组
     *      app()->debug->export(app()->helper->arr->toint($array, true, function($v) {
     *          return $v > 0 && !in_array($v, [10, 20]);
     *      }));
     * ```
     *
     * 以上例子输出：
     *
     * ```php
     *      '-10,10,11,20,3,1'
     *
     *      '10,11,20,3,1'
     *
     *      array (
     *        0 => -10,
     *        2 => 11,
     *        4 => 3,
     *        5 => 1,
     *      )
     *
     *      array (
     *        2 => 11,
     *        4 => 3,
     *        5 => 1,
     *      )
     * ```
     */
    public function toint($array, $join = ',', $exclude = '< 0')
    {
        $array = array_map('intval', $this->trim($array));

        if (null !== $exclude) {
            if ('< 0' === $exclude) { // 排除小于0的值
                $array = array_filter($array, function($v) {
                    return $v > 0;
                });
            } elseif ($exclude instanceof Closure) { // 闭包，自定义排除函数
                $array = array_filter($array, $exclude);
            } else {
                $exclude    = $this->trim($exclude);
                $array      = array_diff($array, $exclude);
            }
        }

        if (null === $join) {
            return $array;
        } else {
            return join($join, $array);
        }
    }

    /**
     * 返回一个不包含指定的键名，即排除指定键的数组
     *
     * @param array $array 需要返回的数组
     * @param array|string $keys 排除的键名，字符串时使用“,”分隔
     * @return array
     * @example
     *  ```php
     *      $array = [
     *          'user_id'   => 1,
     *          'username'  => 'foo',
     *          'email'     => 'foo@example.com',
     *          'password'  => '123456',
     *      ];
     *
     *      app()->debug->export(app()->helper->arr->exclude($array, 'email,password'));
     *      // 等价 app()->debug->export(app()->helper->arr->exclude($array, ['email', 'password']));
     *  ```
     *
     *  以上例子输出：
     *
     *  ```php
     *      array(
     *        'user_id' => 1,
     *        'username' => 'foo',
     *      )
     *  ```
     */
    public function exclude(array $array, $keys)
    {
        $keys = $this->trim($keys);
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * 返回一个只包含指定键名的数组
     *
     * @param array $array 需要返回的数组
     * @param array|string $keys 返回的键名，字符串时使用“,”分隔
     * @return array
     * @example
     *  ```php
     *      $array = [
     *          'user_id'   => 1,
     *          'username'  => 'foo',
     *          'email'     => 'foo@example.com',
     *          'password'  => '123456',
     *      ];
     *
     *      var_export(app()->helper->arr->pickFields($array, 'user_id,username'));
     *      // 等价 var_export(app()->helper->arr->pickFields($array, ['user_id', 'username']));
     *  ```
     *
     *  以上例子输出：
     *
     *  ```php
     *      array(
     *        'user_id' => 1,
     *        'username' => 'foo',
     *      )
     *  ```
     */
    public function pickFields(array $array, $keys)
    {
        $keys = $this->trim($keys);
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * 实现`array_map('trim', $array)`
     *
     * @param array|string $data
     * @param bool|string $force
     * @return array
     */
    public function trim($data, $force = false)
    {
        if (is_array($data)) {
            return $force ? array_map('trim', $data) : $data;
        } else {
            $separator = $force ?: ',';
            return array_map('trim', explode($separator, $data));
        }
    }


    /**
     * 对二维数组进行排序
     *
     * @author chenhuian
     * $data[] = array('volume' => 67, 'edition' => 2);
     *  $data[] = array('volume' => 86, 'edition' => 1);
     *  $data[] = array('volume' => 85, 'edition' => 6);
     *  $data[] = array('volume' => 98, 'edition' => 2);
     *  $data[] = array('volume' => 86, 'edition' => 6);
     *  $data[] = array('volume' => 67, 'edition' => 7);
     *
     *  // Pass the array, followed by the column names and sort flags
     *  $sorted = array_order_by($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
     *
     * @return array
     */
    public  function array_order_by()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
}
