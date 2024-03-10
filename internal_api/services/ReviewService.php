<?php

declare(strict_types=1);

namespace internal_api\services;

use common\helpers\DateHelper;
use common\models\Product;
use common\models\ProductChange;
use common\models\ProductStatus;
use common\models\Review;
use common\models\ReviewStatus;
use common\repositories\ProductChangeRepository;
use common\repositories\ProductRepository;
use common\repositories\ReviewRepository;
use RuntimeException;
use yii\db\Connection;

class ReviewService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductChangeRepository $productChangeRepository,
        private readonly ReviewRepository $reviewRepository,
        private readonly Connection $dbConnection,
    ) {
    }

    public function accept(Review $review): Review
    {
        if ($review->status !== ReviewStatus::SENT->value) {
            throw new RuntimeException('Review is already processed');
        }

        $product = $this->productRepository->findById($review->product_id, needLock: true);

        $transaction = $this->dbConnection->beginTransaction();

        $this->saveReviewResult($review, ReviewStatus::ACCEPTED);
        $this->acceptProductChanges($product, $review);

        $transaction->commit();

        return $review;
    }

    public function decline(Review $review): Review
    {
        if ($review->status !== ReviewStatus::SENT->value) {
            throw new RuntimeException('Review is already processed');
        }

        $product = $this->productRepository->findById($review->product_id, needLock: true);

        $transaction = $this->dbConnection->beginTransaction();

        $this->saveReviewResult($review, ReviewStatus::DECLINED);
        $this->declineProductChanges($product);

        $transaction->commit();

        return $review;
    }

    private function saveReviewResult(Review $review, ReviewStatus $status): void
    {
        $review->status = $status->value;
        $review->processed_at = DateHelper::getCurrentDate();
        $this->reviewRepository->save($review);
    }

    private function acceptProductChanges(Product $product, Review $review): void
    {
        foreach ($review->field_values as $field => $fieldChange) {
            $newValue = $fieldChange['new'];
            $product->$field = $newValue;
        }
        $product->status = ProductStatus::PUBLISHED;
        $this->productRepository->save($product);

        $this->productChangeRepository->deleteById($product->id);
    }

    private function declineProductChanges(Product $product): void
    {
        $product->status = ProductStatus::HIDDEN;
        $this->productRepository->save($product);

        $this->productChangeRepository->deleteById($product->id);
    }
}
