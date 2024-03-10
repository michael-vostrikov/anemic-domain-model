<?php

declare(strict_types=1);

namespace common\repositories;

use common\models\Product;

class ProductRepository
{
    public function __construct(private readonly LockService $lockService)
    {
    }

    public function findById(int $id, bool $needLock): ?Product
    {
        if ($needLock) {
            $this->lockService->lock(Product::class, $id);
        }

        /** @var ?Product $product */
        $product = Product::find()->where(['id' => $id])->one();

        return $product;
    }

    public function save(Product $product): void
    {
        $product->save(false);
    }
}
