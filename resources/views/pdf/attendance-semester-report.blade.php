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
                Prepared by: {{ $lecturerName }}<br>
                @isset($lecturerPhone)Phone: {{ $lecturerPhone }}<br>@endisset
                Generated: {{ $generatedAt }}
            </td>
        </tr>
    </table>
</div>

<div class="doc-title">Semester Attendance Report</div>
<div class="doc-period">
    {{ $semesterLabel }}
    @if ($semStart && $semEnd)
        &nbsp;&nbsp;|&nbsp;&nbsp; {{ $semStart->format('d M Y') }} &ndash; {{ $semEnd->format('d M Y') }}
    @endif
    &nbsp;&nbsp;|&nbsp;&nbsp; Lecturer: {{ $lecturerName }}@isset($lecturerPhone) ({{ $lecturerPhone }})@endisset
    &nbsp;&nbsp;|&nbsp;&nbsp; Total Students: {{ $rows->count() }}
    &nbsp;&nbsp;|&nbsp;&nbsp; Sessions Recorded: {{ $totalSessions }}
</div>

<table class="doc-summary">
    <tr>
        <td style="width:25%"><span class="sl">Avg. Present Rate</span><span class="sv">{{ $avgAttendanceRate }}%</span></td>
        <td style="width:25%"><span class="sl">Avg. TH (Absent) Rate</span><span class="sv">{{ $avgAbsentRate }}%</span></td>
        <td style="width:25%"><span class="sl">Warning Level 2 Students</span><span class="sv">{{ $warn2Count }}</span></td>
        <td style="width:25%"><span class="sl">Perfect Attendance</span><span class="sv">{{ $perfectCount }}</span></td>
    </tr>
</table>

<div class="doc-section">Student Attendance — {{ $semesterLabel }}</div>
<table class="doc-table">
    <thead>
        <tr>
            <th style="width:5%">No.</th>
            <th style="width:35%">Student Name</th>
            <th style="width:16%">Batch</th>
            <th style="width:10%; text-align:center;">Present</th>
            <th style="width:10%; text-align:center;">Absent (TH)</th>
            <th style="width:10%; text-align:center;">Excused (THS)</th>
            <th style="width:8%; text-align:center;">Rate</th>
            <th style="width:6%; text-align:center;">Warning</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $i => $row)
        @php
            $warnLabel = $row->warning_level === 0 ? 'None' : 'Level ' . $row->warning_level;
        @endphp
        <tr>
            <td style="text-align:center;">{{ $i + 1 }}</td>
            <td class="doc-name">{{ $row->student_name }}</td>
            <td><span class="doc-sub">{{ $row->batch_name }}</span></td>
            <td style="text-align:center; font-weight:bold;">{{ $row->total_present }}</td>
            <td style="text-align:center; font-weight:{{ $row->total_absent > 0 ? 'bold' : 'normal' }};">{{ $row->total_absent }}</td>
            <td style="text-align:center;">{{ $row->total_excused }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $row->attendance_rate }}%</td>
            <td style="text-align:center; font-size:7.5pt; text-transform:uppercase; letter-spacing:0.2pt; font-weight:{{ $row->warning_level > 0 ? 'bold' : 'normal' }};">{{ $warnLabel }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center; color:#888; padding:10pt;">No students found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="doc-footer">
    HuffazTrack &mdash; Semester Attendance Report &mdash; {{ $lecturerName }} &mdash; {{ $semesterLabel }} &mdash; Generated {{ $generatedAt }}
</div>
</body>
</html>
