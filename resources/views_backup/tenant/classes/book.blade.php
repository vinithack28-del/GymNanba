<x-layouts.admin
    title="{{ __('classes.book.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('classes.book.title') }}"
    subheading="{{ __('classes.book.subtitle') }}"
>

<style>
.bk-filter { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; margin-bottom:1.25rem; }
.bk-select { border:1px solid var(--app-border); border-radius:.6rem; padding:.45rem .85rem; font-size:.8rem; background:transparent; color:var(--app-text); appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='4 6 8 10 12 6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right .6rem center; padding-right:2rem; }

/* Class cards grid */
.bk-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(18rem,1fr)); gap:1rem; }

.bk-card { border:1px solid var(--app-border); border-radius:1.25rem; overflow:hidden; transition:box-shadow .15s; }
.bk-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.1); }
.bk-card-accent { height:.3rem; }
.bk-card-accent-scheduled { background:linear-gradient(90deg, #059669, #34d399); }
.bk-card-accent-full      { background:linear-gradient(90deg, #3b82f6, #93c5fd); }
.bk-card-body  { padding:1rem 1.25rem; }
.bk-card-name  { font-size:1rem; font-weight:700; margin-bottom:.25rem; }
.bk-card-meta  { font-size:.8rem; color:var(--app-text-muted); display:flex; flex-direction:column; gap:.2rem; margin-bottom:.875rem; }
.bk-card-row   { display:flex; align-items:center; gap:.4rem; }
.bk-capacity   { display:flex; align-items:center; gap:.5rem; font-size:.8rem; margin-bottom:.875rem; }
.bk-progress   { flex:1; height:.35rem; border-radius:999px; background:var(--app-panel-strong); overflow:hidden; }
.bk-progress-bar { height:100%; border-radius:999px; background:var(--app-brand); }
.bk-type-badge { display:inline-block; font-size:.65rem; font-weight:700; padding:.1rem .45rem; border-radius:999px; background:var(--app-panel-strong); color:var(--app-text-muted); text-transform:uppercase; }

/* Book form in card */
.bk-book-form { border-top:1px solid var(--app-border); padding:.875rem 1.25rem; background:var(--app-panel-strong); display:flex; flex-direction:column; gap:.5rem; }
.bk-member-search-wrap { position:relative; }
.bk-search-input { width:100%; border:1px solid var(--app-border); border-radius:.6rem; padding:.45rem .75rem; font-size:.8rem; background:var(--app-panel); color:var(--app-text); outline:none; }
.bk-search-input:focus { border-color:var(--app-brand); }
.bk-search-results { position:absolute; top:100%; left:0; right:0; border:1px solid var(--app-border); border-radius:.6rem; background:var(--app-panel); z-index:20; max-height:12rem; overflow-y:auto; margin-top:.2rem; display:none; }
.bk-member-row { display:flex; align-items:center; gap:.5rem; padding:.5rem .75rem; cursor:pointer; font-size:.8rem; }
.bk-member-row:hover { background:var(--app-panel-strong); }
.bk-btn-brand { border:none; background:var(--app-brand); color:#fff; border-radius:.6rem; padding:.45rem .875rem; font-size:.8rem; font-weight:700; cursor:pointer; width:100%; }
.bk-btn-brand:disabled { opacity:.5; cursor:default; }

.bk-empty { display:flex; flex-direction:column; align-items:center; padding:5rem 1rem; text-align:center; }
.bk-empty-icon { background:var(--app-panel-strong); border:1px solid var(--app-border); border-radius:999px; color:var(--app-text-muted); height:4.5rem; width:4.5rem; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem; }

.bk-tag-full { display:inline-block; font-size:.65rem; font-weight:700; padding:.15rem .5rem; border-radius:999px; background:#dbeafe; color:#1e40af; }
.bk-tag-waitlist { display:inline-block; font-size:.65rem; font-weight:700; padding:.15rem .5rem; border-radius:999px; background:#f3e8ff; color:#6b21a8; }
</style>

{{-- Filter --}}
<form method="GET" action="{{ route('tenant.classes.book') }}">
    <div class="bk-filter">
        @if($branches->isNotEmpty())
            <select name="branch_id" class="bk-select" onchange="this.form.submit()">
                <option value="">{{ __('classes.book.all_branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        @endif
    </div>
</form>

@if($classes->isEmpty())
    <div class="app-panel rounded-[2rem] border">
        <div class="bk-empty">
            <div class="bk-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <p class="font-bold text-lg">{{ __('classes.book.no_classes') }}</p>
            <p class="text-sm text-[var(--app-text-muted)] mt-1">{{ __('classes.book.no_classes_sub') }}</p>
        </div>
    </div>
@else
    <div class="bk-grid">
        @foreach ($classes as $idx => $class)
            @php
                $pct  = $class->max_capacity > 0 ? ($class->booking_count / $class->max_capacity * 100) : 0;
                $full = $class->is_full;
            @endphp
            <div class="app-panel bk-card">
                <div class="bk-card-accent bk-card-accent-{{ $full ? 'full' : 'scheduled' }}"></div>
                <div class="bk-card-body">
                    <p class="bk-card-name">{{ $class->name }}</p>
                    <div class="bk-card-meta">
                        <span class="bk-card-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5 flex-shrink-0"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            {{ $class->class_date->format('D, d M') }}
                        </span>
                        <span class="bk-card-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5 flex-shrink-0"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ substr($class->start_time,0,5) }} – {{ substr($class->end_time,0,5) }}
                        </span>
                        @if($class->trainer)
                        <span class="bk-card-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5 flex-shrink-0"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            {{ $class->trainer->name }}
                        </span>
                        @endif
                        @if($class->branch)
                        <span class="bk-card-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5 flex-shrink-0"><path d="M3 21h18M5 21V7l7-4 7 4v14"/></svg>
                            {{ $class->branch->name }}
                        </span>
                        @endif
                    </div>
                    <div class="bk-capacity">
                        <div class="bk-progress">
                            <div class="bk-progress-bar" style="width:{{ min($pct, 100) }}%"></div>
                        </div>
                        <span>{{ $class->booking_count }}/{{ $class->max_capacity }}</span>
                        @if($full)
                            <span class="bk-tag-full">{{ __('classes.book.full') }}</span>
                        @elseif($class->waitlist_count > 0)
                            <span class="bk-tag-waitlist">+{{ $class->waitlist_count }} {{ __('classes.book.waitlist') }}</span>
                        @else
                            <span style="font-size:.72rem;color:var(--app-text-muted)">{{ str_replace(':n', $class->available_spots, __('classes.book.spots_left')) }}</span>
                        @endif
                    </div>
                    <span class="bk-type-badge">{{ __('classes.types.'.$class->type) }}</span>
                </div>

                {{-- Book form --}}
                @if($canManage && ($class->status === 'scheduled') && (!$full || $class->allow_waitlist))
                <div class="bk-book-form">
                    <div class="bk-member-search-wrap">
                        <input type="text" autocomplete="off"
                               placeholder="{{ __('classes.book.select_member') }}"
                               class="bk-search-input"
                               oninput="bkSearch(this,'bk-results-{{ $idx }}','bk-mid-{{ $idx }}','bk-bookbtn-{{ $idx }}')">
                        <input type="hidden" id="bk-mid-{{ $idx }}" value="">
                        <div class="bk-search-results" id="bk-results-{{ $idx }}"></div>
                    </div>
                    <form method="POST" action="{{ route('tenant.classes.book.store', $class) }}">
                        @csrf
                        <input type="hidden" name="member_id" id="bk-formid-{{ $idx }}">
                        <button type="submit" id="bk-bookbtn-{{ $idx }}" disabled class="bk-btn-brand"
                                onclick="document.getElementById('bk-formid-{{ $idx }}').value=document.getElementById('bk-mid-{{ $idx }}').value">
                            {{ $full ? __('classes.book.waitlist') : __('classes.book.book_btn') }}
                        </button>
                    </form>
                </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($classes->hasPages())
        <div class="mt-5">{{ $classes->links() }}</div>
    @endif
@endif

<script>
    let bkTimers = {};
    async function bkSearch(input, resultsId, midId, btnId) {
        const q   = input.value;
        const box = document.getElementById(resultsId);
        clearTimeout(bkTimers[resultsId]);
        if (q.length < 2) { box.style.display='none'; return; }
        bkTimers[resultsId] = setTimeout(async () => {
            const res  = await fetch(`{{ route('tenant.classes.member-search') }}?q=${encodeURIComponent(q)}`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
            const data = await res.json();
            if (!data.length) {
                box.innerHTML = '<div class="bk-member-row" style="color:var(--app-text-muted);cursor:default">No members found</div>';
            } else {
                box.innerHTML = data.map(m => `<div class="bk-member-row" onclick="bkSelect('${midId}','${btnId}',${m.id},'${bkEsc(m.name)}',this.closest('.bk-member-search-wrap').querySelector('input'))">${bkEsc(m.name)} <span style="font-size:.72rem;color:var(--app-text-muted);margin-left:.3rem">${bkEsc(m.member_code)}</span></div>`).join('');
            }
            box.style.display = 'block';
        }, 280);
    }

    function bkSelect(midId, btnId, id, name, input) {
        document.getElementById(midId).value = id;
        input.value = name;
        document.getElementById(input.closest('.bk-member-search-wrap').querySelector('.bk-search-results').id).style.display = 'none';
        const btn = document.getElementById(btnId);
        btn.disabled = false;
    }

    function bkEsc(s) { const d = document.createElement('div'); d.textContent = String(s??''); return d.innerHTML; }
</script>

</x-layouts.admin>
