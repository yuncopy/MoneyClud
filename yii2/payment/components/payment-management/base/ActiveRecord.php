<?php
namespace app\base;

use yii\db\ActiveRecord as YiiActiveRecord;

/**
 * 基础ActiveRecord
 */
class ActiveRecord extends YiiActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // ActionHistory -> action_history
        return app()->helper->str->revertUcwords(
            substr(strrchr(get_called_class(), '\\'), 1),
            '_'
        );
    }

    /**
     * 获取字段信息
     *
     * @param string $fieldName 字段名
     * @param string $key 获取字段什么信息，当值为**fieldName**时，将通过字段注释获取字段的中文名称
     * @return \yii\db\ColumnSchema|string|null
     */
    public function getFieldInfo($fieldName, $key = null)
    {
        if ('fieldName' == $key) { // 中文名称
            $labels = $this->attributeLabels();
            if (isset($labels[$fieldName])) { // 优先模型中的定义
                return $labels[$fieldName];
            }
        }

        $fields = $this->getTableSchema()->columns;
        if (isset($fields[$fieldName])) {
            $fieldInfo = $fields[$fieldName];
            if (null === $key) {
                return $fieldInfo;
            } elseif ('fieldName' == $key) { // 中文名称
                if ($fieldInfo->comment) { // 获取注释中的以","隔开的第一部份，没有“,”时则取全部注释
                    return explode(',', $fieldInfo->comment)[0];
                } else {
                    return $fieldName;
                }
            } else {
                return $fieldInfo->{$key};
            }
        }
        return null;
    }
}
