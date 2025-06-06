<?php

namespace App;

enum BillingFrequencyEnum: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case WEEKLY = 'weekly';

    public function intervalDays(): int
    {
        return match ($this) {
            self::MONTHLY => 30,
            self::YEARLY => 365,
            self::WEEKLY => 7,
        };
    }
}
