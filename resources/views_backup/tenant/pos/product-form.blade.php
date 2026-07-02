@php
    $editing = isset($product);
@endphp

<x-layouts.admin
    title="{{ $editing ? __('pos.actions.edit_product') : __('pos.actions.add_product') }}"
    eyebrow="Gym Workspace"
    heading="{{ $editing ? __('pos.actions.edit_product') : __('pos.actions.add_product') }}"
    subheading="Capture catalog details, pricing, GST, and stock settings for POS billing."
>
    <form method="POST" action="{{ $editing ? route('tenant.pos.products.update', $product) : route('tenant.pos.products.store') }}" enctype="multipart/form-data" class="app-panel rounded-[2rem] border p-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="pf-label">{{ __('pos.product.name') }}</label>
                <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" class="pf-input" required>
                @error('name') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="pf-label">{{ __('pos.product.category') }}</label>
                <select name="category" class="pf-input" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $product->category ?? '') === $category)>{{ __('pos.categories.'.$category) }}</option>
                    @endforeach
                </select>
                @error('category') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="pf-label">{{ __('pos.product.sku') }}</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" class="pf-input">
                @error('sku') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="pf-label">{{ __('pos.product.unit') }}</label>
                <select name="unit" class="pf-input" required>
                    @foreach ($units as $unit)
                        <option value="{{ $unit }}" @selected(old('unit', $product->unit ?? 'piece') === $unit)>{{ __('pos.units.'.$unit) }}</option>
                    @endforeach
                </select>
                @error('unit') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="pf-label">{{ __('pos.product.cost_price') }}</label>
                <input type="number" step="0.01" min="0" max="999999" name="cost_price" value="{{ old('cost_price', isset($product) ? number_format($product->cost_paise / 100, 2, '.', '') : '') }}" class="pf-input" required>
                @error('cost_price') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="pf-label">{{ __('pos.product.selling_price') }}</label>
                <input type="number" step="0.01" min="0.01" max="999999" name="selling_price" value="{{ old('selling_price', isset($product) ? number_format($product->price_paise / 100, 2, '.', '') : '') }}" class="pf-input" required>
                @error('selling_price') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="pf-label">{{ __('pos.product.gst_rate') }}</label>
                <select name="gst_rate" class="pf-input" required>
                    @foreach ($gstRates as $rate)
                        <option value="{{ $rate }}" @selected((string) old('gst_rate', $product->gst_rate ?? '0') === (string) $rate)>{{ $rate }}%</option>
                    @endforeach
                </select>
                @error('gst_rate') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="pf-label">{{ __('pos.product.current_stock') }}</label>
                <input type="number" name="current_stock" min="0" value="{{ old('current_stock', $product->stock_quantity ?? 0) }}" class="pf-input" required>
                @error('current_stock') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="pf-label">{{ __('pos.product.low_stock_threshold') }}</label>
                <input type="number" name="low_stock_threshold" min="1" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" class="pf-input" required>
                @error('low_stock_threshold') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="pf-label">{{ __('pos.product.status') }}</label>
                <select name="status" class="pf-input" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $product->status ?? 'active') === $status)>{{ __('pos.statuses.'.$status) }}</option>
                    @endforeach
                </select>
                @error('status') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="pf-label">{{ __('pos.product.photo') }}</label>
                <input type="file" name="photo" accept=".jpg,.jpeg,.png" class="pf-input file:mr-4 file:rounded-xl file:border-0 file:bg-[var(--app-brand)] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-950">
                @if ($editing && $product->photo_url)
                    <p class="app-muted mt-2 text-sm">Current photo:
                        <a href="{{ asset('storage/'.$product->photo_url) }}" target="_blank" class="font-semibold text-orange-300 hover:underline">View</a>
                    </p>
                @endif
                @error('photo') <p class="pf-error">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="pf-label">{{ __('pos.product.description') }}</label>
                <textarea name="description" rows="5" class="pf-input">{{ old('description', $product->description ?? '') }}</textarea>
                @error('description') <p class="pf-error">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ((float) old('selling_price', isset($product) ? number_format($product->price_paise / 100, 2, '.', '') : 0) < (float) old('cost_price', isset($product) ? number_format($product->cost_paise / 100, 2, '.', '') : 0))
            <div class="mt-5 rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-300">
                {{ __('pos.product.below_cost_warning') }}
            </div>
        @endif

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="pf-btn-primary">{{ $editing ? __('pos.actions.update_product') : __('pos.actions.save_product') }}</button>
            <a href="{{ route('tenant.pos.products') }}" class="pf-btn-ghost">{{ __('common.cancel') }}</a>
        </div>
    </form>

    @push('styles')
        <style>
            .pf-label { color: var(--app-text); display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.5rem; }
            .pf-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 1rem; color: var(--app-text); font-size: 0.9rem; outline: none; padding: 0.85rem 1rem; width: 100%; }
            .pf-error { color: #fca5a5; font-size: 0.78rem; margin-top: 0.4rem; }
            .pf-btn-primary, .pf-btn-ghost { align-items: center; border-radius: 0.9rem; display: inline-flex; font-size: 0.84rem; font-weight: 600; min-height: 3rem; padding: 0 1.1rem; text-decoration: none; }
            .pf-btn-primary { background: var(--app-brand); color: #0f172a; }
            .pf-btn-ghost { border: 1px solid var(--app-border); color: var(--app-text-muted); }
            .pf-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 50%, transparent); color: var(--app-text); }
        </style>
    @endpush
</x-layouts.admin>
