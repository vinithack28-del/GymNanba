@php
    $pickerId = 'as-picker-' . str_replace('.', '-', uniqid('', true));
@endphp

<form method="GET" action="{{ $action }}" class="as-panel-tight">
    @foreach ($extra ?? [] as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach
    <input type="hidden" name="member_id" id="{{ $pickerId }}-member-id" value="{{ $member?->id }}">
    <label class="as-label">{{ $label ?? 'Select Client' }}</label>
    <div class="as-inline">
        <div class="as-search-wrap" style="flex:1;min-width:240px">
            <input type="text" class="as-input" id="{{ $pickerId }}-input"
                   value="{{ $member ? $member->name . ' · ' . $member->member_code . ' · ' . $member->phone : '' }}"
                   placeholder="Search by client name, phone or email" autocomplete="off">
            <div class="as-search-results" id="{{ $pickerId }}-results"></div>
        </div>
@if ($member)
            <a href="{{ $action }}" class="as-btn as-btn-secondary">Clear</a>
        @endif
    </div>
</form>

<script>
(() => {
    const input = document.getElementById(@json($pickerId . '-input'));
    const box = document.getElementById(@json($pickerId . '-results'));
    const hidden = document.getElementById(@json($pickerId . '-member-id'));
    let timer = null;

    input?.addEventListener('input', () => {
        const q = input.value.trim();
        hidden.value = '';
        if (timer) clearTimeout(timer);
        if (q.length < 2) {
            box.style.display = 'none';
            box.innerHTML = '';
            return;
        }
        timer = setTimeout(async () => {
            const res = await fetch(`{{ route('tenant.assess.member-search') }}?q=${encodeURIComponent(q)}`, {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const data = await res.json();
            box.innerHTML = data.map(m => `
                <div class="as-search-row" data-id="${m.id}" data-name="${(m.name || '').replace(/"/g, '&quot;')}" data-code="${m.member_code}" data-phone="${m.phone}">
                    ${m.name} <span style="color:var(--app-text-muted)">· ${m.member_code} · ${m.phone}</span>
                </div>
            `).join('');
            box.style.display = data.length ? 'block' : 'none';
            box.querySelectorAll('.as-search-row').forEach(row => {
                row.addEventListener('click', () => {
                    hidden.value = row.dataset.id;
                    input.value = `${row.dataset.name} · ${row.dataset.code} · ${row.dataset.phone}`;
                    box.style.display = 'none';
                    input.closest('form').submit();
                });
            });
        }, 180);
    });

    document.addEventListener('click', (event) => {
        if (!box.contains(event.target) && event.target !== input) {
            box.style.display = 'none';
        }
    });
})();
</script>
