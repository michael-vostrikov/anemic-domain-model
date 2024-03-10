<?php

namespace frontend\controllers;

use common\controllers\BaseApiController;
use frontend\forms\CreateProductForm;
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
}
