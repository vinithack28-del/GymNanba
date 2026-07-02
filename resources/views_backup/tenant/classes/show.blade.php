<x-layouts.admin
    title="{{ $class->name }}"
    eyebrow="{{ $class->class_date->format('l, d M Y') }}"
    heading="{{ $class->name }}"
>

<style>
.cls-grid { display:grid; grid-template-columns:1fr 22rem; gap:1.5rem; align-items:start; }
@media(max-width:900px){ .cls-grid { grid-template-columns:1fr; } }
.cls-meta-card { border:1px solid var(--app-border); border-radius:1.5rem; overflow:hidden; }
.cls-meta-head { padding:1rem 1.25rem; border-bottom:1px solid var(--app-border); background:var(--app-panel-strong); display:flex; align-items:center; gap:.75rem; }
.cls-meta-body { padding:1.25rem; display:flex; flex-direction:column; gap:.75rem; }
.cls-meta-row  { display:flex; gap:.5rem; font-size:.875rem; }
.cls-meta-label { color:var(--app-text-muted); width:6.5rem; flex-shrink:0; }

.cls-status { display:inline-flex; align-items:center; font-size:.72rem; font-weight:700; padding:.2rem .65rem; border-radius:999px; text-transform:uppercase; letter-spacing:.05em; }
.cls-status-scheduled { background:#d1fae5; color:#065f46; }
.cls-status-cancelled { background:#fef3c7; color:#92400e; }
.cls-status-completed { background:#f3f4f6; color:#6b7280; }
.cls-status-full      { background:#dbeafe; color:#1e40af; }

.cls-section { border:1px solid var(--app-border); border-radius:1.5rem; overflow:hidden; margin-bottom:1.25rem; }
.cls-sec-head { padding:.875rem 1.25rem; border-bottom:1px solid var(--app-border); background:var(--app-panel-strong); font-size:.875rem; font-weight:700; display:flex; align-items:center; justify-content:space-between; }
.cls-sec-count { font-size:.75rem; color:var(--app-text-muted); font-weight:400; }

.cls-btn { border:1px solid var(--app-border); border-radius:.6rem; padding:.4rem .85rem; font-size:.8rem; font-weight:600; cursor:pointer; background:transparent; color:var(--app-text); text-decoration:none; display:inline-flex; align-items:center; gap:.35rem; }
.cls-btn:hover { background:var(--app-panel-strong); }
.cls-btn-brand { background:var(--app-brand); border-color:var(--app-brand); color:#fff; }
.cls-btn-brand:hover { opacity:.9; background:var(--app-brand); }
.cls-btn-danger { border-color:#ef4444; color:#ef4444; }
.cls-btn-danger:hover { background:#fef2f2; }
.cls-btn-sm { padding:.25rem .6rem; font-size:.75rem; }

/* Book search */
.cls-search-wrap { position:relative; }
.cls-search-results { position:absolute; top:100%; left:0; right:0; border:1px solid var(--app-border); border-radius:.75rem; background:var(--app-panel); z-index:20; max-height:16rem; overflow-y:auto; margin-top:.25rem; display:none; }
.cls-member-row { display:flex; align-items:center; gap:.75rem; padding:.65rem 1rem; cursor:pointer; transition:background .12s; }
.cls-member-row:hover { background:var(--app-panel-strong); }

/* Cancel modal */
.cls-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:40; display:none; align-items:center; justify-content:center; }
.cls-overlay.open { display:flex; }
.cls-modal { background:var(--app-panel); border:1px solid var(--app-border); border-radius:1.5rem; width:min(30rem,92%); overflow:hidden; }
.cls-modal-head { padding:1.1rem 1.5rem; border-bottom:1px solid var(--app-border); font-weight:700; }
.cls-modal-body { padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:1rem; }
.cls-modal-foot { padding:1rem 1.5rem; border-top:1px solid var(--app-border); display:flex; gap:.75rem; justify-content:flex-end; }
.cls-modal-field label { display:block; font-size:.8rem; font-weight:600; color:var(--app-text-muted); margin-bottom:.35rem; }
.cls-modal-field select, .cls-modal-field input { width:100%; border:1px solid var(--app-border); border-radius:.6rem; padding:.5rem .75rem; font-size:.875rem; background:transparent; color:var(--app-text); outline:none; }
.cls-modal-field select:focus, .cls-modal-field input:focus { border-color:var(--app-brand); }
</style>

<div class="cls-grid">

    {{-- ── Left: bookings ─────────────────────────────────────────────────── --}}
    <div>

        {{-- Book a member form --}}
        @if($canManage && $class->status === 'scheduled' && !$class->is_full)
        <div class="cls-section app-panel mb-4">
            <div class="cls-sec-head">{{ __('classes.show.book_member') }}</div>
            <div class="p-4">
                <div class="cls-search-wrap">
                    <input type="text" id="cls-member-search" autocomplete="off"
                           placeholder="{{ __('classes.show.member_ph') }}"
                           class="w-full border border-[var(--app-border)] rounded-[.6rem] px-3 py-2 text-sm bg-transparent outline-none focus:border-[var(--app-brand)]"
                           oninput="clsSearch(this.value)">
                    <div class="cls-search-results" id="cls-search-results"></div>
                </div>
                <form method="POST" action="{{ route('tenant.classes.book.store', $class) }}" id="cls-book-form">
                    @csrf
                    <input type="hidden" name="member_id" id="cls-member-id">
                    <button type="submit" id="cls-book-btn" disabled
                            class="cls-btn cls-btn-brand mt-3 opacity-50 w-full justify-center" style="transition:opacity .15s">
                        {{ __('classes.show.book_member') }}
                    </button>
                </form>
            </div>
        </div>
        @elseif($class->status === 'scheduled' && $class->is_full && $class->allow_waitlist)
        <div class="app-panel cls-section mb-4">
            <div class="cls-sec-head">{{ __('classes.show.book_member') }}</div>
            <div class="p-4">
                <div class="cls-search-wrap">
                    <input type="text" id="cls-member-search" autocomplete="off"
                           placeholder="{{ __('classes.show.member_ph') }}"
                           class="w-full border border-[var(--app-border)] rounded-[.6rem] px-3 py-2 text-sm bg-transparent outline-none focus:border-[var(--app-brand)]"
                           oninput="clsSearch(this.value)">
                    <div class="cls-search-results" id="cls-search-results"></div>
                </div>
                <form method="POST" action="{{ route('tenant.classes.book.store', $class) }}" id="cls-book-form">
                    @csrf
                    <input type="hidden" name="member_id" id="cls-member-id">
                    <button type="submit" id="cls-book-btn" disabled
                            class="cls-btn cls-btn-brand mt-3 opacity-50 w-full justify-center" style="transition:opacity .15s">
                        Add to waitlist
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Booked members --}}
        <div class="cls-section app-panel">
            <div class="cls-sec-head">
                {{ __('classes.show.booked') }}
                <span class="cls-sec-count">{{ $booked->count() }}/{{ $class->max_capacity }}</span>
            </div>
            @if($booked->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-[var(--app-text-muted)]">{{ __('classes.show.no_booked') }}</p>
            @else
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-[var(--app-border)]">
                        @foreach($booked as $booking)
                            <tr class="transition hover:bg-[var(--app-panel-strong)]">
                                <td class="px-5 py-3">
                                    <p class="font-semibold">{{ $booking->member?->name }}</p>
                                    <p class="text-xs text-[var(--app-text-muted)]">{{ $booking->member?->phone }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-[var(--app-text-muted)]">
                                    @if($booking->status !== 'booked')
                                        <span class="cls-status cls-status-{{ $booking->status }}">{{ $booking->status }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($canManage && in_array($booking->status, ['booked']))
                                        <form method="POST" action="{{ route('tenant.classes.booking.cancel', [$class, $booking]) }}"
                                              onsubmit="return confirm('Cancel this booking?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="cls-btn cls-btn-sm cls-btn-danger">{{ __('classes.show.cancel_booking') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Waitlist --}}
        @if($waitlisted->isNotEmpty())
        <div class="cls-section app-panel mt-4">
            <div class="cls-sec-head">
                {{ __('classes.show.waitlisted') }}
                <span class="cls-sec-count">{{ $waitlisted->count() }}</span>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-[var(--app-border)]">
                    @foreach($waitlisted as $i => $booking)
                        <tr class="transition hover:bg-[var(--app-panel-strong)]">
                            <td class="px-5 py-3 text-xs text-[var(--app-text-muted)] w-8">#{{ $booking->waitlist_pos ?? $i+1 }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $booking->member?->name }}</p>
                                <p class="text-xs text-[var(--app-text-muted)]">{{ $booking->member?->phone }}</p>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($canManage)
                                    <form method="POST" action="{{ route('tenant.classes.booking.cancel', [$class, $booking]) }}"
                                          onsubmit="return confirm('Remove from waitlist?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="cls-btn cls-btn-sm cls-btn-danger">Remove</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    {{-- ── Right: meta card + actions ─────────────────────────────────────── --}}
    <div>
        <div class="app-panel cls-meta-card">
            <div class="cls-meta-head">
                @php $st = $class->is_full ? 'full' : $class->status; @endphp
                <span class="cls-status cls-status-{{ $st }}">{{ __('classes.statuses.'.$st) }}</span>
                <span class="text-sm font-semibold">{{ __('classes.types.'.$class->type) }}</span>
            </div>
            <div class="cls-meta-body">
                <div class="cls-meta-row">
                    <span class="cls-meta-label">{{ __('classes.show.date') }}</span>
                    <span>{{ $class->class_date->format('D, d M Y') }}</span>
                </div>
                <div class="cls-meta-row">
                    <span class="cls-meta-label">{{ __('classes.show.time') }}</span>
                    <span>{{ substr($class->start_time,0,5) }} – {{ substr($class->end_time,0,5) }}</span>
                </div>
                <div class="cls-meta-row">
                    <span class="cls-meta-label">{{ __('classes.show.duration') }}</span>
                    <span>{{ $class->duration_minutes }}m</span>
                </div>
                <div class="cls-meta-row">
                    <span class="cls-meta-label">{{ __('classes.show.branch') }}</span>
                    <span>{{ $class->branch?->name }}</span>
                </div>
                @if($class->room)
                <div class="cls-meta-row">
                    <span class="cls-meta-label">{{ __('classes.show.room') }}</span>
                    <span>{{ $class->room }}</span>
                </div>
                @endif
                <div class="cls-meta-row">
                    <span class="cls-meta-label">{{ __('classes.show.trainer') }}</span>
                    <span>{{ $class->trainer?->name ?? '—' }}</span>
                </div>
                <div class="cls-meta-row">
                    <span class="cls-meta-label">{{ __('classes.show.booked') }}</span>
                    <span>{{ $class->booking_count }}/{{ $class->max_capacity }}</span>
                </div>
                @if($class->available_spots > 0)
                <div class="cls-meta-row">
                    <span class="cls-meta-label">{{ __('classes.show.available') }}</span>
                    <span class="text-green-600 font-semibold">{{ $class->available_spots }}</span>
                </div>
                @endif
                @if($class->description)
                <div class="border-t border-[var(--app-border)] pt-3 mt-1">
                    <p class="text-xs text-[var(--app-text-muted)] font-semibold mb-1">{{ __('classes.show.description') }}</p>
                    <p class="text-sm">{{ $class->description }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        @if($canManage)
        <div class="mt-3 flex flex-col gap-2">
            @if($class->status === 'scheduled')
                <a href="{{ route('tenant.classes.edit', $class) }}" class="cls-btn w-full justify-center">
                    {{ __('classes.show.edit_class') }}
                </a>
                @if($class->booking_count === 0 || true)
                <a href="{{ route('tenant.classes.attendance', $class) }}" class="cls-btn cls-btn-brand w-full justify-center">
                    {{ __('classes.show.mark_attendance') }}
                </a>
                @endif
                <button onclick="clsOpenCancel()" class="cls-btn cls-btn-danger w-full justify-center">
                    {{ __('classes.show.cancel_class') }}
                </button>
            @elseif($class->status === 'completed')
                <a href="{{ route('tenant.classes.attendance', $class) }}" class="cls-btn cls-btn-brand w-full justify-center">
                    View attendance
                </a>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- Cancel modal --}}
@if($canManage)
<div class="cls-overlay" id="cls-cancel-overlay">
    <div class="cls-modal">
        <div class="cls-modal-head">{{ __('classes.cancel_modal.title') }}</div>
        <form method="POST" action="{{ route('tenant.classes.cancel', $class) }}">
            @csrf
            <div class="cls-modal-body">
                <p class="text-sm text-[var(--app-text-muted)]">{{ __('classes.cancel_modal.warning') }}</p>
                <div class="cls-modal-field">
                    <label>{{ __('classes.cancel_modal.reason') }}</label>
                    <select name="reason" required>
                        @foreach(__('classes.cancel_modal.reasons') as $val => $label)
                            <option value="{{ $label }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if($class->parent_id)
                <div class="cls-modal-field">
                    <label>{{ __('classes.cancel_modal.scope_label') }}</label>
                    <select name="scope">
                        <option value="this">{{ __('classes.cancel_modal.scope_this') }}</option>
                        <option value="future">{{ __('classes.cancel_modal.scope_future') }}</option>
                    </select>
                </div>
                @else
                    <input type="hidden" name="scope" value="this">
                @endif
            </div>
            <div class="cls-modal-foot">
                <button type="button" onclick="clsCloseCancel()" class="cls-btn">{{ __('classes.cancel_modal.back') }}</button>
                <button type="submit" class="cls-btn cls-btn-danger">{{ __('classes.cancel_modal.confirm') }}</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function clsOpenCancel() { document.getElementById('cls-cancel-overlay').classList.add('open'); }
    function clsCloseCancel() { document.getElementById('cls-cancel-overlay').classList.remove('open'); }

    let clsTimer;
    function clsSearch(q) {
        clearTimeout(clsTimer);
        const box = document.getElementById('cls-search-results');
        if (!box) return;
        if (q.length < 2) { box.style.display = 'none'; return; }
        clsTimer = setTimeout(async () => {
            const res  = await fetch(`{{ route('tenant.classes.member-search') }}?q=${encodeURIComponent(q)}`, { headers: {'X-Requested-With':'XMLHttpRequest'} });
            const data = await res.json();
            if (!data.length) {
                box.innerHTML = '<div class="cls-member-row" style="color:var(--app-text-muted);cursor:default;font-size:.85rem">No members found</div>';
            } else {
                box.innerHTML = data.map(m => `
                    <div class="cls-member-row" onclick="clsSelect(${m.id},'${esc(m.name)}')">
                        <div style="flex:1"><p style="font-size:.875rem;font-weight:600">${esc(m.name)}</p><p style="font-size:.75rem;color:var(--app-text-muted)">${esc(m.member_code)} · ${esc(m.phone)}</p></div>
                    </div>`).join('');
            }
            box.style.display = 'block';
        }, 280);
    }

    function clsSelect(id, name) {
        document.getElementById('cls-member-id').value = id;
        document.getElementById('cls-member-search').value = name;
        document.getElementById('cls-search-results').style.display = 'none';
        const btn = document.getElementById('cls-book-btn');
        if (btn) { btn.disabled = false; btn.classList.remove('opacity-50'); }
    }

    function esc(s) { const d = document.createElement('div'); d.textContent = String(s??''); return d.innerHTML; }
</script>

</x-layouts.admin>
