<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
    ],
    'container' => [
        'singletons' => [
            \yii\db\Connection::class => (require __DIR__ . '/main-local.php')['components']['db'],
            \yii\mutex\MysqlMutex::class => function () {
                return new \yii\mutex\MysqlMutex();
            },
            \GuzzleHttp\Client::class => function () {
                return new \GuzzleHttp\Client([
                    'base_uri' => getenv('ANOTHER_SYSTEM_URL'),
                    'auth' => [getenv('ANOTHER_SYSTEM_USER'), getenv('ANOTHER_SYSTEM_PASSWORD')],
                ]);
            },
        ],
    ],
];
