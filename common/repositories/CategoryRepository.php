<?php

declare(strict_types=1);

namespace common\repositories;

use common\models\Category;

class CategoryRepository
{
    public function __construct(private readonly LockService $lockService)
    {
    }

    public function findById(int $id, bool $needLock): ?Category
    {
        if ($needLock) {
            $this->lockService->lock(Category::class, $id);
        }

        /** @var ?Category $category */
        $category = Category::find()->where(['id' => $id])->one();

        return $category;
    }

    public function save(Category $category): void
    {
        $category->save(false);
    }
}
