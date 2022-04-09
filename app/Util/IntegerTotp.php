<?php

namespace App\Util;

/**
 * Base class for TOTP renderers that produce integers of fixed digit lengths.
 *
 * TOTPs are commonly rendered as either 6 or 8 0-padded digits. This base class implements the algorithm for
 * generating the output, subclasses just need to implement the static method digits() to indicate how many digits are
 * used in the rendering.
 */
abstract class IntegerTotp extends Totp
{
    /**
     * Implement this to indicate how many digits to present.
     *
     * @return int
     */
    public abstract static function digits(): int;

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
            ) % (10 ** static::digits());
        return str_pad("{$code}", 6, "0", STR_PAD_LEFT);
	}
}
