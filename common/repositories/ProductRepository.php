<?php

declare(strict_types=1);

namespace common\repositories;

use common\models\Product;

class ProductRepository
{
    public function save(Product $product): void
    {
        $product->save(false);
    }
}
