<?php

namespace common\models;

/**
 * This is the model class for table "product".
 *
 * @property int $product_id
 * @property mixed $field_values
 *
 * @property-read Category $category
 */
class ProductChange extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_change';
    }
}
