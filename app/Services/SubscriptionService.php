<?php

namespace App\Services;

use App\CurrencyEnum;
use App\BillingFrequencyEnum;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Support\Collection;

class SubscriptionService
{
    public function filterAndGet(array $filters = []): Collection
    {
        $query = Subscription::query();

        if (! empty($filters['billing_frequency'])) {
            $query->where('billing_frequency', $filters['billing_frequency']);
        }

        $min = $filters['min_price'] ?? null;
        $max = $filters['max_price'] ?? null;
        if (! empty($filters['currency'])) {
            $query->where('currency', $filters['currency']);
            if ($min !== null) {
                $query->where('cost', '>=', $min);
            }
            if ($max !== null) {
                $query->where('cost', '<=', $max);
            }
        } else {
            if ($min !== null || $max !== null) {
                $query->where(function ($q) use ($min, $max) {
                    foreach (CurrencyEnum::cases() as $currency) {
                        $rate = $currency->rateToUsd();
                        $q->orWhere(function ($subQ) use ($currency, $rate, $min, $max) {
                            $subQ->where('currency', $currency->value);
                            if ($min !== null) {
                                $subQ->whereRaw("cost * $rate >= ?", [$min]);
                            }
                            if ($max !== null) {
                                $subQ->whereRaw("cost * $rate <= ?", [$max]);
                            }
                        });
                    }
                });
            }
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('start_date', '<=', $filters['month']);
        }

        return $query->get();
    }

    public function getRateBetween(string $from, string $to): float
    {
        $fromEnum = CurrencyEnum::tryFrom($from);
        $toEnum = CurrencyEnum::tryFrom($to);
        if (! $fromEnum || ! $toEnum) {
            return 1.0;
        }

        return $fromEnum->rateToUsd() / $toEnum->rateToUsd();
    }

    public function getTop(Collection $subscriptions, int $count = 5, string $baseCurrency = 'USD'): Collection
    {
        return $subscriptions->sortByDesc(function ($s) use ($baseCurrency) {
            $from = is_object($s->currency) ? $s->currency->value : $s->currency;
            $rate = $this->getRateBetween($from, $baseCurrency);

            return $s->cost * $rate;
        })->take($count)->values();
    }

    public function getGrouped(Collection $subscriptions, string $baseCurrency = 'USD'): Collection
    {
        return $subscriptions->groupBy('billing_frequency')->map(function ($group) use ($baseCurrency) {
            $total = $group->sum(fn ($s) => $s->cost * $this->getRateBetween(is_object($s->currency) ? $s->currency->value : $s->currency, $baseCurrency));
            $avg = $group->avg(fn ($s) => $s->cost * $this->getRateBetween(is_object($s->currency) ? $s->currency->value : $s->currency, $baseCurrency));

            return [
                'count' => $group->count(),
                'total' => $total,
                'avg' => $avg,
            ];
        });
    }

    public function getCurrencySummary(Collection $subscriptions): Collection
    {
        return $subscriptions->groupBy('currency')->map(function ($group, $currency) {
            return [
                'avg' => $group->avg('cost'),
                'currency' => $currency,
            ];
        });
    }

    public function getTotals(Collection $subscriptions, string $baseCurrency = 'USD'): array
    {
        $total30 = $subscriptions->sum(fn ($s) => $this->projectedExpense($s, 30, $baseCurrency));
        $total365 = $subscriptions->sum(fn ($s) => $this->projectedExpense($s, 365, $baseCurrency));

        return [
            'total30' => $total30,
            'total365' => $total365,
        ];
    }

    public function projectedExpense($subscription, $days, string $baseCurrency = 'USD'): float
    {
        $from = is_object($subscription->currency) ? $subscription->currency->value : $subscription->currency;
        $rate = $this->getRateBetween($from, $baseCurrency);
        $cost = $subscription->cost * $rate;
        $start = $subscription->start_date;
        $now = now();
        $windowEnd = $now->copy()->addDays($days);
        if ($start->gt($windowEnd)) {
            return 0;
        }
        $periodStart = $start->gt($now) ? $start->copy() : $now->copy();
        $periodEnd = $windowEnd;
        $freq = is_object($subscription->billing_frequency)
            ? $subscription->billing_frequency
            : BillingFrequencyEnum::tryFrom($subscription->billing_frequency);
        $interval = $freq ? $freq->intervalDays() : 30;
        $activeDays = $periodEnd->diffInDays($periodStart) + 1;
        $periods = $activeDays / $interval;
        return $cost * $periods;
    }

    public function getIndexData(array $filters): array
    {
        $baseCurrency = $filters['base_currency'] ?? 'USD';
        $subscriptions = $this->filterAndGet($filters);
        $top = $this->getTop($subscriptions, 5, $baseCurrency);
        $grouped = $this->getGrouped($subscriptions, $baseCurrency);
        $currencySummary = $this->getCurrencySummary($subscriptions);
        $totals = $this->getTotals($subscriptions, $baseCurrency);
        $currencies = array_map(fn ($c) => ['value' => $c->value, 'label' => $c->value], CurrencyEnum::cases());
        $frequencies = array_map(fn ($f) => ['value' => $f->value, 'label' => ucfirst($f->value)], BillingFrequencyEnum::cases());

        return [
            'subscriptions' => SubscriptionResource::collection($subscriptions),
            'filters' => $filters,
            'top' => SubscriptionResource::collection($top),
            'grouped' => $grouped,
            'currencySummary' => $currencySummary,
            'total30' => $totals['total30'],
            'total365' => $totals['total365'],
            'currencies' => $currencies,
            'frequencies' => $frequencies,
        ];
    }

    public function getCreateData(): array
    {
        $currencies = array_map(fn ($c) => ['value' => $c->value, 'label' => $c->value], CurrencyEnum::cases());
        $frequencies = array_map(fn ($f) => ['value' => $f->value, 'label' => ucfirst($f->value)], BillingFrequencyEnum::cases());

        return [
            'currencies' => $currencies,
            'frequencies' => $frequencies,
        ];
    }

    public function getEditData(Subscription $subscription): array
    {
        $currencies = array_map(fn ($c) => ['value' => $c->value, 'label' => $c->value], CurrencyEnum::cases());
        $frequencies = array_map(fn ($f) => ['value' => $f->value, 'label' => ucfirst($f->value)], BillingFrequencyEnum::cases());

        return [
            'subscription' => new SubscriptionResource($subscription),
            'currencies' => $currencies,
            'frequencies' => $frequencies,
        ];
    }
}
