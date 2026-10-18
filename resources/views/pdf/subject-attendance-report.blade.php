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
                @isset($lecturerName)Prepared by: {{ $lecturerName }}<br>@endisset
                Generated: {{ $generatedAt }}
            </td>
        </tr>
    </table>
</div>

<div class="doc-title">Subject Attendance Report</div>
<div class="doc-period">
    Semester: {{ $period->name ?? '—' }}
    &nbsp;&nbsp;|&nbsp;&nbsp; Date Range: {{ $dateFrom?->format('d M Y') ?? 'Start' }} &ndash; {{ $dateTo?->format('d M Y') ?? 'Now' }}
    &nbsp;&nbsp;|&nbsp;&nbsp; Rows: {{ $rows->count() }}
</div>

<table class="doc-summary">
    <tr>
        <td style="width:33%"><span class="sl">Total Present</span><span class="sv">{{ $totalPresent }}</span></td>
        <td style="width:33%"><span class="sl">Total Absent</span><span class="sv">{{ $totalAbsent }}</span></td>
        <td style="width:34%"><span class="sl">Overall Rate</span><span class="sv">{{ $overallRate !== null ? $overallRate . '%' : '—' }}</span></td>
    </tr>
</table>

<div class="doc-section">Student Subject Attendance</div>
<table class="doc-table">
    <thead>
        <tr>
            <th style="width:19%">Student Name</th>
            <th style="width:14%">Student ID</th>
            <th style="width:18%">Subject</th>
            <th style="width:12%">Section</th>
            <th style="width:10%">Batch</th>
            <th style="width:9%">Present</th>
            <th style="width:9%">Absent</th>
            <th style="width:9%">Attendance %</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
        <tr>
            <td class="doc-name">{{ $row->student_name }}</td>
            <td>{{ $row->student_id }}</td>
            <td>{{ $row->subject_name }}</td>
            <td>{{ $row->section_name ?? '—' }}</td>
            <td>{{ $row->batch_name }}</td>
            <td>{{ $row->present }}</td>
            <td>{{ $row->absent }}</td>
            <td>{{ $row->percentage !== null ? $row->percentage . '%' : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center; color:#888; padding:10pt;">No records match these filters.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="doc-footer">
    HuffazTrack &mdash; Subject Attendance Report &mdash; Generated {{ $generatedAt }}
</div>
</body>
</html>
