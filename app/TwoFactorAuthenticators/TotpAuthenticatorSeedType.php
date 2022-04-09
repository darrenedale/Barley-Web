<?php

namespace App\TwoFactorAuthenticators;

enum TotpAuthenticatorSeedType
{
    case Raw;
    case Base32;
    case Base64;
}
