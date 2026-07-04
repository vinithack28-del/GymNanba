<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberAssessment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class AssessmentController extends Controller
{
    private const PARQ_QUESTIONS = [
        1 => 'Has your doctor ever said that you have a heart condition OR high blood pressure?',
        2 => 'Do you feel pain in your chest at rest, during your daily activities of living, OR when you do physical activity?',
        3 => 'Do you lose balance because of dizziness OR have you lost consciousness in the last 12 months?',
        4 => 'Have you ever been diagnosed with another chronic medical condition (other than heart disease or high blood pressure)?',
        5 => 'Are you currently taking prescribed medications for a chronic medical condition?',
        6 => 'Do you currently have (or have had within the past 12 months) a bone, joint, or soft-tissue problem that could be made worse by becoming more physically active?',
        7 => 'Has your doctor ever said that you should only do medically supervised physical activity?',
    ];

    private const PARQ_FOLLOWUPS = [
        ['key' => 'heart_symptoms', 'label' => 'Do chest pain, palpitations, or breathlessness occur during mild activity?', 'trigger' => 1],
        ['key' => 'chest_clearance', 'label' => 'Has a doctor asked you to limit exercise until review?', 'trigger' => 2],
        ['key' => 'dizziness_falls', 'label' => 'Have dizziness episodes caused falls, blackouts, or instability recently?', 'trigger' => 3],
        ['key' => 'chronic_condition_active', 'label' => 'Is the chronic condition currently active or unstable?', 'trigger' => 4],
        ['key' => 'medication_limitations', 'label' => 'Do your current medications affect exercise tolerance, blood pressure, or balance?', 'trigger' => 5],
        ['key' => 'joint_activity_limit', 'label' => 'Does the bone/joint/soft-tissue issue currently limit movement or cause pain with exercise?', 'trigger' => 6],
        ['key' => 'supervised_only', 'label' => 'Has supervised activity been recommended in the last 12 months?', 'trigger' => 7],
    ];

    public function report(Request $request){
        $this->authorizeModule($request->user(), 'assessment_report', 'view');

        $member = $this->selectedMember($request);
        $records = $member ? $this->latestAssessmentRecords($request->user(), $member) : collect();

        return Inertia::render('Tenant/Assess/Report', $this->baseViewData($request, 'assessment_report', $member) + [
            'records' => $records,
            'summary' => $member ? $this->reportSummary($records) : null,
        ]);
    }

    public function questionnaire(Request $request){
        $this->authorizeModule($request->user(), 'parq', 'view');

        $query = $this->baseRecordQuery($request->user(), MemberAssessment::TYPE_PARQ)
            ->with('member')
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%' . $this->normalizePhoneTerm((string) $request->search) . '%';
                $q->whereHas('member', fn ($mq) => $mq->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term));
            })
            ->when($request->filled('risk_level'), function ($q) use ($request): void {
                $status = match ($request->risk_level) {
                    'low' => 'cleared',
                    'moderate' => 'conditional',
                    'high' => 'medical_clearance_required',
                    default => null,
                };
                if ($status) {
                    $q->where('status', $status);
                }
            })
            ->orderByDesc('updated_at');

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100], true) ? (int) $request->get('per_page') : 25;

        return Inertia::render('Tenant/Assess/Questionnaire', $this->baseViewData($request, 'parq', null) + [
            'records' => $query->paginate($perPage)->withQueryString(),
        ]);
    }

    public function questionnaireCreate(Request $request){
        $this->authorizeModule($request->user(), 'parq', 'add');

        $member = $this->selectedMember($request);
        $record = $member ? $this->singleRecord($request->user(), $member->id, MemberAssessment::TYPE_PARQ) : null;

        return Inertia::render('Tenant/Assess/QuestionnaireForm', $this->baseViewData($request, 'parq', $member) + [
            'record' => $record,
            'questions' => self::PARQ_QUESTIONS,
            'followups' => self::PARQ_FOLLOWUPS,
        ]);
    }

    public function questionnaireEdit(Request $request, MemberAssessment $record){
        $this->authorizeRecord($request->user(), 'parq', 'edit', $record);
        $record->load('member');

        return Inertia::render('Tenant/Assess/QuestionnaireForm', $this->baseViewData($request, 'parq', $record->member) + [
            'record' => $record,
            'questions' => self::PARQ_QUESTIONS,
            'followups' => self::PARQ_FOLLOWUPS,
        ]);
    }

    public function saveQuestionnaire(Request $request): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'parq', $request->integer('member_id'), $request->route('record'));

        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'section1' => ['required', 'array', 'size:7'],
            'section2' => ['nullable', 'array'],
        ]);

        $section1 = collect(self::PARQ_QUESTIONS)->map(function (string $label, int $id) use ($validated): array {
            return [
                'question_id' => $id,
                'question_text' => $label,
                'answer' => filter_var($validated['section1'][$id] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ];
        });

        if ($section1->contains(fn (array $row) => $row['answer'] === null)) {
            return back()->withErrors(['section1' => 'Answer all PAR-Q+ questions.'])->withInput();
        }

        $section2 = collect(self::PARQ_FOLLOWUPS)->map(function (array $row) use ($validated, $section1): array {
            $triggered = (bool) optional($section1->firstWhere('question_id', $row['trigger']))['answer'];
            return [
                'key' => $row['key'],
                'question_text' => $row['label'],
                'trigger' => $row['trigger'],
                'answer' => $triggered
                    ? filter_var(data_get($validated, 'section2.' . $row['key']), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                    : null,
            ];
        });

        $positiveInitial = $section1->contains(fn (array $row) => $row['answer'] === true);
        $followupRisk = $section2->contains(fn (array $row) => $row['answer'] === true);
        $status = ! $positiveInitial
            ? 'cleared'
            : ($followupRisk ? 'medical_clearance_required' : 'conditional');

        $record = $this->singleRecord($request->user(), $member->id, MemberAssessment::TYPE_PARQ);
        $attributes = [
            'tenant_id' => $request->user()->tenant_id,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'type' => MemberAssessment::TYPE_PARQ,
            'title' => 'PAR-Q+ Questionnaire',
            'status' => $status,
            'assessment_date' => today()->toDateString(),
            'payload' => [
                'section1' => $section1->values()->all(),
                'section2' => $section2->values()->all(),
                'flags' => $this->parqFlags($status),
            ],
            'notes' => null,
            'updated_by' => $request->user()->id,
        ];

        if ($record) {
            $record->update($attributes);
        } else {
            MemberAssessment::create($attributes + ['created_by' => $request->user()->id]);
        }

        return redirect()->route('tenant.assess.questionnaire')
            ->with('status', 'PAR-Q+ questionnaire saved.');
    }

    public function nutrition(Request $request){
        $this->authorizeModule($request->user(), 'nutrition', 'view');

        $member = $this->selectedMember($request);
        $plans = $member
            ? $this->recordsForMember($request->user(), $member->id, MemberAssessment::TYPE_NUTRITION)
            : collect();
        $editing = $member && $request->filled('edit')
            ? $plans->firstWhere('id', $request->integer('edit'))
            : null;

        return Inertia::render('Tenant/Assess/Nutrition', $this->baseViewData($request, 'nutrition', $member) + [
            'plans' => $plans,
            'editingPlan' => $editing,
        ]);
    }

    public function storeNutrition(Request $request): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'nutrition', $request->integer('member_id'));
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'plan_name' => ['required', 'string', 'min:2', 'max:100'],
            'plan_date' => ['required', 'date', 'after_or_equal:' . now()->subYear()->toDateString()],
            'goal_notes' => ['nullable', 'string', 'max:500'],
            'meals' => ['required', 'array', 'min:1'],
            'meals.*.meal_name' => ['required', 'string', 'max:100'],
            'meals.*.time' => ['nullable', 'date_format:H:i'],
            'meals.*.food_items' => ['required', 'string', 'max:1000'],
            'meals.*.calories' => ['nullable', 'numeric', 'min:0'],
            'meals.*.protein_g' => ['nullable', 'numeric', 'min:0'],
            'meals.*.carbs_g' => ['nullable', 'numeric', 'min:0'],
            'meals.*.fat_g' => ['nullable', 'numeric', 'min:0'],
            'meals.*.notes' => ['nullable', 'string', 'max:200'],
        ]);

        MemberAssessment::create([
            'tenant_id' => $request->user()->tenant_id,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'type' => MemberAssessment::TYPE_NUTRITION,
            'title' => $validated['plan_name'],
            'assessment_date' => $validated['plan_date'],
            'payload' => ['meals' => array_values($validated['meals'])],
            'notes' => $validated['goal_notes'] ?? null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.nutrition', ['member_id' => $member->id])
            ->with('status', 'Diet plan added.');
    }

    public function updateNutrition(Request $request, MemberAssessment $record): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'nutrition', $request->integer('member_id'), $record);
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'plan_name' => ['required', 'string', 'min:2', 'max:100'],
            'plan_date' => ['required', 'date', 'after_or_equal:' . now()->subYear()->toDateString()],
            'goal_notes' => ['nullable', 'string', 'max:500'],
            'meals' => ['required', 'array', 'min:1'],
            'meals.*.meal_name' => ['required', 'string', 'max:100'],
            'meals.*.time' => ['nullable', 'date_format:H:i'],
            'meals.*.food_items' => ['required', 'string', 'max:1000'],
            'meals.*.calories' => ['nullable', 'numeric', 'min:0'],
            'meals.*.protein_g' => ['nullable', 'numeric', 'min:0'],
            'meals.*.carbs_g' => ['nullable', 'numeric', 'min:0'],
            'meals.*.fat_g' => ['nullable', 'numeric', 'min:0'],
            'meals.*.notes' => ['nullable', 'string', 'max:200'],
        ]);

        $record->update([
            'title' => $validated['plan_name'],
            'assessment_date' => $validated['plan_date'],
            'payload' => ['meals' => array_values($validated['meals'])],
            'notes' => $validated['goal_notes'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.nutrition', ['member_id' => $member->id])
            ->with('status', 'Diet plan updated.');
    }

    public function bodyMetrics(Request $request){
        $this->authorizeModule($request->user(), 'body_metrics', 'view');

        $query = $this->baseRecordQuery($request->user(), MemberAssessment::TYPE_BODY_METRICS)
            ->with('member')
            ->when($request->filled('member_id'), fn ($q) => $q->where('member_id', $request->integer('member_id')))
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%' . $this->normalizePhoneTerm((string) $request->search) . '%';
                $q->whereHas('member', fn ($mq) => $mq->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term)->orWhere('email', 'ilike', $term));
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('assessment_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('assessment_date', '<=', $request->to))
            ->when($request->filled('next_measurement_date'), fn ($q) => $q->whereDate('next_assessment_date', '<=', $request->next_measurement_date))
            ->orderByDesc('assessment_date')
            ->orderByDesc('id');

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100], true) ? (int) $request->get('per_page') : 25;

        return Inertia::render('Tenant/Assess/BodyMetrics', $this->baseViewData($request, 'body_metrics', $this->selectedMember($request)) + [
            'records' => $query->paginate($perPage)->withQueryString(),
            'editingRecord' => $request->filled('edit')
                ? $this->baseRecordQuery($request->user(), MemberAssessment::TYPE_BODY_METRICS)->find($request->integer('edit'))
                : null,
        ]);
    }

    public function storeBodyMetrics(Request $request): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'body_metrics', $request->integer('member_id'));
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'weight_kg' => ['required', 'numeric', 'min:1', 'max:500'],
            'height_cm' => ['required', 'numeric', 'min:50', 'max:300'],
            'waist_cm' => ['nullable', 'numeric', 'min:0'],
            'hip_cm' => ['nullable', 'numeric', 'min:0'],
            'neck_cm' => ['nullable', 'numeric', 'min:0'],
            'body_fat_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'next_measurement_date' => ['nullable', 'date', 'after:measurement_date'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $payload = $validated;
        $payload['bmi'] = $this->bmi((float) $validated['weight_kg'], (float) $validated['height_cm']);
        $payload['bmi_category'] = $this->bmiCategory((float) $payload['bmi']);

        MemberAssessment::create([
            'tenant_id' => $request->user()->tenant_id,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'type' => MemberAssessment::TYPE_BODY_METRICS,
            'title' => 'Body Metrics',
            'assessment_date' => $validated['measurement_date'],
            'next_assessment_date' => $validated['next_measurement_date'] ?? null,
            'payload' => $payload,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.body-metrics', ['member_id' => $member->id])
            ->with('status', 'Body metrics added.');
    }

    public function updateBodyMetrics(Request $request, MemberAssessment $record): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'body_metrics', $request->integer('member_id'), $record);
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'weight_kg' => ['required', 'numeric', 'min:1', 'max:500'],
            'height_cm' => ['required', 'numeric', 'min:50', 'max:300'],
            'waist_cm' => ['nullable', 'numeric', 'min:0'],
            'hip_cm' => ['nullable', 'numeric', 'min:0'],
            'neck_cm' => ['nullable', 'numeric', 'min:0'],
            'body_fat_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'next_measurement_date' => ['nullable', 'date', 'after:measurement_date'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $payload = $validated;
        $payload['bmi'] = $this->bmi((float) $validated['weight_kg'], (float) $validated['height_cm']);
        $payload['bmi_category'] = $this->bmiCategory((float) $payload['bmi']);

        $record->update([
            'assessment_date' => $validated['measurement_date'],
            'next_assessment_date' => $validated['next_measurement_date'] ?? null,
            'payload' => $payload,
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.body-metrics', ['member_id' => $member->id])
            ->with('status', 'Body metrics updated.');
    }

    public function bodyMetricsProgress(Request $request){
        $this->authorizeModule($request->user(), 'body_metrics', 'view');
        $member = $this->selectedMember($request);
        $records = $member
            ? $this->recordsForMember($request->user(), $member->id, MemberAssessment::TYPE_BODY_METRICS)
            : collect();

        return Inertia::render('Tenant/Assess/BodyMetricsProgress', $this->baseViewData($request, 'body_metrics', $member) + [
            'records' => $records,
        ]);
    }

    public function posture(Request $request){
        $this->authorizeModule($request->user(), 'posture', 'view');

        $query = $this->baseRecordQuery($request->user(), MemberAssessment::TYPE_POSTURE)
            ->with('member')
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%' . $this->normalizePhoneTerm((string) $request->search) . '%';
                $q->whereHas('member', fn ($mq) => $mq->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term));
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('assessment_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('assessment_date', '<=', $request->to))
            ->orderByDesc('assessment_date')
            ->orderByDesc('id');

        $records = $query->paginate(25)->withQueryString();

        return Inertia::render('Tenant/Assess/Posture', $this->baseViewData($request, 'posture', $this->selectedMember($request)) + [
            'records' => $records,
            'editingRecord' => $request->filled('edit')
                ? $this->baseRecordQuery($request->user(), MemberAssessment::TYPE_POSTURE)->find($request->integer('edit'))
                : null,
            'summary' => [
                'total' => $query->count(),
                'this_month' => $this->baseRecordQuery($request->user(), MemberAssessment::TYPE_POSTURE)->whereMonth('assessment_date', now()->month)->whereYear('assessment_date', now()->year)->count(),
                'last_month' => $this->baseRecordQuery($request->user(), MemberAssessment::TYPE_POSTURE)->whereMonth('assessment_date', now()->copy()->subMonth()->month)->whereYear('assessment_date', now()->copy()->subMonth()->year)->count(),
            ],
        ]);
    }

    public function storePosture(Request $request): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'posture', $request->integer('member_id'));
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'assessment_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'in:reviewed,pending_review'],
            'head_alignment' => ['nullable', 'string', 'max:100'],
            'shoulder_alignment' => ['nullable', 'string', 'max:100'],
            'spine_curvature' => ['nullable', 'string', 'max:100'],
            'hip_tilt' => ['nullable', 'string', 'max:100'],
            'knee_alignment' => ['nullable', 'string', 'max:100'],
            'foot_position' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        MemberAssessment::create([
            'tenant_id' => $request->user()->tenant_id,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'type' => MemberAssessment::TYPE_POSTURE,
            'title' => 'Posture Assessment',
            'status' => $validated['status'],
            'assessment_date' => $validated['assessment_date'],
            'payload' => collect($validated)->except(['member_id', 'assessment_date', 'status', 'notes'])->all(),
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.posture')->with('status', 'Posture assessment saved.');
    }

    public function updatePosture(Request $request, MemberAssessment $record): RedirectResponse
    {
        $this->memberForWrite($request, 'posture', $request->integer('member_id'), $record);
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'assessment_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'in:reviewed,pending_review'],
            'head_alignment' => ['nullable', 'string', 'max:100'],
            'shoulder_alignment' => ['nullable', 'string', 'max:100'],
            'spine_curvature' => ['nullable', 'string', 'max:100'],
            'hip_tilt' => ['nullable', 'string', 'max:100'],
            'knee_alignment' => ['nullable', 'string', 'max:100'],
            'foot_position' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $record->update([
            'status' => $validated['status'],
            'assessment_date' => $validated['assessment_date'],
            'payload' => collect($validated)->except(['member_id', 'assessment_date', 'status', 'notes'])->all(),
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.posture')->with('status', 'Posture assessment updated.');
    }

    public function balance(Request $request){
        $this->authorizeModule($request->user(), 'balance', 'view');
        $member = $this->selectedMember($request);
        $records = $member
            ? $this->recordsForMember($request->user(), $member->id, MemberAssessment::TYPE_BALANCE)
            : collect();
        $editing = $member && $request->filled('edit') ? $records->firstWhere('id', $request->integer('edit')) : null;

        return Inertia::render('Tenant/Assess/Balance', $this->baseViewData($request, 'balance', $member) + [
            'records' => $records,
            'editingRecord' => $editing,
        ]);
    }

    public function storeBalance(Request $request): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'balance', $request->integer('member_id'));
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'next_measurement_date' => ['nullable', 'date', 'after:measurement_date'],
            'limb_length_cm' => ['required', 'numeric', 'min:0.1'],
            'right_anterior' => ['required', 'numeric', 'min:0.1'],
            'right_posteromedial' => ['required', 'numeric', 'min:0.1'],
            'right_posterolateral' => ['required', 'numeric', 'min:0.1'],
            'left_anterior' => ['required', 'numeric', 'min:0.1'],
            'left_posteromedial' => ['required', 'numeric', 'min:0.1'],
            'left_posterolateral' => ['required', 'numeric', 'min:0.1'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $payload = $this->balancePayload($validated);

        MemberAssessment::create([
            'tenant_id' => $request->user()->tenant_id,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'type' => MemberAssessment::TYPE_BALANCE,
            'title' => 'Y-Balance Test',
            'status' => $payload['overall_status'],
            'assessment_date' => $validated['measurement_date'],
            'next_assessment_date' => $validated['next_measurement_date'] ?? null,
            'payload' => $payload,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.balance', ['member_id' => $member->id])->with('status', 'Balance assessment saved.');
    }

    public function updateBalance(Request $request, MemberAssessment $record): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'balance', $request->integer('member_id'), $record);
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'next_measurement_date' => ['nullable', 'date', 'after:measurement_date'],
            'limb_length_cm' => ['required', 'numeric', 'min:0.1'],
            'right_anterior' => ['required', 'numeric', 'min:0.1'],
            'right_posteromedial' => ['required', 'numeric', 'min:0.1'],
            'right_posterolateral' => ['required', 'numeric', 'min:0.1'],
            'left_anterior' => ['required', 'numeric', 'min:0.1'],
            'left_posteromedial' => ['required', 'numeric', 'min:0.1'],
            'left_posterolateral' => ['required', 'numeric', 'min:0.1'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $payload = $this->balancePayload($validated);

        $record->update([
            'status' => $payload['overall_status'],
            'assessment_date' => $validated['measurement_date'],
            'next_assessment_date' => $validated['next_measurement_date'] ?? null,
            'payload' => $payload,
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.balance', ['member_id' => $member->id])->with('status', 'Balance assessment updated.');
    }

    public function generateBalanceInsight(Request $request, MemberAssessment $record): RedirectResponse
    {
        $this->authorizeRecord($request->user(), 'balance', 'edit', $record);

        $payload = $record->payload ?? [];
        $record->update([
            'ai_insight' => $this->buildBalanceInsight($payload),
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Insight generated.');
    }

    public function vitals(Request $request){
        $this->authorizeModule($request->user(), 'vitals', 'view');
        $member = $this->selectedMember($request);
        $records = $member
            ? $this->recordsForMember($request->user(), $member->id, MemberAssessment::TYPE_VITALS)
            : collect();
        $editing = $member && $request->filled('edit') ? $records->firstWhere('id', $request->integer('edit')) : null;

        return Inertia::render('Tenant/Assess/Vitals', $this->baseViewData($request, 'vitals', $member) + [
            'records' => $records,
            'editingRecord' => $editing,
        ]);
    }

    public function storeVitals(Request $request): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'vitals', $request->integer('member_id'));
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'hr_bpm' => ['required', 'integer', 'min:20', 'max:250'],
            'bp_systolic' => ['required', 'integer', 'min:50', 'max:300'],
            'bp_diastolic' => ['required', 'integer', 'min:20', 'max:200', 'lt:bp_systolic'],
            'next_check_date' => ['nullable', 'date', 'after:measurement_date'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        MemberAssessment::create([
            'tenant_id' => $request->user()->tenant_id,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'type' => MemberAssessment::TYPE_VITALS,
            'title' => 'Vitals Check',
            'assessment_date' => $validated['measurement_date'],
            'next_assessment_date' => $validated['next_check_date'] ?? null,
            'payload' => collect($validated)->except(['member_id', 'measurement_date', 'next_check_date', 'notes'])->all(),
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.vitals', ['member_id' => $member->id])->with('status', 'Vitals record added.');
    }

    public function updateVitals(Request $request, MemberAssessment $record): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'vitals', $request->integer('member_id'), $record);
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'hr_bpm' => ['required', 'integer', 'min:20', 'max:250'],
            'bp_systolic' => ['required', 'integer', 'min:50', 'max:300'],
            'bp_diastolic' => ['required', 'integer', 'min:20', 'max:200', 'lt:bp_systolic'],
            'next_check_date' => ['nullable', 'date', 'after:measurement_date'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $record->update([
            'assessment_date' => $validated['measurement_date'],
            'next_assessment_date' => $validated['next_check_date'] ?? null,
            'payload' => collect($validated)->except(['member_id', 'measurement_date', 'next_check_date', 'notes'])->all(),
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.vitals', ['member_id' => $member->id])->with('status', 'Vitals record updated.');
    }

    public function fitness(Request $request){
        $this->authorizeModule($request->user(), 'fitness', 'view');
        $member = $this->selectedMember($request);
        $tab = in_array($request->get('tab'), ['cardio', 'strength', 'endurance', 'flexibility'], true)
            ? $request->get('tab')
            : 'cardio';
        $type = $this->fitnessType($tab);
        $records = $member ? $this->recordsForMember($request->user(), $member->id, $type) : collect();
        $editing = $member && $request->filled('edit') ? $records->firstWhere('id', $request->integer('edit')) : null;

        return Inertia::render('Tenant/Assess/Fitness', $this->baseViewData($request, 'fitness', $member) + [
            'tab' => $tab,
            'records' => $records,
            'editingRecord' => $editing,
        ]);
    }

    public function storeFitness(Request $request): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'fitness', $request->integer('member_id'));
        $tab = in_array($request->input('tab'), ['cardio', 'strength', 'endurance', 'flexibility'], true)
            ? $request->input('tab')
            : 'cardio';

        $validated = $request->validate($this->fitnessRules($tab));
        $payload = $this->fitnessPayload($member, $tab, $validated);

        MemberAssessment::create([
            'tenant_id' => $request->user()->tenant_id,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'type' => $this->fitnessType($tab),
            'title' => $payload['title'],
            'status' => $payload['status'] ?? null,
            'assessment_date' => $validated['measurement_date'],
            'next_assessment_date' => $validated['next_measurement_date'] ?? null,
            'payload' => $payload['payload'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.fitness', ['member_id' => $member->id, 'tab' => $tab])->with('status', 'Fitness test saved.');
    }

    public function updateFitness(Request $request, MemberAssessment $record): RedirectResponse
    {
        $member = $this->memberForWrite($request, 'fitness', $request->integer('member_id'), $record);
        $tab = in_array($request->input('tab'), ['cardio', 'strength', 'endurance', 'flexibility'], true)
            ? $request->input('tab')
            : 'cardio';

        $validated = $request->validate($this->fitnessRules($tab));
        $payload = $this->fitnessPayload($member, $tab, $validated);

        $record->update([
            'type' => $this->fitnessType($tab),
            'title' => $payload['title'],
            'status' => $payload['status'] ?? null,
            'assessment_date' => $validated['measurement_date'],
            'next_assessment_date' => $validated['next_measurement_date'] ?? null,
            'payload' => $payload['payload'],
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('tenant.assess.fitness', ['member_id' => $member->id, 'tab' => $tab])->with('status', 'Fitness test updated.');
    }

    public function goalForecasting(Request $request){
        $this->authorizeModule($request->user(), 'goal_forecasting', 'view');
        $member = $this->selectedMember($request);
        $result = null;

        if ($member && $request->filled('calculate')) {
            $validated = $request->validate([
                'member_id' => ['required', 'integer'],
                'current_weight_kg' => ['required', 'numeric', 'min:0.1'],
                'goal_type' => ['required', 'in:weight_loss,weight_gain,maintain'],
                'target_weight_kg' => ['required', 'numeric', 'min:0.1'],
                'weekly_rate' => ['required', 'in:slow,recommended,extreme'],
            ]);

            $result = $this->goalForecast($member, $validated);
        }

        return Inertia::render('Tenant/Assess/GoalForecasting', $this->baseViewData($request, 'goal_forecasting', $member) + [
            'result' => $result,
            'latestBodyMetrics' => $member ? $this->latestRecord($request->user(), $member->id, MemberAssessment::TYPE_BODY_METRICS) : null,
        ]);
    }

    public function destroy(Request $request, MemberAssessment $record): RedirectResponse
    {
        $module = $this->moduleForType($record->type);
        $this->authorizeRecord($request->user(), $module, 'delete', $record);

        $request->validate([
            'confirm_name' => ['required', 'in:' . $record->member->name],
        ]);

        $memberId = $record->member_id;
        $tab = $request->input('tab');
        $route = $this->routeForModule($module);
        $record->delete();

        $params = array_filter([
            'member_id' => $memberId,
            'tab' => $tab,
        ]);

        return redirect()->route($route, $params)->with('status', 'Assessment record deleted.');
    }

    public function memberSearch(Request $request): JsonResponse
    {
        abort_unless($request->user()->canAccess(
            'assessment_report.view|parq.view|nutrition.view|body_metrics.view|posture.view|balance.view|vitals.view|fitness.view|goal_forecasting.view'
        ), 403);

        $term = trim((string) $request->get('q'));
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $term = $this->normalizePhoneTerm($term);

        $members = $this->eligibleMembersQuery($request->user())
            ->where(function ($q) use ($term): void {
                $like = '%' . $term . '%';
                $q->where('name', 'ilike', $like)
                    ->orWhere('phone', 'ilike', $like)
                    ->orWhere('email', 'ilike', $like)
                    ->orWhere('member_code', 'ilike', $like);
            })
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email', 'member_code']);

        return response()->json($members);
    }

    private function baseViewData(Request $request, string $module, ?Member $member): array
    {
        $user = $request->user();

        return [
            'member' => $member,
            'selectedMemberId' => $member?->id,
            'moduleKey' => $module,
            'canAdd' => $user->canAccess($module . '.add'),
            'canEdit' => $user->canAccess($module . '.edit'),
            'canDelete' => $user->canAccess($module . '.delete'),
            'moduleTitles' => $this->moduleTitles(),
        ];
    }

    private function baseRecordQuery($user, string $type)
    {
        return MemberAssessment::query()
            ->forTenant($user->tenant_id)
            ->where('type', $type)
            ->when($user->effectiveBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId));
    }

    private function recordsForMember($user, int $memberId, string $type): Collection
    {
        return $this->baseRecordQuery($user, $type)
            ->where('member_id', $memberId)
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get();
    }

    private function latestRecord($user, int $memberId, string $type): ?MemberAssessment
    {
        return $this->baseRecordQuery($user, $type)
            ->where('member_id', $memberId)
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->first();
    }

    private function singleRecord($user, int $memberId, string $type): ?MemberAssessment
    {
        return $this->baseRecordQuery($user, $type)
            ->where('member_id', $memberId)
            ->latest('id')
            ->first();
    }

    private function latestAssessmentRecords($user, Member $member): Collection
    {
        $types = [
            'parq' => MemberAssessment::TYPE_PARQ,
            'nutrition' => MemberAssessment::TYPE_NUTRITION,
            'body_metrics' => MemberAssessment::TYPE_BODY_METRICS,
            'posture' => MemberAssessment::TYPE_POSTURE,
            'balance' => MemberAssessment::TYPE_BALANCE,
            'vitals' => MemberAssessment::TYPE_VITALS,
            'fitness_cardio' => MemberAssessment::TYPE_FITNESS_CARDIO,
            'fitness_strength' => MemberAssessment::TYPE_FITNESS_STRENGTH,
            'fitness_endurance' => MemberAssessment::TYPE_FITNESS_ENDURANCE,
            'fitness_flexibility' => MemberAssessment::TYPE_FITNESS_FLEXIBILITY,
        ];

        return collect($types)->mapWithKeys(fn (string $type, string $key) => [$key => $this->latestRecord($user, $member->id, $type)]);
    }

    private function reportSummary(Collection $records): array
    {
        $completed = $records->filter()->count();
        $riskFlags = [];
        $lastUpdated = $records->filter()->map(fn (MemberAssessment $record) => $record->updated_at)->sortDesc()->first();

        if (($records['parq']?->status ?? null) === 'medical_clearance_required') {
            $riskFlags[] = 'Medical clearance required';
        }
        if (in_array($records['balance']?->status, ['moderate_risk', 'high_risk'], true)) {
            $riskFlags[] = 'Balance risk';
        }

        return [
            'overall_score' => $completed > 0 ? min(100, (int) round(($completed / 9) * 100)) : 0,
            'sections_completed' => $completed,
            'last_updated' => $lastUpdated,
            'risk_flags' => $riskFlags,
        ];
    }

    private function selectedMember(Request $request): ?Member
    {
        if (! $request->filled('member_id')) {
            return null;
        }

        return $this->eligibleMembersQuery($request->user())->find($request->integer('member_id'));
    }

    private function eligibleMembersQuery($user)
    {
        return Member::query()
            ->forTenant($user->tenant_id)
            ->withStatus('active')
            ->whereNotNull('plan_id')
            ->when($user->effectiveBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->orderBy('name');
    }

    private function memberForWrite(Request $request, string $module, ?int $memberId, ?MemberAssessment $record = null): Member
    {
        $action = $record ? 'edit' : 'add';
        $this->authorizeModule($request->user(), $module, $action);

        $member = $this->eligibleMembersQuery($request->user())->findOrFail($memberId);

        if ($record) {
            abort_unless($record->tenant_id === $request->user()->tenant_id, 404);
            abort_unless($record->member_id === $member->id, 422);
            abort_unless($record->branch_id === $member->branch_id, 422);
        }

        return $member;
    }

    private function authorizeRecord($user, string $module, string $action, MemberAssessment $record): void
    {
        $this->authorizeModule($user, $module, $action);
        abort_unless($record->tenant_id === $user->tenant_id, 404);
        if ($branchId = $user->effectiveBranchId()) {
            abort_unless((int) $record->branch_id === (int) $branchId, 403);
        }
    }

    private function authorizeModule($user, string $module, string $action): void
    {
        abort_unless($user->canAccess($module . '.' . $action), 403);
    }

    private function normalizePhoneTerm(string $term): string
    {
        $digits = preg_replace('/\D+/', '', $term);
        if (str_starts_with($digits, '91') && strlen($digits) > 10) {
            $digits = substr($digits, -10);
        }

        return $digits !== '' ? $digits : $term;
    }

    private function parqFlags(string $status): array
    {
        return match ($status) {
            'medical_clearance_required' => [['type' => 'medical_clearance', 'description' => 'Medical clearance is recommended before starting training.']],
            'conditional' => [['type' => 'follow_up', 'description' => 'Review the positive answers before prescribing intensity.']],
            default => [],
        };
    }

    private function bmi(float $weightKg, float $heightCm): float
    {
        $heightM = $heightCm / 100;
        return round($weightKg / ($heightM * $heightM), 2);
    }

    private function bmiCategory(float $bmi): string
    {
        return match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25 => 'Normal weight',
            $bmi < 30 => 'Overweight',
            default => 'Obese',
        };
    }

    private function balancePayload(array $validated): array
    {
        $right = round((($validated['right_anterior'] + $validated['right_posteromedial'] + $validated['right_posterolateral']) / (3 * $validated['limb_length_cm'])) * 100, 2);
        $left = round((($validated['left_anterior'] + $validated['left_posteromedial'] + $validated['left_posterolateral']) / (3 * $validated['limb_length_cm'])) * 100, 2);
        $asymmetry = round(abs($right - $left), 2);

        $status = match (true) {
            $right < 70 || $left < 70 => 'high_risk',
            $right < 80 || $left < 80 || $asymmetry > 6 => 'moderate_risk',
            $right < 89 || $left < 89 || $asymmetry >= 4 => 'asymmetrical',
            default => 'balanced',
        };

        return [
            'limb_length_cm' => (float) $validated['limb_length_cm'],
            'right' => [
                'anterior' => (float) $validated['right_anterior'],
                'posteromedial' => (float) $validated['right_posteromedial'],
                'posterolateral' => (float) $validated['right_posterolateral'],
                'composite_pct' => $right,
            ],
            'left' => [
                'anterior' => (float) $validated['left_anterior'],
                'posteromedial' => (float) $validated['left_posteromedial'],
                'posterolateral' => (float) $validated['left_posterolateral'],
                'composite_pct' => $left,
            ],
            'asymmetry_pct' => $asymmetry,
            'overall_status' => $status,
        ];
    }

    private function buildBalanceInsight(array $payload): string
    {
        $status = str($payload['overall_status'] ?? 'balanced')->replace('_', ' ')->title();
        $right = number_format((float) data_get($payload, 'right.composite_pct', 0), 1);
        $left = number_format((float) data_get($payload, 'left.composite_pct', 0), 1);
        $asymmetry = number_format((float) data_get($payload, 'asymmetry_pct', 0), 1);

        return "Overall status is {$status}. Right composite score is {$right}%, left composite score is {$left}%, with {$asymmetry}% asymmetry. Focus on unilateral stability drills, controlled reach work, and ankle-hip control before progressing intensity.";
    }

    private function fitnessRules(string $tab): array
    {
        $common = [
            'member_id' => ['required', 'integer'],
            'tab' => ['required', 'in:cardio,strength,endurance,flexibility'],
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'next_measurement_date' => ['nullable', 'date', 'after:measurement_date'],
            'notes' => ['nullable', 'string', 'max:300'],
        ];

        return $common + match ($tab) {
            'cardio' => [
                'test_type' => ['required', 'in:cooper_12_min,run_1_5_mile,walk_1_mile'],
                'test_value' => ['required', 'numeric', 'min:0.1'],
                'hrr' => ['nullable', 'numeric', 'min:0'],
            ],
            'strength' => [
                'test_name' => ['required', 'string', 'min:2', 'max:100'],
                'test_value' => ['required', 'numeric', 'min:0.1'],
                'unit' => ['required', 'in:kg,N,lbs'],
            ],
            'endurance' => [
                'test_name' => ['required', 'string', 'min:2', 'max:100'],
                'reps' => ['required', 'integer', 'min:0'],
                'interpretation' => ['nullable', 'string', 'max:150'],
            ],
            default => [
                'test_name' => ['required', 'string', 'min:2', 'max:100'],
                'distance_cm' => ['required', 'numeric'],
                'interpretation' => ['nullable', 'string', 'max:150'],
            ],
        };
    }

    private function fitnessPayload(Member $member, string $tab, array $validated): array
    {
        return match ($tab) {
            'cardio' => $this->cardioPayload($member, $validated),
            'strength' => [
                'title' => $validated['test_name'],
                'payload' => [
                    'test_name' => $validated['test_name'],
                    'test_value' => (float) $validated['test_value'],
                    'unit' => $validated['unit'],
                ],
            ],
            'endurance' => [
                'title' => $validated['test_name'],
                'payload' => [
                    'test_name' => $validated['test_name'],
                    'reps' => (int) $validated['reps'],
                    'interpretation' => $validated['interpretation'] ?? null,
                ],
            ],
            default => [
                'title' => $validated['test_name'],
                'payload' => [
                    'test_name' => $validated['test_name'],
                    'distance_cm' => (float) $validated['distance_cm'],
                    'interpretation' => $validated['interpretation'] ?? null,
                ],
            ],
        };
    }

    private function cardioPayload(Member $member, array $validated): array
    {
        $vo2max = 0.0;
        $interpretation = null;

        if ($validated['test_type'] === 'cooper_12_min') {
            $vo2max = round(((float) $validated['test_value'] - 504.9) / 44.73, 2);
            $interpretation = $vo2max >= 52 ? 'Excellent' : ($vo2max >= 42 ? 'Good' : ($vo2max >= 32 ? 'Average' : 'Below average'));
        } elseif ($validated['test_type'] === 'run_1_5_mile') {
            $vo2max = round(3.5 + (483 / (float) $validated['test_value']), 2);
        } else {
            $latestBody = $this->latestRecord(request()->user(), $member->id, MemberAssessment::TYPE_BODY_METRICS);
            $weightKg = (float) data_get($latestBody?->payload, 'weight_kg', 0);
            $weightLbs = $weightKg * 2.20462;
            $age = $member->dob ? Carbon::parse($member->dob)->age : 0;
            $genderFactor = $member->gender === 'male' ? 1 : 0;
            $hrr = (float) ($validated['hrr'] ?? 0);
            $vo2max = round(132.853 - (0.0769 * $weightLbs) - (0.3877 * $age) + (6.315 * $genderFactor) - (3.2649 * (float) $validated['test_value']) - (0.1565 * $hrr), 2);
        }

        return [
            'title' => 'Cardiorespiratory Fitness',
            'payload' => [
                'test_type' => $validated['test_type'],
                'test_value' => (float) $validated['test_value'],
                'hrr' => isset($validated['hrr']) ? (float) $validated['hrr'] : null,
                'vo2max' => $vo2max,
                'interpretation' => $interpretation,
            ],
        ];
    }

    private function goalForecast(Member $member, array $validated): array
    {
        $rates = [
            'slow' => 0.25,
            'recommended' => 0.5,
            'extreme' => 1.0,
        ];

        $current = (float) $validated['current_weight_kg'];
        $target = (float) $validated['target_weight_kg'];
        $weeklyRate = $rates[$validated['weekly_rate']];
        $difference = round($target - $current, 2);
        $weeks = $validated['goal_type'] === 'maintain'
            ? 0
            : (int) ceil(abs($difference) / $weeklyRate);
        $months = round($weeks / 4, 1);
        $targetDate = now()->copy()->addWeeks($weeks)->toDateString();
        $forecastSeries = collect(range(0, max($weeks, 1)))->map(function (int $week) use ($current, $difference, $weeks) {
            $portion = $weeks > 0 ? ($week / $weeks) : 0;
            return [
                'date' => now()->copy()->addWeeks($week)->toDateString(),
                'forecasted_weight_kg' => round($current + ($difference * $portion), 2),
            ];
        });

        $actualSeries = $this->recordsForMember(request()->user(), $member->id, MemberAssessment::TYPE_BODY_METRICS)
            ->map(fn (MemberAssessment $record) => [
                'date' => $record->assessment_date?->toDateString(),
                'actual_weight_kg' => (float) data_get($record->payload, 'weight_kg', 0),
            ])
            ->values();

        $energy = $this->goalEnergyBreakdown($member, $current, $validated['goal_type'], $validated['weekly_rate']);

        return [
            'weight_diff_kg'        => $difference,
            'weekly_rate_kg'        => $weeklyRate,
            'weekly_rate_key'       => $validated['weekly_rate'],
            'goal_type'             => $validated['goal_type'],
            'current_weight'        => $current,
            'target_weight'         => $target,
            'duration_weeks'        => $weeks,
            'duration_months'       => $months,
            'estimated_target_date' => $targetDate,
            'forecast_series'       => $forecastSeries,
            'actual_series'         => $actualSeries,
            'energy'                => $energy,
        ];
    }

    private function goalEnergyBreakdown(Member $member, float $weightKg, string $goalType, string $weeklyRate): ?array
    {
        if (! $member->dob || ! $member->gender) {
            return null;
        }

        $latestBody = $this->latestRecord(request()->user(), $member->id, MemberAssessment::TYPE_BODY_METRICS);
        $heightCm = (float) data_get($latestBody?->payload, 'height_cm', 0);
        if ($heightCm <= 0) {
            return null;
        }

        $age = Carbon::parse($member->dob)->age;
        $bmr = $member->gender === 'male'
            ? (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) + 5
            : (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) - 161;
        $tdee = $bmr * 1.55;
        $adjustments = ['slow' => 275, 'recommended' => 550, 'extreme' => 1100];
        $delta = $adjustments[$weeklyRate] ?? 550;
        $dailyTarget = match ($goalType) {
            'weight_loss' => $tdee - $delta,
            'weight_gain' => $tdee + $delta,
            default => $tdee,
        };

        return [
            'bmr' => round($bmr, 0),
            'tdee' => round($tdee, 0),
            'daily_target_kcal' => round($dailyTarget, 0),
            'weekly_adjustment_kcal' => round(($dailyTarget - $tdee) * 7, 0),
        ];
    }

    private function fitnessType(string $tab): string
    {
        return match ($tab) {
            'strength' => MemberAssessment::TYPE_FITNESS_STRENGTH,
            'endurance' => MemberAssessment::TYPE_FITNESS_ENDURANCE,
            'flexibility' => MemberAssessment::TYPE_FITNESS_FLEXIBILITY,
            default => MemberAssessment::TYPE_FITNESS_CARDIO,
        };
    }

    private function moduleForType(string $type): string
    {
        return match ($type) {
            MemberAssessment::TYPE_PARQ => 'parq',
            MemberAssessment::TYPE_NUTRITION => 'nutrition',
            MemberAssessment::TYPE_BODY_METRICS => 'body_metrics',
            MemberAssessment::TYPE_POSTURE => 'posture',
            MemberAssessment::TYPE_BALANCE => 'balance',
            MemberAssessment::TYPE_VITALS => 'vitals',
            MemberAssessment::TYPE_FITNESS_CARDIO,
            MemberAssessment::TYPE_FITNESS_STRENGTH,
            MemberAssessment::TYPE_FITNESS_ENDURANCE,
            MemberAssessment::TYPE_FITNESS_FLEXIBILITY => 'fitness',
            default => 'assessment_report',
        };
    }

    private function routeForModule(string $module): string
    {
        return match ($module) {
            'parq' => 'tenant.assess.questionnaire',
            'nutrition' => 'tenant.assess.nutrition',
            'body_metrics' => 'tenant.assess.body-metrics',
            'posture' => 'tenant.assess.posture',
            'balance' => 'tenant.assess.balance',
            'vitals' => 'tenant.assess.vitals',
            'fitness' => 'tenant.assess.fitness',
            'goal_forecasting' => 'tenant.assess.goal-forecasting',
            default => 'tenant.assess.report',
        };
    }

    private function moduleTitles(): array
    {
        return [
            'assessment_report' => 'Assessment Report',
            'parq' => 'Questionnaire (PAR-Q+)',
            'nutrition' => 'Nutritional Assessment',
            'body_metrics' => 'Body Metrics',
            'posture' => 'Posture Assessment',
            'balance' => 'Balance Assessment',
            'vitals' => 'Vitals Check',
            'fitness' => 'Fitness Assessment',
            'goal_forecasting' => 'Goal Forecasting',
        ];
    }
}

