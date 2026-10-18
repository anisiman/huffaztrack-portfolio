<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<style>

@page { margin: 12mm 10mm; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #1a1a1a; background: #fff; }

.doc-header { padding-bottom: 8pt; margin-bottom: 10pt; border-bottom: 2pt solid #1e3a5f; }
.doc-header-table { width: 100%; border-collapse: collapse; }
.doc-brand { font-size: 12pt; font-weight: bold; color: #1e3a5f; }
.doc-brand-sub { font-size: 7pt; color: #555; margin-top: 2pt; }
.doc-meta { text-align: right; font-size: 7pt; color: #444; line-height: 1.7; vertical-align: top; }

.doc-title { font-size: 12pt; font-weight: bold; color: #1a1a1a; margin-bottom: 3pt; text-align: center; text-transform: uppercase; }
.doc-period { font-size: 8pt; color: #444; padding-bottom: 8pt; margin-bottom: 8pt; border-bottom: 0.5pt solid #ccc; text-align: center; }

.reg-range-label { font-size: 9pt; font-weight: bold; color: #1e3a5f; margin: 14pt 0 5pt; }

.reg-table { border-collapse: collapse; }
.reg-table th, .reg-table td { border: 0.5pt solid #999; text-align: center; vertical-align: middle; }
.reg-table th { background: #e6e6e6; font-weight: bold; font-size: 7.5pt; padding: 4pt 2pt; line-height: 1.3; }
.reg-table td { font-size: 7.5pt; padding: 3pt 2pt; line-height: 1.25; }
.reg-col-bil  { width: 20pt; }
.reg-col-name {
    width: 300pt; text-align: left !important; padding-left: 6pt !important; font-size: 8.5pt;
    white-space: normal; word-wrap: break-word;
}
.reg-table th.reg-col-name { white-space: normal; font-size: 7.5pt; }
.reg-col-day  { width: 25pt; }
.reg-table tbody tr:nth-child(even) td { background: #f7f7f7; }
.reg-summary-label td.reg-col-name { font-weight: bold; text-align: right !important; padding-right: 4pt !important; }
.reg-summary-row td { font-weight: bold; background: #f0f0f0; }

.reg-legend { margin-top: 6pt; font-size: 7pt; color: #444; }

.days-block { margin-top: 14pt; }
.days-line { font-size: 8.5pt; margin-bottom: 6pt; }
.days-line .blank { display: inline-block; min-width: 60pt; border-bottom: 0.75pt solid #333; text-align: center; }

.sign-block { width: 100%; border-collapse: collapse; margin-top: 16pt; }
.sign-block .sign-heading-cell {
    width: 50%; background: #d9d9d9; font-weight: bold; font-size: 8.5pt;
    padding: 5pt 10pt; border: 1pt solid #333;
}
.sign-block .sign-body-cell {
    width: 50%; vertical-align: top; font-size: 8.5pt; padding: 8pt 10pt 10pt;
    border: 1pt solid #333;
}
.sign-block .sign-text { line-height: 1.5; margin-bottom: 26pt; }
.sign-block .sign-line { border-bottom: 0.75pt solid #333; margin-bottom: 4pt; }
.sign-block .sign-caption { text-align: center; font-size: 8pt; }

.doc-footer { margin-top: 12pt; padding-top: 4pt; border-top: 1pt solid #bbb; font-size: 7pt; color: #555; text-align: center; }
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
                Halaqah / Batch: {{ $filterHalaqah !== '' ? $filterHalaqah : 'Semua Batch' }}<br>
                Jumlah Pelajar: {{ $totalStudents }}<br>
                Dijana pada: {{ $generatedAt }}
            </td>
        </tr>
    </table>
</div>

<div class="doc-title">Rekod Kehadiran Harian</div>
<div class="doc-period">
    Bulan: {{ $monthStart->format('F Y') }}
    &nbsp;&nbsp;|&nbsp;&nbsp; Tempoh: {{ $monthStart->format('d M') }} &ndash; {{ $monthEnd->format('d M Y') }}
</div>

@php
    // Split the day columns into blocks of 15 so the name column has room to
    // breathe — a single 1..31 table crushes the name column far too narrow.
    $dayRanges = [];
    for ($blockStart = 1; $blockStart <= $daysInMonth; $blockStart += 15) {
        $dayRanges[] = [$blockStart, min($blockStart + 14, $daysInMonth)];
    }
@endphp

@foreach ($dayRanges as $range)
    @php
        [$from, $to] = $range;
        $tableWidth = 20 + 300 + 25 * ($to - $from + 1);
    @endphp

    <div class="reg-range-label">Hari {{ $from }} &ndash; {{ $to }}</div>

    <table class="reg-table" style="width: {{ $tableWidth }}pt;">
        <thead>
            <tr>
                <th class="reg-col-bil" style="width: 20pt;">Bil</th>
                <th class="reg-col-name" style="width: 300pt;">Nama Penuh Pelajar<br>(Mengikut susunan abjad)</th>
                @for ($d = $from; $d <= $to; $d++)
                    <th class="reg-col-day" style="width: 25pt;">{{ $d }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="reg-col-name">{{ $row->student_name }}</td>
                @for ($d = $from; $d <= $to; $d++)
                    <td>{{ $row->days[$d] }}</td>
                @endfor
            </tr>
            @empty
            <tr><td colspan="{{ 2 + ($to - $from + 1) }}" style="padding:8pt; color:#888;">Tiada data pelajar.</td></tr>
            @endforelse

            <tr class="reg-summary-row reg-summary-label">
                <td></td>
                <td class="reg-col-name">Jumlah pelajar yang TIDAK HADIR</td>
                @for ($d = $from; $d <= $to; $d++)
                    <td>{{ $dailyTakHadir[$d] }}</td>
                @endfor
            </tr>
            <tr class="reg-summary-row reg-summary-label">
                <td></td>
                <td class="reg-col-name">Jumlah pelajar yang HADIR</td>
                @for ($d = $from; $d <= $to; $d++)
                    <td>{{ $dailyHadir[$d] }}</td>
                @endfor
            </tr>
            <tr class="reg-summary-row reg-summary-label">
                <td></td>
                <td class="reg-col-name">Jumlah semua pelajar</td>
                @for ($d = $from; $d <= $to; $d++)
                    <td>{{ $totalStudents }}</td>
                @endfor
            </tr>
        </tbody>
    </table>
@endforeach

<div class="reg-legend">/ = Hadir &nbsp;&nbsp; X = Tidak Hadir &nbsp;&nbsp; S = Sebab (bercuti/sakit)</div>

<div class="days-block">
    <div class="days-line">Jumlah hari belajar bulan lepas <span class="blank">&nbsp;</span> hari</div>
    <div class="days-line">Jumlah hari belajar bulan ini <span class="blank">&nbsp;</span> hari</div>
    <div class="days-line">Jumlah hingga akhir bulan ini <span class="blank">&nbsp;</span> hari</div>
</div>

<table class="sign-block">
    <tr>
        <td class="sign-heading-cell">Pengakuan</td>
        <td class="sign-heading-cell">Disemak Oleh</td>
    </tr>
    <tr>
        <td class="sign-body-cell">
            <div class="sign-text">Saya mengaku bahawa butiran / maklumat diatas adalah benar dan saya bertanggungjawab terhadapnya,</div>
            <div class="sign-line">&nbsp;</div>
            <div class="sign-caption">(T/Tangan Guru Tasmi')</div>
        </td>
        <td class="sign-body-cell">
            <div class="sign-text">&nbsp;</div>
            <div class="sign-line">&nbsp;</div>
            <div class="sign-caption">T/Tangan Penyelaras Tahfiz</div>
        </td>
    </tr>
</table>

<div class="doc-footer">
    HuffazTrack &mdash; Rekod Kehadiran Harian &mdash; {{ $monthStart->format('F Y') }} &mdash; Dijana pada {{ $generatedAt }}
</div>
</body>
</html>
