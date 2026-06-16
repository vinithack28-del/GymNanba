<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SubscriptionService;
use Illuminate\Contracts\View\View;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService)
    {
    }

    public function index(): View
    {
        return view('admin.subscriptions.index', [
            'subscriptions' => $this->subscriptionService->paginate(),
        ]);
    }
}
