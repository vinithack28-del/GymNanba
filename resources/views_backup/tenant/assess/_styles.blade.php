<style>
.as-shell{display:flex;flex-direction:column;gap:1.25rem}
.as-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.as-title{font-size:1.25rem;font-weight:700;color:var(--app-text)}
.as-sub{font-size:.9rem;color:var(--app-text-muted);margin-top:.2rem}
.as-panel{background:var(--app-panel);border:1px solid var(--app-border);border-radius:1.5rem;padding:1.25rem}
.as-panel-tight{background:var(--app-panel);border:1px solid var(--app-border);border-radius:1.25rem;padding:1rem}
.as-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:1rem}
.as-col-4{grid-column:span 4}.as-col-6{grid-column:span 6}.as-col-8{grid-column:span 8}.as-col-12{grid-column:span 12}
@media(max-width:900px){.as-col-4,.as-col-6,.as-col-8{grid-column:span 12}}
.as-label{display:block;font-size:.75rem;font-weight:600;margin-bottom:.35rem;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.04em}
.as-input,.as-select,.as-textarea{width:100%;border:1px solid var(--app-border);background:var(--app-panel-strong);color:var(--app-text);border-radius:.9rem;padding:.7rem .9rem;font-size:.9rem;outline:none}
.as-textarea{min-height:94px;resize:vertical}
.as-input:focus,.as-select:focus,.as-textarea:focus{border-color:var(--app-brand)}
.as-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;border-radius:.95rem;padding:.72rem 1rem;font-size:.88rem;font-weight:700;text-decoration:none;transition:opacity .15s ease;border:1px solid transparent;cursor:pointer}
.as-btn:hover{opacity:.88}
.as-btn-primary{background:var(--app-brand);color:#fff}
.as-btn-secondary{background:var(--app-panel-strong);color:var(--app-text);border-color:var(--app-border)}
.as-btn-danger{background:#b91c1c;color:#fff}
.as-table-wrap{overflow-x:auto}
.as-table{width:100%;border-collapse:collapse;font-size:.9rem}
.as-table th,.as-table td{padding:.85rem .85rem;border-top:1px solid var(--app-border);vertical-align:top}
.as-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--app-text-muted);background:var(--app-panel-strong);border-top:none;text-align:left}
.as-badge{display:inline-flex;align-items:center;padding:.2rem .55rem;border-radius:999px;font-size:.72rem;font-weight:700}
.as-badge-green{background:#dcfce7;color:#166534}.as-badge-amber{background:#fef3c7;color:#92400e}.as-badge-red{background:#fee2e2;color:#991b1b}.as-badge-slate{background:#e2e8f0;color:#334155}
.as-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem}
@media(max-width:900px){.as-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
.as-stat{background:var(--app-panel);border:1px solid var(--app-border);border-radius:1.3rem;padding:1rem}
.as-stat-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--app-text-muted);font-weight:700}
.as-stat-value{font-size:1.45rem;font-weight:800;color:var(--app-text);margin-top:.3rem}
.as-help{font-size:.78rem;color:var(--app-text-muted)}
.as-empty{padding:2rem;text-align:center;color:var(--app-text-muted);font-size:.92rem}
.as-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.as-inline{display:flex;gap:.75rem;flex-wrap:wrap;align-items:end}
.as-tabs{display:flex;gap:.5rem;flex-wrap:wrap}
.as-tab{display:inline-flex;align-items:center;padding:.65rem 1rem;border-radius:999px;border:1px solid var(--app-border);background:var(--app-panel);color:var(--app-text);font-weight:700;text-decoration:none}
.as-tab-active{background:var(--app-brand);border-color:var(--app-brand);color:#fff}
.as-kv{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}
@media(max-width:760px){.as-kv{grid-template-columns:1fr}}
.as-kv-item{border:1px solid var(--app-border);background:var(--app-panel-strong);border-radius:1rem;padding:.9rem}
.as-kv-key{font-size:.74rem;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.06em}
.as-kv-val{font-size:1rem;color:var(--app-text);font-weight:700;margin-top:.25rem}
.as-search-wrap{position:relative}
.as-search-results{position:absolute;z-index:20;left:0;right:0;top:calc(100% + .35rem);background:var(--app-panel);border:1px solid var(--app-border);border-radius:1rem;box-shadow:0 12px 28px rgba(15,23,42,.12);display:none;max-height:16rem;overflow:auto}
.as-search-row{padding:.7rem .85rem;cursor:pointer;color:var(--app-text);font-size:.88rem}
.as-search-row:hover{background:var(--app-panel-strong)}
</style>

<script>
function assessConfirmDelete(formId, expectedName) {
    const typed = window.prompt(`Type ${expectedName} to confirm permanent deletion.`);
    if (typed === null) return false;
    if (typed !== expectedName) {
        alert('Client name does not match.');
        return false;
    }
    const form = document.getElementById(formId);
    if (!form) return false;
    const input = form.querySelector('input[name="confirm_name"]');
    if (input) input.value = typed;
    form.submit();
    return true;
}
</script>
