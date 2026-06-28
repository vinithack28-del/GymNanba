<x-layouts.admin
    title="Member Registrations"
    eyebrow="Members"
    heading="Online Registrations"
    subheading="Review and confirm members who registered via the online form."
>
    <x-slot:headerAction>
        <div class="flex items-center gap-2">
            <a href="{{ route('tenant.members.index') }}"
               class="app-panel-strong inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition hover:opacity-80">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Members
            </a>
            <button id="link-btn"
               class="inline-flex items-center gap-2 rounded-full border border-[var(--app-border)] bg-[color-mix(in_srgb,var(--app-brand)_10%,transparent)] px-4 py-2.5 text-sm font-semibold text-[var(--app-brand)] transition hover:opacity-80">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                Registration Link
            </button>
        </div>
    </x-slot:headerAction>

    {{-- Validation errors (phone conflict, etc.) --}}
    @if ($errors->has('phone'))
        <div class="mb-5 rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            {{ $errors->first('phone') }}
        </div>
    @endif

    {{-- Status tabs --}}
    <div class="reg-tabs mb-5">
        @foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected'] as $tab => $label)
            <a href="{{ route('tenant.members.registrations.index', ['status' => $tab]) }}"
               class="reg-tab {{ $status === $tab ? 'reg-tab-active' : '' }}">
                {{ $label }}
                <span class="reg-tab-count {{ $status === $tab ? 'reg-tab-count-active' : '' }}">{{ $counts[$tab] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Table --}}
    @if ($registrations->isEmpty())
        <div class="app-panel flex flex-col items-center gap-3 rounded-[2rem] border py-20 text-center">
            <svg class="h-10 w-10 text-[var(--app-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <p class="text-base font-semibold">No {{ $status }} registrations</p>
            <p class="app-muted text-sm">Share the registration link to get people to sign up.</p>
        </div>
    @else
        <div class="app-panel overflow-hidden rounded-[2rem] border">
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-[var(--app-border)] text-left text-sm">
                    <thead class="app-panel-strong text-xs font-semibold uppercase tracking-[0.12em] text-[var(--app-text-muted)]">
                        <tr>
                            <th class="px-5 py-3.5">Name</th>
                            <th class="px-5 py-3.5">Phone</th>
                            <th class="px-5 py-3.5">Email</th>
                            <th class="px-5 py-3.5">Gender / DOB</th>
                            <th class="px-5 py-3.5">Submitted</th>
                            @if ($status === 'pending')
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            @elseif ($status === 'confirmed')
                                <th class="px-5 py-3.5">Confirmed By</th>
                            @else
                                <th class="px-5 py-3.5">Reason</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--app-border)]">
                        @foreach ($registrations as $reg)
                            <tr class="transition hover:bg-[color-mix(in_srgb,var(--app-panel-strong)_60%,transparent)]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[color-mix(in_srgb,var(--app-brand-soft)_70%,transparent)] text-xs font-bold text-[var(--app-brand)]">
                                            {{ strtoupper(substr($reg->name, 0, 1)) }}
                                        </span>
                                        <span class="font-semibold">{{ $reg->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs">{{ $reg->phone }}</td>
                                <td class="px-5 py-4 text-xs text-[var(--app-text-muted)]">{{ $reg->email ?: '—' }}</td>
                                <td class="px-5 py-4 text-xs text-[var(--app-text-muted)]">
                                    {{ $reg->gender ? ucfirst($reg->gender) : '—' }}
                                    @if ($reg->dob) · {{ $reg->dob->format('d M Y') }} @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-[var(--app-text-muted)]">
                                    {{ $reg->created_at->format('d M, h:i A') }}
                                </td>
                                @if ($status === 'pending')
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                class="reg-confirm-btn rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 text-xs font-semibold text-emerald-400 transition hover:bg-emerald-500/20"
                                                data-id="{{ $reg->id }}"
                                                data-name="{{ $reg->name }}"
                                                onclick="openConfirmModal(this)"
                                            >Confirm</button>
                                            <button
                                                type="button"
                                                class="rounded-xl bg-red-500/10 border border-red-500/20 px-3 py-1.5 text-xs font-semibold text-red-400 transition hover:bg-red-500/20"
                                                data-id="{{ $reg->id }}"
                                                data-name="{{ $reg->name }}"
                                                onclick="openRejectModal(this)"
                                            >Reject</button>
                                        </div>
                                    </td>
                                @elseif ($status === 'confirmed')
                                    <td class="px-5 py-4 text-xs text-[var(--app-text-muted)]">
                                        {{ $reg->confirmedBy?->name ?? '—' }}<br>
                                        <span class="text-[0.65rem]">{{ $reg->confirmed_at?->format('d M Y') }}</span>
                                    </td>
                                @else
                                    <td class="px-5 py-4 text-xs text-[var(--app-text-muted)] max-w-[200px]">
                                        {{ $reg->rejected_reason ?: '—' }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $registrations->links() }}</div>
    @endif

    {{-- ── Confirm Modal ──────────────────────────────────────────────────── --}}
    <div id="confirm-modal" class="reg-modal-backdrop hidden">
        <div class="reg-modal">
            <div class="reg-modal-head">
                <h3>Confirm Registration</h3>
                <button type="button" onclick="closeModal('confirm-modal')" class="reg-modal-close">✕</button>
            </div>
            <p class="reg-modal-sub" id="confirm-sub">Assign a plan to confirm this registration.</p>

            <form id="confirm-form" method="POST" action="">
                @csrf
                <div class="reg-field">
                    <label class="reg-label">Membership Plan <span class="text-red-400">*</span></label>
                    <select name="plan_id" class="reg-input" required>
                        <option value="">Select a plan…</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} — Rs. {{ number_format($plan->total_price_paise / 100, 2) }} / {{ $plan->duration_label }}@if($plan->gst_amount_paise > 0) (incl. GST)@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="reg-field">
                    <label class="reg-label">Start Date <span class="text-red-400">*</span></label>
                    <input type="date" name="start_date" class="reg-input" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="reg-actions">
                    <button type="button" onclick="closeModal('confirm-modal')" class="reg-btn-ghost">Cancel</button>
                    <button type="submit" class="reg-btn-primary">Confirm & Add as Member</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Reject Modal ───────────────────────────────────────────────────── --}}
    <div id="reject-modal" class="reg-modal-backdrop hidden">
        <div class="reg-modal">
            <div class="reg-modal-head">
                <h3>Reject Registration</h3>
                <button type="button" onclick="closeModal('reject-modal')" class="reg-modal-close">✕</button>
            </div>
            <p class="reg-modal-sub" id="reject-sub">This registration will be marked as rejected.</p>

            <form id="reject-form" method="POST" action="">
                @csrf
                <div class="reg-field">
                    <label class="reg-label">Reason <span class="reg-muted">(optional)</span></label>
                    <textarea name="reason" class="reg-input" rows="3" maxlength="500" placeholder="e.g. Duplicate registration, incomplete information…"></textarea>
                </div>
                <div class="reg-actions">
                    <button type="button" onclick="closeModal('reject-modal')" class="reg-btn-ghost">Cancel</button>
                    <button type="submit" class="reg-btn-danger">Reject Registration</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Registration Link Modal ─────────────────────────────────────────── --}}
    <div id="link-modal" class="reg-modal-backdrop hidden">
        <div class="reg-modal">
            <div class="reg-modal-head">
                <h3>Registration Link</h3>
                <button type="button" onclick="closeModal('link-modal')" class="reg-modal-close">✕</button>
            </div>
            <p class="reg-modal-sub">Share this link so people can register online.</p>

            <div class="reg-url-row">
                <input type="text" id="reg-url-input" readonly value="{{ $registrationUrl }}" class="reg-url-input">
                <button type="button" onclick="copyUrl()" class="reg-copy-btn" id="copy-btn">Copy</button>
            </div>

            <div class="reg-divider"></div>
            <p class="reg-label mb-3">Share via Email</p>

            @if (session('email_sent'))
                <p class="text-xs text-emerald-400 mb-3">{{ session('email_sent') }}</p>
            @endif

            <form method="POST" action="{{ route('tenant.members.registration-link.email') }}">
                @csrf
                <div class="flex gap-2">
                    <input type="email" name="email" class="reg-input flex-1" placeholder="recipient@example.com" required>
                    <button type="submit" class="reg-btn-primary shrink-0">Send</button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <style>
    .reg-tabs { display: flex; gap: 0.2rem; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; padding: 0.2rem; width: fit-content; }
    .reg-tab { align-items: center; border-radius: 0.65rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.8rem; font-weight: 500; gap: 0.35rem; padding: 0.35rem 0.75rem; text-decoration: none; transition: background 140ms, color 140ms; white-space: nowrap; }
    .reg-tab:hover { color: var(--app-text); }
    .reg-tab-active { background: var(--app-panel); box-shadow: 0 1px 4px rgba(0,0,0,.12); color: var(--app-text); font-weight: 600; }
    .reg-tab-count { background: var(--app-panel-strong); border-radius: 999px; font-size: 0.65rem; min-width: 1.1rem; padding: 0.05rem 0.3rem; text-align: center; }
    .reg-tab-count-active { background: color-mix(in srgb, var(--app-brand-soft) 80%, transparent); color: var(--app-brand); }

    .reg-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 60; display: flex; align-items: center; justify-content: center; padding: 1rem; }
    .reg-modal-backdrop.hidden { display: none; }
    .reg-modal { background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 1.5rem; padding: 1.5rem; width: 100%; max-width: 460px; }
    .reg-modal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
    .reg-modal-head h3 { font-size: 1.05rem; font-weight: 700; }
    .reg-modal-close { background: transparent; border: none; color: var(--app-text-muted); cursor: pointer; font-size: 1rem; line-height: 1; padding: 0.2rem; }
    .reg-modal-sub { color: var(--app-text-muted); font-size: 0.82rem; margin-bottom: 1.25rem; }
    .reg-field { display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0.85rem; }
    .reg-label { color: var(--app-text-muted); font-size: 0.78rem; font-weight: 500; }
    .reg-muted { color: var(--app-text-muted); font-weight: 400; }
    .reg-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text); font-size: 0.875rem; outline: none; padding: 0.5rem 0.75rem; width: 100%; transition: border-color 150ms; }
    .reg-input:focus { border-color: color-mix(in srgb, var(--app-brand) 60%, var(--app-border)); }
    .reg-actions { display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.25rem; }
    .reg-btn-primary { background: var(--app-brand); border: none; border-radius: 0.65rem; color: #0f172a; cursor: pointer; font-size: 0.82rem; font-weight: 600; padding: 0.5rem 1rem; transition: opacity 150ms; white-space: nowrap; }
    .reg-btn-primary:hover { opacity: 0.88; }
    .reg-btn-ghost { background: transparent; border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text-muted); cursor: pointer; font-size: 0.82rem; font-weight: 500; padding: 0.5rem 1rem; transition: background 140ms; }
    .reg-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); }
    .reg-btn-danger { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.25); border-radius: 0.65rem; color: #f87171; cursor: pointer; font-size: 0.82rem; font-weight: 600; padding: 0.5rem 1rem; transition: opacity 150ms; }
    .reg-btn-danger:hover { opacity: 0.85; }
    .reg-url-row { display: flex; gap: 0.5rem; align-items: center; }
    .reg-url-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text-muted); flex: 1; font-family: monospace; font-size: 0.75rem; outline: none; padding: 0.5rem 0.75rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .reg-copy-btn { background: color-mix(in srgb, var(--app-brand) 12%, transparent); border: 1px solid color-mix(in srgb, var(--app-brand) 30%, var(--app-border)); border-radius: 0.65rem; color: var(--app-brand); cursor: pointer; font-size: 0.78rem; font-weight: 600; padding: 0.5rem 0.85rem; transition: opacity 150ms; white-space: nowrap; }
    .reg-copy-btn:hover { opacity: 0.85; }
    .reg-divider { height: 1px; background: var(--app-border); margin: 1rem 0; }
    </style>
    @endpush

    <script>
    function openConfirmModal(btn) {
        const id   = btn.dataset.id;
        const name = btn.dataset.name;
        document.getElementById('confirm-sub').textContent = `Confirming: ${name}`;
        document.getElementById('confirm-form').action = `/members/registrations/${id}/confirm`;
        document.getElementById('confirm-modal').classList.remove('hidden');
    }

    function openRejectModal(btn) {
        const id   = btn.dataset.id;
        const name = btn.dataset.name;
        document.getElementById('reject-sub').textContent = `Rejecting: ${name}`;
        document.getElementById('reject-form').action = `/members/registrations/${id}/reject`;
        document.getElementById('reject-modal').classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    document.getElementById('link-btn')?.addEventListener('click', function () {
        document.getElementById('link-modal').classList.remove('hidden');
    });

    function copyUrl() {
        const input = document.getElementById('reg-url-input');
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = document.getElementById('copy-btn');
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = 'Copy', 1800);
        }).catch(() => {
            input.select();
            document.execCommand('copy');
        });
    }

    // Close modals on backdrop click
    document.querySelectorAll('.reg-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function (e) {
            if (e.target === this) this.classList.add('hidden');
        });
    });

    // Auto-open link modal if email was just sent
    @if(session('email_sent'))
        document.getElementById('link-modal').classList.remove('hidden');
    @endif
    </script>

</x-layouts.admin>
