<?php

namespace App\Util;

class EightDigitTotp extends IntegerTotp
{
    public function __construct(string $seed, int $interval = self::DefaultInterval, DateTime|int $baseline = self::DefaultBaselineTime)
    {
        parent::__construct(8, $seed, $interval, $baseline);
    }
}
