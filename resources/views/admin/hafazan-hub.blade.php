@extends('layouts/admin')

@section('title', 'Reports')

@push('styles')
<style>
.rh-page  { display:flex; flex-direction:column; gap:2rem; }
.rh-header { display:flex; flex-direction:column; gap:0.3rem; }
.rh-title    { margin:0; font-size:2.25rem; font-weight:700; color:#1e293b; line-height:1.25; }
.rh-subtitle { margin:0.125rem 0 0; font-size:0.875rem; color:#94a3b8; }

.rh-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:1.5rem;
}
@media(max-width:960px){ .rh-grid { grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px){ .rh-grid { grid-template-columns:1fr; } }

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
    padding:1.5rem 1.5rem 1.1rem;
    border-bottom:1px solid #f1f5f9;
    display:flex;
    align-items:flex-start;
    gap:1rem;
}
.rh-card__icon {
    width:2.5rem; height:2.5rem;
    border-radius:0.75rem;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.rh-card__icon svg { width:1.15rem; height:1.15rem; }
.rh-card__icon--daily    { background:#f5f3ff; color:#7c3aed; }
.rh-card__icon--weekly   { background:#ede9fe; color:#6d28d9; }
.rh-card__icon--monthly  { background:#e0e7ff; color:#4338ca; }
.rh-card__icon--attend   { background:#dcfce7; color:#16a34a; }
.rh-card__icon--semester { background:#fef3c7; color:#d97706; }

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
.rh-card__meta { flex:1; }
.rh-card__name { margin:0; font-size:1rem; font-weight:700; color:#1e293b; }
.rh-card__desc { margin:0.25rem 0 0; font-size:0.8rem; color:#94a3b8; line-height:1.5; }

.rh-card__body { padding:1.1rem 1.5rem; display:flex; flex-direction:column; gap:0.6rem; flex:1; }
.rh-field-label {
    font-size:0.72rem; font-weight:700; color:#94a3b8;
    text-transform:uppercase; letter-spacing:.05em;
}
.rh-field-input {
    width:100%; min-height:2.3rem; padding:0 0.85rem;
    border:1px solid #e2e8f0; border-radius:0.7rem;
    background:#f8fafc; color:#334155;
    font-size:0.875rem; cursor:pointer; outline:none; box-sizing:border-box;
}
.rh-field-input:focus { border-color:#7c3aed; background:#fff; }

.rh-card__foot { padding:0 1.5rem 1.5rem; display:flex; gap:0.6rem; }
.rh-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:0.4rem;
    min-height:2.25rem; padding:0 1.1rem;
    border:0; border-radius:0.65rem;
    font-size:0.85rem; font-weight:700; cursor:pointer; text-decoration:none;
}
.rh-btn--primary {
    background:linear-gradient(90deg,#5b21b6,#7c3aed);
    color:#fff; box-shadow:0 6px 18px rgba(124,58,237,.2); flex:1;
}
.rh-btn--primary:disabled { opacity:.5; cursor:not-allowed; }

/* ── Global batch selector (mirrors Masterlist's batch-nav) ── */
.rh-batch-nav {
    display:flex; gap:0.5rem; overflow-x:auto; padding-bottom:8px;
}
.rh-batch-chip {
    padding:0.5rem 1.25rem;
    background:#fff; border:1px solid #e2e8f0; border-radius:10px;
    color:#64748b; text-decoration:none;
    font-size:0.875rem; font-weight:500;
    transition:all 0.2s ease; white-space:nowrap; flex-shrink:0;
}
.rh-batch-chip:hover { border-color:#7c3aed; color:#7c3aed; background:#f5f3ff; }
.rh-batch-chip.active {
    background:#7c3aed; color:#fff; border-color:#7c3aed;
    box-shadow:0 4px 12px rgba(124,58,237,.2);
}
</style>
@endpush

@section('content')
<div class="rh-page">

    <div class="rh-header">
        <h1 class="rh-title">
            @if ($section === 'attendance')
                Attendance Reports
            @elseif ($section === 'hafazan')
                Hafazan Reports
            @else
                Reports
            @endif
        </h1>
        <p class="rh-subtitle">
            Select a report type and period, then preview.
            @if ($currentBatch !== 'Overall')
                &nbsp;Currently filtering: <strong>{{ $currentBatch }}</strong>.
            @endif
        </p>
    </div>

    {{-- ── Global batch selector — pick once, every card below previews that batch ── --}}
    <div class="rh-batch-nav">
        <a href="{{ route('admin.reports', array_filter(['section' => $section ?: null, 'batch' => 'Overall'])) }}"
            class="rh-batch-chip {{ $currentBatch === 'Overall' ? 'active' : '' }}">Overall</a>
        @foreach ($halaqahOptions as $h)
            <a href="{{ route('admin.reports', array_filter(['section' => $section ?: null, 'batch' => $h])) }}"
                class="rh-batch-chip {{ $currentBatch === $h ? 'active' : '' }}">{{ $h }}</a>
        @endforeach
    </div>

    @if ($section === '' || $section === 'hafazan')
    {{-- ── Hafazan Reports ── --}}
    <div class="rh-section">
        <div class="rh-section__label">Hafazan Reports</div>
        <div class="rh-grid">

            {{-- Daily Hafazan --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--daily">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Daily Hafazan</h2>
                        <p class="rh-card__desc">Today's tasmi coverage — who recorded and who's missing.</p>
                    </div>
                </div>
                <form class="rh-card__body" method="GET" action="{{ route('admin.reports.hafazan.daily') }}" id="form-daily">
                    <label class="rh-field-label" for="daily-date">Date</label>
                    <input class="rh-field-input" type="date" id="daily-date" name="date"
                        value="{{ $today->format('Y-m-d') }}" max="{{ $today->format('Y-m-d') }}"
                        onchange="this.form.submit()">
                    <input type="hidden" name="halaqah" value="{{ $currentBatch === 'Overall' ? '' : $currentBatch }}">
                </form>
                <div class="rh-card__foot">
                    <button type="submit" form="form-daily" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                </div>
            </div>

            {{-- Weekly Hafazan --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--weekly">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Weekly Hafazan</h2>
                        <p class="rh-card__desc">Muqarrar progress per lecturer — healthy, at-risk, and missing.</p>
                    </div>
                </div>
                <form class="rh-card__body" method="GET" action="{{ route('admin.reports.hafazan.weekly') }}" id="form-weekly">
                    <label class="rh-field-label" for="weekly-week">Week</label>
                    <select class="rh-field-input" id="weekly-week" name="muqarrar_id" onchange="this.form.submit()">
                        @forelse ($allWeeks as $w)
                            <option value="{{ $w->muqarrar_id }}">
                                Week {{ $w->week }}
                                @if ($w->week_start_date)
                                    ({{ \Carbon\Carbon::parse($w->week_start_date)->format('d M') }})
                                @endif
                                — pg {{ $w->start_page }}–{{ $w->end_page }}
                            </option>
                        @empty
                            <option value="">No weeks configured</option>
                        @endforelse
                    </select>
                    <input type="hidden" name="halaqah" value="{{ $currentBatch === 'Overall' ? '' : $currentBatch }}">
                </form>
                <div class="rh-card__foot">
                    <button type="submit" form="form-weekly" class="rh-btn rh-btn--primary" {{ $allWeeks->isEmpty() ? 'disabled' : '' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                </div>
            </div>

            {{-- Monthly Hafazan --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--monthly">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Monthly Hafazan</h2>
                        <p class="rh-card__desc">Monthly session coverage and activity per lecturer.</p>
                    </div>
                </div>
                <form class="rh-card__body" method="GET" action="{{ route('admin.reports.hafazan.monthly') }}" id="form-monthly">
                    <label class="rh-field-label" for="monthly-month">Month</label>
                    <select class="rh-field-input" id="monthly-month" name="month" onchange="this.form.submit()">
                        @foreach ($months as $m)
                            <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="halaqah" value="{{ $currentBatch === 'Overall' ? '' : $currentBatch }}">
                </form>
                <div class="rh-card__foot">
                    <button type="submit" form="form-monthly" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                </div>
            </div>

            {{-- Semester Hafazan --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--semester">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Semester Hafazan</h2>
                        <p class="rh-card__desc">Full-semester hafazan progress — pages, sessions, weekly avg per student.</p>
                    </div>
                </div>
                <form class="rh-card__body" method="GET" action="{{ route('admin.reports.hafazan.semester') }}" id="form-sem-hfz">
                    <label class="rh-field-label" for="sem-hfz">Academic Semester</label>
                    <select class="rh-field-input" id="sem-hfz" name="academic_period_id" onchange="this.form.submit()">
                        @foreach ($periodOptions as $p)
                            <option value="{{ $p['value'] }}" {{ $currentPeriodId === $p['value'] ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="halaqah" value="{{ $currentBatch === 'Overall' ? '' : $currentBatch }}">
                </form>
                <div class="rh-card__foot">
                    <button type="submit" form="form-sem-hfz" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                </div>
            </div>

        </div>
    </div>
    @endif

    @if ($section === '' || $section === 'attendance')
    {{-- ── Attendance Reports ── --}}
    <div class="rh-section">
        <div class="rh-section__label">Attendance Reports</div>
        <div class="rh-grid">

            {{-- Attendance Daily --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--attend">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Attendance Daily</h2>
                        <p class="rh-card__desc">Full attendance list for all students on a selected date.</p>
                    </div>
                </div>
                <form class="rh-card__body" method="GET" action="{{ route('admin.reports.attendance.daily') }}" id="form-att-daily">
                    <label class="rh-field-label" for="att-daily-date">Date</label>
                    <input class="rh-field-input" type="date" id="att-daily-date" name="date"
                        value="{{ $today->format('Y-m-d') }}" max="{{ $today->format('Y-m-d') }}"
                        onchange="this.form.submit()">
                    <input type="hidden" name="halaqah" value="{{ $currentBatch === 'Overall' ? '' : $currentBatch }}">
                </form>
                <div class="rh-card__foot">
                    <button type="submit" form="form-att-daily" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                </div>
            </div>

            {{-- Attendance Weekly --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--attend">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Attendance Weekly</h2>
                        <p class="rh-card__desc">Weekly attendance summary — present, TH, THS, and rate per student.</p>
                    </div>
                </div>
                <form class="rh-card__body" method="GET" action="{{ route('admin.reports.attendance.weekly') }}" id="form-att-weekly">
                    <label class="rh-field-label" for="att-weekly-date">Any date in the week</label>
                    <input class="rh-field-input" type="date" id="att-weekly-date" name="week_start"
                        value="{{ $today->format('Y-m-d') }}" max="{{ $today->format('Y-m-d') }}"
                        onchange="this.form.submit()">
                    <input type="hidden" name="halaqah" value="{{ $currentBatch === 'Overall' ? '' : $currentBatch }}">
                </form>
                <div class="rh-card__foot">
                    <button type="submit" form="form-att-weekly" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                </div>
            </div>

            {{-- Attendance Monthly --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--monthly">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Attendance Monthly</h2>
                        <p class="rh-card__desc">Monthly attendance analytics — present, absent, excused per student.</p>
                    </div>
                </div>
                <form class="rh-card__body" method="GET" action="{{ route('admin.reports.attendance.monthly') }}" id="form-att-monthly">
                    <label class="rh-field-label" for="att-monthly-month">Month</label>
                    <select class="rh-field-input" id="att-monthly-month" name="month" onchange="this.form.submit()">
                        @foreach ($months as $m)
                            <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="halaqah" value="{{ $currentBatch === 'Overall' ? '' : $currentBatch }}">
                </form>
                <div class="rh-card__foot">
                    <button type="submit" form="form-att-monthly" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
                    </button>
                </div>
            </div>

            {{-- Semester Attendance --}}
            <div class="rh-card">
                <div class="rh-card__top">
                    <div class="rh-card__icon rh-card__icon--semester">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div class="rh-card__meta">
                        <h2 class="rh-card__name">Semester Attendance</h2>
                        <p class="rh-card__desc">Full-semester attendance — present, TH, THS, rate and warning level per student.</p>
                    </div>
                </div>
                <form class="rh-card__body" method="GET" action="{{ route('admin.reports.attendance.semester') }}" id="form-sem-att">
                    <label class="rh-field-label" for="sem-att">Academic Semester</label>
                    <select class="rh-field-input" id="sem-att" name="academic_period_id" onchange="this.form.submit()">
                        @foreach ($periodOptions as $p)
                            <option value="{{ $p['value'] }}" {{ $currentPeriodId === $p['value'] ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="halaqah" value="{{ $currentBatch === 'Overall' ? '' : $currentBatch }}">
                </form>
                <div class="rh-card__foot">
                    <button type="submit" form="form-sem-att" class="rh-btn rh-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Preview
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
                        <p class="rh-card__desc">Present/absent counts and attendance % per student per subject, filterable by semester, subject, batch, lecturer and date range.</p>
                    </div>
                </div>
                <div class="rh-card__foot" style="padding-top:1.1rem;">
                    <a href="{{ route('admin.reports.subjectAttendance') }}" class="rh-btn rh-btn--primary">
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
    @endif

</div>
@endsection
