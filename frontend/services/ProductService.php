<?php

declare(strict_types=1);

namespace frontend\services;

use common\helpers\DateHelper;
use common\models\Product;
use common\models\ProductStatus;
use common\models\User;
use common\repositories\ProductRepository;
use frontend\forms\CreateProductForm;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function create(CreateProductForm $form, User $user): Product
    {
        $product = new Product();

        $product->user_id = $user->id;
        $product->status = ProductStatus::HIDDEN->value;
        $product->created_at = DateHelper::getCurrentDate();

        $product->category_id = null;
        $product->name = $form->name;
        $product->description = '';

        $this->productRepository->save($product);

        return $product;
    }
}
