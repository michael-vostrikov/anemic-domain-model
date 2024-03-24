<?php

namespace frontend\controllers;

use common\controllers\BaseApiController;
use frontend\forms\CreateProductForm;
use frontend\forms\SaveProductForm;
use frontend\services\ProductService;
use yii\filters\VerbFilter;
use yii\web\Response;

class ProductController extends BaseApiController
{
    public function __construct(
        $id,
        $module,
        $config,
        private readonly ProductService $productService,
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'save' => ['post'],
                    'view' => ['get'],
                    'send-for-review' => ['post'],
                ],
            ],
        ]);
    }

    public function actionCreate(): Response
    {
        $form = new CreateProductForm();
        $form->load($this->request->post(), '');

        $product = $this->productService->create($form, $this->getCurrentUser());

        return $this->successResponse($product->toArray());
    }

    public function actionSave(int $id): Response
    {
        $form = new SaveProductForm();
        $form->load($this->request->post(), '');

        $product = $this->productService->save($id, $form, $this->getCurrentUser());

        return $this->successResponse($product->toArray());
    }

    public function actionView(int $id): Response
    {
        $product = $this->productService->view($id, $this->getCurrentUser());

        return $this->successResponse($product->toArray());
    }

    public function actionSendForReview(int $id): Response
    {
        // We always need to pass current user to check access in service
        $review = $this->productService->sendForReview($id, $this->getCurrentUser());

        return $this->successResponse($review->toArray());
    }
}
