<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionFilterRequest;
use App\Http\Requests\SubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    private SubscriptionService $service;

    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SubscriptionFilterRequest $request): Response
    {
        $data = $this->service->getIndexData($request->validated());

        return Inertia::render('Subscription/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $data = $this->service->getCreateData();

        return Inertia::render('Subscription/Create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubscriptionRequest $request): RedirectResponse
    {
        Subscription::create($request->validated());

        return to_route('subscriptions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription): Response
    {
        return Inertia::render('Subscription/Show', [
            'subscription' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription): Response
    {
        $data = $this->service->getEditData($subscription);

        return Inertia::render('Subscription/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $subscription->update($request->validated());

        return to_route('subscriptions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return to_route('subscriptions.index');
    }
}
