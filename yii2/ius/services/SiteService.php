<?php
/**
 *
 *
 * author: Wei Chang Yue
 * date: 2019/01/24 11:01
 */

namespace app\services;

use Yii;

class SiteService extends CommonService
{


    /**
     * 获取站点数据信息
     *
     * @author chenhuian
     * @param array $where 条件数据
     * @return array
     *
    */
    public static function getSiteData($where){

        $site = [];
        if(is_numeric($where)){
            $site = \app\models\Site::findOne($where)->toArray();
        }else if(is_array($where)){
            $site = \app\models\Site::find($where)->all()->toArray();
        }
        return $site;
    }

}