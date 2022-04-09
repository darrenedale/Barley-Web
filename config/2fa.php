<?php

use App\TwoFactorAuthenticators\TotpAuthenticatorSeedType;

return [
    "authenticator" => "totp",

    "authenticators" => [
        "totp" => [
            /*
             |--------------------------------------------------------------------------
             | How many digits the TOTP password should contain.
             |--------------------------------------------------------------------------
             |
             | TOTP passwords are commonly expected as 6- or 8-digit numbers. Any number
             | of digits is feasible, up to the maximum number of digits in the decimal
             | rendering of a 32-bit unsigned integer.
             |
             | The default is 6 since this is the most prevalent in the wild.
             */
            "digits" => 6,

            /*
             |--------------------------------------------------------------------------
             | How each user's seed is stored.
             |--------------------------------------------------------------------------
             |
             | TOTP seeds (AKA shared secrets) are binary byte sequences. These are
             | often stored encoded as Base32, hence this is the default. You can use
             | this item to specify how you store the TOTP secrets for your users.
             */
            "seed-type" => TotpAuthenticatorSeedType::Base32,

            /*
             |--------------------------------------------------------------------------
             | Where each user's seed is stored.
             |--------------------------------------------------------------------------
             |
             | TOTP seeds should be stored in the database with your user records
             | (preferably encrypted or hashed). This item tells the authenticator which
             | field to read for TOTP seeds.
             */
            "seed-field" => "totp_seed",

            /*
             |--------------------------------------------------------------------------
             | The TOTP update interval.
             |--------------------------------------------------------------------------
             |
             | TOTP passwords change on an interval. Most commonly this interval is 30
             | seconds. If you need a different interval, you can specify it here. It
             | must be a positive integer, and is always measured in seconds.
             */
            "interval" => 30,

            /*
             |--------------------------------------------------------------------------
             | The TOTP base time.
             |--------------------------------------------------------------------------
             |
             | The points in time at which the TOTP password changes are all the
             | multiples of the interval from a fixed point in time. This item specifies
             | that fixed point in time. It is measured as the number of seconds since
             | the Unix epoch (1970-01-01 00:00:00) in UTC.
             |
             | The default, which is the most commonly used base time, is 0 - the exact
             | second of the Unix epoch.
             */
            "base-time" => 0,
        ],
    ],
];
