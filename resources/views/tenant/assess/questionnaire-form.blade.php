<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')

@php
    $section1Answers = collect(old('section1', collect(data_get($record?->payload, 'section1', []))->mapWithKeys(fn ($row) => [$row['question_id'] => $row['answer'] === true ? '1' : '0'])->all()));
    $section2Answers = collect(old('section2', collect(data_get($record?->payload, 'section2', []))->mapWithKeys(fn ($row) => [$row['key'] => $row['answer'] === true ? '1' : '0'])->all()));
    $isEdit = $record !== null;
@endphp

<div class="as-shell">
    <div class="as-head">
        <div>
            <a href="{{ route('tenant.assess.questionnaire') }}"
               style="align-items:center;color:var(--app-text-muted);display:inline-flex;font-size:.82rem;font-weight:600;gap:.4rem;margin-bottom:.6rem;text-decoration:none">
                <svg style="height:14px;width:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Records
            </a>
            <div class="as-title">{{ $isEdit ? 'Edit PAR-Q+' : 'Add PAR-Q+' }}</div>
            <div class="as-sub">{{ $isEdit ? 'Update the PAR-Q+ questionnaire for '.$record->member->name : 'Record a new PAR-Q+ questionnaire for a client.' }}</div>
        </div>
        @if ($record)
            <span class="as-badge {{ $record->status === 'cleared' ? 'as-badge-green' : ($record->status === 'conditional' ? 'as-badge-amber' : 'as-badge-red') }}">
                {{ str($record->status)->replace('_', ' ')->title() }}
            </span>
        @endif
    </div>

    @include('tenant.assess._nav')

    @include('tenant.assess._member-picker', [
        'action' => $isEdit ? route('tenant.assess.questionnaire.edit', $record) : route('tenant.assess.questionnaire.create'),
        'member' => $member,
    ])

    @if (! $member)
        <div class="as-panel as-empty">Select a client to open the PAR-Q+ form.</div>
    @elseif ($canAdd || ($record && $canEdit))
        <form method="POST" action="{{ route('tenant.assess.questionnaire.save') }}" class="as-panel">
            @csrf
            <input type="hidden" name="member_id" value="{{ $member->id }}">


            @if ($errors->any())
                <div style="background:color-mix(in srgb,#ef4444 10%,transparent);border:1px solid #ef444444;border-radius:1rem;color:#dc2626;font-size:.85rem;margin-bottom:1rem;padding:.85rem 1rem">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="as-label">Section 1 — General Health</div>
            <div class="as-grid">
                @foreach ($questions as $id => $label)
                    <div class="as-col-12">
                        <div class="as-panel-tight">
                            <div style="font-weight:600;color:var(--app-text)">{{ $id }}. {{ $label }}</div>
                            <div class="as-actions" style="margin-top:.75rem">
                                <label><input type="radio" name="section1[{{ $id }}]" value="1" {{ (string) $section1Answers->get($id) === '1' ? 'checked' : '' }}> Yes</label>
                                <label><input type="radio" name="section1[{{ $id }}]" value="0" {{ (string) $section1Answers->get($id) === '0' ? 'checked' : '' }}> No</label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="as-label" style="margin-top:1rem">Section 2 — Follow-up</div>
            <div class="as-grid">
                @foreach ($followups as $row)
                    <div class="as-col-6">
                        <div class="as-panel-tight">
                            <div style="font-weight:600;color:var(--app-text)">{{ $row['label'] }}</div>
                            <div class="as-help" style="margin:.3rem 0 .65rem">Shown when Q{{ $row['trigger'] }} is Yes.</div>
                            <div class="as-actions">
                                <label><input type="radio" name="section2[{{ $row['key'] }}]" value="1" {{ (string) $section2Answers->get($row['key']) === '1' ? 'checked' : '' }}> Yes</label>
                                <label><input type="radio" name="section2[{{ $row['key'] }}]" value="0" {{ (string) $section2Answers->get($row['key']) === '0' ? 'checked' : '' }}> No</label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="as-actions" style="margin-top:1rem">
                <button class="as-btn as-btn-primary" type="submit">{{ $isEdit ? 'Update Questionnaire' : 'Save Questionnaire' }}</button>
                <a href="{{ route('tenant.assess.questionnaire') }}" class="as-btn as-btn-secondary">Cancel</a>
            </div>
        </form>
    @else
        <div class="as-panel as-empty">You have view access only for this module.</div>
    @endif
</div>
</x-layouts.admin>
