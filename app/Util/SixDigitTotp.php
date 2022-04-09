<?php

namespace App\Util;

use DateTime;

class SixDigitTotp extends IntegerTotp
{
    public function __construct(string $seed, int $interval = self::DefaultInterval, DateTime|int $baseline = self::DefaultBaselineTime)
    {
        parent::__construct(6, $seed, $interval, $baseline);
    }
}
