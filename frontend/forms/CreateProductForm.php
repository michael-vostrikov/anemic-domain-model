<?php

namespace frontend\forms;

use yii\base\Model;

class CreateProductForm extends Model
{
    public $name;

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 100],
        ];
    }
}
