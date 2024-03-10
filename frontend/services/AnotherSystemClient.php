<?php

declare(strict_types=1);

namespace frontend\services;

use common\models\Review;

class AnotherSystemClient
{
    public function sendReview(Review $review): void
    {
        // pretend that we send something with HTTP request
    }
}
