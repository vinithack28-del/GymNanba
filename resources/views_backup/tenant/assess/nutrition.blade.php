<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')
<div class="as-shell">
    <div class="as-head"><div><div class="as-title">{{ $moduleTitles[$moduleKey] }}</div><div class="as-sub">Create and review diet plans for the selected client.</div></div></div>
    @include('tenant.assess._nav')
    @include('tenant.assess._member-picker', ['action' => route('tenant.assess.nutrition'), 'member' => $member])

    @if ($member && ($canAdd || ($editingPlan && $canEdit)))
        <form method="POST" action="{{ $editingPlan ? route('tenant.assess.nutrition.update', $editingPlan) : route('tenant.assess.nutrition.store') }}" class="as-panel">
            @csrf
            @if ($editingPlan) @method('PUT') @endif
            <input type="hidden" name="member_id" value="{{ $member->id }}">
            <div class="as-grid">
                <div class="as-col-4"><label class="as-label">Plan name</label><input class="as-input" name="plan_name" value="{{ old('plan_name', $editingPlan->title ?? '') }}" required></div>
                <div class="as-col-4"><label class="as-label">Plan date</label><input class="as-input" type="date" name="plan_date" value="{{ old('plan_date', optional($editingPlan?->assessment_date)->toDateString() ?? now()->toDateString()) }}" required></div>
                <div class="as-col-12"><label class="as-label">Goal / notes</label><textarea class="as-textarea" name="goal_notes">{{ old('goal_notes', $editingPlan->notes ?? '') }}</textarea></div>
                @php $meals = old('meals', data_get($editingPlan?->payload, 'meals', [['meal_name' => '', 'food_items' => '']])); @endphp
                @foreach ($meals as $index => $meal)
                    <div class="as-col-12">
                        <div class="as-panel-tight">
                            <div class="as-grid">
                                <div class="as-col-4"><label class="as-label">Meal name</label><input class="as-input" name="meals[{{ $index }}][meal_name]" value="{{ $meal['meal_name'] ?? '' }}" required></div>
                                <div class="as-col-4"><label class="as-label">Time</label><input class="as-input" type="time" name="meals[{{ $index }}][time]" value="{{ $meal['time'] ?? '' }}"></div>
                                <div class="as-col-4"><label class="as-label">Calories</label><input class="as-input" type="number" step="0.01" name="meals[{{ $index }}][calories]" value="{{ $meal['calories'] ?? '' }}"></div>
                                <div class="as-col-12"><label class="as-label">Food items</label><textarea class="as-textarea" name="meals[{{ $index }}][food_items]" required>{{ $meal['food_items'] ?? '' }}</textarea></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="as-actions" style="margin-top:1rem"><button class="as-btn as-btn-primary" type="submit">{{ $editingPlan ? 'Save Plan' : 'Create Diet Plan' }}</button></div>
        </form>
    @endif

    <div class="as-panel">
        <div class="as-title" style="font-size:1rem">Diet Plans</div>
        @if (! $member)
            <div class="as-empty">Select a client to view diet plans.</div>
        @elseif ($plans->isEmpty())
            <div class="as-empty">No diet plans yet.</div>
        @else
            <div class="as-table-wrap">
                <table class="as-table">
                    <thead><tr><th>Plan date</th><th>Plan name</th><th>Meals</th><th>Actions</th></tr></thead>
                    <tbody>
                    @foreach ($plans as $plan)
                        <tr>
                            <td>{{ $plan->assessment_date?->format('d M Y') }}</td>
                            <td>
                                <div style="font-weight:700;color:var(--app-text)">{{ $plan->title }}</div>
                                @if ($plan->notes)<div class="as-help">{{ $plan->notes }}</div>@endif
                                <details style="margin-top:.45rem"><summary class="as-help" style="cursor:pointer">View meals</summary>
                                    <ul style="margin:.5rem 0 0 1rem;color:var(--app-text)">
                                        @foreach (data_get($plan->payload, 'meals', []) as $meal)
                                            <li>{{ $meal['meal_name'] ?? 'Meal' }}: {{ $meal['food_items'] ?? '' }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            </td>
                            <td>{{ count(data_get($plan->payload, 'meals', [])) }}</td>
                            <td class="as-actions">
                                @if ($canEdit)<a class="as-btn as-btn-secondary" href="{{ route('tenant.assess.nutrition', ['member_id' => $member->id, 'edit' => $plan->id]) }}">Edit</a>@endif
                                @if ($canDelete)
                                    <form id="del-nut-{{ $plan->id }}" method="POST" action="{{ route('tenant.assess.records.destroy', $plan) }}">@csrf @method('DELETE')<input type="hidden" name="confirm_name"><button type="button" class="as-btn as-btn-danger" onclick="assessConfirmDelete('del-nut-{{ $plan->id }}', @json($member->name))">Delete</button></form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
</x-layouts.admin>
