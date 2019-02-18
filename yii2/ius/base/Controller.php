<?php
/**
 * 基础控制器
 */
namespace app\base;

use Yii;
use app\models\Admin;
use app\models\Site;

class Controller extends \yii\web\Controller
{
    
    public function beforeAction($action)
    {


    }


    /**
     * 定义输出格式
     *
     * @author chenhuian
     * @return string
    */
    public static function jsonResponse($statusCode = 200,$data=[], $msg = 'SUCCESS')
    {
        header('Content-type:text/html;charset=utf-8');
        $outData = [
            'status'=> $statusCode,
            'msg'   => $msg,
            'data'  => ''
        ];
        if($statusCode == 200){
            $outData['data'] =  $data;
        }
        $result = json_encode( $outData, JSON_UNESCAPED_UNICODE );
        die($result);
    }
}
