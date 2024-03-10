<?php

declare(strict_types=1);

namespace common\repositories;

use RuntimeException;
use yii\mutex\MysqlMutex;

class LockService
{
    private const LOCK_TIMEOUT = 10;

    public function __construct(private readonly MysqlMutex $mutex)
    {
    }

    public function lock($className, $id): void
    {
        // lock is released automatically when connection is closed
        $lockName = $className . ':' . $id;
        $acquired = $this->mutex->acquire($lockName, self::LOCK_TIMEOUT);

        if (!$acquired) {
            throw new RuntimeException('Cannot acquire lock');
        }
    }

    public function release($className, $id): void
    {
        $lockName = $className . ':' . $id;
        $this->mutex->release($lockName);
    }
}
