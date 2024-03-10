<?php

namespace frontend\forms;

use common\models\Category;
use yii\base\Model;

class SaveProductForm extends Model
{
    public $category_id;
    public $name;
    public $description;

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 100],
            ['category_id', 'integer'],
            ['category_id', 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id'], 'message' => 'Category not found'],
            ['description', 'string', 'max' => 2000],
        ];
    }
}
