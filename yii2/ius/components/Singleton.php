<?php
/**
 * Created by PhpStorm.
 * User: chenhuian
 * Date: 2019年2月18日
 */

namespace app\components;


trait Singleton
{
    private static $instance;


    /**
     * @author chenhuian
     * @param mixed $args  传递参数
     * @return object
    */
    static function getInstance(...$args)
    {
        if(!isset(self::$instance)){
            self::$instance = new static(...$args); //后期静态绑定
        }
        return self::$instance;
    }
}