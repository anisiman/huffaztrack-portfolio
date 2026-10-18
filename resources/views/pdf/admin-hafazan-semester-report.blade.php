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
.doc-group td { background: #d4d4d4 !important; font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5pt; color: #1e3a5f; padding: 4pt 8pt !important; }
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

<div class="doc-title">Semester Hafazan Report — Administration</div>
<div class="doc-period">
    {{ $semesterLabel }}
    @if ($semStart && $semEnd)
        &nbsp;&nbsp;|&nbsp;&nbsp; {{ $semStart->format('d M Y') }} &ndash; {{ $semEnd->format('d M Y') }}
    @endif
    &nbsp;&nbsp;|&nbsp;&nbsp; Total Students: {{ $rows->count() }}
    @if ($semesterEndPage > 0)
        &nbsp;&nbsp;|&nbsp;&nbsp; Semester Target Page: {{ $semesterEndPage }}
    @endif
</div>

<table class="doc-summary">
    <tr>
        <td style="width:25%"><span class="sl">Completed</span><span class="sv">{{ $completedCount }}</span></td>
        <td style="width:25%"><span class="sl">In Progress</span><span class="sv">{{ $inProgressCount }}</span></td>
        <td style="width:25%"><span class="sl">Not Recorded</span><span class="sv">{{ $notRecordedCount }}</span></td>
        <td style="width:25%"><span class="sl">Total Students</span><span class="sv">{{ $rows->count() }}</span></td>
    </tr>
</table>

@php
    $grouped = $rows->groupBy('batch_name')->sortKeys();
    $rowNum  = 0;
@endphp

<div class="doc-section">Student Hafazan — {{ $semesterLabel }}</div>
<table class="doc-table">
    <thead>
        <tr>
            <th style="width:4%">No.</th>
            <th style="width:26%">Student Name</th>
            <th style="width:18%">Lecturer</th>
            <th style="width:10%; text-align:center;">Latest Page</th>
            <th style="width:8%; text-align:center;">Sessions</th>
            <th style="width:12%; text-align:center;">Wkly Avg</th>
            <th style="width:10%; text-align:center;">Progress</th>
            <th style="width:12%; text-align:center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($grouped as $batchName => $batchRows)
        <tr class="doc-group"><td colspan="8">{{ $batchName ?: 'Unassigned' }}</td></tr>
        @foreach ($batchRows->sortBy('student_name') as $row)
        @php $rowNum++; @endphp
        <tr>
            <td style="text-align:center;">{{ $rowNum }}</td>
            <td class="doc-name">{{ $row->student_name }}</td>
            <td><span class="doc-sub">{{ $row->lecturer_name }}</span></td>
            <td style="text-align:center; font-weight:bold;">
                {{ $row->latest_page !== null ? $row->latest_page : '—' }}
                @if ($semesterEndPage > 0 && $row->latest_page !== null)
                    <span class="doc-mute" style="font-size:7pt; font-weight:normal;"> / {{ $semesterEndPage }}</span>
                @endif
            </td>
            <td style="text-align:center;">{{ $row->total_sessions ?: '—' }}</td>
            <td style="text-align:center;">{{ $row->weekly_avg !== null ? $row->weekly_avg . ' pg/wk' : '—' }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $row->progress_pct > 0 ? $row->progress_pct . '%' : '—' }}</td>
            <td style="text-align:center; font-weight:bold; font-size:7.5pt; text-transform:uppercase; letter-spacing:0.2pt;">{{ $row->status }}</td>
        </tr>
        @endforeach
        @endforeach
        @if ($rows->isEmpty())
        <tr><td colspan="8" style="text-align:center; color:#888; padding:10pt;">No data found.</td></tr>
        @endif
    </tbody>
</table>

<div class="doc-footer">
    HuffazTrack &mdash; Semester Hafazan Report (Administration) &mdash; {{ $semesterLabel }} &mdash; Generated {{ $generatedAt }}
</div>
</body>
</html>
