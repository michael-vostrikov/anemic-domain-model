<?php

declare(strict_types=1);

namespace internal_api\services;

use common\exceptions\EntityNotFoundException;
use common\models\Product;
use common\models\Review;
use common\repositories\ProductRepository;
use common\repositories\ReviewRepository;
use yii\db\Connection;

// INFO: This is an application service
class ReviewService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ReviewRepository $reviewRepository,
        private readonly Connection $dbConnection,
    ) {
    }

    private function findReview(int $reviewId): Review
    {
        $review = $this->reviewRepository->findById($reviewId, needLock: true);

        if ($review === null) {
            throw new EntityNotFoundException();
        }

        return $review;
    }

    private function findProduct(int $productId): Product
    {
        $product = $this->productRepository->findById($productId, needLock: true);

        if ($product === null) {
            throw new EntityNotFoundException();
        }

        return $product;
    }

    public function accept(int $reviewId): Review
    {
        $review = $this->findReview($reviewId);
        $product = $this->findProduct($review->product_id);

        // Should it be in a domain service? What if someone accepts changes in one entity without domain service?
        $review->accept();
        $product->acceptChangesFromReview($review);

        // Direct transactions for several aggregates are not allowed by DDD
        $transaction = $this->dbConnection->beginTransaction();
        $this->reviewRepository->save($review);
        $this->productRepository->save($product);
        $transaction->commit();

        return $review;
    }

    public function decline(int $reviewId): Review
    {
        $review = $this->findReview($reviewId);
        $product = $this->findProduct($review->product_id);

        $review->decline();
        $product->declineChangesFromReview($review);

        $transaction = $this->dbConnection->beginTransaction();
        $this->reviewRepository->save($review);
        $this->productRepository->save($product);
        $transaction->commit();

        return $review;
    }
}
