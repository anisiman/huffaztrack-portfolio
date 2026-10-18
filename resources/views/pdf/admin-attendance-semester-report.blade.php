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
.doc-subgroup td { background: #eee !important; font-size: 7pt; font-weight: bold; color: #444; padding: 3pt 8pt 3pt 16pt !important; }
.doc-name { font-weight: bold; color: #111; }
.doc-sub  { font-size: 7pt; color: #666; }
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

<div class="doc-title">Semester Attendance Report — Administration</div>
<div class="doc-period">
    {{ $semesterLabel }}
    @if ($semStart && $semEnd)
        &nbsp;&nbsp;|&nbsp;&nbsp; {{ $semStart->format('d M Y') }} &ndash; {{ $semEnd->format('d M Y') }}
    @endif
    &nbsp;&nbsp;|&nbsp;&nbsp; Total Students: {{ $rows->count() }}
    &nbsp;&nbsp;|&nbsp;&nbsp; Total Sessions Recorded: {{ $totalSessions }}
</div>

<table class="doc-summary">
    <tr>
        <td style="width:25%"><span class="sl">Avg. Present Rate</span><span class="sv">{{ $avgAttendanceRate }}%</span></td>
        <td style="width:25%"><span class="sl">Avg. TH (Absent) Rate</span><span class="sv">{{ $avgAbsentRate }}%</span></td>
        <td style="width:25%"><span class="sl">Warning Level 2 Students</span><span class="sv">{{ $warn2Count }}</span></td>
        <td style="width:25%"><span class="sl">Perfect Attendance</span><span class="sv">{{ $perfectCount }}</span></td>
    </tr>
</table>

@php
    $grouped = $rows->groupBy('batch_name')->sortKeys();
    $rowNum  = 0;
@endphp

<div class="doc-section">Student Attendance — {{ $semesterLabel }}</div>
<table class="doc-table">
    <thead>
        <tr>
            <th style="width:4%">No.</th>
            <th style="width:34%">Student Name</th>
            <th style="width:12%; text-align:center;">Present</th>
            <th style="width:12%; text-align:center;">Absent (TH)</th>
            <th style="width:12%; text-align:center;">Excused (THS)</th>
            <th style="width:13%; text-align:center;">Att. Rate</th>
            <th style="width:13%; text-align:center;">Warning</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($grouped as $batchName => $batchRows)
        <tr class="doc-group"><td colspan="7">{{ $batchName ?: 'Unassigned' }} ({{ $batchRows->count() }})</td></tr>
        @php $byLecturer = $batchRows->groupBy('lecturer_name')->sortKeys(); @endphp
        @foreach ($byLecturer as $lecturerName => $lecturerRows)
        <tr class="doc-subgroup"><td colspan="7">{{ $lecturerName ?: 'Unassigned' }} ({{ $lecturerRows->count() }})</td></tr>
        @foreach ($lecturerRows->sortBy('student_name') as $row)
        @php
            $rowNum++;
            $warnLabel = $row->warning_level === 0 ? 'None' : 'Level ' . $row->warning_level;
        @endphp
        <tr>
            <td style="text-align:center;">{{ $rowNum }}</td>
            <td class="doc-name">{{ $row->student_name }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $row->total_present }}</td>
            <td style="text-align:center; font-weight:{{ $row->total_absent > 0 ? 'bold' : 'normal' }};">{{ $row->total_absent }}</td>
            <td style="text-align:center;">{{ $row->total_excused }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $row->attendance_rate }}%</td>
            <td style="text-align:center; font-size:7.5pt; text-transform:uppercase; letter-spacing:0.2pt; font-weight:{{ $row->warning_level > 0 ? 'bold' : 'normal' }};">{{ $warnLabel }}</td>
        </tr>
        @endforeach
        @endforeach
        @endforeach
        @if ($rows->isEmpty())
        <tr><td colspan="7" style="text-align:center; color:#888; padding:10pt;">No data found.</td></tr>
        @endif
    </tbody>
</table>

<div class="doc-footer">
    HuffazTrack &mdash; Semester Attendance Report (Administration) &mdash; {{ $semesterLabel }} &mdash; Generated {{ $generatedAt }}
</div>
</body>
</html>
