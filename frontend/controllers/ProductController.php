<?php

namespace frontend\controllers;

use common\controllers\BaseApiController;
use common\models\Product;
use common\repositories\ProductRepository;
use frontend\forms\CreateProductForm;
use frontend\forms\SaveProductForm;
use frontend\services\ProductService;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductController extends BaseApiController
{
    public function __construct(
        $id,
        $module,
        $config,
        private readonly ProductRepository $productRepository,
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

        if (!$form->validate()) {
            return $this->validationErrorResponse($form->getErrors());
        }

        $product = $this->productService->create($form, $this->getCurrentUser());

        return $this->successResponse($product->toArray());
    }

    public function actionSave(int $id): Response
    {
        $product = $this->findEntity($id, needLock: true);
        sleep(3);  // for testing locks

        $validationResult = $this->productService->isEditAllowed($product);
        if ($validationResult->hasErrors()) {
            return $this->validationErrorResponse($validationResult->getErrors());
        }

        $form = new SaveProductForm();
        $form->load($this->request->post(), '');
        if (!$form->validate()) {
            return $this->validationErrorResponse($form->getErrors());
        }

        $product = $this->productService->save($validationResult, $form);

        return $this->successResponse($product->toArray());
    }

    public function actionView(int $id): Response
    {
        $product = $this->findEntity($id, needLock: false);
        $product = $this->productService->view($product);

        return $this->successResponse($product->toArray());
    }

    public function actionSendForReview(int $id): Response
    {
        $product = $this->findEntity($id, needLock: true);
        sleep(3);  // for testing locks

        $productValidationResult = $this->productService->isSendForReviewAllowed($product);
        if ($productValidationResult->hasErrors()) {
            return $this->validationErrorResponse($productValidationResult->getErrors());
        }

        $review = $this->productService->sendForReview($productValidationResult, $this->getCurrentUser());

        return $this->successResponse($review->toArray());
    }

    private function findEntity(int $id, bool $needLock): Product
    {
        $product = $this->productRepository->findById($id, $needLock);

        if ($product === null) {
            throw new NotFoundHttpException('Entity not found');
        }

        $isAccessAllowed = $product->user_id === $this->getCurrentUser()->id;
        if (!$isAccessAllowed) {
            throw new ForbiddenHttpException('Access denied');
        }

        return $product;
    }
}
