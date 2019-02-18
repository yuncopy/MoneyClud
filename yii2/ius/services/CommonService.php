<?php

/**
 *
 * 上传文件服务
 *
 */

namespace app\services;

use Yii;

class CommonService
{


    /**
     *日期格式
     *
     * @author chenhuian
     * @return string
    */
    public static function ymd(){

        return date('Ymd');
    }

    /**
     *日期格式
     *
     * @author chenhuian
     * @return string
     */
    public static function timeInt(){

        return time();
    }

    /**
     * 分类ID
    */
    public static function getCateId($classifys){
        $cateId = 0;
        if($classifys){
            $cateId = substr($classifys, strrpos($classifys, ',')+1);
        }
        return $cateId;
    }


    /**
     * @author chenhuian
     * @return int
    */
    public static function addUserId(){
        return Yii::$app->session->get('admin_info')['id']; // 登陆用户ID
    }

    /**
     * @author chenhuian
     * @return int
     */
    public static function addUserData(){
        return Yii::$app->session->get('admin_info'); // 登陆用户ID
    }


    /**
     * 生成随机字符串 字母和数字组成
     * @author www
     * @return string $str
    */
    public static function getRand($len=6,$chars=null)
    {
        if (is_null($chars)){
            $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }



}
