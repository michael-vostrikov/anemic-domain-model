<?php

declare(strict_types=1);

namespace common\repositories;

use common\models\ProductChange;

class ProductChangeRepository
{
    public function findById(int $productId): ?ProductChange
    {
        /** @var ?ProductChange $productChange */
        $productChange = ProductChange::find()->where(['product_id' => $productId])->one();

        return $productChange;
    }

    public function save(ProductChange $productChange): void
    {
        $productChange->save(false);
    }

    public function delete(ProductChange $productChange): void
    {
        $productChange->delete();
    }
}
