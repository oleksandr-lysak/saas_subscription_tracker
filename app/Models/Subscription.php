<?php

namespace App\Models;

use App\BillingFrequencyEnum;
use App\CurrencyEnum;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'name',
        'cost',
        'billing_frequency',
        'currency',
        'start_date',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'currency' => CurrencyEnum::class,
        'billing_frequency' => BillingFrequencyEnum::class,
    ];
}
