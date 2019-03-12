<?php

namespace app\models;

use Yii;
use app\base\ActiveRecord;


class SourcePosition extends ActiveRecord
{

    use \app\components\Singleton;  //方法复用

    /*
     * 表名
     */
    public static function tableName()
    {
        return 'source_position';
    }


    //=======start====更新方法====

    /**
     *
     * 保存/更新主题数据
     *
     * v2 版本更新
     *
     * @author chenhuian
     * @param  array $data 数据
     * @param  int/array $where  更新ID
     * @return boolean
     *
     */

    public function insertOrUpdate($data = [], $where = 0)
    {

        try {
            if (!$where and $data) { // 新增
                $model  = $this;
            } elseif ($data and $where) { // 修改
                $model = $this->findOne($where);
                if (!$model) {
                    $model = $this;  // 查询没有则新增
                }
            }

            //过滤表字段
            $data = $this->makeFieldData($data);

            //组合数据
            if (count($data) == count($data, 1)) {  // 一维数组
                foreach ($data as $key => $value) :
                    $model -> $key = $value;
                endforeach;

                //echo $model->find()->createCommand()->getRawSql();
                return $model->save() ? $model->primaryKey : false;

            } else {     // 多维数组
                //再执行批量插入

                if (!empty($data)) {
                    $field_name = array_keys(current($data));
                    return Yii::$app->db
                        ->createCommand()
                        ->batchInsert(self::tableName(), $field_name, $data)
                        //->getRawSql();  //测试
                        ->execute();
                }
                return false;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();  //调试
            return false;
        }
    }

    /**
     *
     * 处理数据对应数据库字段
     * @author chenhuian
     * @param array $data 待处理数据
     * @return array $makeData 合法数据
     */
    public function makeFieldData($data){

        $field = $this->getFieldNames();
        $makeData = [];
        if(count($data) == count($data, 1)){ //一维度
            foreach ($data as $key => $value){
                if(in_array($key,$field)){
                    $makeData[$key] = $value;
                }
            }
        }else{  // 二维数组
            foreach ($data as $key => $value){
                foreach ($value as $k => $v){
                    if(in_array($k,$field)){
                        $makeData[$key][$k] = $v;
                    }
                }
            }
        }
        return $makeData;
    }

    /**
     *
     * 获取表字段
     *
     * @author chenhuian
     * @param string $table 表名称
     * @return
     */
    public function getFieldNames($table='')
    {
        $table = $table? $table : self::tableName();
        $tableSchema = Yii::$app->db->schema->getTableSchema($table);
        $fields = \yii\helpers\ArrayHelper::getColumn($tableSchema->columns, 'name', false);
        return $fields;
    }


    /**
     *
     * 直接给条件查询
     *
     * @author chenhuian
     * @param array $where  $where['andWhere'] = ['>=', 'end_time', self::time()];
     * @param array $select
     * @param array $order
     * @return array $data
     *
     */
    public function getListData($select = [], $where = [], $order = ['position_id'=>SORT_DESC])
    {
        $query = $this->find()->select($select);
        return $this->anSetWhere(function () use ($query, $where, $order) {
            if (array_key_exists('andWhere', $where)) {
                $andWhere = array_pop($where);
                $queryWhere =  $query->where($where);
                if(count($andWhere) == count($andWhere,1)){  // 一维数组
                    $query = $queryWhere->andWhere($andWhere);
                }else{
                    foreach ($andWhere as $wh){
                        $query = $queryWhere->andWhere($wh);
                    }
                }
            } else {
                $query = $where ? $query->where($where) : $query;
            }
            $data = $query->asArray()->orderBy($order)->all();
            //echo $query->createCommand()->getRawSql();
            return  $data;
        });
    }


    /**
     *
     * 设置where 条件
     * @author chenhuian
     * @param callable $callback 设置回调函数
     * @return mixed
     */
    public function anSetWhere($callback)
    {
        return ($callback instanceof \Closure) ? call_user_func($callback) : $callback;
    }

}
