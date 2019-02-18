<?php
/**
 * 管理员ActiveRecord
 */
namespace app\base;

use yii;

class AdminActiveRecord extends ActiveRecord
{
    public static function getDb()
    {
        return yii::$app->db;
    }

    /**
    * 自定义打印方法
    * @param  [type] $data [description]
    * @return [type]       [description]
    */
    public function p($data = '', $data2 = '', $data3 = '', $data4 = '') 
    {
        echo "<pre>";print_r($data);echo "\n";
        if($data2)
        {
            echo "<pre>";print_r($data2);echo "\n";
        }

        if($data3)
        {
            echo "<pre>";print_r($data3);echo "\n";
        }

        if($data4)
        {
            echo "<pre>";print_r($data4);echo "\n";
        }

        die;
    }
}
