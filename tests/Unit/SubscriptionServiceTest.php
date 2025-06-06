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
        $now = Carbon::create(2024, 6, 1, 0, 0, 0);
        Carbon::setTestNow($now);
        $sub = $this->makeSubscription([
            'cost' => 100,
            'currency' => CurrencyEnum::USD,
            'billing_frequency' => BillingFrequencyEnum::MONTHLY,
            'start_date' => $now->copy()->subDays(10),
        ]);
        $windowEnd = $now->copy()->addDays(30);
        $periodStart = $sub->start_date->gt($now) ? $sub->start_date->copy() : $now->copy();
        $periodEnd = $windowEnd;
        $activeDays = $periodStart->diffInDays($periodEnd) + 1;
        $interval = 30;
        $expected = 100 * ($activeDays / $interval);
        $expense = $this->service->projectedExpense($sub, 30, 'USD');
        $this->assertEquals($expected, $expense, '', 0.01);
        Carbon::setTestNow();
    }

    public function test_projected_expense_yearly()
    {
        $now = Carbon::create(2024, 6, 1, 0, 0, 0);
        Carbon::setTestNow($now);
        $sub = $this->makeSubscription([
            'cost' => 1200,
            'currency' => CurrencyEnum::USD,
            'billing_frequency' => BillingFrequencyEnum::YEARLY,
            'start_date' => $now->copy()->subDays(10),
        ]);
        $windowEnd = $now->copy()->addDays(365);
        $periodStart = $sub->start_date->gt($now) ? $sub->start_date->copy() : $now->copy();
        $periodEnd = $windowEnd;
        $activeDays = $periodStart->diffInDays($periodEnd) + 1;
        $interval = 365;
        $expected = 1200 * ($activeDays / $interval);
        $expense = $this->service->projectedExpense($sub, 365, 'USD');
        $this->assertEquals($expected, $expense, '', 0.01);
        Carbon::setTestNow();
    }

    public function test_projected_expense_future_start()
    {
        $now = Carbon::create(2024, 6, 1, 0, 0, 0);
        Carbon::setTestNow($now);
        $sub = $this->makeSubscription([
            'cost' => 100,
            'currency' => CurrencyEnum::USD,
            'billing_frequency' => BillingFrequencyEnum::MONTHLY,
            'start_date' => $now->copy()->addDays(10),
        ]);
        $windowEnd = $now->copy()->addDays(30);
        $periodStart = $sub->start_date->gt($now) ? $sub->start_date->copy() : $now->copy();
        $periodEnd = $windowEnd;
        $activeDays = $periodStart->diffInDays($periodEnd) + 1;
        $interval = 30;
        $expected = 100 * ($activeDays / $interval);
        $expense = $this->service->projectedExpense($sub, 30, 'USD');
        $this->assertEquals($expected, $expense, '', 0.01);
        Carbon::setTestNow();
    }

    public function test_projected_expense_currency_conversion()
    {
        $now = Carbon::create(2024, 6, 1, 0, 0, 0);
        Carbon::setTestNow($now);
        $sub = $this->makeSubscription([
            'cost' => 100,
            'currency' => CurrencyEnum::EUR,
            'billing_frequency' => BillingFrequencyEnum::MONTHLY,
            'start_date' => $now->copy()->subDays(10),
        ]);
        $windowEnd = $now->copy()->addDays(30);
        $periodStart = $sub->start_date->gt($now) ? $sub->start_date->copy() : $now->copy();
        $periodEnd = $windowEnd;
        $activeDays = $periodStart->diffInDays($periodEnd) + 1;
        $interval = 30;
        $expected = 100 * 1.08 * ($activeDays / $interval);
        $expense = $this->service->projectedExpense($sub, 30, 'USD');
        $this->assertEquals($expected, $expense, '', 0.01);
        Carbon::setTestNow();
    }

    public function test_get_totals()
    {
        $now = Carbon::create(2024, 6, 1, 0, 0, 0);
        Carbon::setTestNow($now);
        $subs = new Collection([
            $this->makeSubscription(['cost' => 100, 'currency' => CurrencyEnum::USD, 'start_date' => $now->copy()->subDays(10)]),
            $this->makeSubscription(['cost' => 200, 'currency' => CurrencyEnum::EUR, 'start_date' => $now->copy()->subDays(10)]),
        ]);
        $windowEnd = $now->copy()->addDays(30);
        $periodStart = $now->copy();
        $periodEnd = $windowEnd;
        $activeDays = $periodStart->diffInDays($periodEnd) + 1;
        $interval = 30;
        $expected = 100 * ($activeDays / $interval) + 200 * 1.08 * ($activeDays / $interval);
        $totals = $this->service->getTotals($subs, 'USD');
        $this->assertEquals($expected, $totals['total30'], '', 0.01);
        Carbon::setTestNow();
    }
} 