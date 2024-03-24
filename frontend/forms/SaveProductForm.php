<?php

namespace frontend\forms;

use common\models\Category;
use common\repositories\CategoryRepository;
use common\validators\ConvertToEntity;
use yii\base\Model;

class SaveProductForm extends Model
{
    public $category_id;
    public $name;
    public $description;

    public ?Category $category;

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 100],
            ['description', 'string', 'max' => 2000],
            ['category_id', 'integer'],
            ['category_id', ConvertToEntity::class, 'repository' => [CategoryRepository::class, 'findById'], 'targetProperty' => 'category'],

            ['category', function (SaveProductForm $model) {
                if ($model->category === null) {
                    $this->addError('category_id', 'Cannot find category');
                } elseif (!$model->category->is_active) {
                    $this->addError('category_id', 'Category is not active');
                }
            }],
        ];
    }
}
