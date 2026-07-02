@php
    $items = [
        ['key' => 'assessment_report', 'label' => __('common.tenant_nav.assessment_report'), 'route' => route('tenant.assess.report')],
        ['key' => 'parq', 'label' => __('common.tenant_nav.parq'), 'route' => route('tenant.assess.questionnaire')],
        ['key' => 'nutrition', 'label' => __('common.tenant_nav.nutrition'), 'route' => route('tenant.assess.nutrition')],
        ['key' => 'body_metrics', 'label' => __('common.tenant_nav.body_metrics'), 'route' => route('tenant.assess.body-metrics')],
        ['key' => 'posture', 'label' => __('common.tenant_nav.posture'), 'route' => route('tenant.assess.posture')],
        ['key' => 'balance', 'label' => __('common.tenant_nav.balance'), 'route' => route('tenant.assess.balance')],
        ['key' => 'vitals', 'label' => __('common.tenant_nav.vitals'), 'route' => route('tenant.assess.vitals')],
        ['key' => 'fitness', 'label' => __('common.tenant_nav.fitness'), 'route' => route('tenant.assess.fitness')],
        ['key' => 'goal_forecasting', 'label' => __('common.tenant_nav.goal_forecasting'), 'route' => route('tenant.assess.goal-forecasting')],
    ];
@endphp

<div class="as-tabs">
    @foreach ($items as $item)
        @if (auth()->user()->canAccess(match ($item['key']) {
            'assessment_report' => 'assessment_report.view',
            'parq' => 'parq.view',
            'nutrition' => 'nutrition.view',
            'body_metrics' => 'body_metrics.view',
            'posture' => 'posture.view',
            'balance' => 'balance.view',
            'vitals' => 'vitals.view',
            'fitness' => 'fitness.view',
            default => 'goal_forecasting.view',
        }))
            <a href="{{ $item['route'] }}" class="as-tab {{ $moduleKey === $item['key'] ? 'as-tab-active' : '' }}">{{ $item['label'] }}</a>
        @endif
    @endforeach
</div>
