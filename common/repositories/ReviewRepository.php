<?php

declare(strict_types=1);

namespace common\repositories;

use common\models\Review;

class ReviewRepository
{
    public function save(Review $review): void
    {
        $review->save(false);
    }
}
