<?php

namespace common\models;

use common\helpers\DateHelper;
use RuntimeException;

/**
 * This is the model class for table "review".
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $status
 * @property mixed $field_values
 * @property string $created_at
 * @property ?string $processed_at
 */
class Review extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'review';
    }

    public function create(Product $product, User $user)
    {
        $this->user_id = $user->id;
        $this->product_id = $this->id;
        $this->field_values = $this->buildReviewFieldValues($product);
        $this->status = ReviewStatus::CREATED->value;
        $this->created_at = DateHelper::getCurrentDate();
        $this->processed_at = null;
    }

    private function buildReviewFieldValues(Product $product): array
    {
        $reviewFieldValues = [];
        $productFieldValues = $product->productChange->field_values;
        foreach ($productFieldValues as $key => $newValue) {
            $oldValue = $product->$key;
            $fieldChange = ['new' => $newValue, 'old' => $oldValue];
            $reviewFieldValues[$key] = $fieldChange;
        }

        return $reviewFieldValues;
    }

    public function markAsSent(): void
    {
        $this->status = ReviewStatus::SENT->value;
    }

    // These methods are used only in internal api

    public function isResultProcessingAllowed(): bool
    {
        if ($this->status !== ReviewStatus::SENT->value) {
            return false;
        }

        return true;
    }

    public function accept(): void
    {
        if (!$this->isResultProcessingAllowed()) {
            throw new RuntimeException('Review is already processed');
        }

        $this->status = ReviewStatus::ACCEPTED->value;
        $this->processed_at = DateHelper::getCurrentDate();
    }

    public function decline(): void
    {
        if (!$this->isResultProcessingAllowed()) {
            throw new RuntimeException('Review is already processed');
        }

        $this->status = ReviewStatus::DECLINED->value;
        $this->processed_at = DateHelper::getCurrentDate();
    }
}
