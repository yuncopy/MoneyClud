<?php
/**
 *
 *
 * author: Wei Chang Yue
 * date: 2019/01/24 11:01
 */

namespace app\services;

use Yii;
use \app\models\SourcePosition as Sp;


class SourcePositionService extends CommonService
{


    /**
     * 获取站点数据信息
     *
     * @author chenhuian
     * @param array $where 条件数据
     * @return array
     *
    */
    public static function getMaxPositionID(){

        return Sp::find()->max('position_id');

    }

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
            $adminData = self::addUserData();
            foreach ($data as $row) {

                $addData['add_user_id'] = $adminData['id'];
                $addData['update_time'] = self::timeInt();
                $addData['add_time']    = self::timeInt();
                $addData['add_by']      = $adminData['real_name'];
                $addData['add_date']    = self::ymd();
                $addData['file_count']  = 1;

                isset($row['s3FilePath']) && $addData['pic'] = $row['s3FilePath'];
                isset($row['collectionName']) && $addData['position_name'] = $row['collectionName'];
                isset($row['classifys']) && $addData['cate_id'] = self::getCateId($row['classifys']);
                isset($row['site']) && $addData['site_id'] = $row['site'];
                isset($row['countryName']) && $addData['state_code'] = $row['countryName'];
                isset($row['size']) && $addData['image_size'] = $row['size'];
                isset($row['name']) && $addData['suffix'] = strrchr($row['name'], '.');
                isset($row['type']) && $addData['file_type'] = $row['type'];
            }
            $addData && $res = (new Sp)->insertOrUpdate($addData);
        }
        return  $res;
    }



    /**
     * 累加修改数据
     *
     * @author chenhuian
     * @param array $data 数据
     * @param int $id 数据ID
     * @return boolean
     *
     */
    public static function updateCountData($data,$id){

        $res = false;
        if($data && is_array($data)){
            return Sp::updateAllCounters($data, ['id' => $id]);
        }
        return  $res;
    }


    /**
     * 整体修改数据
     *
     * @author chenhuian
     * @param array $data 数据
     * @param int $id 数据ID
     * @return boolea
    */

    public static function updateData($data,$id){

        $res = false;
        if($data && is_array($data)){
            $table = sp::tableName();
            $upSqlData = [];
            foreach ($data as $key => $value){
                if(is_string($value)){
                    $upSqlData[] = "`{$key}` = CONCAT('{$value}',{$key})";
                }else if(is_numeric($value)){
                    $upSqlData[] = "`{$key}` = `{$key}` + {$value}";
                }
            }
            $upSql = "UPDATE {$table} SET ".implode(',',$upSqlData)." WHERE `position_id` = {$id}";
            $res = Yii::$app->db->createCommand($upSql)->execute();
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
            $res = sp::getInstance()->getListData(['*'],$data);
        }
        return  $res;
    }

















}