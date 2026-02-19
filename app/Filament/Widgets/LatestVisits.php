<?php
// ═══════════════════════════════════════════════════════════
// FILE: app/Filament/Widgets/LatestVisits.php
// ═══════════════════════════════════════════════════════════

namespace App\Filament\Widgets;

use App\Models\KunjunganToko;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestVisits extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = '5 Kunjungan Toko Terakhir';
    protected static bool $isLazy = true;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KunjunganToko::query()
                    // ✅ OPT: select kolom spesifik — tidak load foto_kunjungan (besar)
                    ->select([
                        'id', 'sales_id', 'nama_toko',
                        'location', 'is_suspicious', 'created_at',
                    ])
                    // ✅ OPT: eager load sales hanya kolom yang ditampilkan
                    ->with(['sales:id,nama'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('H:i')
                    ->description(fn($record) => $record->created_at->format('d/m/Y')),

                Tables\Columns\TextColumn::make('sales.nama')
                    ->label('Sales')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nama_toko')
                    ->label('Toko'),

                Tables\Columns\IconColumn::make('is_suspicious')
                    ->label('GPS')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle'),
            ])
            ->paginated(false);
    }
}