<x-layouts.admin
    title="Plans"
    eyebrow="{{ __('plans.eyebrow') }}"
    heading="{{ __('plans.heading') }}"
    subheading="{{ __('plans.subheading') }}"
>
    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <form method="POST" action="{{ route('admin.plans.store') }}" class="space-y-4 rounded-[2rem] border border-white/10 bg-white/5 p-6">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-200">{{ __('plans.fields.plan_name') }}</label>
                <input name="name" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">{{ __('plans.fields.billing_cycle') }}</label>
                    <select name="billing_cycle" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                        @foreach (['Monthly', 'Quarterly', 'Annual'] as $cycle)
                            <option value="{{ $cycle }}">{{ $cycle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">{{ __('plans.fields.price') }}</label>
                    <input type="number" step="0.01" name="price_inr" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">{{ __('plans.fields.max_members') }}</label>
                    <input type="number" name="max_members" value="0" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">{{ __('plans.fields.max_branches') }}</label>
                    <input type="number" name="max_branches" value="0" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">{{ __('plans.fields.max_staff') }}</label>
                    <input type="number" name="max_staff_accounts" value="0" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">{{ __('plans.fields.status') }}</label>
                    <select name="status" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-200">{{ __('plans.fields.description') }}</label>
                <textarea name="description" rows="3" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none"></textarea>
            </div>
            <div>
                <p class="mb-3 text-sm font-medium text-slate-200">{{ __('plans.fields.feature_flags') }}</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach (['pos' => 'Inventory / POS', 'analytics' => 'Advanced analytics', 'white_label' => 'White-label', 'api_access' => 'API access', 'biometric' => 'Biometric integration', 'whatsapp' => 'WhatsApp integration', 'gst_mode' => 'GST compliance mode'] as $value => $label)
                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300">
                            <input type="checkbox" name="features[]" value="{{ $value }}" class="h-4 w-4 rounded border-white/10 bg-slate-950/70 text-orange-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
            <label class="flex items-center gap-3 text-sm text-slate-300">
                <input type="checkbox" name="trial_eligible" value="1" class="h-4 w-4 rounded border-white/10 bg-slate-950/70 text-orange-500">
                {{ __('plans.fields.trial_eligible') }}
            </label>
            <button type="submit" class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                {{ __('plans.buttons.save') }}
            </button>
        </form>

        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <div class="overflow-hidden rounded-[1.5rem] border border-white/10">
                <table class="w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-slate-950/60 text-slate-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Plan</th>
                            <th class="px-4 py-3 font-medium">Cycle</th>
                            <th class="px-4 py-3 font-medium">Price</th>
                            <th class="px-4 py-3 font-medium">Limits</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-white/5">
                        @foreach ($plans as $plan)
                            <tr>
                                <td class="px-4 py-4">
                                    <p class="font-semibold">{{ $plan->name }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $plan->description }}</p>
                                </td>
                                <td class="px-4 py-4">{{ $plan->billing_cycle }}</td>
                                <td class="px-4 py-4">Rs. {{ number_format($plan->price_paise / 100, 2) }}</td>
                                <td class="px-4 py-4 text-xs text-slate-300">
                                    Members: {{ $plan->max_members ?: 'Unlimited' }}<br>
                                    Branches: {{ $plan->max_branches ?: 'Unlimited' }}<br>
                                    Staff: {{ $plan->max_staff_accounts ?: 'Unlimited' }}
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full {{ $plan->status === 'active' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-500/15 text-slate-300' }} px-3 py-1 text-xs uppercase tracking-[0.2em]">
                                        {{ $plan->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
