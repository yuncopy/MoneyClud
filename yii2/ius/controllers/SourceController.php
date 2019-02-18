<?php

namespace app\controllers;

use app\services\ImageService;
use Yii;
use app\base\Controller;
use app\models\Site;


//优化使用
use app\helpers\Error;

class SourceController extends Controller
{
    public $fileType;

    function init() {
        header('Cache-Control:no-cache,must-revalidate');
        header('Pragma:no-cache');
        $this->fileType = ImageService::$fileType;
    }

    /**
     * 上传图片到s3
     * @param type $arr
     * @param type $image_id
     * @param type $isReplace
     * @return string
     */
    public function actionUpload() {

        set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);

        return $this->anUploadImage(); // 一并提交 优化方法

    }



    /**
     *
     * 请图片CDN缓存
     *
     * @author chenhuian
     * @param string $site_id 站点ID
     * @param string $image_url 图片URL
     * @return boolean
     *
     */
    public function clearCdnImage($site_id,$image_url){

        if($site_id && YII_ENV == 'product'){  //product dev

            $site = Site::findOne($site_id); // 获取网站信息
            $site_name = !empty($site['site_cdn_name']) ? strtolower($site['site_cdn_name']) : strtolower($site['site_name']);
            $http_image_url =  $this->replaceImageCDN($site_name, $image_url);
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
     *
     * 接管图片上传处理
     *
     * @author chenhuian
     * @param
     *
    */
    public function anUploadImage(){

        //验证处理
        $postData = Yii::$app->request->post();
        $file = \yii\web\UploadedFile::getInstanceByName('file');

        !$file && Error::getMessage(6001, true,'file'); // 验证是否为空

        $fileData = \yii\helpers\ArrayHelper::toArray($file);
        $pathParts = pathinfo($fileData['name']);

        //参数验证
        ImageService::isChinese($pathParts['basename']) && Error::getMessage(6002, true); //验证中文
        ImageService::isVarName($pathParts['basename']) && Error::getMessage(6003, true); //验证特殊字符
        ImageService::isOverSize($fileData['tempName']) && Error::getMessage(6004, true); //文件超过指定大小
        ImageService::isAllow($pathParts['extension']) && Error::getMessage(6012, true); //允许文件格式


        //使用事务，建议减少使用，可以使用其他方式保证数据一致性
        $transaction = Yii::$app->db->beginTransaction();
        try{

            $upload = \app\services\UploadService::doImageUpload($fileData,$postData);
            $code = $upload ? 200 : 6001;
            $transaction->commit();

        }catch (\Exception $e){

            $code = $e->getMessage();
            $transaction->rollBack();

        }finally{
            self::jsonResponse($code,[],Error::getMessage(6011));
        }
    }

}
