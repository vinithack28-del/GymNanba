<x-layouts.admin
    title="{{ __('pos.products_title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('pos.products_title') }}"
    subheading="Manage merchandise, supplements, and consumables available at the counter."
>
    <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['label' => __('pos.stats.products'), 'value' => $stats['total'], 'color' => 'var(--app-text)'],
            ['label' => __('pos.stats.active'), 'value' => $stats['active'], 'color' => '#1D9E75'],
            ['label' => __('pos.stats.inactive'), 'value' => $stats['inactive'], 'color' => '#888780'],
            ['label' => __('pos.stats.low_stock'), 'value' => $stats['low_stock'], 'color' => '#F97316'],
        ] as $card)
            <div class="app-panel rounded-2xl border p-4">
                <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ $card['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold" style="color: {{ $card['color'] }}">{{ number_format($card['value']) }}</p>
            </div>
        @endforeach
    </div>

    <div class="app-panel mb-5 rounded-[2rem] border p-4">
        <form method="GET" action="{{ route('tenant.pos.products') }}" class="flex flex-wrap items-center gap-2">
            <div class="pos-search-wrap flex-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('pos.filters.search_products') }}" class="pos-search-input">
            </div>

            <select name="category" onchange="this.form.submit()" class="pos-filter-select">
                <option value="">{{ __('pos.filters.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ __('pos.categories.'.$category) }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="pos-filter-select">
                <option value="">{{ __('pos.filters.all_statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('pos.statuses.'.$status) }}</option>
                @endforeach
            </select>

            @if (request()->hasAny(['search', 'category', 'status']))
                <a href="{{ route('tenant.pos.products') }}" class="pos-btn-ghost">{{ __('pos.filters.clear') }}</a>
            @endif

            @if ($canManageProducts)
                <a href="{{ route('tenant.pos.products.create') }}" class="pos-btn-primary">{{ __('pos.actions.add_product') }}</a>
            @endif
        </form>
    </div>

    @if ($products->isEmpty())
        <div class="app-panel flex min-h-[24rem] flex-col items-center justify-center gap-4 rounded-[2rem] border px-6 py-20 text-center">
            <div class="pos-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M6 6h15l-1.5 8.5a2 2 0 0 1-2 1.5H9a2 2 0 0 1-2-1.6L5.2 4H3"/><circle cx="9" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/></svg>
            </div>
            <p class="text-base font-semibold">{{ __('pos.product.empty') }}</p>
            <p class="app-muted text-sm">{{ __('pos.product.empty_help') }}</p>
            @if ($canManageProducts)
                <a href="{{ route('tenant.pos.products.create') }}" class="pos-btn-primary mt-2">{{ __('pos.actions.add_product') }}</a>
            @endif
        </div>
    @else
        <div class="pos-product-grid">
            @foreach ($products as $product)
                <article class="app-panel pos-product-card rounded-[1.7rem] border">
                    <div class="pos-product-thumb">
                        @if ($product->photo_url)
                            <img src="{{ asset('storage/'.$product->photo_url) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="pos-product-thumb-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 6h15l-1.5 8.5a2 2 0 0 1-2 1.5H9a2 2 0 0 1-2-1.6L5.2 4H3"/><circle cx="9" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/></svg>
                            </span>
                        @endif
                    </div>

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-semibold">{{ $product->name }}</h3>
                                <p class="app-muted mt-1 text-xs">{{ $product->sku ?: 'No SKU' }}</p>
                            </div>
                            <span class="pos-pill">{{ __('pos.categories.'.$product->category) }}</span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="app-muted text-[0.72rem] uppercase tracking-[0.18em]">Price</p>
                                <p class="mt-1 font-semibold">₹{{ $product->price_rupees }}</p>
                            </div>
                            <div>
                                <p class="app-muted text-[0.72rem] uppercase tracking-[0.18em]">Stock</p>
                                <p class="mt-1 font-semibold {{ $product->is_low_stock ? 'text-[#F97316]' : '' }}">{{ $product->stock_quantity }}</p>
                            </div>
                            <div>
                                <p class="app-muted text-[0.72rem] uppercase tracking-[0.18em]">GST</p>
                                <p class="mt-1 font-semibold">{{ rtrim(rtrim((string) $product->gst_rate, '0'), '.') }}%</p>
                            </div>
                            <div>
                                <p class="app-muted text-[0.72rem] uppercase tracking-[0.18em]">Status</p>
                                <p class="mt-1"><span class="pos-status {{ $product->status === 'active' ? 'pos-status-active' : 'pos-status-muted' }}">{{ __('pos.statuses.'.$product->status) }}</span></p>
                            </div>
                        </div>

                        @if ($product->description)
                            <p class="app-muted mt-4 text-sm">{{ Str::limit($product->description, 100) }}</p>
                        @endif
                    </div>

                    @if ($canManageProducts)
                        <div class="pos-card-actions">
                            <a href="{{ route('tenant.pos.products.edit', $product) }}" class="pos-card-action">{{ __('common.edit') }}</a>
                            <form method="POST" action="{{ route('tenant.pos.products.status', $product) }}" class="contents">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="pos-card-action">{{ $product->status === 'active' ? __('pos.actions.mark_inactive') : __('pos.actions.mark_active') }}</button>
                            </form>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-5">{{ $products->links() }}</div>
    @endif

    @push('styles')
        <style>
            .pos-search-wrap { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; display: flex; gap: 0.55rem; min-width: 15rem; padding: 0 0.9rem; }
            .pos-search-input { background: transparent; border: none; color: var(--app-text); font-size: 0.9rem; height: 3rem; outline: none; width: 100%; }
            .pos-filter-select { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; color: var(--app-text); font-size: 0.84rem; min-width: 10rem; outline: none; padding: 0.8rem 0.95rem; }
            .pos-btn-primary, .pos-btn-ghost { align-items: center; border-radius: 0.9rem; display: inline-flex; font-size: 0.84rem; font-weight: 600; min-height: 3rem; padding: 0 1rem; text-decoration: none; white-space: nowrap; }
            .pos-btn-primary { background: var(--app-brand); color: #0f172a; }
            .pos-btn-ghost { border: 1px solid var(--app-border); color: var(--app-text-muted); }
            .pos-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 52%, transparent); color: var(--app-text); }
            .pos-product-grid { display: grid; gap: 1rem; grid-template-columns: repeat(1, minmax(0, 1fr)); }
            @media (min-width: 768px) { .pos-product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            @media (min-width: 1200px) { .pos-product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
            .pos-product-card { display: flex; flex-direction: column; overflow: hidden; }
            .pos-product-thumb { align-items: center; background: color-mix(in srgb, var(--app-brand-soft) 40%, var(--app-panel-strong)); display: flex; height: 11rem; justify-content: center; overflow: hidden; }
            .pos-product-thumb-icon { color: var(--app-brand); display: inline-flex; height: 2rem; width: 2rem; }
            .pos-pill { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; font-size: 0.68rem; font-weight: 600; padding: 0.2rem 0.55rem; white-space: nowrap; }
            .pos-status { border-radius: 999px; display: inline-flex; font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.55rem; }
            .pos-status-active { background: rgba(29, 158, 117, 0.12); color: #1D9E75; }
            .pos-status-muted { background: rgba(136, 135, 128, 0.15); color: #888780; }
            .pos-card-actions { border-top: 1px solid color-mix(in srgb, var(--app-border) 70%, transparent); display: flex; margin-top: auto; }
            .pos-card-action { background: transparent; border: none; border-right: 1px solid color-mix(in srgb, var(--app-border) 70%, transparent); color: var(--app-text-muted); cursor: pointer; flex: 1; font-size: 0.82rem; font-weight: 600; padding: 0.85rem 0.75rem; text-align: center; text-decoration: none; }
            .pos-card-action:last-child { border-right: none; }
            .pos-card-action:hover { background: color-mix(in srgb, var(--app-border) 50%, transparent); color: var(--app-text); }
            .pos-empty-icon { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); display: inline-flex; height: 4.5rem; justify-content: center; width: 4.5rem; }
            .pos-empty-icon svg { height: 2rem; width: 2rem; }
        </style>
    @endpush
</x-layouts.admin>
