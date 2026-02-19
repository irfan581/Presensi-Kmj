<?php
// ═══════════════════════════════════════════════════════════
// FILE: app/Filament/Widgets/AbsentSales.php
// ═══════════════════════════════════════════════════════════

namespace App\Filament\Widgets;

use App\Models\Sales;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class AbsentSales extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Sales Belum Absen (Hari Ini)';
    protected static bool $isLazy = true;

    public function table(Table $table): Table
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        return $table
            ->query(
                // ✅ OPT: 1 query dengan whereDoesntHave + subquery
                // Lebih bersih dari 2 query (pluck + whereNotIn)
                // MySQL optimizer handle subquery ini dengan baik karena ada index sales_id+tanggal
                Sales::query()
                    ->select(['id', 'nama', 'area', 'no_hp']) // ✅ Pilih kolom minimum
                    ->where('is_active', true)
                    ->whereDoesntHave('presensi', function ($q) use ($today) {
                        $q->where('tanggal', $today);
                    })
                    ->orderBy('nama')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Sales')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('area')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('no_hp')
                    ->label('Tegur via WA')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->formatStateUsing(fn() => 'Hubungi')
                    ->url(fn(Sales $record) =>
                        'https://wa.me/' . preg_replace('/\D/', '', $record->no_hp)
                        . '?text=' . urlencode("Halo {$record->nama}, mohon segera lakukan absen presensi hari ini.")
                    )
                    ->openUrlInNewTab(),
            ])
            ->paginated(false); // ✅ 7 sales — tidak perlu pagination
    }
}