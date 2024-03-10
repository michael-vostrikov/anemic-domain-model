<?php

declare(strict_types=1);

namespace frontend\services;

use common\helpers\DateHelper;
use common\models\Product;
use common\models\ProductChange;
use common\models\ProductStatus;
use common\models\User;
use common\repositories\ProductChangeRepository;
use common\repositories\ProductRepository;
use frontend\forms\CreateProductForm;
use frontend\forms\SaveProductForm;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductChangeRepository $productChangeRepository,
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

    public function isEditAllowed(Product $product): ProductValidationResult
    {
        $productValidationResult = new ProductValidationResult($product);

        if ($product->status === ProductStatus::ON_REVIEW->value) {
            $productValidationResult->addError('status', 'Product is on review');
        }

        return $productValidationResult;
    }

    public function save(ProductValidationResult $productValidationResult, SaveProductForm $form): ProductChange
    {
        $product = $productValidationResult->getProduct();
        $productChange = $this->productChangeRepository->findById($product->id);

        if ($productChange === null) {
            $productChange = new ProductChange();
            $productChange->product_id = $product->id;
        }

        $fieldValues = [];
        if ($form->category_id !== $product->category_id) {
            $fieldValues['category_id'] = $form->category_id;
        }
        if ($form->name !== $product->name) {
            $fieldValues['name'] = $form->name;
        }
        if ($form->description !== $product->description) {
            $fieldValues['description'] = $form->description;
        }
        $productChange->field_values = $fieldValues;

        $this->productChangeRepository->save($productChange);

        return $productChange;
    }

    public function view(Product $product): Product
    {
        $productChange = $this->productChangeRepository->findById($product->id);

        $this->applyChanges($product, $productChange);

        return $product;
    }

    private function applyChanges(Product $product, ?ProductChange $productChange): void
    {
        if ($productChange !== null) {
            foreach ($productChange->field_values as $field => $value) {
                $product->$field = $value;
            }
        }
    }
}
