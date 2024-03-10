<?php

declare(strict_types=1);

namespace common\helpers;

class DateHelper
{
    public static function getCurrentDate(): string
    {
        return date('Y-m-d H:i:s');
    }
}
