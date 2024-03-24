<?php

use common\helpers\DateHelper;
use common\models\Category;
use common\models\Product;
use common\models\ProductChange;
use common\models\ProductStatus;
use common\models\Review;
use common\models\User;

class m240303_193900_add_demo_data extends yii\db\Migration
{
    public function up()
    {
        $user = new User();
        $user->id = 1;
        $user->username = 'user';
        $user->email = 'user@example.com';
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword('123456');
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->save();

        $user = new User();
        $user->id = 2;
        $user->username = 'user_2';
        $user->email = 'user_2@example.com';
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword('123456');
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->save();

        $user = new User();
        $user->id = 3;
        $user->username = 'internal_api_user';
        $user->email = 'api_token';
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword('123456');
        $user->generateAuthKey();
        $user->verification_token = 'api_token';
        $user->save();

        $category = new Category();
        $category->id = 1;
        $category->name = 'Category 1';
        $category->is_active = true;
        $category->save();

        $category = new Category();
        $category->id = 2;
        $category->name = 'Category 2';
        $category->is_active = false;
        $category->save();


        $product = new Product();
        $product->id = 1;
        $product->user_id = 1;
        $product->category_id = 1;
        $product->name = 'Product 1';
        $product->description = '';
        $product->status = ProductStatus::HIDDEN;
        $product->created_at = DateHelper::getCurrentDate();
        $product->save();

        $product = new Product();
        $product->id = 2;
        $product->user_id = 1;
        $product->category_id = null;
        $product->name = 'Product 2';
        $product->description = '';
        $product->status = ProductStatus::ON_REVIEW;
        $product->created_at = DateHelper::getCurrentDate();
        $product->save();

        $product = new Product();
        $product->id = 3;
        $product->user_id = 2;
        $product->category_id = null;
        $product->name = 'Product 3';
        $product->description = '';
        $product->status = ProductStatus::HIDDEN;
        $product->created_at = DateHelper::getCurrentDate();
        $product->save();
    }

    public function down()
    {
        Review::deleteAll();
        ProductChange::deleteAll();
        Product::deleteAll();
        Category::deleteAll();
        User::deleteAll();
    }
}
