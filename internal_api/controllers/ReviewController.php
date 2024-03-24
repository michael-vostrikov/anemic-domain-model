<?php

namespace internal_api\controllers;

use common\controllers\BaseApiController;
use internal_api\services\ReviewService;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\web\Response;

class ReviewController extends BaseApiController
{
    public function __construct(
        $id,
        $module,
        $config,
        private readonly ReviewService $reviewService,
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
        $review = $this->reviewService->accept($id);

        return $this->successResponse($review->toArray());
    }

    public function actionDecline(int $id): Response
    {
        $review = $this->reviewService->decline($id);

        return $this->successResponse($review->toArray());
    }
}
