<?php

namespace common\controllers;

use common\exceptions\AccessDeniedException;
use common\exceptions\EntityNotFoundException;
use common\exceptions\ValidationException;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
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

    public function runAction($id, $params = [])
    {
        try {
            return parent::runAction($id, $params);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception->getErrors());
        } catch (EntityNotFoundException $exception) {
            throw new NotFoundHttpException('Entity not found');
        } catch (AccessDeniedException $exception) {
            throw new ForbiddenHttpException('Access denied');
        }
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
