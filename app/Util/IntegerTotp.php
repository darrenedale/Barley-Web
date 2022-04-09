<?php

namespace App\Util;

use App\Exceptions\InvalidTotpDigitsException;
use App\Exceptions\InvalidTotpIntervalException;
use DateTime;

/**
 * Base class for TOTP renderers that produce integers of fixed digit lengths.
 *
 * TOTPs are commonly rendered as either 6 or 8 0-padded digits. This base class implements the algorithm for
 * generating the output, subclasses just need to implement the static method digits() to indicate how many digits are
 * used in the rendering.
 */
class IntegerTotp extends Totp
{
    /**
     * @var int The number of digits in the generated TOTP passwords. Always >= 1.
     */
    private int $m_digits;

    /**
     * @throws \App\Exceptions\InvalidTotpIntervalException
     * @throws \App\Exceptions\InvalidTotpDigitsException
     */
    public function __construct(int $digits, string $seed, int $interval = self::DefaultInterval, DateTime|int $baseline = self::DefaultBaselineTime)
    {
        if (1 > $digits) {
            throw new InvalidTotpDigitsException($digits, "Number of digits must be >= 1.");
        }

        parent::__construct($seed, $interval, $baseline);
        $this->m_digits = $digits;
    }

    /**
     * @return int The number of digits in the generated TOTP password.
     */
    public function digits(): int
    {
        return $this->m_digits;
    }

	/**
     * Render the current code as a padded integer.
     *
	 * @inheritDoc
	 */
	public function currentCode(): string
	{
        $code = $this->currentRawCode();
        $offset = ord($code[19]) & 0xf;

        // NOTE static:: here is guaranteed to refer to a non-abstract subclass - static::digits() WILL be implemented
        $code = (
                (ord($code[$offset]) & 0x7f) << 24
                | ord($code[$offset + 1]) << 16
                | ord($code[$offset + 2]) << 8
                | ord($code[$offset + 3])
            ) % (10 ** $this->digits());
        return str_pad("{$code}", 6, "0", STR_PAD_LEFT);
	}
}
