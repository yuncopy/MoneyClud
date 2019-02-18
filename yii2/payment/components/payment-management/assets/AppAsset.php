<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'static/dist/css/bootstrap/css/bootstrap.min.css',   
        'static/dist/css/AdminLTE.min.css?v=20180703',
        'static/dist/css/skins/skin-green.min.css',
        'static/dist/css/Font-Awesome-master/css/font-awesome.min.css',        
        'static/dist/css/common_min.css',
        'static/dist/css/bootstrap-table.min.css?20180315', 
        'static/dist/css/bootstrap-datetimepicker.min.css',        
    ];
    public $js = [
        'static/dist/minjs/jquery-1.11.3.min.js',
        'static/dist/minjs/LAB.min.js',
        'static/dist/minjs/bootstrap.min.js',   
        'static/dist/minjs/layer/layer.js',
        'static/dist/minjs/common.min.js?20180514', 
        'static/dist/minjs/bootstrap-table.min.js?20180331',    
        'static/dist/minjs/bootstrap-table-zh-CN.min.js?20180702',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
        //'app\assets\FontAwesomeAsset',
        //'app\assets\AdminLteAsset',
    ];
}
