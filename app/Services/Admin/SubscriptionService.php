<?php

namespace App\Services\Admin;

use App\Models\Subscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionService
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Subscription::query()
            ->with(['tenant', 'plan', 'creator'])
            ->latest()
            ->paginate($perPage);
    }
}

