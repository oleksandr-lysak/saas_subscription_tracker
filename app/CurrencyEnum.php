<?php

namespace App;

enum CurrencyEnum: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case UAH = 'UAH';

    public function rateToUsd(): float
    {
        return match ($this) {
            self::USD => 1.0,
            self::EUR => 1.08, // 1 EUR = 1.08 USD
            self::UAH => 0.027, // 1 UAH = 0.027 USD
        };
    }
}
