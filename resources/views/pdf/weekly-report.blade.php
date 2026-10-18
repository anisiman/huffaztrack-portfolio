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

.doc-note { margin-top: 8pt; font-size: 7.5pt; color: #555; border-top: 0.5pt solid #ddd; padding-top: 5pt; }
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
                Prepared by: {{ $lecturerName }}<br>
                @isset($lecturerPhone)Phone: {{ $lecturerPhone }}<br>@endisset
                Generated: {{ $generatedAt }}
            </td>
        </tr>
    </table>
</div>

<div class="doc-title">Weekly Hafazan Report</div>
<div class="doc-period">
    {{ $weekLabel }}
    &nbsp;&nbsp;|&nbsp;&nbsp; Muqarrar: Pg {{ $muqarrar?->start_page ?? '—' }} &rarr; {{ $muqarrar?->end_page ?? '—' }}
    &nbsp;&nbsp;|&nbsp;&nbsp; End-of-week target: Pg {{ $checkpoint ?: '—' }}
    &nbsp;&nbsp;|&nbsp;&nbsp; Lecturer: {{ $lecturerName }}@isset($lecturerPhone) ({{ $lecturerPhone }})@endisset
    @if (!empty($filterHalaqah))
        &nbsp;&nbsp;|&nbsp;&nbsp; Halaqah: {{ $filterHalaqah }}
    @endif
</div>

<table class="doc-summary">
    <tr>
        <td style="width:25%"><span class="sl">On Track</span><span class="sv">{{ $onTrackCount }}</span></td>
        <td style="width:25%"><span class="sl">Completed</span><span class="sv">{{ $completedCount }}</span></td>
        <td style="width:25%"><span class="sl">Behind</span><span class="sv">{{ $behindCount }}</span></td>
        <td style="width:25%"><span class="sl">Not Recorded</span><span class="sv">{{ $notRecordedCount }}</span></td>
    </tr>
</table>

<div class="doc-section">Student Progress — {{ $weekLabel }}</div>
<table class="doc-table">
    <thead>
        <tr>
            <th style="width:4%">No.</th>
            <th style="width:27%">Student Name</th>
            <th style="width:16%">Halaqah / Batch</th>
            <th style="width:10%; text-align:center;">Target Pg.</th>
            <th style="width:10%; text-align:center;">Latest Pg.</th>
            <th style="width:9%; text-align:center;">Remaining</th>
            <th style="width:13%; text-align:center;">Gap</th>
            <th style="width:11%; text-align:center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $i => $row)
        <tr>
            <td style="text-align:center;">{{ $i + 1 }}</td>
            <td>
                <span class="doc-name">{{ $row->student_name }}</span><br>
                <span class="doc-sub">{{ $row->student_id }}</span>
            </td>
            <td>{{ $row->batch_name }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $row->target_page }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $row->latest_page !== null ? $row->latest_page : '—' }}</td>
            <td style="text-align:center;">
                @if ($row->remaining !== null) {{ $row->remaining }} @else — @endif
            </td>
            <td style="text-align:center;">
                @if (! $row->has_record)
                    <span class="doc-mute">—</span>
                @elseif ($row->gap < 0)
                    +{{ abs($row->gap) }} ahead
                @elseif ($row->gap === 0)
                    On target
                @else
                    {{ $row->gap }} behind
                @endif
            </td>
            <td style="text-align:center; font-weight:bold; font-size:7.5pt; text-transform:uppercase; letter-spacing:0.2pt;">{{ $row->status }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center; color:#888; padding:14pt;">No data available.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="doc-note">
    <strong>Status key:</strong> &nbsp;
    COMPLETED — reached end page. &nbsp;
    ON TRACK — at or ahead of weekly muqarrar. &nbsp;
    BEHIND — below muqarrar (1–5 pg / 6–10 pg / 11+ pg). &nbsp;
    NOT RECORDED — no tasmi' recorded this week.
</div>

<div class="doc-footer">
    HuffazTrack &mdash; Weekly Hafazan Report &mdash; {{ $weekLabel }} &mdash; Prepared by {{ $lecturerName }} &mdash; Generated {{ $generatedAt }}
</div>
</body>
</html>
