<?php

namespace App\Filament\Widgets;

use App\Models\Sales;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AbsentSales extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Sales Belum Absen (Hari Ini)';
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = true;

    public function table(Table $table): Table
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        return $table
            ->deferLoading()
            ->query(
                Sales::query()
                    ->select(['id', 'nama', 'area', 'no_hp'])
                    ->where('is_active', true)
                    ->whereDoesntHave('presensi', fn (Builder $query) => 
                        $query->whereDate('tanggal', $today)
                    )
                    ->orderBy('nama')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Sales')
                    ->weight('bold')
                    ->searchable(),

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
            ->paginated(false);
    }
}