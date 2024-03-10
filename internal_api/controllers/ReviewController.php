<?php

namespace internal_api\controllers;

use common\controllers\BaseApiController;
use common\models\Review;
use common\repositories\ReviewRepository;
use internal_api\services\ReviewService;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ReviewController extends BaseApiController
{
    public function __construct(
        $id,
        $module,
        $config,
        private readonly ReviewService $reviewService,
        private readonly ReviewRepository $reviewRepository,
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return array_merge([
            'cors'  => [
                'class' => \yii\filters\Cors::class,
                'cors'  => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['POST'],
                    'Access-Control-Allow-Credentials' => false,
                    'Access-Control-Max-Age' => 10,
                    'Access-Control-Allow-Headers' => ['Authorization', 'Content-Type', 'X-Csrf-Token'],
                ],
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::class,
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'accept' => ['post'],
                    'decline' => ['post'],
                ],
            ],
        ], parent::behaviors());
    }

    public function actionAccept(int $id): Response
    {
        $review = $this->findEntity($id, needLock: true);

        $review = $this->reviewService->accept($review);

        return $this->successResponse($review->toArray());
    }

    public function actionDecline(int $id): Response
    {
        $review = $this->findEntity($id, needLock: true);

        $review = $this->reviewService->decline($review);

        return $this->successResponse($review->toArray());
    }

    private function findEntity(int $id, bool $needLock): Review
    {
        $review = $this->reviewRepository->findById($id, $needLock);

        if ($review === null) {
            throw new NotFoundHttpException('Entity not found');
        }

        return $review;
    }
}
