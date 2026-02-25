<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Aktivitas - {{ $sales->nama ?? 'Tim Sales' }}</title>
    <style>
    @page {
        margin: 0.8cm;
    }

    body {
        font-family: 'Helvetica', sans-serif;
        font-size: 10px;
        color: #333;
        line-height: 1.5;
    }

    .header {
        text-align: center;
        border-bottom: 3px solid #10b981;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }

    .header h2 {
        margin: 0;
        color: #065f46;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .company-name {
        color: #d97706;
        font-size: 12px;
        font-weight: bold;
        margin-top: 3px;
    }

    /* Tabel Informasi & Ringkasan */
    .info-table {
        width: 100%;
        margin-bottom: 15px;
        background-color: #f0fdf4;
        padding: 10px;
        border: 1px solid #d1fae5;
        border-radius: 4px;
    }

    .summary-box {
        background-color: #ffffff;
        border: 1px solid #10b981;
        padding: 5px 10px;
        display: inline-block;
        margin-top: 5px;
        border-radius: 4px;
    }

    .summary-item {
        display: inline-block;
        margin-right: 20px;
        font-size: 11px;
    }

    .summary-count {
        color: #059669;
        font-weight: bold;
        font-size: 13px;
    }

    /* Tabel Utama */
    .main-table {
        width: 100%;
        border-collapse: collapse;
    }

    .main-table th {
        background-color: #10b981;
        color: white;
        border: 1px solid #059669;
        padding: 8px 5px;
        text-align: center;
        text-transform: uppercase;
        font-size: 9px;
    }

    .main-table td {
        border: 1px solid #e5e7eb;
        padding: 6px 5px;
        vertical-align: middle;
    }

    .main-table tr:nth-child(even) {
        background-color: #f9fafb;
    }

    .badge {
        padding: 3px 6px;
        border-radius: 3px;
        color: white;
        font-weight: bold;
        font-size: 7px;
        display: block;
        text-align: center;
        text-transform: uppercase;
    }

    .bg-absen {
        background-color: #059669;
    }

    .bg-kunjungan {
        background-color: #d97706;
    }

    .bg-izin {
        background-color: #4f46e5;
    }

    .footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        text-align: right;
        font-style: italic;
        font-size: 8px;
        color: #9ca3af;
        border-top: 1px solid #e5e7eb;
        padding-top: 5px;
    }

    .suspicious-text {
        color: #dc2626;
        font-weight: bold;
        font-size: 7px;
        margin-top: 2px;
    }

    .location-link {
        color: #2563eb;
        text-decoration: none;
        font-size: 8px;
    }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN REKAP AKTIVITAS KARYAWAN</h2>
        <div class="company-name">CV. KEMBAR JAYA MANDIRI</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="12%"><strong>Nama Sales</strong></td>
            <td width="38%">: <span style="color: #065f46; font-weight:bold;">{{ $sales->nama ?? '-' }}</span></td>
            <td width="12%"><strong>Periode</strong></td>
            <td width="38%">: {{ $date_range }}</td>
        </tr>
        <tr>
            <td><strong>NIK / Area</strong></td>
            <td>: {{ $sales->nik ?? '-' }} / {{ $sales->area ?? '-' }}</td>
            <td><strong>Dicetak Oleh</strong></td>
            <td>: {{ auth()->user()->name }}</td>
        </tr>
        <tr>
            <td colspan="4">
                <div class="summary-box">
                    <div class="summary-item">
                        Total Kehadiran: <span class="summary-count">{{ $total_hadir }}</span> Hari
                    </div>
                    <div class="summary-item">
                        Total Kunjungan Toko: <span class="summary-count">{{ $total_kunjungan }}</span> Toko
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="12%">Waktu</th>
                <th width="10%">Tipe</th>
                <th width="25%">Detail Aktivitas</th>
                <th width="25%">Lokasi GPS</th>
                <th width="28%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $item)
            <tr>
                <td align="center">
                    {{ \Carbon\Carbon::parse($item['waktu'])->format('d/m/y') }}<br>
                    <strong>{{ \Carbon\Carbon::parse($item['waktu'])->format('H:i') }}</strong>
                </td>

                <td>
                    @php
                    $badgeClass = match($item['type']) {
                    'Absen' => 'bg-absen',
                    'Toko', 'Kunjungan' => 'bg-kunjungan',
                    'Izin' => 'bg-izin',
                    default => 'bg-absen',
                    };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $item['type'] }}</span>
                </td>

                <td>
                    <strong>{{ $item['detail'] }}</strong>
                    @if(isset($item['suspicious']) && $item['suspicious'])
                    <div class="suspicious-text">TERINDIKASI FAKE GPS / MOCK</div>
                    @endif
                </td>

                <td>
                    @if(!empty($item['location']) && $item['location'] != '-')
                    <span class="location-link">{{ $item['location'] }}</span>
                    @else
                    <span style="color: #9ca3af; font-style: italic;">Tidak ada koordinat</span>
                    @endif
                </td>

                <td>{{ $item['keterangan'] ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" align="center" style="padding: 20px; color: #9ca3af;">
                    Tidak ditemukan data aktivitas untuk periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistem Absensi CV. KEMBAR JAYA MANDIRI | Halaman: {PAGENO} | Dicetak:
        {{ now()->translatedFormat('d F Y, H:i') }}
    </div>
</body>

</html>