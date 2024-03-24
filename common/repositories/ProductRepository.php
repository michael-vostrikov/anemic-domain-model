<?php

declare(strict_types=1);

namespace common\repositories;

use common\models\Product;
use common\models\ProductChange;

class ProductRepository
{
    public function __construct(
        private readonly LockService $lockService,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function findById(int $id, bool $needLock): ?Product
    {
        if ($needLock) {
            $this->lockService->lock(Product::class, $id);
        }

        /** @var ?Product $product */
        $product = Product::find()->with('productChange')->where(['id' => $id])->one();

        $this->initProduct($product, $needLock);
        return $product;
    }

    private function initProduct(Product $product, bool $needLock): void
    {
        // INFO: Category is a separate aggregate, because it has 'is_active' property,
        // which can be changed in a corresponding business action,
        // so we need to use repository.

        $category = $this->categoryRepository->findById($product->category_id, $needLock);
        $product->populateRelation('category', $category);

        $productChange = $product->productChange;
        $newCategoryId = $productChange->field_values['category_id'];
        $newCategory = $this->categoryRepository->findById($newCategoryId, $needLock);
        $productChange->populateRelation('category', $newCategory);
    }

    public function save(Product $product): void
    {
        $product->save(false);

        $productChange = $product->productChange;
        if ($productChange === null) {
            ProductChange::deleteAll(['productId' => $product->id]);
        } else {
            $productChange->product_id = $product->id;
            $productChange->save();
        }
    }
}
