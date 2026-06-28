<x-layouts.admin
    title="{{ __('pos.stock_title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('pos.stock_title') }}"
    subheading="Track inventory levels, restock items, and log adjustments with a full audit trail."
>
    <div class="mb-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="app-panel rounded-2xl border p-4"><p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.stats.products') }}</p><p class="mt-2 text-2xl font-semibold">{{ number_format($summary['products']) }}</p></div>
        <div class="app-panel rounded-2xl border p-4"><p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.stats.low_stock') }}</p><p class="mt-2 text-2xl font-semibold text-[#F97316]">{{ number_format($summary['low_stock']) }}</p></div>
        <div class="app-panel rounded-2xl border p-4"><p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.stats.stock_value') }}</p><p class="mt-2 text-2xl font-semibold">₹{{ number_format($summary['stock_value_paise'] / 100, 2) }}</p></div>
        <div class="app-panel rounded-2xl border p-4"><p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.stats.out_of_stock') }}</p><p class="mt-2 text-2xl font-semibold text-[#E24B4A]">{{ number_format($summary['out_of_stock']) }}</p></div>
    </div>

    @if ($canManageStock)
        <div class="mb-5 grid gap-5 xl:grid-cols-2">
            <section class="app-panel rounded-[2rem] border p-5">
                <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.actions.restock') }}</p>
                <form method="POST" action="{{ route('tenant.pos.stock.restock') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="st-label">{{ __('pos.stock.product') }}</label>
                        <select name="product_id" class="st-input" required>
                            @foreach ($productOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.stock.quantity_added') }}</label>
                        <input type="number" min="1" name="quantity" class="st-input" required>
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.stock.cost_price') }}</label>
                        <input type="number" step="0.01" min="0" name="cost_price" class="st-input" required>
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.stock.supplier') }}</label>
                        <input type="text" name="supplier" class="st-input">
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.stock.reference') }}</label>
                        <input type="text" name="reference" class="st-input">
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.stock.date') }}</label>
                        <input type="date" name="date" value="{{ now()->toDateString() }}" class="st-input" required>
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.sales.branch') }}</label>
                        <select name="branch_id" class="st-input">
                            <option value="">{{ __('pos.filters.all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="st-label">{{ __('pos.stock.notes') }}</label>
                        <textarea name="notes" rows="3" class="st-input"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="st-btn-primary">{{ __('pos.actions.restock') }}</button>
                    </div>
                </form>
            </section>

            <section class="app-panel rounded-[2rem] border p-5">
                <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.actions.adjust') }}</p>
                <form method="POST" action="{{ route('tenant.pos.stock.adjust') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="st-label">{{ __('pos.stock.product') }}</label>
                        <select name="product_id" class="st-input" required>
                            @foreach ($productOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.stock.quantity_change') }}</label>
                        <input type="number" name="quantity_change" class="st-input" required>
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.stock.reason') }}</label>
                        <select name="reason" class="st-input" required>
                            @foreach ($adjustmentReasons as $reason)
                                <option value="{{ $reason }}">{{ __('pos.adjustment_reasons.'.$reason) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.stock.date') }}</label>
                        <input type="date" name="date" value="{{ now()->toDateString() }}" class="st-input" required>
                    </div>
                    <div>
                        <label class="st-label">{{ __('pos.sales.branch') }}</label>
                        <select name="branch_id" class="st-input">
                            <option value="">{{ __('pos.filters.all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="st-label">{{ __('pos.stock.notes') }}</label>
                        <textarea name="notes" rows="3" class="st-input"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="st-btn-primary">{{ __('pos.actions.adjust') }}</button>
                    </div>
                </form>
            </section>
        </div>
    @endif

    <div class="app-panel rounded-[2rem] border p-4">
        <form method="GET" action="{{ route('tenant.pos.stock') }}" class="flex flex-wrap items-center gap-2">
            <div class="st-search-wrap flex-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('pos.filters.search_stock') }}" class="st-search-input">
            </div>
            <select name="status" class="st-input max-w-[11rem]">
                <option value="">{{ __('pos.filters.all_statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('pos.statuses.active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('pos.statuses.inactive') }}</option>
            </select>
            <label class="st-check">
                <input type="checkbox" name="low_stock_only" value="1" @checked(request()->boolean('low_stock_only'))>
                <span>{{ __('pos.filters.low_stock_only') }}</span>
            </label>
            <button type="submit" class="st-btn-ghost">{{ __('common.save') }}</button>
            @if (request()->hasAny(['search', 'status', 'low_stock_only']))
                <a href="{{ route('tenant.pos.stock') }}" class="st-btn-ghost">{{ __('pos.filters.clear') }}</a>
            @endif
        </form>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="app-panel overflow-hidden rounded-[2rem] border">
            @if ($products->isEmpty())
                <div class="flex min-h-[22rem] flex-col items-center justify-center gap-4 px-6 py-20 text-center">
                    <div class="st-empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M8 7h13"/><path d="M8 12h13"/><path d="M8 17h13"/><path d="M3 7h.01"/><path d="M3 12h.01"/><path d="M3 17h.01"/></svg>
                    </div>
                    <p class="text-base font-semibold">{{ __('pos.stock.empty') }}</p>
                    <p class="app-muted text-sm">{{ __('pos.stock.empty_help') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="app-table-head">
                            <tr>
                                @foreach ([__('pos.stock.product'), __('pos.product.category'), __('pos.stock.current_stock'), __('pos.stock.cost_price'), __('pos.stock.stock_value'), __('pos.stock.low_stock'), __('pos.stock.last_restock'), __('common.actions')] as $head)
                                    <th class="px-4 py-3 text-left font-medium">{{ $head }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                @php
                                    $lastRestock = $product->stockMovements()->where('type', 'restock')->latest('movement_date')->first();
                                @endphp
                                <tr class="border-t border-[var(--app-border)]">
                                    <td class="px-4 py-4">
                                        <div>
                                            <p class="font-semibold">{{ $product->name }}</p>
                                            <p class="app-muted text-xs">{{ $product->sku ?: 'No SKU' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">{{ __('pos.categories.'.$product->category) }}</td>
                                    <td class="px-4 py-4 font-semibold {{ $product->is_low_stock ? 'text-[#F97316]' : '' }}">{{ $product->stock_quantity }}</td>
                                    <td class="px-4 py-4">₹{{ $product->cost_rupees }}</td>
                                    <td class="px-4 py-4">₹{{ number_format($product->stock_value_paisa / 100, 2) }}</td>
                                    <td class="px-4 py-4">
                                        @if ($product->is_low_stock)
                                            <span class="st-low-pill">Alert</span>
                                        @else
                                            <span class="app-muted">No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">{{ $lastRestock?->movement_date?->format('d M Y') ?? '—' }}</td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('tenant.pos.stock', ['product_id' => $product->id]) }}" class="st-row-btn">{{ __('pos.stock.history') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-[var(--app-border)] px-4 py-3">{{ $products->links() }}</div>
            @endif
        </section>

        <section class="app-panel rounded-[2rem] border p-5">
            <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.stock.history') }}</p>
            @if ($selectedProduct)
                <h2 class="mt-1 text-lg font-semibold">{{ $selectedProduct->name }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($selectedProduct->stockMovements as $movement)
                        <div class="rounded-[1.2rem] border border-[var(--app-border)] bg-[var(--app-panel-strong)] px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold">{{ ucfirst($movement->type) }}</p>
                                    <p class="app-muted mt-1 text-xs">{{ $movement->movement_date?->format('d M Y') }} · {{ $movement->branch?->name ?? 'All branches' }}</p>
                                </div>
                                <span class="text-sm font-semibold {{ $movement->quantity < 0 ? 'text-[#E24B4A]' : 'text-[#1D9E75]' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</span>
                            </div>
                            @if ($movement->reason || $movement->reference)
                                <p class="app-muted mt-2 text-sm">{{ $movement->reason ?: $movement->reference }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="app-muted mt-4 text-sm">No movement history recorded yet.</p>
                    @endforelse
                </div>
            @else
                <p class="app-muted mt-4 text-sm">Pick a product from the table to inspect its recent stock movements.</p>
            @endif
        </section>
    </div>

    @push('styles')
        <style>
            .st-label { color: var(--app-text); display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.45rem; }
            .st-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; color: var(--app-text); font-size: 0.84rem; min-height: 2.9rem; outline: none; padding: 0.75rem 0.9rem; width: 100%; }
            .st-btn-primary, .st-btn-ghost, .st-row-btn { align-items: center; border-radius: 0.9rem; display: inline-flex; font-size: 0.82rem; font-weight: 600; min-height: 2.9rem; padding: 0 1rem; text-decoration: none; }
            .st-btn-primary { background: var(--app-brand); color: #0f172a; }
            .st-btn-ghost, .st-row-btn { border: 1px solid var(--app-border); color: var(--app-text-muted); }
            .st-search-wrap { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; display: flex; gap: 0.55rem; min-width: 15rem; padding: 0 0.9rem; }
            .st-search-input { background: transparent; border: none; color: var(--app-text); font-size: 0.9rem; height: 3rem; outline: none; width: 100%; }
            .st-check { align-items: center; display: inline-flex; gap: 0.55rem; padding: 0 0.3rem; }
            .st-check input { accent-color: var(--app-brand); }
            .st-low-pill { background: rgba(249, 115, 22, 0.14); border-radius: 999px; color: #F97316; display: inline-flex; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.55rem; }
            .st-empty-icon { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); display: inline-flex; height: 4.5rem; justify-content: center; width: 4.5rem; }
            .st-empty-icon svg { height: 2rem; width: 2rem; }
        </style>
    @endpush
</x-layouts.admin>
