<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cost' => $this->cost,
            'billing_frequency' => $this->billing_frequency,
            'currency' => is_object($this->currency) ? $this->currency->value : $this->currency,
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'formatted_start_date' => $this->start_date ? $this->start_date->format('d.m.Y') : null,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
