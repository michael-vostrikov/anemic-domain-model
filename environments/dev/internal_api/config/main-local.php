<?php

// configuration adjustments for 'dev' environment
$config['bootstrap'][] = 'debug';
$config['modules']['debug'] = [
    'class' => \yii\debug\Module::class,
];

return $config;
