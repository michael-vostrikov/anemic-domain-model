<?php

declare(strict_types=1);

namespace frontend\services;

use common\helpers\DateHelper;
use common\models\Product;
use common\models\ProductChange;
use common\models\ProductStatus;
use common\models\Review;
use common\models\ReviewStatus;
use common\models\User;
use common\repositories\ProductChangeRepository;
use common\repositories\ProductRepository;
use common\repositories\ReviewRepository;
use frontend\forms\CreateProductForm;
use frontend\forms\SaveProductForm;
use RuntimeException;
use yii\db\Connection;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductChangeRepository $productChangeRepository,
        private readonly ReviewRepository $reviewRepository,
        private readonly Connection $dbConnection,
        private readonly AnotherSystemClient $anotherSystemClient,
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

    public function isSendForReviewAllowed(Product $product): ProductValidationResult
    {
        $productChange = $this->productChangeRepository->findById($product->id);
        $validationResult = new ProductValidationResult($product, $productChange);

        $newProduct = clone $product;
        $this->applyChanges($newProduct, $productChange);

        if ($newProduct->status === ProductStatus::ON_REVIEW->value) {
            $validationResult->addError('status', 'Product is already on review');
        } elseif ($productChange === null) {
            $validationResult->addError('id', 'No changes to send');
        } else {
            if ($newProduct->category_id === null) {
                $validationResult->addError('category_id', 'Category is not set');
            }
            if ($newProduct->name === '') {
                $validationResult->addError('name', 'Name is not set');
            }
            if ($newProduct->description === '') {
                $validationResult->addError('description', 'Description is not set');
            }
            if (strlen($newProduct->description) < 300) {
                $validationResult->addError('description', 'Description is too small');
            }
        }

        return $validationResult;
    }

    public function sendForReview(ProductValidationResult $productValidationResult, User $user): Review
    {
        $product = $productValidationResult->getProduct();
        $productChange = $productValidationResult->getProductChange();
        if ($productChange === null) {
            throw new RuntimeException('This should not happen');
        }

        $reviewFieldValues = $this->buildReviewFieldValues($product, $productChange);

        $review = new Review();
        $review->user_id = $user->id;
        $review->product_id = $product->id;
        $review->field_values = $reviewFieldValues;
        $review->status = ReviewStatus::CREATED->value;
        $review->created_at = DateHelper::getCurrentDate();
        $review->processed_at = null;

        $product->status = ProductStatus::ON_REVIEW;

        $transaction = $this->dbConnection->beginTransaction();
        $this->productRepository->save($product);
        $this->reviewRepository->save($review);
        $transaction->commit();

        $this->sendToAnotherSystem($review);

        $review->status = ReviewStatus::SENT->value;
        $this->reviewRepository->save($review);

        return $review;
    }

    private function buildReviewFieldValues(Product $product, ProductChange $productChange): array
    {
        $reviewFieldValues = [];
        $productFieldValues = $productChange->field_values;
        foreach ($productFieldValues as $key => $newValue) {
            $oldValue = $product->$key;
            $fieldChange = ['new' => $newValue, 'old' => $oldValue];
            $reviewFieldValues[$key] = $fieldChange;
        }

        return $reviewFieldValues;
    }

    private function sendToAnotherSystem(Review $review): void
    {
        $this->anotherSystemClient->sendReview($review);
    }
}
