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

    /* Header dengan aksen Hijau Emerald */
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
        /* Oranye Gelap agar lebih terbaca */
        font-size: 12px;
        font-weight: bold;
        margin-top: 3px;
    }

    /* Tabel Informasi */
    .info-table {
        width: 100%;
        margin-bottom: 15px;
        background-color: #f0fdf4;
        /* Hijau sangat muda */
        padding: 8px;
        border: 1px solid #d1fae5;
        border-radius: 4px;
    }

    .info-table td {
        padding: 2px 5px;
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
        /* Tengah secara vertikal agar rapi */
    }

    .main-table tr:nth-child(even) {
        background-color: #f9fafb;
    }

    /* Styling Badge */
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

    /* Hijau Tua */
    .bg-kunjungan {
        background-color: #d97706;
    }

    /* Oranye Gelap */
    .bg-izin {
        background-color: #4f46e5;
    }

    /* Indigo */
    .bg-danger {
        background-color: #dc2626;
    }

    /* Merah */

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
        margin-top: 3px;
        color: #dc2626;
        font-weight: bold;
        font-size: 7px;
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
            <td width="12%"><strong>Subjek</strong></td>
            <td width="38%">: <span
                    style="color: #065f46; font-weight:bold;">{{ $sales ? $sales->nama : 'SELURUH TIM SALES' }}</span>
            </td>
            <td width="12%"><strong>Periode</strong></td>
            <td width="38%">: {{ $date_range }}</td>
        </tr>
        <tr>
            <td><strong>NIK / Area</strong></td>
            <td>: {{ $sales ? ($sales->nik . ' / ' . $sales->area) : 'Semua Area' }}</td>
            <td><strong>Dicetak Oleh</strong></td>
            <td>: {{ auth()->user()->name }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="12%">Waktu</th>
                @if(!$sales) <th width="15%">Nama Sales</th> @endif
                <th width="8%">Tipe</th>
                <th width="20%">Detail Aktivitas</th>
                <th width="22%">Lokasi GPS</th>
                <th width="23%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $item)
            <tr>
                <td align="center">
                    {{ \Carbon\Carbon::parse($item['waktu'])->format('d/m/y') }}<br>
                    <strong>{{ \Carbon\Carbon::parse($item['waktu'])->format('H:i') }}</strong>
                </td>

                @if(!$sales)
                <td><strong>{{ $item['sales_name'] }}</strong></td>
                @endif

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
                    <div class="suspicious-text">
                        {{ strtoupper($item['reason'] ?? 'FAKE GPS') }}
                    </div>
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
                <td colspan="{{ !$sales ? 6 : 5 }}" align="center" style="padding: 20px; color: #9ca3af;">
                    Tidak ditemukan data aktivitas untuk periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistem Absensi CV. KEMBAR JAYA MANDIRI | Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}
    </div>
</body>

</html>