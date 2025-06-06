<?php

namespace Tests\Unit;

use App\Services\SubscriptionService;
use App\CurrencyEnum;
use App\BillingFrequencyEnum;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class SubscriptionServiceTest extends TestCase
{
    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    private function makeSubscription(array $attrs = [])
    {
        return (object) array_merge([
            'cost' => 100,
            'currency' => CurrencyEnum::USD,
            'billing_frequency' => BillingFrequencyEnum::MONTHLY,
            'start_date' => Carbon::now()->subDays(10),
        ], $attrs);
    }

    public function test_projected_expense_monthly()
    {
        $sub = $this->makeSubscription([
            'cost' => 100,
            'currency' => CurrencyEnum::USD,
            'billing_frequency' => BillingFrequencyEnum::MONTHLY,
            'start_date' => Carbon::now()->subDays(10),
        ]);
        $expense = $this->service->projectedExpense($sub, 30, 'USD');
        $this->assertEquals(100, $expense);
    }

    public function test_projected_expense_yearly()
    {
        $sub = $this->makeSubscription([
            'cost' => 1200,
            'currency' => CurrencyEnum::USD,
            'billing_frequency' => BillingFrequencyEnum::YEARLY,
            'start_date' => Carbon::now()->subDays(10),
        ]);
        $expense = $this->service->projectedExpense($sub, 365, 'USD');
        $this->assertEquals(1200, $expense);
    }


    public function test_projected_expense_currency_conversion()
    {
        $sub = $this->makeSubscription([
            'cost' => 100,
            'currency' => CurrencyEnum::EUR,
            'billing_frequency' => BillingFrequencyEnum::MONTHLY,
            'start_date' => Carbon::now()->subDays(10),
        ]);
        $expense = $this->service->projectedExpense($sub, 30, 'USD');
        $this->assertEquals(100 * 1.08, $expense);
    }


    public function test_get_totals()
    {
        $subs = new Collection([
            $this->makeSubscription(['cost' => 100, 'currency' => CurrencyEnum::USD]),
            $this->makeSubscription(['cost' => 200, 'currency' => CurrencyEnum::EUR]),
        ]);
        $totals = $this->service->getTotals($subs, 'USD');
        $expected = 100 + 200 * 1.08;
        $this->assertEquals($expected, $totals['total30']);
    }
} 