<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SubscriptionService;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService)
    {
    }

    public function index()
    {
        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $this->subscriptionService->paginate(),
        ]);
    }
}

