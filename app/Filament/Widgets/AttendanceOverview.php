<?php
// ═══════════════════════════════════════════════════════════
// FILE: app/Filament/Widgets/AttendanceOverview.php
// ═══════════════════════════════════════════════════════════

namespace App\Filament\Widgets;

use App\Models\Presensi;
use App\Models\KunjunganToko;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceOverview extends BaseWidget
{
    protected static bool $isLazy = true;
    // ✅ OPT: Shared hosting — naikkan polling jadi 120s
    // 60s terlalu agresif untuk shared hosting (setiap menit = beban ekstra)
    protected static ?string $pollingInterval = '120s';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        // ✅ OPT: Cache 120s — sinkron dengan polling interval
        // Tidak ada gunanya query setiap 60s jika data berubah setiap jam
        $stats = Cache::remember("dashboard_stats_{$today}", 120, function () use ($today) {

            // ✅ OPT: 1 query dengan DB::select untuk 3 stat sekaligus
            // Sebelum: 2 query terpisah (presensiStats + KunjunganToko::count())
            $result = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM presensis WHERE tanggal = ?) as total_masuk,
                    (SELECT COUNT(*) FROM presensis WHERE tanggal = ? AND jam_pulang IS NULL) as on_duty,
                    (SELECT COUNT(*) FROM kunjungan_tokos WHERE DATE(created_at) = ?) as total_kunjungan
            ", [$today, $today, $today]);

            return [
                'total_masuk'      => (int) ($result->total_masuk ?? 0),
                'on_duty'          => (int) ($result->on_duty ?? 0),
                'total_kunjungan'  => (int) ($result->total_kunjungan ?? 0),
            ];
        });

        return [
            Stat::make('Sales Masuk Hari Ini', $stats['total_masuk'])
                ->description('Total absen masuk hari ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Sedang Bertugas', $stats['on_duty'])
                ->description('Belum absen pulang')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('warning'),

            Stat::make('Kunjungan Toko', $stats['total_kunjungan'])
                ->description('Total kunjungan hari ini')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
        ];
    }
}