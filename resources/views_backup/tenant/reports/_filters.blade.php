{{--
    Shared filter bar for all report pages.
    Props:
      $range    — resolveRange() result
      $branches — Branch collection
      $branchId — selected branch id or null
      $plans    — optional Plan collection (pass null to hide plan filter)
      $planId   — selected plan id or null
      $exportRoute — route name for CSV export (e.g. 'tenant.reports.revenue.export')
--}}
<form method="GET" action="" class="mb-6">
    <div class="rounded-2xl p-4 flex flex-wrap gap-3 items-end" style="background:var(--app-panel);border:1px solid var(--app-border)">

        {{-- Date preset --}}
        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('reports.filters.period') }}</label>
            <select name="range" onchange="rptToggleCustom(this.value)"
                    class="rounded-xl border px-3 py-2 text-sm outline-none"
                    style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                @foreach (__('reports.filters.presets') as $key => $label)
                    <option value="{{ $key }}" @selected($range['preset'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Custom date range --}}
        <div id="rptCustomRange" class="{{ $range['preset'] === 'custom' ? 'flex' : 'hidden' }} gap-2 items-end">
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('common.from') }}</label>
                <input type="date" name="from" value="{{ $range['preset'] === 'custom' ? $range['from']->toDateString() : '' }}"
                       class="rounded-xl border px-3 py-2 text-sm outline-none"
                       style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('common.to') }}</label>
                <input type="date" name="to" value="{{ $range['preset'] === 'custom' ? $range['to']->toDateString() : '' }}"
                       class="rounded-xl border px-3 py-2 text-sm outline-none"
                       style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            </div>
        </div>

        {{-- Branch filter (multi-branch only) --}}
        @if ($branches->count() > 1)
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('common.branch') }}</label>
                <select name="branch_id"
                        class="rounded-xl border px-3 py-2 text-sm outline-none"
                        style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Plan filter (if plans passed) --}}
        @isset($plans)
            @if ($plans->count() > 0)
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('reports.filters.plan') }}</label>
                    <select name="plan_id"
                            class="rounded-xl border px-3 py-2 text-sm outline-none"
                            style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach ($plans as $p)
                            <option value="{{ $p->id }}" @selected(($planId ?? null) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        @endisset

        <button type="submit"
                class="rounded-xl px-4 py-2 text-sm font-medium text-white"
                style="background:var(--app-brand)">
            {{ __('common.filter') }}
        </button>

        @isset($exportRoute)
            <a href="{{ $exportRoute }}&{{ http_build_query(request()->only(['range','from','to','branch_id','plan_id'])) }}"
               class="rounded-xl border px-4 py-2 text-sm font-medium"
               style="border-color:var(--app-border);color:var(--app-text-muted)">
                ↓ CSV
            </a>
        @endisset

        {{-- Active period display --}}
        <span class="ml-auto text-xs self-center" style="color:var(--app-text-muted)">
            {{ $range['from']->format('d M Y') }} – {{ $range['to']->format('d M Y') }}
        </span>
    </div>
</form>

@push('scripts')
<script>
function rptToggleCustom(val) {
    document.getElementById('rptCustomRange')?.classList.toggle('hidden', val !== 'custom');
    document.getElementById('rptCustomRange')?.classList.toggle('flex', val === 'custom');
}
</script>
@endpush
