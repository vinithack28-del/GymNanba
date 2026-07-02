<x-layouts.admin
    title="{{ __('pos.sales_title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('pos.sales_title') }}"
    subheading="Bill store items, link sales to members, and review daily revenue from the same screen."
>
    <div class="mb-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="app-panel rounded-2xl border p-4">
            <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.stats.today_sales') }}</p>
            <p class="mt-2 text-2xl font-semibold">{{ number_format($summary['today_count']) }}</p>
        </div>
        <div class="app-panel rounded-2xl border p-4">
            <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.stats.today_total') }}</p>
            <p class="mt-2 text-2xl font-semibold text-[#1D9E75]">₹{{ number_format($summary['today_total_paise'] / 100, 2) }}</p>
        </div>
        <div class="app-panel rounded-2xl border p-4">
            <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.stats.month_total') }}</p>
            <p class="mt-2 text-2xl font-semibold">₹{{ number_format($summary['month_total_paise'] / 100, 2) }}</p>
        </div>
        <div class="app-panel rounded-2xl border p-4">
            <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.stats.today_gst') }}</p>
            <p class="mt-2 text-2xl font-semibold text-[#38BDF8]">₹{{ number_format($summary['gst_paise'] / 100, 2) }}</p>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.4fr_0.9fr]">
        <section class="app-panel rounded-[2rem] border p-4">
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <div class="ps-browser-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                    <input id="product-search" type="text" placeholder="{{ __('pos.filters.search_products') }}" class="ps-browser-input">
                </div>
                <div class="ps-tab-wrap">
                    <button type="button" class="ps-tab ps-tab-active" data-category-filter="">All</button>
                    @foreach ($categories as $category)
                        <button type="button" class="ps-tab" data-category-filter="{{ $category }}">{{ __('pos.categories.'.$category) }}</button>
                    @endforeach
                </div>
            </div>

            <div id="product-grid" class="ps-browser-grid">
                @foreach ($products as $product)
                    @php
                        $productPayload = json_encode([
                            'id' => $product->id,
                            'name' => $product->name,
                            'category' => $product->category,
                            'price_paise' => $product->price_paise,
                            'gst_rate' => (float) $product->gst_rate,
                            'stock_quantity' => $product->stock_quantity,
                            'sku' => $product->sku,
                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                    @endphp
                    <button
                        type="button"
                        class="ps-product-btn {{ $product->is_low_stock ? 'ps-product-low' : '' }} {{ $product->stock_quantity < 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                        data-product='{{ $productPayload }}'
                        data-search="{{ Str::lower($product->name.' '.$product->sku.' '.$product->category) }}"
                        data-category="{{ $product->category }}"
                        @disabled($product->stock_quantity < 1)
                    >
                        <span class="flex items-start justify-between gap-3">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold">{{ $product->name }}</span>
                                <span class="app-muted mt-1 block text-xs">{{ $product->sku ?: __('pos.categories.'.$product->category) }}</span>
                            </span>
                            <span class="ps-cat-pill">{{ __('pos.categories.'.$product->category) }}</span>
                        </span>
                        <span class="mt-4 flex items-end justify-between gap-3">
                            <span class="text-base font-semibold">₹{{ $product->price_rupees }}</span>
                            <span class="text-xs {{ $product->is_low_stock ? 'text-[#F97316]' : 'app-muted' }}">Stock {{ $product->stock_quantity }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="app-panel rounded-[2rem] border p-4">
            <form method="POST" action="{{ route('tenant.pos.sales.checkout') }}" id="checkout-form">
                @csrf
                <input type="hidden" name="items_payload" id="items-payload">
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.sales.cart_title') }}</p>
                        <h2 class="mt-1 text-lg font-semibold">{{ __('pos.actions.checkout') }}</h2>
                    </div>
                    <span id="cart-count" class="ps-count-pill">0</span>
                </div>

                <div id="cart-empty" class="mt-6 rounded-[1.4rem] border border-dashed border-[var(--app-border)] px-4 py-8 text-center">
                    <p class="text-sm font-semibold">{{ __('pos.sales.empty_cart') }}</p>
                </div>

                <div id="cart-items" class="mt-5 hidden space-y-3"></div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="ps-label">{{ __('pos.sales.member_link') }}</label>
                        <select name="member_id" class="ps-input">
                            <option value="">Walk-in / no member</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }} · {{ $member->member_code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="ps-label">{{ __('pos.sales.discount') }}</label>
                            <div class="grid grid-cols-[0.8fr_1fr] gap-2">
                                <select name="discount_type" class="ps-input" {{ $canApplyDiscount ? '' : 'disabled' }}>
                                    <option value="flat">{{ __('pos.sales.discount_flat') }}</option>
                                    <option value="percent">{{ __('pos.sales.discount_percent') }}</option>
                                </select>
                                <input type="number" step="0.01" min="0" name="discount_value" class="ps-input" {{ $canApplyDiscount ? '' : 'disabled' }}>
                            </div>
                            @unless ($canApplyDiscount)
                                <p class="app-muted mt-1 text-xs">Discounts require owner or branch manager access.</p>
                            @endunless
                        </div>

                        <div>
                            <label class="ps-label">{{ __('pos.sales.method') }}</label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($methods as $method)
                                    <label class="ps-pay-option">
                                        <input type="radio" name="method" value="{{ $method }}" class="sr-only" @checked($loop->first)>
                                        <span>{{ __('pos.methods.'.$method) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="ps-label">{{ __('pos.sales.reference') }}</label>
                            <input type="text" name="reference" class="ps-input" placeholder="Txn / receipt / notes ref">
                        </div>
                        <div>
                            <label class="ps-label">{{ __('pos.sales.notes') }}</label>
                            <input type="text" name="notes" class="ps-input" placeholder="Optional note">
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-2 rounded-[1.4rem] border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-4">
                    <div class="flex items-center justify-between text-sm"><span class="app-muted">{{ __('pos.sales.subtotal') }}</span><span id="subtotal-text">₹0.00</span></div>
                    <div class="flex items-center justify-between text-sm"><span class="app-muted">{{ __('pos.sales.gst') }}</span><span id="gst-text">₹0.00</span></div>
                    <div class="flex items-center justify-between border-t border-[var(--app-border)] pt-3 text-base font-semibold"><span>{{ __('pos.sales.total') }}</span><span id="total-text">₹0.00</span></div>
                </div>

                <button type="submit" class="ps-checkout-btn mt-5 w-full">{{ __('pos.actions.checkout') }}</button>
            </form>
        </section>
    </div>

    <section class="app-panel mt-5 rounded-[2rem] border p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.actions.today_tally') }}</p>
                <h2 class="mt-1 text-lg font-semibold">{{ __('pos.sales.history') }}</h2>
            </div>
            <form method="GET" action="{{ route('tenant.pos.sales') }}" class="flex flex-wrap items-center gap-2">
                <input type="date" name="from" value="{{ request('from') }}" class="ps-filter-input">
                <input type="date" name="to" value="{{ request('to') }}" class="ps-filter-input">
                <select name="branch_id" class="ps-filter-input">
                    <option value="">{{ __('pos.filters.all_branches') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <select name="method" class="ps-filter-input">
                    <option value="">{{ __('pos.filters.all_methods') }}</option>
                    @foreach ($methods as $method)
                        <option value="{{ $method }}" @selected(request('method') === $method)>{{ __('pos.methods.'.$method) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ps-mini-btn">{{ __('common.save') }}</button>
            </form>
        </div>

        <div class="mt-5 grid gap-4 xl:grid-cols-[1.3fr_1fr]">
            <div class="overflow-x-auto rounded-[1.5rem] border border-[var(--app-border)]">
                <table class="min-w-full text-sm">
                    <thead class="app-table-head">
                        <tr>
                            @foreach ([__('pos.sales.date'), __('pos.sales.bill_number'), __('pos.sales.member'), __('pos.sales.items'), __('pos.sales.subtotal'), __('pos.sales.gst'), __('pos.sales.total'), __('pos.sales.method'), __('pos.sales.staff'), __('pos.sales.branch'), __('common.actions')] as $head)
                                <th class="px-4 py-3 text-left font-medium">{{ $head }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr class="border-t border-[var(--app-border)]">
                                <td class="px-4 py-4">{{ $sale->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $sale->bill_number }}</td>
                                <td class="px-4 py-4">{{ $sale->member?->name ?? 'Walk-in' }}</td>
                                <td class="px-4 py-4 app-muted">{{ $sale->items_summary }}</td>
                                <td class="px-4 py-4">₹{{ $sale->subtotal_rupees }}</td>
                                <td class="px-4 py-4">₹{{ $sale->gst_rupees }}</td>
                                <td class="px-4 py-4 font-semibold">₹{{ $sale->total_rupees }}</td>
                                <td class="px-4 py-4">{{ $sale->method_label }}</td>
                                <td class="px-4 py-4">{{ $sale->seller?->name ?? 'Owner / system' }}</td>
                                <td class="px-4 py-4">{{ $sale->branch?->name ?? '—' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('tenant.pos.sales.show', $sale) }}" class="ps-row-btn">{{ __('pos.actions.view_bill') }}</a>
                                        @if ($canRefund && !$sale->refunded_at)
                                            <form method="POST" action="{{ route('tenant.pos.sales.refund', $sale) }}" onsubmit="return confirm('Refund this bill and restock all items?')">
                                                @csrf
                                                <input type="hidden" name="refund_reason" value="Counter refund">
                                                <button type="submit" class="ps-row-btn ps-row-btn-danger">{{ __('pos.actions.refund') }}</button>
                                            </form>
                                        @elseif ($sale->refunded_at)
                                            <span class="ps-refunded-pill">{{ __('pos.statuses.refunded') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-10 text-center app-muted">{{ __('pos.sales.empty_history') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="rounded-[1.5rem] border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.actions.today_tally') }}</p>
                        <h3 class="mt-1 font-semibold">{{ \Illuminate\Support\Carbon::parse($tallyDate)->format('d M Y') }}</h3>
                    </div>
                    <form method="GET" action="{{ route('tenant.pos.sales') }}">
                        <input type="date" name="tally_date" value="{{ $tallyDate }}" class="ps-filter-input" onchange="this.form.submit()">
                    </form>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach ($tally['by_method'] as $method => $amount)
                        <div class="rounded-2xl border border-[var(--app-border)] px-3 py-3">
                            <p class="app-muted text-xs uppercase">{{ __('pos.methods.'.$method) }}</p>
                            <p class="mt-2 font-semibold">₹{{ number_format($amount / 100, 2) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between"><span class="app-muted">GST collected</span><span>₹{{ number_format($tally['gst_paise'] / 100, 2) }}</span></div>
                    <div class="flex items-center justify-between"><span class="app-muted">Cash in hand</span><span>₹{{ number_format($tally['cash_paise'] / 100, 2) }}</span></div>
                    <div class="flex items-center justify-between"><span class="app-muted">Card / UPI</span><span>₹{{ number_format($tally['cashless_paise'] / 100, 2) }}</span></div>
                </div>

                <div class="mt-5 grid gap-4">
                    <div>
                        <p class="mb-2 text-xs font-medium uppercase tracking-[0.22em] app-muted">By category</p>
                        <div class="space-y-2 text-sm">
                            @forelse ($tally['by_category'] as $label => $amount)
                                <div class="flex items-center justify-between"><span>{{ $label }}</span><span>₹{{ number_format($amount / 100, 2) }}</span></div>
                            @empty
                                <p class="app-muted">No category totals for this date.</p>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-medium uppercase tracking-[0.22em] app-muted">By staff</p>
                        <div class="space-y-2 text-sm">
                            @forelse ($tally['by_staff'] as $label => $amount)
                                <div class="flex items-center justify-between"><span>{{ $label }}</span><span>₹{{ number_format($amount / 100, 2) }}</span></div>
                            @empty
                                <p class="app-muted">No staff totals for this date.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">{{ $sales->links() }}</div>
    </section>

    @push('styles')
        <style>
            .ps-browser-search { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.95rem; display: flex; gap: 0.55rem; min-width: 17rem; padding: 0 0.9rem; }
            .ps-browser-input { background: transparent; border: none; color: var(--app-text); font-size: 0.9rem; height: 3rem; outline: none; width: 100%; }
            .ps-tab-wrap { display: flex; flex-wrap: wrap; gap: 0.4rem; }
            .ps-tab { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); cursor: pointer; font-size: 0.78rem; font-weight: 600; padding: 0.45rem 0.8rem; }
            .ps-tab-active { background: color-mix(in srgb, var(--app-brand-soft) 85%, transparent); border-color: color-mix(in srgb, var(--app-brand) 26%, var(--app-border)); color: var(--app-brand); }
            .ps-browser-grid { display: grid; gap: 0.9rem; grid-template-columns: repeat(1, minmax(0, 1fr)); }
            @media (min-width: 768px) { .ps-browser-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            .ps-product-btn { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 1.25rem; color: var(--app-text); cursor: pointer; padding: 1rem; text-align: left; transition: border-color 140ms, transform 140ms; }
            .ps-product-btn:hover { border-color: color-mix(in srgb, var(--app-brand) 30%, var(--app-border)); transform: translateY(-1px); }
            .ps-product-low { box-shadow: inset 0 0 0 1px rgba(249, 115, 22, 0.22); }
            .ps-cat-pill, .ps-count-pill, .ps-refunded-pill { border-radius: 999px; display: inline-flex; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.55rem; }
            .ps-cat-pill { background: var(--app-panel); color: var(--app-text-muted); }
            .ps-count-pill { background: var(--app-brand-soft); color: var(--app-brand); }
            .ps-refunded-pill { background: rgba(226, 75, 74, 0.12); color: #E24B4A; }
            .ps-label { color: var(--app-text); display: block; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.45rem; }
            .ps-input, .ps-filter-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; color: var(--app-text); font-size: 0.84rem; min-height: 2.9rem; outline: none; padding: 0 0.9rem; width: 100%; }
            .ps-pay-option { cursor: pointer; }
            .ps-pay-option span { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; display: flex; font-size: 0.82rem; font-weight: 600; justify-content: center; min-height: 2.9rem; }
            .ps-pay-option input:checked + span { background: color-mix(in srgb, var(--app-brand-soft) 85%, transparent); border-color: color-mix(in srgb, var(--app-brand) 26%, var(--app-border)); color: var(--app-brand); }
            .ps-cart-line { border: 1px solid var(--app-border); border-radius: 1rem; padding: 0.9rem; }
            .ps-cart-line input { background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 0.7rem; color: var(--app-text); min-height: 2.25rem; outline: none; text-align: center; width: 3.5rem; }
            .ps-icon-btn, .ps-mini-btn, .ps-row-btn, .ps-checkout-btn { border-radius: 0.85rem; font-size: 0.82rem; font-weight: 600; }
            .ps-icon-btn { background: var(--app-panel); border: 1px solid var(--app-border); color: var(--app-text); height: 2.25rem; width: 2.25rem; }
            .ps-mini-btn, .ps-row-btn { background: transparent; border: 1px solid var(--app-border); color: var(--app-text-muted); display: inline-flex; min-height: 2.5rem; padding: 0 0.85rem; text-decoration: none; align-items: center; }
            .ps-row-btn-danger { color: #E24B4A; border-color: rgba(226, 75, 74, 0.25); }
            .ps-checkout-btn { background: var(--app-brand); border: none; color: #0f172a; min-height: 3.1rem; }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                const searchInput = document.getElementById('product-search');
                const productGrid = document.getElementById('product-grid');
                const cartItems = document.getElementById('cart-items');
                const cartEmpty = document.getElementById('cart-empty');
                const countEl = document.getElementById('cart-count');
                const itemsPayload = document.getElementById('items-payload');
                const subtotalText = document.getElementById('subtotal-text');
                const gstText = document.getElementById('gst-text');
                const totalText = document.getElementById('total-text');
                const categoryTabs = document.querySelectorAll('[data-category-filter]');
                const products = Array.from(document.querySelectorAll('[data-product]'));
                const money = (paise) => `₹${(paise / 100).toFixed(2)}`;
                const cart = new Map();
                let activeCategory = '';

                const recalc = () => {
                    const rows = Array.from(cart.values());
                    let subtotal = 0;
                    let gst = 0;

                    cartItems.innerHTML = '';

                    rows.forEach((item) => {
                        const lineSubtotal = item.qty * item.price_paise;
                        const lineGst = Math.round(lineSubtotal * (item.gst_rate / 100));
                        subtotal += lineSubtotal;
                        gst += lineGst;

                        const row = document.createElement('div');
                        row.className = 'ps-cart-line';
                        row.innerHTML = `
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">${item.name}</p>
                                    <p class="app-muted mt-1 text-xs">₹${(item.price_paise / 100).toFixed(2)} · GST ${item.gst_rate}%</p>
                                </div>
                                <button type="button" class="ps-icon-btn" data-remove="${item.id}">×</button>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button type="button" class="ps-icon-btn" data-step="${item.id}" data-dir="-1">−</button>
                                    <input type="number" min="1" value="${item.qty}" data-qty="${item.id}">
                                    <button type="button" class="ps-icon-btn" data-step="${item.id}" data-dir="1">+</button>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold">${money(lineSubtotal + lineGst)}</p>
                                    <p class="app-muted text-xs">Stock ${item.stock_quantity}</p>
                                </div>
                            </div>
                        `;
                        cartItems.appendChild(row);
                    });

                    const total = subtotal + gst;
                    subtotalText.textContent = money(subtotal);
                    gstText.textContent = money(gst);
                    totalText.textContent = money(total);
                    countEl.textContent = rows.reduce((sum, row) => sum + row.qty, 0);
                    cartItems.classList.toggle('hidden', rows.length === 0);
                    cartEmpty.classList.toggle('hidden', rows.length !== 0);
                    itemsPayload.value = JSON.stringify(rows.map((item) => ({
                        product_id: item.id,
                        qty: item.qty,
                        unit_price_paise: item.price_paise,
                    })));
                };

                const applyFilters = () => {
                    const term = (searchInput?.value || '').trim().toLowerCase();

                    products.forEach((node) => {
                        const matchesCategory = !activeCategory || node.dataset.category === activeCategory;
                        const matchesTerm = !term || node.dataset.search.includes(term);
                        node.classList.toggle('hidden', !(matchesCategory && matchesTerm));
                    });
                };

                products.forEach((node) => {
                    node.addEventListener('click', () => {
                        const product = JSON.parse(node.dataset.product);
                        if (product.stock_quantity < 1) return;
                        const current = cart.get(product.id) || product;
                        current.qty = Math.min((current.qty || 0) + 1, product.stock_quantity || 9999);
                        if (current.qty < 1) current.qty = 1;
                        cart.set(product.id, current);
                        recalc();
                    });
                });

                cartItems.addEventListener('click', (event) => {
                    const removeId = event.target.getAttribute('data-remove');
                    const stepId = event.target.getAttribute('data-step');
                    if (removeId) {
                        cart.delete(Number(removeId));
                        recalc();
                    }
                    if (stepId) {
                        const item = cart.get(Number(stepId));
                        if (!item) return;
                        item.qty += Number(event.target.getAttribute('data-dir'));
                        if (item.qty <= 0) {
                            cart.delete(item.id);
                        } else if (item.qty > item.stock_quantity) {
                            item.qty = item.stock_quantity;
                        } else {
                            cart.set(item.id, item);
                        }
                        recalc();
                    }
                });

                cartItems.addEventListener('change', (event) => {
                    const id = event.target.getAttribute('data-qty');
                    if (!id) return;
                    const item = cart.get(Number(id));
                    if (!item) return;
                    item.qty = Math.max(1, Math.min(Number(event.target.value || 1), item.stock_quantity));
                    cart.set(item.id, item);
                    recalc();
                });

                categoryTabs.forEach((tab) => {
                    tab.addEventListener('click', () => {
                        activeCategory = tab.dataset.categoryFilter || '';
                        categoryTabs.forEach((btn) => btn.classList.toggle('ps-tab-active', btn === tab));
                        applyFilters();
                    });
                });

                searchInput?.addEventListener('input', applyFilters);
                document.getElementById('checkout-form')?.addEventListener('submit', (event) => {
                    if (cart.size === 0) {
                        event.preventDefault();
                        alert('Add at least one product to the cart.');
                    }
                });

                recalc();
                applyFilters();
            })();
        </script>
    @endpush
</x-layouts.admin>
