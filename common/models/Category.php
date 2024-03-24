<?php

namespace common\models;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }
}
