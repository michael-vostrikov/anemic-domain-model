<?php

declare(strict_types=1);

namespace frontend\services;

use common\exceptions\AccessDeniedException;
use common\exceptions\EntityNotFoundException;
use common\exceptions\ValidationException;
use common\models\Product;
use common\models\Review;
use common\models\User;
use common\repositories\ProductRepository;
use common\repositories\ReviewRepository;
use frontend\forms\CreateProductForm;
use frontend\forms\SaveProductForm;
use yii\db\Connection;

// INFO: This is an application service
class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ReviewRepository $reviewRepository,
        private readonly Connection $dbConnection,
        private readonly AnotherSystemClient $anotherSystemClient,
    ) {
    }

    public function create(CreateProductForm $form, User $user): Product
    {
        if (!$form->validate()) {
            throw new ValidationException($form->getErrors());
        }

        $product = new Product();
        $product->create($user, $form->name);

        $transaction = $this->dbConnection->beginTransaction();
        $this->productRepository->save($product);
        $transaction->commit();

        return $product;
    }

    private function findProduct(int $productId, User $user): Product
    {
        $product = $this->productRepository->findById($productId, needLock: true);

        // We imitate controller behavior with exceptions
        if ($product === null) {
            throw new EntityNotFoundException();
        }

        if (!$product->isOwner($user)) {
            throw new AccessDeniedException();
        }

        return $product;
    }

    public function save(int $productId, SaveProductForm $form, User $user): Product
    {
        $product = $this->findProduct($productId, $user);

        $product->edit($form);

        $transaction = $this->dbConnection->beginTransaction();
        $this->productRepository->save($product);
        $transaction->commit();

        return $product;
    }

    public function view(int $productId, User $user): Product
    {
        $product = $this->findProduct($productId, $user);

        return $product->getProductWithAppliedChanges();
    }

    public function sendForReview(int $productId, User $user): Review
    {
        $product = $this->findProduct($productId, $user);

        $review = $product->sendForReview($user);

        // We have a logic 'save - send - save'.
        // This is a business logic, it comes from business requirements.
        // Should we move it somewhere else from this class?

        // How to do this correctly? Transactions should not cross aggregate boundaries by DDD.
        $transaction = $this->dbConnection->beginTransaction();
        $this->productRepository->save($product);
        $this->reviewRepository->save($review);
        $transaction->commit();

        $this->sendToAnotherSystem($review);

        $review->markAsSent();

        $transaction = $this->dbConnection->beginTransaction();
        $this->reviewRepository->save($review);
        $transaction->commit();

        return $review;
    }

    private function sendToAnotherSystem(Review $review): void
    {
        $this->anotherSystemClient->sendReview($review);
    }
}
