<?php

namespace App\Util;

class EightDigitTotp extends IntegerTotp
{
	/**
	 * @inheritDoc
	 */
	public static function digits(): int
	{
        return 8;
	}
}
