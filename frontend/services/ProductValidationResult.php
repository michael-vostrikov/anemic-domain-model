<?php

declare(strict_types=1);

namespace frontend\services;

use common\models\Product;
use common\models\ProductChange;
use RuntimeException;

class ProductValidationResult
{
    /** @var array<string, string[]> Field => errors */
    private array $errors = [];

    private ?Product $product;
    private ?ProductChange $productChange;

    public function __construct(?Product $product, ?ProductChange $productChange = null)
    {
        $this->product = $product;
        $this->productChange = $productChange;
    }

    public function addError(string $field, string $error): void
    {
        $this->product = null;
        $this->productChange = null;
        $this->errors[$field][] = $error;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getProduct(): Product
    {
        if ($this->product === null) {
            throw new RuntimeException('Success result is not set');
        }

        return $this->product;
    }

    public function getProductChange(): ?ProductChange
    {
        return $this->productChange;
    }
}
