<?php

declare(strict_types=1);

namespace common\repositories;

use common\models\Review;

class ReviewRepository
{
    public function __construct(private readonly LockService $lockService)
    {
    }

    public function findById(int $id, bool $needLock): ?Review
    {
        if ($needLock) {
            $this->lockService->lock(Review::class, $id);
        }

        /** @var ?Review $review */
        $review = Review::find()->where(['id' => $id])->one();

        return $review;
    }

    public function save(Review $review): void
    {
        $review->save(false);
    }
}
