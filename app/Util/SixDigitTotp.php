<?php

namespace App\Util;

class SixDigitTotp extends IntegerTotp
{
    public static function digits(): int
    {
        return 6;
    }
}
