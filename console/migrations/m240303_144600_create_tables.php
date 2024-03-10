<?php

class m240303_144600_create_tables extends yii\db\Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('product', [
            'id' => $this->primaryKey()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'category_id' => $this->integer(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->string(2000)->notNull(),
            'status' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex('product_category', 'product', 'category_id', false);
        $this->createIndex('product_user', 'product', 'user_id', false);


        $this->createTable('product_change', [
            'product_id' => $this->integer()->notNull(),
            'field_values' => $this->json()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('', 'product_change', 'product_id');


        $this->createTable('review', [
            'id' => $this->primaryKey()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'field_values' => $this->json()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'processed_at' => $this->dateTime()->null(),
        ], $tableOptions);

        $this->createIndex('review_user', 'review', 'user_id', false);

        $this->createIndex('review_product', 'review', 'product_id', false);


        $this->createTable('category', [
            'id' => $this->primaryKey()->notNull(),
            'name' => $this->string(100)->notNull(),
        ], $tableOptions);


        $this->addForeignKey('product_user', 'product', 'user_id', 'user', 'id', null, null);
        $this->addForeignKey('product_category', 'product', 'category_id', 'category', 'id', null, null);

        $this->addForeignKey('review_product', 'review', 'product_id', 'product', 'id', null, null);
        $this->addForeignKey('review_user', 'review', 'user_id', 'user', 'id', null, null);

        $this->addForeignKey('product_change_product', 'product_change', 'product_id', 'product', 'id', null, null);
    }

    public function down()
    {
        $this->dropTable('review');
        $this->dropTable('product_change');
        $this->dropTable('product');
        $this->dropTable('category');
    }
}
