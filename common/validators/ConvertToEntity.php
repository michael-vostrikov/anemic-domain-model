<?php

namespace common\validators;

use Yii;
use yii\validators\Validator;

class ConvertToEntity extends Validator
{
    /** @var callable */
    public $repository;

    public array $additionalArgs = [];

    public string $targetAttribute;

    public function validateAttribute($model, $attribute): void
    {
        $entityId = $model->$attribute;
        $args = [...[$entityId], ...$this->additionalArgs];

        $repositoryCallable = $this->repository;
        if (is_string($repositoryCallable[0])) {
            $repositoryCallable[0] = Yii::$container->get($repositoryCallable[0]);
        }
        $entity = $repositoryCallable(...$args);

        $targetAttribute = $this->targetAttribute;
        $model->$targetAttribute = $entity;
    }
}
