<?php

namespace common\controllers;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

class BaseApiController extends Controller
{
    /** @var Request */
    public $request = 'request';

    /** @var Response */
    public $response = 'response';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action): bool
    {
        $this->response->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }

    protected function validationErrorResponse(array $errors): Response
    {
        $this->response->statusCode = 400;

        return $this->asJson($errors);
    }

    protected function successResponse(array $data): Response
    {
        $this->response->statusCode = 200;

        return $this->asJson($data);
    }

    protected function getCurrentUser(): User
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        return $user;
    }
}
