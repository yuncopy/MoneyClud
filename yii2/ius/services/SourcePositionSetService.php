<?php
/**
 *
 *
 * author: Wei Chang Yue
 * date: 2019/01/24 11:01
 */

namespace app\services;

use Yii;

use \app\models\SourcePositionSet as SpSet;

class SourcePositionSetService extends CommonService
{


    /**
     * 添加数据
     *
     * @author chenhuian
     * @param array $data 入库数据
     * @return boolean
     *
    */
    public static function insertData(...$data){

        $addData = [];
        $res = false;
        if($data){
            $an_collection_id = $GLOBALS['an_collection_id'];
            $adminData = self::addUserData();
            foreach ($data as $row) {
                $addData['position_id'] = $an_collection_id;
                $addData['add_user_id'] = $adminData['id'];
                $addData['update_time'] = self::timeInt();
                $addData['add_time']    = self::timeInt();
                $addData['add_by']      = $adminData['real_name'];

                isset($row['name']) && $addData['image_name'] = $row['name'];
                isset($row['s3FilePath']) && $addData['image_url'] = $row['s3FilePath'];
                isset($row['s3FilePath']) && $addData['image_webp'] = $row['s3FilePath'] . '.webp';
                isset($row['size']) && $addData['image_space'] = $row['size'];
                isset($row['width']) && $addData['image_size'] = $row['width'] . 'x' . $row['height'];

                isset($row['classifys']) && $addData['cate_id'] = substr($row['classifys'], strrpos($row['classifys'], ',')+1);
                isset($row['site']) && $addData['site_id'] = $row['site'];
                isset($row['countryName']) && $addData['state_code'] = $row['countryName'];
                isset($row['name']) && $addData['suffix'] = strrchr($row['name'], '.');
                isset($row['type']) && $addData['file_type'] = $row['type'];
            }
            $res = (new SpSet)->insertOrUpdate($addData);
        }
        return  $res;
    }


    /**
     *
     * 查询数据
     *
     * @author chenhuian
     * @param array $data
     * @return array $res
     */
    public static function getListData($data){

        $res = false;
        if($data && is_array($data)){
            $res = (new SpSet)->getListData(['*'],$data);
        }
        return  $res;
    }

}