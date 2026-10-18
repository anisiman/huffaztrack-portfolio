@extends('layouts/lecturer')

@section('title', 'Reports')

@push('styles')
<style>
/* ── Page ─────────────────────────────────── */
.rh-page  { display:flex; flex-direction:column; gap:1.75rem; }
.rh-header { display:flex; flex-direction:column; gap:0.25rem; }
.rh-title    { margin:0; font-size:2.25rem; font-weight:700; letter-spacing:-0.02em; color:#0f172a; }
.rh-subtitle { margin:0; font-size:0.92rem; color:#94a3b8; }

/* ── Section group ────────────────────────── */
.rh-section { display:flex; flex-direction:column; gap:0.75rem; }
.rh-section__label {
    display:flex; align-items:center; gap:0.5rem;
    font-size:0.72rem; font-weight:700; color:#94a3b8;
    text-transform:uppercase; letter-spacing:.07em;
}
.rh-section__label::after {
    content:''; flex:1; height:1px; background:#e2e8f0;
}

/* ── Cards grid ───────────────────────────── */
.rh-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:1.25rem;
}
@media(max-width:900px){ .rh-grid { grid-template-columns:1fr; } }

/* ── Single card ──────────────────────────── */
.rh-card {
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:1.25rem;
    overflow:hidden;
    box-shadow:0 10px 28px rgba(15,23,42,.06);
    display:flex;
    flex-direction:column;
}
.rh-card__top {
    padding:1.4rem 1.5rem 1rem;
    border-bottom:1px solid #f1f5f9;
    display:flex;
    align-items:flex-start;
    gap:0.9rem;
}
.rh-card__icon {
    width:2.4rem;
    height:2.4rem;
    border-radius:0.7rem;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
}
.rh-card__icon svg { width:1.15rem; height:1.15rem; }
.rh-card__icon--daily    { background:#f5f3ff; color:#7c3aed; }
.rh-card__icon--weekly   { background:#ede9fe; color:#6d28d9; }
.rh-card__icon--attend   { background:#e0e7ff; color:#4338ca; }
.rh-card__icon--semester { background:#fef3c7; color:#d97706; }
.rh-card__meta { flex:1; }
.rh-card__name { margin:0; font-size:1rem; font-weight:700; color:#1e293b; }
.rh-card__desc { margin:0.2rem 0 0; font-size:0.8rem; color:#94a3b8; line-height:1.5; }

/* ── Card body (selector) ─────────────────── */
.rh-card__body { padding:1.1rem 1.5rem; display:flex; flex-direction:column; gap:0.6rem; flex:1; }
.rh-field-label {
    font-size:0.72rem; font-weight:700; color:#94a3b8;
    text-transform:uppercase; letter-spacing:.05em;
}
.rh-field-input {
    width:100%;
    min-height:2.3rem;
    padding:0 0.85rem;
    border:1px solid #e2e8f0;
    border-radius:0.7rem;
    background:#f8fafc;
    color:#334155;
    font-size:0.875rem;
    cursor:pointer;
    outline:none;
    box-sizing:border-box;
}
.rh-field-input:focus { border-color:#7c3aed; background:#fff; }

/* ── Card footer (buttons) ────────────────── */
.rh-card__foot {
    padding:0 1.5rem 1.4rem;
    display:flex;
    gap:0.6rem;
}
.rh-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:0.4rem;
    min-height:2.2rem; padding:0 1.1rem;
    border:0; border-radius:0.65rem;
    font-size:0.85rem; font-weight:700; cursor:pointer;
    text-decoration:none;
}
.rh-btn--primary {
    background:linear-gradient(90deg,#5b21b6,#7c3aed);
    color:#fff;
    box-shadow:0 6px 18px rgba(124,58,237,.2);
    flex:1;
}
.rh-btn--pdf {
    background:#f1f5f9;
    color:#94a3b8;
    border:1px solid #e2e8f0;
    cursor:not-allowed;
    gap:0.35rem;
}
.rh-btn--pdf svg { width:0.9rem; height:0.9rem; }

/* ── Info note ────────────────────────────── */
.rh-note {
    display:flex; align-items:center; gap:0.6rem;
    background:#f5f3ff; border:1px solid #ddd6fe;
    border-radius:0.8rem; padding:0.7rem 1rem;
    font-size:0.82rem; color:#5b21b6;
}
.rh-note svg { width:1rem; height:1rem; flex-shrink:0; }

/* ── PDF button (active) ──────────────────── */
.rh-btn--pdf-active {
    background:#fff;
    color:#64748b;
    border:1px solid #e2e8f0;
    gap:0.35rem;
}
.rh-btn--pdf-active:hover {
    background:#f5f3ff;
    color:#6d28d9;
    border-color:#c4b5fd;
}
.rh-btn--pdf-active svg { width:0.9rem; height:0.9rem; }
</style>
@endpush

@section('content')
<div class="rh-page">

    {{-- ── Header ── --}}
    <div class="rh-header">
        <h1 class="rh-title">Reports</h1>
        <p class="rh-subtitle">Select a report type, choose the period, then preview or export.</p>
    </div>

    {{-- ── Info note ── --}}
    <div class="rh-note">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        Choose a report period below and click <strong>Preview</strong> to view the data, or <strong>Download PDF</strong> to export directly.
    </div>

    {{-- ── Hafazan Reports ── --}}
    <div class="rh-section">
        <div class="rh-section__label">Hafazan Reports</div>
        <div class="rh-grid">

            {{-- ── Daily Hafazan Report ── --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--daily">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Daily Hafazan Report</h2>
                        <p class="rh-card__desc">Pages reached by each student on a specific day.</p>
                    </div>
                </div>

                <form class="rh-card__body" method="GET" action="{{ route('lecturer.reports.daily') }}" id="form-daily">
                    <label class="rh-field-label" for="daily-date">Select Date</label>
                    <input class="rh-field-input" type="date" id="daily-date" name="date"
                        value="{{ $today->format('Y-m-d') }}"
                        max="{{ $today->format('Y-m-d') }}">
                </form>

                <div class="rh-card__foot">
                    <button type="submit" form="form-daily" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                    <button type="button" class="rh-btn rh-btn--pdf-active" onclick="downloadPdf('daily')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>

            {{-- ── Weekly Hafazan Report ── --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--weekly">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Weekly Hafazan Report</h2>
                        <p class="rh-card__desc">Progress vs. weekly muqarrar per student.</p>
                    </div>
                </div>

                <form class="rh-card__body" method="GET" action="{{ route('lecturer.reports.weekly') }}" id="form-weekly">
                    <label class="rh-field-label" for="weekly-week">Select Week</label>
                    <select class="rh-field-input" id="weekly-week" name="muqarrar_id">
                        @forelse ($allWeeks as $w)
                            <option value="{{ $w->muqarrar_id }}">
                                Week {{ $w->week }}
                                @if ($w->week_start_date)
                                    ({{ \Carbon\Carbon::parse($w->week_start_date)->format('d M') }})
                                @endif
                                — pg {{ $w->start_page }}–{{ $w->end_page }}
                            </option>
                        @empty
                            <option value="">No weeks available</option>
                        @endforelse
                    </select>
                </form>

                <div class="rh-card__foot">
                    <button type="submit" form="form-weekly" class="rh-btn rh-btn--primary" {{ $allWeeks->isEmpty() ? 'disabled' : '' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                    <button type="button" class="rh-btn rh-btn--pdf-active" onclick="downloadPdf('weekly')" {{ $allWeeks->isEmpty() ? 'disabled' : '' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>

            {{-- ── Hafazan Monthly Report ── --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--weekly">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Monthly Hafazan Record</h2>
                        <p class="rh-card__desc">Monthly hafazan progress — latest page, sessions, avg weekly gain.</p>
                    </div>
                </div>

                <form class="rh-card__body" method="GET" action="{{ route('lecturer.reports.hafazan.monthly') }}" id="form-hfz-monthly">
                    <label class="rh-field-label" for="hfz-month">Select Month</label>
                    <select class="rh-field-input" id="hfz-month" name="month">
                        @foreach ($months as $m)
                            <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="rh-card__foot">
                    <button type="submit" form="form-hfz-monthly" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                    <button type="button" class="rh-btn rh-btn--pdf-active" onclick="downloadPdf('hfz-monthly')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>

            {{-- ── Semester Hafazan Report ── --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--semester">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Semester Hafazan Record</h2>
                        <p class="rh-card__desc">Full-semester hafazan — pages, sessions, weekly avg gain per student.</p>
                    </div>
                </div>

                <form class="rh-card__body" method="GET" action="{{ route('lecturer.reports.hafazan.semester') }}" id="form-hfz-sem">
                    <label class="rh-field-label" for="hfz-sem">Academic Semester</label>
                    <select class="rh-field-input" id="hfz-sem" name="academic_period_id">
                        @foreach ($periodOptions as $p)
                            <option value="{{ $p['value'] }}" {{ $currentPeriodId === $p['value'] ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="rh-card__foot">
                    <button type="submit" form="form-hfz-sem" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                    <button type="button" class="rh-btn rh-btn--pdf-active" onclick="downloadPdf('hfz-sem')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Attendance Reports ── --}}
    <div class="rh-section">
        <div class="rh-section__label">Attendance Reports</div>
        <div class="rh-grid">

            {{-- ── Attendance Daily Report ── --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--daily">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Daily Attendance Reports</h2>
                        <p class="rh-card__desc">Full attendance list for a single day with status and remarks.</p>
                    </div>
                </div>

                <form class="rh-card__body" method="GET" action="{{ route('lecturer.reports.attendance.daily') }}" id="form-att-daily">
                    <label class="rh-field-label" for="att-daily-date">Select Date</label>
                    <input class="rh-field-input" type="date" id="att-daily-date" name="date"
                        value="{{ $today->format('Y-m-d') }}"
                        max="{{ $today->format('Y-m-d') }}">
                </form>

                <div class="rh-card__foot">
                    <button type="submit" form="form-att-daily" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                    <button type="button" class="rh-btn rh-btn--pdf-active" onclick="downloadPdf('att-daily')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>

            {{-- ── Attendance Weekly Report ── --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--attend">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Weekly Attendance Reports</h2>
                        <p class="rh-card__desc">Weekly attendance summary — present, TH, THS, and rate per student.</p>
                    </div>
                </div>

                <form class="rh-card__body" method="GET" action="{{ route('lecturer.reports.attendance.weekly') }}" id="form-att-weekly">
                    <label class="rh-field-label" for="att-weekly-date">Select any date in the week</label>
                    <input class="rh-field-input" type="date" id="att-weekly-date" name="week_start"
                        value="{{ $today->format('Y-m-d') }}"
                        max="{{ $today->format('Y-m-d') }}">
                </form>

                <div class="rh-card__foot">
                    <button type="submit" form="form-att-weekly" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                    <button type="button" class="rh-btn rh-btn--pdf-active" onclick="downloadPdf('att-weekly')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>

            {{-- ── Attendance Monthly Report ── --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--attend">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Monthly Attendance Reports</h2>
                        <p class="rh-card__desc">Monthly attendance summary — present, absent, excused.</p>
                    </div>
                </div>

                <form class="rh-card__body" method="GET" action="{{ route('lecturer.reports.attendance') }}" id="form-attend">
                    <label class="rh-field-label" for="attend-month">Select Month</label>
                    <select class="rh-field-input" id="attend-month" name="month">
                        @foreach ($months as $m)
                            <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="rh-card__foot">
                    <button type="submit" form="form-attend" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                    <button type="button" class="rh-btn rh-btn--pdf-active" onclick="downloadPdf('attend')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>

            {{-- ── Semester Attendance Report ── --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--semester">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Semester Attendance Report</h2>
                        <p class="rh-card__desc">Full-semester attendance — present, TH, THS, rate and warning level.</p>
                    </div>
                </div>

                <form class="rh-card__body" method="GET" action="{{ route('lecturer.reports.attendance.semester') }}" id="form-att-sem">
                    <label class="rh-field-label" for="att-sem">Academic Semester</label>
                    <select class="rh-field-input" id="att-sem" name="academic_period_id">
                        @foreach ($periodOptions as $p)
                            <option value="{{ $p['value'] }}" {{ $currentPeriodId === $p['value'] ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="rh-card__foot">
                    <button type="submit" form="form-att-sem" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                    <button type="button" class="rh-btn rh-btn--pdf-active" onclick="downloadPdf('att-sem')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Subject Attendance Reports ── --}}
    <div class="rh-section">
        <div class="rh-section__label">Subject Attendance Reports</div>
        <div class="rh-grid">

            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--attend">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s4.332.477 5.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Subject Attendance</h2>
                        <p class="rh-card__desc">Present/absent counts and attendance % for your own subjects and batches only.</p>
                    </div>
                </div>
                <div class="rh-card__foot" style="padding-top:1.1rem;">
                    <a href="{{ route('lecturer.reports.subjectAttendance') }}" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        View Report
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

@section('scripts')
<script>
const pdfRoutes = {
    daily:       '{{ route("lecturer.reports.daily.pdf") }}',
    weekly:      '{{ route("lecturer.reports.weekly.pdf") }}',
    attend:      '{{ route("lecturer.reports.attendance.pdf") }}',
    'hfz-monthly': '{{ route("lecturer.reports.hafazan.monthly.pdf") }}',
    'att-daily':   '{{ route("lecturer.reports.attendance.daily.pdf") }}',
    'att-weekly':  '{{ route("lecturer.reports.attendance.weekly.pdf") }}',
    'hfz-sem':     '{{ route("lecturer.reports.hafazan.semester.pdf") }}',
    'att-sem':     '{{ route("lecturer.reports.attendance.semester.pdf") }}',
};
const paramKeys = {
    daily:         { inputId: 'daily-date',      param: 'date' },
    weekly:        { inputId: 'weekly-week',      param: 'muqarrar_id' },
    attend:        { inputId: 'attend-month',     param: 'month' },
    'hfz-monthly': { inputId: 'hfz-month',        param: 'month' },
    'att-daily':   { inputId: 'att-daily-date',   param: 'date' },
    'att-weekly':  { inputId: 'att-weekly-date',  param: 'week_start' },
    'hfz-sem':     { inputId: 'hfz-sem',          param: 'academic_period_id' },
    'att-sem':     { inputId: 'att-sem',          param: 'academic_period_id' },
};

function downloadPdf(type) {
    const { inputId, param } = paramKeys[type];
    const val = document.getElementById(inputId).value;
    if (!val) return;
    window.location.href = pdfRoutes[type] + '?' + param + '=' + encodeURIComponent(val);
}
</script>
@endsection

@endsection
