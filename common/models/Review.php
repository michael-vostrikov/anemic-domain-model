<?php

namespace common\models;

/**
 * This is the model class for table "review".
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $status
 * @property mixed $field_values
 * @property string $created_at
 * @property ?string $processed_at
 */
class Review extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'review';
    }
}
