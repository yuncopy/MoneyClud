<?php
namespace app\helpers;

/**
 * @author chenhuian
 * @date 2018年9月19日9:18:55
 *
 * 错误码提示类
 */

class Error
{

    /**
     * @author chenhuian
     *
     * 定义错误码
     *
    */
    public static $_map=[

        //公共部分
        '200'=>'操作成功',
        '201'=>'操作失败',
        '400'=>'参数缺失',


        //上传图片验证
        '6001'=>'上传图片出错',
        '6002'=>'上传文件名称不能包含中文!',
        '6003'=>'上传文件名称不能包含特殊字符',
        '6004'=>"上传文件超过30M，图片不可上传",
        '6005'=>"上传图片出错-图片信息",
        '6006'=>"上传图片出错-添加图片",
        '6007'=>"上传图片出错-添加图片集",
        '6008'=>"上传图片出错-更新图片集",
        '6009'=>"上传图片出错-上传S3",
        '6010'=>"上传图片出错-上传Webp失败",
        '6011'=>"恭喜您，上传图片成功",
        '6012'=>"上传文件格式不允许",

    ];


    /**
     *
     * @author chenhuian
     * @param string/int $code 错误码
     * @param string  $str 额外参数
     * @param boolean $die 是否直接输出
     * @return string 提示信息
     *
    */
    public static function getMessage($code,$die=false,$str='')
    {
        try {
            $code = is_numeric($code) ? (string)($code) : $code;
            if (isset(self::$_map[$code])) {
                $message = "[{$code}]-".self::$_map[$code];
                $message .= $str ? "({$str})" : '';
                return $die ? die(json_encode(['status'=>$code,'msg'=>$message],JSON_UNESCAPED_UNICODE)) : $message;
            }
        } catch (\Exception $e) {
            echo 'Error',  $e->getMessage().PHP_EOL;
        }
    }


    /***
     * 设置错误信息
     *
     * @author chenhuian
     * @date 2018年9月19日10:01:38
     * @param array $message 设置信息  ['400'=>'未找到']
     * @return array
    */

    public static function setMessage($message = [])
    {

        $code = (string)key($message);
        if(isset(self::$_map[$code])){
            return self::$_map[$code];
        }else{
            return array_merge(self::$_map,$message);
        }
    }
}
