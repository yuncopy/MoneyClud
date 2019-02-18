<?php
/**
 *
 *
 * author: Wei Chang Yue
 * date: 2019/01/24 11:01
 */

namespace app\services;

use app\helpers\SiteHelper;
use app\models\Category;
use app\models\Region;
use app\models\Site;
use app\models\SourcePosition;
use app\models\SourcePositionSet;
use phpDocumentor\Reflection\Types\Self_;
use Yii;

class ImageService
{



    //======================start====================2019年2月15日=========

    /**
     * 打印SQL语句
     *
     * @author chenhuian
     */
    public static function wSql($query){
        if(is_object($query)){
            die($query->createCommand()->getRawSql());
        }
    }



    /**
     * 请求CDN
     *
     * @author chenhuian
     * @param array $item
     * @param string $key
    */
    public static function clearCdnImage($item=[],$key='image_url'){

        if(YII_ENV_PROD){
            $http_image_url = self::getImageURL($item,$key);
            $url = "http://purge.faout.com:8080/clear?url=".$http_image_url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($ch); //已经获取到内容,没有输出到页面上.
            curl_close($ch);
        }
    }

    /**
     * 特殊字符
     * @author chenhuian
     *
    */
    public static function isVarName($imageFileName1){

        $specialPattern = Yii::$app->params['imagePattern']['specialPattern'];

        if(preg_match($specialPattern,$imageFileName1)){ //不允许特殊字符
            return true;
        }
        return false;
    }


    /**
     *
     * 验证是否为中文
     * @author chenhuian
     * @param string $imageFileName 文件名称
     * @return boolean
    */
    public static function isChinese($imageFileName){

        $specialPattern = Yii::$app->params['imagePattern']['chinesePattern'];

        if(preg_match($specialPattern,$imageFileName)){ //中文
            return true;
        }
        return false;
    }


    /**
     *
     * 检查文件大小是否超过指定大小
     *
     * @author chenhuian
     * @param string $localDesImgFilePath 文件路径
     * @param int $size 文件大小
     * @return boolean
     *
     * 1024 * 1024 * 30  30M
    */
    public static function isOverSize($localDesImgFilePath,$size=31457280){

        return filesize( $localDesImgFilePath ) > $size ? true : false;
    }



    /**
     * 允许上传格式
     *
     * @author chenhuian
    */
    public  static $allowExt= ['jpg','png','gif','psd','svg','ai','css','mp4','html'];
    public static function isAllow($ext){

        return  !in_array($ext,self::$allowExt) ? true: false;

    }
    /**
     * 获取随机字符串
     * @param type $length
     * @param type $char
     * @return boolean
     */
    function strRand($length = 2) {
        $char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (!is_int($length) || $length < 0) {
            return false;
        }

        $string = '';
        for ($i = $length; $i > 0; $i--) {
            $string .= $char[mt_rand(0, strlen($char) - 1)];
        }
        return $string;
    }






}