<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>

@@page { margin: 20mm 22mm; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1a1a1a; background: #fff; }

.doc-header { padding-bottom: 10pt; margin-bottom: 18pt; border-bottom: 2pt solid #1e3a5f; }
.doc-header-table { width: 100%; border-collapse: collapse; }
.doc-brand { font-size: 13pt; font-weight: bold; color: #1e3a5f; }
.doc-brand-sub { font-size: 7pt; color: #555; margin-top: 2pt; }
.doc-meta { text-align: right; font-size: 7.5pt; color: #444; line-height: 1.8; vertical-align: top; }

.doc-title { font-size: 12.5pt; font-weight: bold; color: #1a1a1a; margin-bottom: 3pt; }
.doc-period { font-size: 8pt; color: #444; padding-bottom: 12pt; margin-bottom: 14pt; border-bottom: 0.5pt solid #ccc; }

.doc-summary { width: 100%; border-collapse: collapse; border: 1pt solid #bbb; margin-bottom: 14pt; }
.doc-summary td { padding: 6pt 10pt; font-size: 8pt; border-right: 0.5pt solid #ccc; border-bottom: 0.5pt solid #ccc; vertical-align: middle; }
.doc-summary tr:last-child td { border-bottom: none; }
.doc-summary td:last-child { border-right: none; }
.sl { color: #555; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.2pt; display: block; }
.sv { font-weight: bold; color: #1a1a1a; font-size: 9.5pt; }

.doc-section { font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5pt; color: #1e3a5f; margin-bottom: 6pt; }

.doc-table { width: 100%; border-collapse: collapse; border: 1pt solid #bbb; }
.doc-table th { background: #e6e6e6; font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3pt; color: #1a1a1a; padding: 6pt 8pt; text-align: left; border-bottom: 1.5pt solid #aaa; border-right: 0.5pt solid #ccc; }
.doc-table th:last-child { border-right: none; }
.doc-table td { font-size: 8.5pt; color: #222; padding: 6pt 8pt; border-bottom: 0.5pt solid #ddd; border-right: 0.5pt solid #ddd; vertical-align: middle; }
.doc-table td:last-child { border-right: none; }
.doc-table tbody tr:nth-child(even) td { background: #f5f5f5; }
.doc-name { font-weight: bold; color: #111; }
.doc-sub  { font-size: 7pt; color: #666; }
.doc-num  { font-weight: bold; }
.doc-mute { color: #777; }

.doc-footer { margin-top: 16pt; padding-top: 4pt; border-top: 1pt solid #bbb; font-size: 7pt; color: #555; text-align: center; }
</style>
</head>
<body>

<div class="doc-header">
    <table class="doc-header-table">
        <tr>
            <td>
                <div class="doc-brand">HuffazTrack</div>
                <div class="doc-brand-sub">Tahfiz Attendance &amp; Hafazan Monitoring System</div>
            </td>
            <td class="doc-meta">
                Scope: System-wide (All Batches)<br>
                Generated: {{ $generatedAt }}
            </td>
        </tr>
    </table>
</div>

<div class="doc-title">Daily Hafazan Report — Administration</div>
<div class="doc-period">
    Date: {{ $date->format('l, d F Y') }}@if ($date->isSameDay($today)) (Today)@endif
    &nbsp;&nbsp;|&nbsp;&nbsp; Total Students: {{ array_sum($hafDailyCounts) }}
    @if ($hafDailyMuq)
        &nbsp;&nbsp;|&nbsp;&nbsp; Muqarrar Week {{ $hafDailyMuq->week }}: Pg {{ $hafDailyMuq->start_page }} &rarr; {{ $hafDailyMuq->end_page }}
        @if ($hafDailyChk > 0)
            &nbsp;&nbsp;|&nbsp;&nbsp; Today's Target: Pg {{ $hafDailyChk }}
        @endif
    @endif
</div>

<table class="doc-summary">
    <tr>
        <td style="width:25%"><span class="sl">Completed</span><span class="sv">{{ $hafDailyCounts['Completed'] }}</span></td>
        <td style="width:25%"><span class="sl">On Track</span><span class="sv">{{ $hafDailyCounts['On Track'] }}</span></td>
        <td style="width:25%"><span class="sl">Behind</span><span class="sv">{{ $hafDailyCounts['Behind'] }}</span></td>
        <td style="width:25%"><span class="sl">Not Recorded</span><span class="sv">{{ $hafDailyCounts['Not Recorded'] }}</span></td>
    </tr>
</table>

<div class="doc-section">Lecturer Breakdown — {{ $date->format('d F Y') }}</div>
<table class="doc-table">
    <thead>
        <tr>
            <th style="width:4%">No.</th>
            <th style="width:28%">Lecturer</th>
            <th style="width:15%">Batch</th>
            <th style="width:8%; text-align:center;">Students</th>
            <th style="width:9%; text-align:center;">Completed</th>
            <th style="width:9%; text-align:center;">On Track</th>
            <th style="width:9%; text-align:center;">Behind</th>
            <th style="width:9%; text-align:center;">Not Rec.</th>
            <th style="width:9%; text-align:center;">Coverage</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($lecturerSummaries as $i => $row)
        <tr>
            <td style="text-align:center;">{{ $i + 1 }}</td>
            <td class="doc-name">{{ $row->lecturer_name }}</td>
            <td><span class="doc-sub">{{ $row->batch_name }}</span></td>
            <td style="text-align:center; font-weight:bold;">{{ $row->total }}</td>
            <td style="text-align:center;">{{ $row->completed }}</td>
            <td style="text-align:center;">{{ $row->on_track }}</td>
            <td style="text-align:center; font-weight:{{ $row->behind > 0 ? 'bold' : 'normal' }};">{{ $row->behind }}</td>
            <td style="text-align:center;" class="doc-mute">{{ $row->not_recorded }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $row->coverage_pct }}%</td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center; color:#888; padding:10pt;">No data for this date.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="doc-footer">
    HuffazTrack &mdash; Daily Hafazan Report (Administration) &mdash; {{ $date->format('d F Y') }} &mdash; Generated {{ $generatedAt }}
</div>
</body>
</html>
