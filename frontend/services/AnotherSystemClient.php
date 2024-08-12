<?php

declare(strict_types=1);

namespace frontend\services;

use common\models\Review;
use GuzzleHttp\Client as GuzzleClient;

class AnotherSystemClient
{
    public function __construct(
        private readonly GuzzleClient $httpClient,
    ) {
    }

    public function sendReview(Review $review): void
    {
        // comment this line for testing
        $this->httpClient->post('/create-review', ['json' => $review->toArray()]);
    }
}
