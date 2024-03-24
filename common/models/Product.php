<?php

namespace common\models;

use common\exceptions\ValidationException;
use common\helpers\DateHelper;
use frontend\forms\SaveProductForm;
use RuntimeException;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int $user_id
 * @property ?int $category_id
 * @property string $name
 * @property string $description
 * @property int $status
 * @property string $created_at
 *
 * @property-read ProductChange $productChange
 * @property-read Category $category
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    // INFO: Probably it should be in constructor, but it will not work with ActiveRecord.
    // But even if it will be in constructor, we will have to use hacks like Reflection to initialize entities loaded from database.
    public function create(User $user, string $name): void
    {
        $this->user_id = $user->id;
        $this->name = $name;

        $this->status = ProductStatus::HIDDEN->value;
        $this->category_id = null;
        $this->description = '';

        // How to inject it as an instance? As an argument together with business parameters?
        $this->created_at = DateHelper::getCurrentDate();
    }

    public function getProductChange(): ActiveQuery
    {
        return $this->hasOne(ProductChange::class, ['product_id' => 'id']);
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function validateIsEditAllowed(): array
    {
        $errors = [];
        if ($this->status === ProductStatus::ON_REVIEW->value) {
            $errors['status'][] = 'Product is on review';
        }

        return $errors;
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function edit(SaveProductForm $form): void
    {
        $errors = $this->validateIsEditAllowed();
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // Is it correct to validate input DTO inside entity?
        // Should we pass non-validated input data or duplicate validation here and in application service?
        if (!$form->validate()) {
            throw new ValidationException($form->getErrors());
        }

        $productChange = $this->ensureProductChange();

        $fieldValues = [];
        if ($form->category->id !== $this->category_id) {
            $fieldValues['category_id'] = $form->category->id;
        }
        if ($form->name !== $this->name) {
            $fieldValues['name'] = $form->name;
        }
        if ($form->description !== $this->description) {
            $fieldValues['description'] = $form->description;
        }
        $productChange->field_values = $fieldValues;
    }

    private function ensureProductChange(): ProductChange
    {
        $productChange = $this->productChange;

        if ($productChange === null) {
            // Here we mix business logic and details of implementation
            $productChange = new ProductChange();
            $this->populateRelation('productChange', $productChange);
        }

        return $productChange;
    }

    public function getProductWithAppliedChanges(): Product
    {
        $newProduct = new Product($this->getAttributes());
        $this->applyChanges($newProduct, $this->productChange);

        return $newProduct;
    }

    private function applyChanges(Product $product, ?ProductChange $productChange): void
    {
        if ($productChange !== null) {
            // If there will be 30 fields, images and attached files, this method will be very big,
            // and it will not be possible to move something to a separate component
            foreach ($productChange->field_values as $field => $value) {
                $product->$field = $value;
            }

            $product->populateRelation('category', $productChange->category);
        }
    }

    public function validateIsSendForReviewAllowed(): array
    {
        // How to pass external validators here, like barcode validator or spell checker?

        $errors = [];

        $newProduct = $this->getProductWithAppliedChanges();

        if ($this->status === ProductStatus::ON_REVIEW->value) {
            $errors['status'][] = 'Product is already on review';
        } elseif ($this->productChange === null) {
            $errors['id'][] = 'No changes to send';
        } else {
            // Actually the invariant here is that category must exist in database and be active.
            // We don't know at this point how $newProduct->category was loaded, so we cannot be sure about this.

            if ($newProduct->category_id === null) {
                $errors['category_id'][] = 'Category is not set';
            } elseif ($newProduct->category === null) {
                $errors['category_id'][] = 'Category not found';
            } elseif ($newProduct->category->is_active !== true) {
                $errors['category_id'][] = 'Category is not active';
            }

            if ($newProduct->name === '') {
                $errors['name'][] = 'Name is not set';
            }
            if ($newProduct->description === '') {
                $errors['description'][] = 'Description is not set';
            }
            if (strlen($newProduct->description) < 300) {
                $errors['description'][] = 'Description is too small';
            }
        }

        return $errors;
    }

    public function sendForReview(User $user): Review
    {
        $errors = $this->validateIsSendForReviewAllowed();
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // It's not ok to manage Review aggregate from Product aggregate.
        // Should it be in a domain service? What if someone will create review object without domain service?
        $this->status = ProductStatus::ON_REVIEW;
        $review = new Review();
        $review->create($this, $user);

        return $review;
    }

    // These methods are used only in internal api.
    // Same way we will have here methods for admin panel, which usually allow to set any state without checking constraints.
    // Or we will have another Product entity for admin area because it's another bounded context,
    // and we can break constraints of current Product entity.

    public function acceptChangesFromReview(Review $review): void
    {
        // It is already checked in Review::accept(), should we check here too?
        if (!$review->isResultProcessingAllowed()) {
            throw new RuntimeException('Review is already processed');
        }

        foreach ($review->field_values as $field => $fieldChange) {
            $newValue = $fieldChange['new'];
            $this->$field = $newValue;
        }

        $this->status = ProductStatus::PUBLISHED;

        $this->deleteProductChange();
    }

    public function declineChangesFromReview(Review $review): void
    {
        // It is already checked in Review::decline(), should we check here too?
        if (!$review->isResultProcessingAllowed()) {
            throw new RuntimeException('Review is already processed');
        }

        $this->status = ProductStatus::HIDDEN;

        $this->deleteProductChange();
    }

    private function deleteProductChange(): void
    {
        unset($this['productChange']);
    }
}
