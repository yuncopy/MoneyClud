<?php
/**
 *
 *
 * author: Wei Chang Yue
 * date: 2019/01/24 11:01
 */

namespace app\services;

use Yii;

class CategoryService extends CommonService
{


    /**
     * 获取站点数据信息
     *
     * @author chenhuian
     * @param array $where 条件数据
     * @return array
     *
    */
    public static function getCategoryData($where){

        return \app\models\Category::findOne($where)->toArray();

    }

}