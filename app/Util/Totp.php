<?php

namespace App\Util;

use App\Exceptions\InvalidBase32DataException;
use App\Exceptions\InvalidBase64DataException;
use App\Exceptions\InvalidTotpIntervalException;
use DateTime;
use DateTimeZone;
use JetBrains\PhpStorm\Pure;

/**
 * Abstract base class for generating TOTP codes.
 */
abstract class Totp
{
    /**
     * The default update interval for codes.
     */
    public const DefaultInterval = 30;

    /**
     * The default baseline time for codes.
     */
    public const DefaultBaselineTime = 0;

    /**
     * The hashing algorithm to use when generating HMACs.
     */
    protected const HashAlgorithm = "sha1";

    /**
     * @var string|null The seed for the code.
     */
    private ?string $m_seed;

    /**
     * @var int The interval, in seconds, at which the code changes.
     */
    private int $m_interval;

    /**
     * @var int The baseline time against which codes are generated.
     */
    private int $m_baselineTime;

    /**
     * Initialise a new TOTP.
     *
     * If the baseline is specified as an int, it is interpreted as the number of seconds since the Unix epoch.
     *
     * @param string $seed The seed for the code. This must be the binary representation of the seed.
     * @param int $interval The update interval for the code. Defaults to 30 seconds.
     * @param int|\DateTime $baseline The baseline time from which the code is generated.
     * Defaults to 0.
     *
     * @throws \App\Exceptions\InvalidTotpIntervalException if the interval is not a positive integer.
     */
    public function __construct(string $seed, int $interval = self::DefaultInterval, int | DateTime $baseline = self::DefaultBaselineTime)
    {
        $this->setSeed($seed);
        $this->setInterval($interval);
        $this->m_baselineTime = $baseline;
    }

    /**
     * Check whether a seed has been set.
     *
     * @return bool
     */
    public function hasSeed(): bool
    {
        return isset($this->m_seed);
    }

    /**
     * Fetch the raw seed.
     *
     * The raw seed is a byte sequence, not technically a string. It is likely to contain non-printable bytes.
     *
     * @return string The raw seed.
     */
    public function seed(): string
    {
        return $this->m_seed;
    }

    /**
     * @return string The seed, base64 encoded so that it's printable.
     */
    #[Pure] public function base64Seed(): string
    {
        return base64_encode($this->seed());
    }

    /**
     * Set the seed for generated codes.
     *
     * @param string $seed The binary seed.
     */
    public function setSeed(string $seed)
    {
        $this->m_seed = $seed;
    }

    /**
     * Set the seed for generated codes.
     *
     * The provided seed must be base32 encoded.
     *
     * @param string $seed
     *
     * @throws InvalidBase32DataException if the provided seed is not a valid base32 encoding.
     */
    public function setBase32Seed(string $seed)
    {
        $this->setSeed(Base32::decode($seed));
    }

    /**
     * Set the seed for generated codes.
     *
     * The provided seed must be base64 encoded.
     *
     * @param string $seed The binary seed, base64-encoded.
     *
     * @throws InvalidBase64DataException if the provided seed is not a valid base64 encoding.
     */
    public function setBase64Seed(string $seed)
    {
        $rawSeed = base64_decode($seed);

        if (false === $rawSeed) {
            throw new InvalidBase64DataException($seed);
        }

        $this->setSeed($rawSeed);
    }

    /**
     * Fetch the interval at which the TOTP code changes, in seconds.
     *
     * @return int The interval.
     */
    public function interval(): int
    {
        return $this->m_interval;
    }

    /**
     * @param int $interval
     *
     * @throws InvalidTotpIntervalException
     */
    public function setInterval(int $interval)
    {
        if (1 > $interval) {
            throw new InvalidTotpIntervalException($interval, "The interval for a TOTP must be >= 1 second.");
        }

        $this->m_interval = $interval;
    }

    /**
     * Fetch the baseline against which the TOTP codes will be generated.
     *
     * The baseline is returned as the number of seconds since the Unix epoch.
     *
     * @return int The baseline number of seconds.
     */
    public function baseline(): int
    {
        return $this->m_baselineTime;
    }

    /**
     * The baseline against which codes are generated as a DateTime object.
     *
     * @return \DateTime The baseline time.
     */
    public function baselineDateTime(): DateTime
    {
        return DateTime::createFromFormat("U", "{$this->m_baselineTime}", new DateTimeZone("UTC"));
    }

    /**
     * Set the baseline time against which OTP codes are generated.
     *
     * The baseline can be set either as an integer number of seconds since the Unix epoch or as a PHP DateTime object.
     * If using a DateTime object, make sure you know what time it represents in UTC since it is the number of seconds
     * since 1970-01-01 00:00:00 UTC that will be used as the baseline. (In effect, the DateTime you provide is
     * converted to UTC before the number of seconds is calculated.)
     *
     * @param int|\DateTime $baseline The
     *
     * @return void
     */
    public function setBaseline(int | DateTime $baseline)
    {
        if ($baseline instanceof DateTime) {
            $baseline = $baseline->getTimestamp();
        }

        $this->m_baselineTime = $baseline;
    }

    /**
     * Fetch the current counter for the code.
     *
     * @return string The 64 bits of the counter, in BIG ENDIAN format.
     * @noinspection PhpDocMissingThrowsInspection DateTime() constructor will not throw.
     */
    protected function counter(): string
    {
        /** @noinspection PhpUnhandledExceptionInspection DateTime constructor guaranteed not to throw here */
        return pack("J", (int) floor(((new DateTime("now", new DateTimeZone("UTC")))->getTimestamp() - $this->baseline()) / $this->interval()));
    }

    /**
     * Fetch the current TOTP code in raw format.
     *
     * This is the raw byte sequence generated using the seed, baseline and interval.
     *
     * @return string The current TOTP code.
     */
    public function currentRawCode(): string
    {
        return self::hmac($this->seed(), $this->counter());
    }

    /**
     * Fetch the current TOTP code in a user-presentable format.
     *
     * Subclasses should reimplement this method to produce readable representations of the current raw code.
     * Commonly this is 6- or 8- decimal digits produced according to a defined algorithm that works with the raw
     * code.
     *
     * @return string The current TOTP code, formatted for display.
     */
    public abstract function currentCode(): string;

    /**
     * Helper to generate the TOTP HMAC for a given key and message.
     *
     * The TOTP algorithm uses SHA1 as the hashing algorithm for HMACs.
     *
     * @param string $key The key.
     * @param string $message The message.
     *
     * @return string The HMAC for the key and message.
     */
    protected static function hmac(string $key, string $message): string
    {
        $blockSize = 64;

        if (strlen($key) > $blockSize) {
            $key = hash(self::HashAlgorithm, $key, true);
        } else if (strlen($key) < $blockSize) {
            $key = str_pad($key, $blockSize, "\x00");
        }

        $oKeyPad = str_repeat("\x5c", $blockSize);
        $iKeyPad = str_repeat("\x36", $blockSize);

        for ($i = 0; $i < $blockSize; ++$i) {
            $oKeyPad[$i] = chr(ord($oKeyPad[$i]) ^ ord($key[$i]));
            $iKeyPad[$i] = chr(ord($iKeyPad[$i]) ^ ord($key[$i]));
        }

        return hash(self::HashAlgorithm, $oKeyPad . hash(self::HashAlgorithm, $iKeyPad . $message, true), true);
    }
}
