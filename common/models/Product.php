<?php

namespace common\models;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int $user_id
 * @property ?int $category_id
 * @property string $name
 * @property string $description
 * @property int $status
 * @property string $created_at
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }
}
