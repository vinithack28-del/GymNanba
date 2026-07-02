<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentServiceRecord;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EquipmentController extends Controller
{
    public function create(Request $request){
        abort_unless($request->user()->canAccess('equipment.add'), 403);

        return Inertia::render('Tenant/Equipment/Form', [
            'types' => Equipment::TYPES,
            'statuses' => Equipment::STATUSES,
        ]);
    }

    public function index(Request $request){
        abort_unless($request->user()->canAccess('equipment.view'), 403);

        $tenantId = $request->user()->tenant->id;

        $query = Equipment::with('branch')
            ->where('tenant_id', $tenantId);

        if ($branchId = $request->user()->effectiveBranchId()) {
            $query->where('branch_id', $branchId);
        }

        if ($search = $request->get('search')) {
            $s = '%' . $search . '%';
            $query->where(fn ($q) => $q->where('name', 'ilike', $s)
                ->orWhere('brand', 'ilike', $s)
                ->orWhere('model', 'ilike', $s));
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $equipment = $query->orderBy('name')->get();

        // Summary counts (unfiltered for same branch scope)
        $summaryBase = Equipment::where('tenant_id', $tenantId);
        if ($branchId = $request->user()->effectiveBranchId()) {
            $summaryBase->where('branch_id', $branchId);
        }

        $summary = [
            'total'       => (clone $summaryBase)->count(),
            'operational' => (clone $summaryBase)->where('status', 'operational')->count(),
            'maintenance' => (clone $summaryBase)->where('status', 'maintenance')->count(),
            'broken'      => (clone $summaryBase)->where('status', 'broken')->count(),
        ];

        $canAdd          = $request->user()->canAccess('equipment.add');
        $canEdit         = $request->user()->canAccess('equipment.edit');
        $canDelete       = $request->user()->canAccess('equipment.delete');
        $canServiceRecord = $request->user()->canAccess('equipment.service_record');

        return Inertia::render('Tenant/Equipment/Index', compact(
            'equipment', 'summary', 'canAdd', 'canEdit', 'canDelete', 'canServiceRecord'
        ));
    }

    public function details(Request $request, Equipment $equipment): JsonResponse
    {
        $this->authorizeEquipment($request, $equipment);
        abort_unless($request->user()->canAccess('equipment.view'), 403);

        $equipment->load(['branch', 'serviceRecords']);

        return response()->json($this->equipmentPayload($equipment));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless($request->user()->canAccess('equipment.add'), 403);

        $validated = $request->validate([
            'name'                  => 'required|string|min:2|max:150',
            'type'                  => 'required|in:cardio,strength,free_weights,functional,other',
            'brand'                 => 'nullable|string|max:100',
            'model'                 => 'nullable|string|max:100',
            'purchase_date'         => 'nullable|date|before_or_equal:today',
            'warranty_expiry'       => 'nullable|date',
            'purchase_price'        => 'nullable|numeric|min:0',
            'status'                => 'nullable|in:operational,maintenance,broken',
            'location'              => 'nullable|string|max:200',
            'notes'                 => 'nullable|string|max:1000',
            'branch_id'             => 'nullable|exists:branches,id',
        ]);

        $tenantId = $request->user()->tenant->id;
        $staff    = Staff::where('user_id', $request->user()->id)->where('tenant_id', $tenantId)->first();

        $equipment = Equipment::create([
            'tenant_id'             => $tenantId,
            'branch_id'             => $validated['branch_id'] ?? $request->user()->effectiveBranchId(),
            'name'                  => $validated['name'],
            'type'                  => $validated['type'],
            'brand'                 => $validated['brand'] ?? null,
            'model'                 => $validated['model'] ?? null,
            'purchase_date'         => $validated['purchase_date'] ?? null,
            'warranty_expiry'       => $validated['warranty_expiry'] ?? null,
            'purchase_price_paise'  => isset($validated['purchase_price'])
                                        ? (int) round($validated['purchase_price'] * 100)
                                        : null,
            'status'                => $validated['status'] ?? 'operational',
            'location'              => $validated['location'] ?? null,
            'notes'                 => $validated['notes'] ?? null,
            'created_by'            => $staff?->id,
        ]);

        $equipment->load('branch');

        if (! $request->expectsJson()) {
            return redirect()
                ->route('tenant.equipment.index')
                ->with('status', 'Equipment added successfully.');
        }

        return response()->json($this->equipmentPayload($equipment), 201);
    }

    public function update(Request $request, Equipment $equipment): JsonResponse
    {
        $this->authorizeEquipment($request, $equipment);
        abort_unless($request->user()->canAccess('equipment.edit'), 403);

        $validated = $request->validate([
            'name'                  => 'required|string|min:2|max:150',
            'type'                  => 'required|in:cardio,strength,free_weights,functional,other',
            'brand'                 => 'nullable|string|max:100',
            'model'                 => 'nullable|string|max:100',
            'purchase_date'         => 'nullable|date|before_or_equal:today',
            'warranty_expiry'       => 'nullable|date',
            'purchase_price'        => 'nullable|numeric|min:0',
            'status'                => 'required|in:operational,maintenance,broken',
            'location'              => 'nullable|string|max:200',
            'notes'                 => 'nullable|string|max:1000',
        ]);

        $equipment->update([
            'name'                  => $validated['name'],
            'type'                  => $validated['type'],
            'brand'                 => $validated['brand'] ?? null,
            'model'                 => $validated['model'] ?? null,
            'purchase_date'         => $validated['purchase_date'] ?? null,
            'warranty_expiry'       => $validated['warranty_expiry'] ?? null,
            'purchase_price_paise'  => isset($validated['purchase_price'])
                                        ? (int) round($validated['purchase_price'] * 100)
                                        : null,
            'status'                => $validated['status'],
            'location'              => $validated['location'] ?? null,
            'notes'                 => $validated['notes'] ?? null,
        ]);

        $equipment->load(['branch', 'serviceRecords']);

        return response()->json($this->equipmentPayload($equipment));
    }

    public function destroy(Request $request, Equipment $equipment): JsonResponse
    {
        $this->authorizeEquipment($request, $equipment);
        abort_unless($request->user()->canAccess('equipment.delete'), 403);

        $equipment->delete();

        return response()->json(['deleted' => true]);
    }

    public function storeServiceRecord(Request $request, Equipment $equipment): JsonResponse
    {
        $this->authorizeEquipment($request, $equipment);
        abort_unless($request->user()->canAccess('equipment.service_record'), 403);

        $validated = $request->validate([
            'service_date'     => 'required|date|before_or_equal:today',
            'service_type'     => 'required|in:maintenance,repair,inspection,calibration,cleaning,replacement',
            'cost'             => 'required|numeric|min:0',
            'service_provider' => 'nullable|string|max:200',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $tenantId = $request->user()->tenant->id;
        $staff    = Staff::where('user_id', $request->user()->id)->where('tenant_id', $tenantId)->first();

        $record = EquipmentServiceRecord::create([
            'equipment_id'     => $equipment->id,
            'tenant_id'        => $tenantId,
            'service_date'     => $validated['service_date'],
            'service_type'     => $validated['service_type'],
            'cost_paise'       => (int) round($validated['cost'] * 100),
            'service_provider' => $validated['service_provider'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'created_by'       => $staff?->id,
        ]);

        return response()->json($this->recordPayload($record), 201);
    }

    public function destroyServiceRecord(Request $request, Equipment $equipment, EquipmentServiceRecord $record): JsonResponse
    {
        $this->authorizeEquipment($request, $equipment);
        abort_unless($request->user()->canAccess('equipment.service_record'), 403);
        abort_unless($record->equipment_id === $equipment->id, 404);

        $record->delete();

        return response()->json(['deleted' => true]);
    }

    // ── Summary endpoint for stats refresh ────────────────────────────────────

    public function summary(Request $request): JsonResponse
    {
        abort_unless($request->user()->canAccess('equipment.view'), 403);

        $tenantId = $request->user()->tenant->id;
        $base = Equipment::where('tenant_id', $tenantId);
        if ($branchId = $request->user()->effectiveBranchId()) {
            $base->where('branch_id', $branchId);
        }

        return response()->json([
            'total'       => (clone $base)->count(),
            'operational' => (clone $base)->where('status', 'operational')->count(),
            'maintenance' => (clone $base)->where('status', 'maintenance')->count(),
            'broken'      => (clone $base)->where('status', 'broken')->count(),
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function authorizeEquipment(Request $request, Equipment $equipment): void
    {
        abort_unless($equipment->tenant_id === $request->user()->tenant?->id, 404);
    }

    private function equipmentPayload(Equipment $equipment): array
    {
        $types    = Equipment::TYPES;
        $statuses = Equipment::STATUSES;

        return [
            'id'                   => $equipment->id,
            'name'                 => $equipment->name,
            'type'                 => $equipment->type,
            'type_label'           => $types[$equipment->type] ?? $equipment->type,
            'brand'                => $equipment->brand,
            'model'                => $equipment->model,
            'purchase_date'        => $equipment->purchase_date?->toDateString(),
            'purchase_date_fmt'    => $equipment->purchase_date?->format('d M Y'),
            'warranty_expiry'      => $equipment->warranty_expiry?->toDateString(),
            'warranty_expiry_fmt'  => $equipment->warranty_expiry?->format('d M Y'),
            'warranty_expired'     => $equipment->isWarrantyExpired(),
            'purchase_price_paise' => $equipment->purchase_price_paise,
            'purchase_price_fmt'   => $equipment->purchase_price_paise !== null
                                        ? '₹' . number_format($equipment->purchase_price_paise / 100, 0)
                                        : null,
            'status'               => $equipment->status,
            'status_label'         => $statuses[$equipment->status] ?? $equipment->status,
            'location'             => $equipment->location,
            'notes'                => $equipment->notes,
            'branch_name'          => $equipment->branch?->name,
            'created_at'           => $equipment->created_at?->format('d M Y'),
            'service_records'      => $equipment->serviceRecords->map(fn ($r) => $this->recordPayload($r))->values(),
        ];
    }

    private function recordPayload(EquipmentServiceRecord $record): array
    {
        $types = EquipmentServiceRecord::TYPES;
        return [
            'id'               => $record->id,
            'service_date'     => $record->service_date->toDateString(),
            'service_date_fmt' => $record->service_date->format('d M Y'),
            'service_type'     => $record->service_type,
            'service_type_label' => $types[$record->service_type] ?? $record->service_type,
            'cost_paise'       => $record->cost_paise,
            'cost_fmt'         => '₹' . number_format($record->cost_paise / 100, 0),
            'service_provider' => $record->service_provider,
            'notes'            => $record->notes,
        ];
    }
}
