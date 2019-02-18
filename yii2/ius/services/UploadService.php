<?php

/**
 *
 * 上传文件服务
 *
 */

namespace app\services;

use app\models\Admin;
use Yii;
use app\helpers\Error;

class UploadService extends CommonService
{

    private static $allowFileType = ['webp','jpg','png','gif'];

    /**
     * 上传文件到S3服务器
     *
     * @author chenhuian
     * @param array $params 上传参数
     * $params = [
     *  's3FilePath'=> 'ZF/image/55/20190215_648/England-details2.jpg',
     *  'localDesImgFilePath'=> '/tmp/phps9960H',
     *  'imageType'=> 'image/jpeg',
     * ]
     *
     * @return boolean
     *
     */

    public static function uploadToS3Image($params)
    {
        if ($params) {

            //必要验证参数
            !isset($params['s3FilePath']) && Error::getMessage(400, true,'s3FilePath');
            !isset($params['localDesImgFilePath']) && Error::getMessage(400, true,'localDesImgFilePath');
            !isset($params['imageType']) && Error::getMessage(400, true,'imageType');


            //组合数据
            $params = [
                's3FilePath' => $params['s3FilePath'],
                'localDesImgFilePath' => $params['localDesImgFilePath'],
                'imageType' => $params['localDesImgFilePath'],
            ];

            //加载类库
            require_once(__DIR__ . '/../components/awss3/aws-autoloader.php');

            //实例化类
            $Client = new \Aws\S3\S3Client([
                'version'       => 'latest', // 固定值
                'region'        => Yii::$app->params['s3']['region'],
                'credentials'   => [
                    'key'       => Yii::$app->params['s3']['key'],
                    'secret'    => Yii::$app->params['s3']['secret'],
                ],
            ]);

            try {

                $s3UploadResult = $Client->putObject(array(
                    'Bucket' => Yii::$app->params['s3']['bucket'],
                    'Key' => $params['s3FilePath'],
                    'SourceFile' => $params['localDesImgFilePath'],
                    "CacheControl" => "max-age=315360000",
                    "ContentType" => $params['imageType'],
                    "Expires" => date("D, d M Y H:i:s T", strtotime("+10 years"))
                ));

                if ($s3UploadResult) {
                    //已上传成功，等网上S3服务器能正常打开图片， 因为上传后到能正常打开图片需要一点时间
                    $Client->waitUntil('ObjectExists', array(
                        'Bucket' => Yii::$app->params['s3']['bucket'],
                        'Key' => $params['s3FilePath']
                    ));
                    return true;
                }
            } catch (\Exception $e) {
                // Catch an S3 specific exception.
                $error = $e->getMessage();
            } catch (S3Exception $e) {
                // Catch an S3 specific exception.
                $error = $e->getMessage();
            } catch (AwsException $e) {
                // This catches the more generic AwsException. You can grab information
                // from the exception using methods of the exception object.
                $error = "错误类型: " . $e->getAwsErrorType() . ", 错误代码: " . $e->getAwsErrorCode() . ", s3请求ID: " . $e->getAwsRequestId();
            }
        }
        return false;
    }


    /**
     * 生成webp图片
     * Array
     *  (
     *      [format] => jpg
     *      [originalImage] => /tmp/phpJyNvWd
     *      [webpImage] => /home/www/ius/htdocs/temporary/Ov800.jpg.webp
     *   )
     * @param  array $params 数组， 键名对应值
     * @return boolean
     */
    public static function getWebpImage($params=array())
    {
        !isset($params['originalImage']) && Error::getMessage(400, true,'originalImage');
        !isset($params['webpImage']) && Error::getMessage(400, true,'webpImage');

        $fileType = strtolower(pathinfo($params['originalImage'],PATHINFO_EXTENSION ));
        if(in_array($fileType,self::$allowFileType)){
            try {
                $IM = new \imagick();
                // $IM->setResolution(72,72);//设置图片像素
                $IM->setCompressionQuality(100);//图片质量
                $IM->readImage($params['originalImage']);//读原图
                $IM->setImageFormat('webp'); //转换的图片格式,也可以为png,gif,webp等格式
                if($IM->writeImage($params['webpImage']) != true){//保存图片
                    return false;
                }
                if(file_exists($params['webpImage']))//是否生成成功
                    return true;
                else
                    return false;
            } catch (\Exception $e) {
                var_dump($e->getMessage());exit;
            }
        }
        return true;
    }


    /***
     * 获取图片宽高
     *
     * @author chenhuian
     * @param  array $params 参数
     * @return mixed
     *
    */

    public static function getFileWidthHeight($params = array())
    {

        //必要验证，不使用异常处理了
        !isset($params['fileType']) && Error::getMessage(400, true,'fileType');
        !isset($params['localDesImgFilePath']) && Error::getMessage(400, true,'localDesImgFilePath');

        //能获取宽高的图片类型
        $fileType = strtolower($params['fileType']);
        $imageInfo = [0,0];
        if(in_array($fileType,self::$allowFileType)){
            try {

                switch ($params['fileType']) {
                    case 'webp':
                        $image = new \Imagick($params['localDesImgFilePath']);
                        $imageInfo[0] = $image->getImageWidth();
                        $imageInfo[1] = $image->getImageHeight();
                        break;
                    case 'jpg':
                    case 'png':
                    case 'gif':
                        $imageInfo = getimagesize( $params['localDesImgFilePath'] );
                        if(!$imageInfo){
                            $image = new \Imagick($params['localDesImgFilePath']);
                            $imageInfo[0] = $image->getImageWidth();
                            $imageInfo[1] = $image->getImageHeight();
                        }
                        break;
                    default:

                        break;
                }
                return $imageInfo;
            } catch (\Exception $e) {
                //die($e->getMessage());
                return false;
            }
        }
        return $imageInfo;

    }


    /**
     *
     * 处理网络图片地址--拼接S3路径
     *
     * 链接组成方式： 站点缓存/站点/分类ID/日期_图片集ID/图片名称
     * @author chenhuian
     * @param array $file 文件表单数据
     * @param array $post 内容数据
     * @return string  站点缓存/站点/分类ID/日期_图片集ID
     *
     *
    */
    public static function setWebImageURL($file,$post){

        $strURL = '';
        if($file && $post){

            //验证参数，所看即所得
            empty($post['site']) && Error::getMessage(400, true,'site');
            empty($post['classifys']) && Error::getMessage(400, true,'classifys');


            //逻辑处理
            $imageType = $file['type'];
            switch ($imageType){
                case 'video/mp4':
                    $str = "video";
                    break;
                case 'text/css':
                    $str = "text";
                    break;
                default:
                    $str = "image";
                    break;
            }

            $siteId = $post['site'];  //站点ID
            $classId = $post['classifys']; //分类ID字符串
            $collection = !empty($post['collection']) ? $post['collection'] : 0;  //图片集ID
            $class_id = substr(strrchr($classId, ","), 1); //分类ID最大

            $site = SiteService::getSiteData($siteId);
            $site_code = $site['site_code'];  //站点简称

            global  $an_collection_id;
            $an_collection_id = $max_id = $collection ? $collection : intval(SourcePositionService::getMaxPositionID()) + 1;
            $code = '';
            if($class_id){
                $category = CategoryService::getCategoryData(['id'=>$class_id]);
                $code = $category['state_code']; //分类简称
            }
            $class_id =   $code ? $code : $class_id;
            $ymd = self::ymd();
            $strURL = "{$site_code}/{$str}/{$class_id}/{$ymd}_{$max_id}";
        }
        return $strURL;
    }



    /**
     *
     * 统一处理图片上传操作
     *
     * @author chenhuian
     * @param array $file 文件上线信息
     * @param array $post POST数据
     * @throws  \Exception 抛异常处理
     * @return mixed
     *
     *
     */
    public static function doImageUpload($file,$post){

        if($file && $post){

            $url = self::setWebImageURL($file,$post);  //获取图片组合链接
            $fileName = $file['name'];
            $s3FilePath = "{$url}/{$fileName}";

            //组合数据
            $localDesImgFilePath = $file['tempName'];
            $imageType = $file['type'];

            $uploadData = [
                's3FilePath' => $s3FilePath,
                'localDesImgFilePath' => $localDesImgFilePath,
                'imageType' => $imageType,
            ];


            //获取图片宽高
            $wh = self::getFileWidthHeight(array_merge(
                ['fileType'=>strtolower(pathinfo($s3FilePath, PATHINFO_EXTENSION))],
                $uploadData
            ));
            if(!$wh) throw new \Exception(6005);


            //添加数据库入库

            //1、处理图片集
            if(!$post['collection'] && $post['collectionName']){  // 有图片集名称，新增图片集数据

                //查询图片集是否存在
                $resData = SourcePositionService::getListData([
                    'site_id'=>$post['site'],
                    'cate_id'=>self::getCateId($post['classifys']),
                    'state_code'=>$post['countryName'],
                    'position_name'=>$post['collectionName'],
                ]);
                if($resData){
                    $rsUp = self::updateSourcePosition($file,$resData);
                    if(!$rsUp) throw new \Exception(6008);
                }else{

                    $spRes = SourcePositionService::insertData($file,$post,$uploadData);
                    if(!$spRes) throw new \Exception(6007);
                }

            }else if($post['collection']){

                $resCollection = SourcePositionService::getListData([
                    'position_id'=>$post['collection']
                ]);
                if($resCollection){
                    $rsUpC = self::updateSourcePosition($file,$resCollection); //更新图片集相关数据
                    if(!$rsUpC) throw new \Exception(6008);
                }
            }


            //2、是否存在覆盖图片
            $spSetS3 = SourcePositionSetService::getListData([
                'image_name'=>$fileName,
                'image_url' =>$s3FilePath,
                'site_id'=>$post['site'],
                'cate_id'=>self::getCateId($post['classifys'])
            ]);

            if($spSetS3){
                list($dirname,$basename,$extension,$filename) = array_values(pathinfo($uploadData['s3FilePath']));
                $randStr = self::getRand(3);
                $uploadData['s3FilePath'] = "{$dirname}/{$filename}_{$randStr}.{$extension}";
            }

            //3、处理图片内容
            $spSetRes = SourcePositionSetService::insertData($file,$post,$uploadData,['width'=>$wh[0],'height'=>$wh[1]]);
            if(!$spSetRes) throw new \Exception(6006);


            //4、执行上传图片到S3服务器
            $upload = self::uploadToS3Image($uploadData);
            if(!$upload) throw new \Exception(6009);

            //5、上传webp图片
            $uploadWebp = self::uploadToS3ImageWebP($file,$url);
            if(!$uploadWebp) throw new \Exception(6010);

            return $uploadWebp;
        }
        return false;
    }


    /**
     * 更新必要图片集数据
     *
     * @author chenhuian
     * @param array $file
     * @param array $resData  二维数组
     * @return
    */
    public static function updateSourcePosition($file,$resData){

        if($file && $resData){
            $oneData = current($resData);
            $suffix = '.'.pathinfo($file['name'],PATHINFO_EXTENSION);
            $upData = [
                'image_size'=>intval($file['size']),
                'file_count'=>intval(1),
            ];
            stripos($oneData['suffix'], $suffix) === false && $upData['suffix'] = $suffix;
            $rsUp =  SourcePositionService::updateData($upData,$oneData['position_id']);  //更新图片集相关数据
            return $rsUp;
        }
    }



    /**
     *
     * 上传webp文件
     *
     * @author chenhuian
     * @param array $file  上传文件数据
     * @param string $url   S3地址
     * @return boolean
     *
    */
    public static function uploadToS3ImageWebP($file,$url){

        $webp = false;
        if($file && $url){
            $webpImage =  ROOT_PATH.'/temporary/';
            $uploadName= $file['name'];
            $params = array(
                'format' => strtolower(pathinfo($uploadName,PATHINFO_EXTENSION )),  //jpg
                'originalImage' => $file['tempName'], //  /tmp/phpJyNvWd
                'webpImage' => $webpImage.$uploadName.'.webp', // /home/www/ius/htdocs/temporary/Ov800.jpg.webp
            );
            //生成图片
            $makeWebp = self::getWebpImage($params);

            //上传Webp图片
            $uploadData = array(
                's3FilePath' => "{$url}/{$uploadName}.webp",  // ZF/image/55/20190215_648/England-details2.jpg.webp'
                'localDesImgFilePath' => $params['webpImage'], // /home/www/ius/htdocs/temporary/Ov800.jpg.webp
                'imageType' => 'image/webp',
            );
            $webp = $makeWebp &&  self::uploadToS3Image($uploadData);
            if($webp && is_file($params['webpImage'])){
                gc_collect_cycles(); // 强制收集所有现存的垃圾循环
                @unlink($params['webpImage']);  // 上传成功删除本服务器的webp图片
            }
        }
        return  $webp ? true :false;
    }



}


























