<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Member;
use App\Models\PosProduct;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\PosStockMovement;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PosController extends Controller
{
    public function products(Request $request){
        $user = $request->user();
        $this->ensureProductAccess($user);

        $query = PosProduct::query()->forTenant($user->tenant_id);

        if ($search = trim((string) $request->get('search'))) {
            $query->search($search);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $products = $query->orderBy('name')->paginate(18)->withQueryString();
        $base = PosProduct::query()->forTenant($user->tenant_id);

        return Inertia::render('Tenant/Pos/Products', [
            'products' => $products,
            'stats' => [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('status', 'active')->count(),
                'inactive' => (clone $base)->where('status', 'inactive')->count(),
                'low_stock' => (clone $base)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            ],
            'categories' => PosProduct::CATEGORIES,
            'statuses' => PosProduct::STATUSES,
            'canManageProducts' => $this->canManageProducts($user),
        ]);
    }

    public function createProduct(Request $request){
        $user = $request->user();
        $this->ensureCanManageProducts($user);

        return Inertia::render('Tenant/Pos/ProductForm'[
            'categories' => PosProduct::CATEGORIES,
            'units' => PosProduct::UNITS,
            'gstRates' => PosProduct::GST_RATES,
            'statuses' => PosProduct::STATUSES,
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->ensureCanManageProducts($user);

        $validated = $this->validateProduct($request);
        $product = PosProduct::query()->create($this->productPayload($user, $validated, $request));

        return redirect()->route('tenant.pos.products')->with('status', "Product {$product->name} created.");
    }

    public function editProduct(Request $request, PosProduct $product){
        $user = $request->user();
        $this->ensureCanManageProducts($user);
        $this->ensureTenantProduct($user, $product);

        return Inertia::render('Tenant/Pos/ProductForm'[
            'product' => $product,
            'categories' => PosProduct::CATEGORIES,
            'units' => PosProduct::UNITS,
            'gstRates' => PosProduct::GST_RATES,
            'statuses' => PosProduct::STATUSES,
        ]);
    }

    public function updateProduct(Request $request, PosProduct $product): RedirectResponse
    {
        $user = $request->user();
        $this->ensureCanManageProducts($user);
        $this->ensureTenantProduct($user, $product);

        $validated = $this->validateProduct($request, $product);
        $product->update($this->productPayload($user, $validated, $request, $product));

        return redirect()->route('tenant.pos.products')->with('status', "Product {$product->name} updated.");
    }

    public function toggleProductStatus(Request $request, PosProduct $product): RedirectResponse
    {
        $user = $request->user();
        $this->ensureCanManageProducts($user);
        $this->ensureTenantProduct($user, $product);

        $nextStatus = $product->status === 'active' ? 'inactive' : 'active';
        $product->update(['status' => $nextStatus]);

        return back()->with('status', "{$product->name} marked as {$nextStatus}.");
    }

    public function sales(Request $request){
        $user = $request->user();
        $this->ensureSalesAccess($user);

        $salesQuery = PosSale::query()
            ->forTenant($user->tenant_id)
            ->with(['branch', 'member', 'seller', 'items'])
            ->when($branchId = $this->filterBranchId($user, $request->get('branch_id')), fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->string('method')))
            ->when($request->filled('staff_id'), fn ($q) => $q->where('sold_by', $request->integer('staff_id')))
            ->when($request->filled('member_id'), fn ($q) => $q->where('member_id', $request->integer('member_id')));

        $sales = $salesQuery->orderByDesc('created_at')->paginate(12)->withQueryString();
        $tallyDate = $request->get('tally_date', now()->toDateString());

        return Inertia::render('Tenant/Pos/Sales', [
            'products' => PosProduct::query()
                ->forTenant($user->tenant_id)
                ->where('status', 'active')
                ->orderBy('category')
                ->orderBy('name')
                ->get(),
            'sales' => $sales,
            'branches' => $this->branchOptions($user),
            'members' => Member::query()->forTenant($user->tenant_id)->orderBy('name')->limit(150)->get(),
            'staffOptions' => Staff::query()->forTenant($user->tenant_id)->orderBy('name')->get(),
            'methods' => PosSale::METHODS,
            'categories' => PosProduct::CATEGORIES,
            'selectedBranchId' => $this->checkoutBranchId($user, $request),
            'canApplyDiscount' => $this->canApplyDiscount($user),
            'canRefund' => $this->canRefund($user),
            'summary' => $this->salesSummary($user),
            'tallyDate' => $tallyDate,
            'tally' => $this->dailyTally($user, $tallyDate, $this->filterBranchId($user, $request->get('branch_id'))),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $sale = $this->performCheckout($request->user(), $this->checkoutDataFromWeb($request));

        return redirect()
            ->route('tenant.pos.sales.show', $sale)
            ->with('status', "Sale {$sale->bill_number} created.");
    }

    public function showSale(Request $request, PosSale $sale){
        $user = $request->user();
        $this->ensureTenantSale($user, $sale);
        $this->ensureSalesAccess($user);

        return Inertia::render('Tenant/Pos/SaleShow', [
            'sale' => $sale->load(['branch', 'member', 'seller', 'refundActor', 'items.product']),
            'canRefund' => $this->canRefund($user) && !$sale->refunded_at,
        ]);
    }

    public function refundSale(Request $request, PosSale $sale): RedirectResponse
    {
        $user = $request->user();
        $this->ensureCanRefund($user);
        $this->ensureTenantSale($user, $sale);

        $validated = $request->validate([
            'refund_reason' => ['required', 'string', 'max:200'],
        ]);

        if ($sale->refunded_at) {
            throw ValidationException::withMessages([
                'refund_reason' => 'This bill has already been refunded.',
            ]);
        }

        DB::transaction(function () use ($user, $sale, $validated): void {
            $sale->loadMissing('items');

            foreach ($sale->items as $item) {
                $product = PosProduct::query()->lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock_quantity', $item->qty);

                PosStockMovement::query()->create([
                    'product_id' => $product->id,
                    'tenant_id' => $sale->tenant_id,
                    'branch_id' => $sale->branch_id,
                    'sale_id' => $sale->id,
                    'type' => 'return',
                    'quantity' => $item->qty,
                    'reason' => $validated['refund_reason'],
                    'reference' => $sale->bill_number,
                    'movement_date' => now()->toDateString(),
                    'created_by' => $user->staffProfile?->id,
                ]);
            }

            $sale->update([
                'refunded_at' => now(),
                'refunded_by' => $user->id,
                'refund_reason' => $validated['refund_reason'],
            ]);
        });

        return back()->with('status', "Sale {$sale->bill_number} refunded.");
    }

    public function stock(Request $request){
        $user = $request->user();
        $this->ensureStockAccess($user);

        $query = PosProduct::query()->forTenant($user->tenant_id);

        if ($search = trim((string) $request->get('search'))) {
            $query->search($search);
        }

        if ($request->boolean('low_stock_only')) {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $products = $query->orderBy('name')->paginate(18)->withQueryString();
        $selectedProduct = null;
        if ($request->filled('product_id')) {
            $selectedProduct = PosProduct::query()
                ->forTenant($user->tenant_id)
                ->with(['stockMovements' => fn ($q) => $q->with(['creator', 'branch'])->latest()->limit(12)])
                ->find($request->integer('product_id'));
        }

        $base = PosProduct::query()->forTenant($user->tenant_id);

        return Inertia::render('Tenant/Pos/Stock', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'productOptions' => PosProduct::query()->forTenant($user->tenant_id)->where('status', 'active')->orderBy('name')->get(),
            'branches' => $this->branchOptions($user),
            'adjustmentReasons' => PosStockMovement::ADJUSTMENT_REASONS,
            'canManageStock' => $this->canManageStock($user),
            'summary' => [
                'products' => (clone $base)->count(),
                'low_stock' => (clone $base)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
                'stock_value_paise' => (clone $base)->get()->sum->stock_value_paisa,
                'out_of_stock' => (clone $base)->where('stock_quantity', 0)->count(),
            ],
        ]);
    }

    public function restock(Request $request): RedirectResponse
    {
        $this->performRestock($request->user(), $this->restockData($request));

        return back()->with('status', 'Stock restocked.');
    }

    public function adjust(Request $request): RedirectResponse
    {
        $this->performAdjustment($request->user(), $this->adjustData($request));

        return back()->with('status', 'Stock adjusted.');
    }

    public function apiProducts(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureProductAccess($user);

        $products = PosProduct::query()
            ->forTenant($user->tenant_id)
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($q) => $q->search($request->string('search')))
            ->orderBy('name')
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function apiStoreProduct(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureCanManageProducts($user);

        $validated = $this->validateProduct($request);
        $product = PosProduct::query()->create($this->productPayload($user, $validated, $request));

        return response()->json(['product_id' => $product->id], 201);
    }

    public function apiUpdateProduct(Request $request, PosProduct $product): JsonResponse
    {
        $user = $request->user();
        $this->ensureCanManageProducts($user);
        $this->ensureTenantProduct($user, $product);

        $validated = $this->validateProduct($request, $product, true);
        $product->update($this->productPayload($user, $validated, $request, $product, true));

        return response()->json(['product' => $product->fresh()]);
    }

    public function apiSales(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureSalesAccess($user);

        $sales = PosSale::query()
            ->forTenant($user->tenant_id)
            ->with(['member', 'branch', 'seller', 'items'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->when($branchId = $this->filterBranchId($user, $request->get('branch_id')), fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('staff_id'), fn ($q) => $q->where('sold_by', $request->integer('staff_id')))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->string('method')))
            ->when($request->filled('member_id'), fn ($q) => $q->where('member_id', $request->integer('member_id')))
            ->orderByDesc('created_at')
            ->paginate(min(100, max(1, $request->integer('limit', 20))))
            ->withQueryString();

        return response()->json([
            'sales' => $sales->items(),
            'total' => $sales->total(),
        ]);
    }

    public function apiCheckout(Request $request): JsonResponse
    {
        $sale = $this->performCheckout($request->user(), $this->checkoutDataFromApi($request));

        return response()->json([
            'sale_id' => $sale->id,
            'bill_number' => $sale->bill_number,
            'receipt_url' => route('tenant.pos.sales.show', $sale),
        ], 201);
    }

    public function apiStock(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->ensureStockAccess($user);

        $products = PosProduct::query()
            ->forTenant($user->tenant_id)
            ->when($request->boolean('low_stock_only'), fn ($q) => $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold'))
            ->orderBy('name')
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function apiRestock(Request $request): JsonResponse
    {
        $newQuantity = $this->performRestock($request->user(), $this->restockData($request));

        return response()->json(['new_stock_quantity' => $newQuantity]);
    }

    public function apiAdjust(Request $request): JsonResponse
    {
        $newQuantity = $this->performAdjustment($request->user(), $this->adjustData($request));

        return response()->json(['new_stock_quantity' => $newQuantity]);
    }

    private function validateProduct(Request $request, ?PosProduct $product = null, bool $partial = false): array
    {
        $tenantId = $request->user()->tenant_id;
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'min:2', 'max:100', Rule::unique('pos_products')->where('tenant_id', $tenantId)->ignore($product?->id)],
            'category' => [$required, Rule::in(PosProduct::CATEGORIES)],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('pos_products')->where('tenant_id', $tenantId)->ignore($product?->id)],
            'unit' => [$required, Rule::in(PosProduct::UNITS)],
            'cost_price' => [$required, 'numeric', 'min:0', 'max:999999'],
            'selling_price' => [$required, 'numeric', 'gt:0', 'max:999999'],
            'gst_rate' => [$required, Rule::in(PosProduct::GST_RATES)],
            'current_stock' => [$required, 'integer', 'min:0'],
            'low_stock_threshold' => [$required, 'integer', 'min:1'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => [$required, Rule::in(PosProduct::STATUSES)],
        ]);
    }

    private function productPayload(User $user, array $validated, Request $request, ?PosProduct $product = null, bool $partial = false): array
    {
        $payload = [];

        $map = [
            'name' => 'name',
            'category' => 'category',
            'sku' => 'sku',
            'unit' => 'unit',
            'description' => 'description',
            'status' => 'status',
        ];

        foreach ($map as $input => $column) {
            if (!$partial || array_key_exists($input, $validated)) {
                $payload[$column] = $validated[$input] ?? null;
            }
        }

        foreach ([
            'cost_price' => 'cost_paise',
            'selling_price' => 'price_paise',
            'current_stock' => 'stock_quantity',
            'low_stock_threshold' => 'low_stock_threshold',
            'gst_rate' => 'gst_rate',
        ] as $input => $column) {
            if (!$partial || array_key_exists($input, $validated)) {
                $payload[$column] = in_array($input, ['cost_price', 'selling_price'], true)
                    ? $this->rupeesToPaise($validated[$input] ?? 0)
                    : $validated[$input];
            }
        }

        if ($request->hasFile('photo')) {
            $payload['photo_url'] = $request->file('photo')->store('pos/products', 'public');
        } elseif (!$partial && !$product) {
            $payload['photo_url'] = null;
        }

        if (!$product) {
            $payload['tenant_id'] = $user->tenant_id;
        }

        return $payload;
    }

    private function checkoutDataFromWeb(Request $request): array
    {
        $validated = $request->validate([
            'items_payload' => ['required', 'string'],
            'method' => ['required', Rule::in(PosSale::METHODS)],
            'reference' => ['nullable', 'string', 'max:100'],
            'discount_type' => ['nullable', Rule::in(['percent', 'flat'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'member_id' => ['nullable', Rule::exists('members', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $items = json_decode($validated['items_payload'], true);
        if (!is_array($items) || $items === []) {
            throw ValidationException::withMessages(['items_payload' => 'Add at least one product to the cart.']);
        }

        return [
            'items' => $items,
            'method' => $validated['method'],
            'reference' => $validated['reference'] ?? null,
            'discount_type' => $validated['discount_type'] ?? null,
            'discount_value' => $validated['discount_value'] ?? 0,
            'member_id' => $validated['member_id'] ?? null,
            'branch_id' => (int) $validated['branch_id'],
            'notes' => $validated['notes'] ?? null,
        ];
    }

    private function checkoutDataFromApi(Request $request): array
    {
        return $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('pos_products', 'id')],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price_paise' => ['nullable', 'integer', 'min:1'],
            'method' => ['required', Rule::in(PosSale::METHODS)],
            'reference' => ['nullable', 'string', 'max:100'],
            'discount_paise' => ['nullable', 'integer', 'min:0'],
            'member_id' => ['nullable', Rule::exists('members', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function performCheckout(User $user, array $data): PosSale
    {
        $this->ensureSalesAccess($user);
        $branchId = $this->checkoutBranchIdFromValue($user, $data['branch_id'] ?? null);
        $discountPaise = $data['discount_paise'] ?? null;

        if ($discountPaise === null) {
            $discountPaise = $this->resolveDiscountPaise(
                $user,
                $data['discount_type'] ?? null,
                (float) ($data['discount_value'] ?? 0),
                $data['items']
            );
        } elseif ($discountPaise > 0) {
            $this->ensureCanApplyDiscount($user);
        }

        return DB::transaction(function () use ($user, $data, $branchId, $discountPaise): PosSale {
            $products = collect($data['items'])
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $productMap = PosProduct::query()
                ->forTenant($user->tenant_id)
                ->whereIn('id', $products)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $saleRows = [];
            $subtotal = 0;
            $gstTotal = 0;

            foreach ($data['items'] as $row) {
                $product = $productMap->get((int) $row['product_id']);
                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more selected products are invalid.',
                    ]);
                }

                $qty = max(1, (int) $row['qty']);
                if (!$this->allowsNegativeStock() && $product->stock_quantity < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "INSUFFICIENT_STOCK: {$product->name} has only {$product->stock_quantity} left.",
                    ]);
                }

                $unitPrice = isset($row['unit_price_paise']) ? (int) $row['unit_price_paise'] : $product->price_paise;
                $lineSubtotal = $unitPrice * $qty;
                $lineGst = (int) round($lineSubtotal * ((float) $product->gst_rate / 100));
                $lineTotal = $lineSubtotal + $lineGst;

                $subtotal += $lineSubtotal;
                $gstTotal += $lineGst;

                $saleRows[] = compact('product', 'qty', 'unitPrice', 'lineSubtotal', 'lineGst', 'lineTotal');
            }

            $discountPaise = min((int) $discountPaise, $subtotal + $gstTotal);
            $sale = PosSale::query()->create([
                'tenant_id' => $user->tenant_id,
                'branch_id' => $branchId,
                'bill_number' => $this->nextBillNumber($user->tenant_id),
                'member_id' => $data['member_id'] ?? null,
                'subtotal_paise' => $subtotal,
                'gst_paise' => $gstTotal,
                'discount_paise' => $discountPaise,
                'total_paise' => $subtotal + $gstTotal - $discountPaise,
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'sold_by' => $user->staffProfile?->id,
            ]);

            foreach ($saleRows as $row) {
                $row['product']->decrement('stock_quantity', $row['qty']);

                PosSaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $row['product']->id,
                    'product_name' => $row['product']->name,
                    'qty' => $row['qty'],
                    'unit_price_paise' => $row['unitPrice'],
                    'gst_rate' => $row['product']->gst_rate,
                    'line_subtotal_paise' => $row['lineSubtotal'],
                    'gst_paise' => $row['lineGst'],
                    'line_total_paise' => $row['lineTotal'],
                ]);

                PosStockMovement::query()->create([
                    'product_id' => $row['product']->id,
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $branchId,
                    'sale_id' => $sale->id,
                    'type' => 'sale',
                    'quantity' => -1 * $row['qty'],
                    'reference' => $sale->bill_number,
                    'movement_date' => now()->toDateString(),
                    'created_by' => $user->staffProfile?->id,
                ]);
            }

            return $sale->load(['branch', 'member', 'seller', 'items']);
        });
    }

    private function restockData(Request $request): array
    {
        return $request->validate([
            'product_id' => ['required', Rule::exists('pos_products', 'id')],
            'quantity' => ['required', 'integer', 'min:1'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'supplier' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);
    }

    private function performRestock(User $user, array $data): int
    {
        $this->ensureCanManageStock($user);

        return DB::transaction(function () use ($user, $data): int {
            $product = PosProduct::query()
                ->forTenant($user->tenant_id)
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $product->increment('stock_quantity', (int) $data['quantity']);
            $product->update(['cost_paise' => $this->rupeesToPaise($data['cost_price'])]);

            PosStockMovement::query()->create([
                'product_id' => $product->id,
                'tenant_id' => $user->tenant_id,
                'branch_id' => $this->filterBranchId($user, $data['branch_id'] ?? null),
                'type' => 'restock',
                'quantity' => (int) $data['quantity'],
                'cost_paise' => $this->rupeesToPaise($data['cost_price']),
                'reason' => $data['notes'] ?? $data['supplier'] ?? null,
                'reference' => $data['reference'] ?? null,
                'movement_date' => $data['date'],
                'created_by' => $user->staffProfile?->id,
            ]);

            return (int) $product->fresh()->stock_quantity;
        });
    }

    private function adjustData(Request $request): array
    {
        return $request->validate([
            'product_id' => ['required', Rule::exists('pos_products', 'id')],
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', Rule::in(PosStockMovement::ADJUSTMENT_REASONS)],
            'date' => ['required', 'date'],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('tenant_id', $request->user()->tenant_id)],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);
    }

    private function performAdjustment(User $user, array $data): int
    {
        $this->ensureCanManageStock($user);

        if ((int) $data['quantity_change'] < -5 && !in_array($user->role, ['tenant_owner', 'branch_manager'], true)) {
            abort(403, 'APPROVAL_REQUIRED');
        }

        return DB::transaction(function () use ($user, $data): int {
            $product = PosProduct::query()
                ->forTenant($user->tenant_id)
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $newQuantity = $product->stock_quantity + (int) $data['quantity_change'];
            if (!$this->allowsNegativeStock() && $newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity_change' => 'Stock cannot go negative.',
                ]);
            }

            $product->update(['stock_quantity' => $newQuantity]);

            PosStockMovement::query()->create([
                'product_id' => $product->id,
                'tenant_id' => $user->tenant_id,
                'branch_id' => $this->filterBranchId($user, $data['branch_id'] ?? null),
                'type' => 'adjustment',
                'quantity' => (int) $data['quantity_change'],
                'reason' => str($data['reason'])->replace('_', ' ')->title()->toString(),
                'reference' => $data['notes'] ?? null,
                'movement_date' => $data['date'],
                'created_by' => $user->staffProfile?->id,
            ]);

            return $newQuantity;
        });
    }

    private function nextBillNumber(int $tenantId): string
    {
        $count = PosSale::query()->where('tenant_id', $tenantId)->lockForUpdate()->count() + 1;

        return 'BILL-'.str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    private function salesSummary(User $user): array
    {
        $base = PosSale::query()->forTenant($user->tenant_id);

        return [
            'today_count' => (clone $base)->whereDate('created_at', now()->toDateString())->count(),
            'today_total_paise' => (clone $base)->whereDate('created_at', now()->toDateString())->sum('total_paise'),
            'month_total_paise' => (clone $base)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_paise'),
            'gst_paise' => (clone $base)->whereDate('created_at', now()->toDateString())->sum('gst_paise'),
        ];
    }

    private function dailyTally(User $user, string $date, ?int $branchId = null): array
    {
        $sales = PosSale::query()
            ->forTenant($user->tenant_id)
            ->with(['items.product', 'seller', 'branch'])
            ->whereDate('created_at', $date)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $byMethod = collect(PosSale::METHODS)->mapWithKeys(fn ($method) => [
            $method => (int) $sales->where('method', $method)->sum('total_paise'),
        ]);

        $byCategory = $sales
            ->flatMap(fn (PosSale $sale) => $sale->items->map(function (PosSaleItem $item) {
                return [
                    'category' => $item->product?->category_label ?? 'Unknown',
                    'total_paise' => $item->line_total_paise,
                ];
            }))
            ->groupBy('category')
            ->map(fn ($rows) => $rows->sum('total_paise'));

        $byStaff = $sales
            ->groupBy(fn (PosSale $sale) => $sale->seller?->name ?? 'Owner / system')
            ->map(fn ($rows) => $rows->sum('total_paise'));

        $byBranch = $sales
            ->groupBy(fn (PosSale $sale) => $sale->branch?->name ?? 'Unassigned')
            ->map(fn ($rows) => $rows->sum('total_paise'));

        return [
            'date' => $date,
            'by_method' => $byMethod,
            'by_category' => $byCategory,
            'by_staff' => $byStaff,
            'by_branch' => $byBranch,
            'gst_paise' => (int) $sales->sum('gst_paise'),
            'cash_paise' => (int) $sales->where('method', 'cash')->sum('total_paise'),
            'cashless_paise' => (int) $sales->whereIn('method', ['upi', 'card'])->sum('total_paise'),
        ];
    }

    private function branchOptions(User $user)
    {
        return Branch::query()
            ->forTenant($user->tenant_id)
            ->when($user->role === 'branch_manager', fn ($q) => $q->where('id', $user->branch_id))
            ->active()
            ->orderByRaw('is_primary DESC, name ASC')
            ->get();
    }

    private function checkoutBranchId(User $user, Request $request): ?int
    {
        return $this->checkoutBranchIdFromValue($user, $request->get('branch_id') ?: session('gymos_selected_branch_id'));
    }

    private function checkoutBranchIdFromValue(User $user, mixed $branchId): int
    {
        if ($user->role === 'branch_manager' || $user->role === 'pos') {
            return (int) $user->branch_id;
        }

        $resolved = filled($branchId)
            ? Branch::query()->forTenant($user->tenant_id)->active()->find((int) $branchId)
            : Branch::query()->forTenant($user->tenant_id)->active()->orderByRaw('is_primary DESC, id ASC')->first();

        abort_unless($resolved, 422, 'Select an active branch for billing.');

        return (int) $resolved->id;
    }

    private function filterBranchId(User $user, mixed $branchId): ?int
    {
        if ($user->role === 'branch_manager' || $user->role === 'pos') {
            return (int) $user->branch_id;
        }

        if (filled($branchId)) {
            return (int) $branchId;
        }

        return session('gymos_selected_branch_id') ? (int) session('gymos_selected_branch_id') : null;
    }

    private function resolveDiscountPaise(User $user, ?string $discountType, float $discountValue, array $items): int
    {
        if (!$discountType || $discountValue <= 0) {
            return 0;
        }

        $this->ensureCanApplyDiscount($user);

        $productMap = PosProduct::query()
            ->forTenant($user->tenant_id)
            ->whereIn('id', collect($items)->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        $subtotal = collect($items)->sum(function (array $item) use ($productMap) {
            $product = $productMap->get((int) $item['product_id']);
            if (!$product) {
                return 0;
            }

            $unitPrice = isset($item['unit_price_paise']) ? (int) $item['unit_price_paise'] : $product->price_paise;
            return $unitPrice * max(1, (int) $item['qty']);
        });

        return $discountType === 'percent'
            ? (int) round($subtotal * min(100, $discountValue) / 100)
            : $this->rupeesToPaise($discountValue);
    }

    private function allowsNegativeStock(): bool
    {
        return false;
    }

    private function rupeesToPaise(float|int|string $value): int
    {
        return (int) round(((float) $value) * 100);
    }

    private function ensureTenantProduct(User $user, PosProduct $product): void
    {
        abort_unless($product->tenant_id === $user->tenant_id, 403);
    }

    private function ensureTenantSale(User $user, PosSale $sale): void
    {
        abort_unless($sale->tenant_id === $user->tenant_id, 403);
        if (in_array($user->role, ['branch_manager', 'pos'], true)) {
            abort_unless($sale->branch_id === $user->branch_id, 403);
        }
    }

    private function ensureSalesAccess(User $user): void
    {
        abort_unless(in_array($user->role, ['tenant_owner', 'branch_manager', 'accountant', 'pos'], true), 403);
    }

    private function ensureStockAccess(User $user): void
    {
        $this->ensureSalesAccess($user);
    }

    private function ensureProductAccess(User $user): void
    {
        abort_unless(in_array($user->role, ['tenant_owner', 'branch_manager', 'accountant'], true), 403);
    }

    private function ensureCanManageProducts(User $user): void
    {
        abort_unless($this->canManageProducts($user), 403);
    }

    private function ensureCanManageStock(User $user): void
    {
        abort_unless($this->canManageStock($user), 403);
    }

    private function ensureCanApplyDiscount(User $user): void
    {
        abort_unless($this->canApplyDiscount($user), 403);
    }

    private function ensureCanRefund(User $user): void
    {
        abort_unless($this->canRefund($user), 403);
    }

    private function canManageProducts(User $user): bool
    {
        return in_array($user->role, ['tenant_owner', 'branch_manager', 'accountant'], true);
    }

    private function canManageStock(User $user): bool
    {
        return in_array($user->role, ['tenant_owner', 'branch_manager', 'accountant'], true);
    }

    private function canApplyDiscount(User $user): bool
    {
        return in_array($user->role, ['tenant_owner', 'branch_manager'], true);
    }

    private function canRefund(User $user): bool
    {
        return in_array($user->role, ['tenant_owner', 'branch_manager', 'accountant'], true);
    }
}
