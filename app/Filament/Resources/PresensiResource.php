<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresensiResource\Pages;
use App\Models\Presensi;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class PresensiResource extends Resource
{
    protected static ?string $model           = Presensi::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Absensi Sales';
    protected static ?int    $navigationSort  = 2;

    public static function canCreate(): bool      { return false; }
    public static function canEdit($record): bool { return false; }

    // ─── NAVIGATION BADGE ─────────────────────────────────────

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::remember('presensi_on_duty_count', 60, function () {
            return Presensi::whereDate('tanggal', now())
                ->whereNotNull('jam_masuk')
                ->whereNull('jam_pulang')
                ->count();
        });

        return $count > 0 ? "{$count} On Duty" : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // ─── TABLE ────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query
                ->select([
                    'id', 'sales_id', 'tanggal', 'status',
                    'jam_masuk', 'jam_perangkat_masuk', 'jam_pulang',
                    'foto_masuk', 'location_masuk',
                    'is_suspicious', 'keterangan',
                ])
                ->with('sales:id,nama,area')
            )
            ->defaultSort('tanggal', 'desc')
            ->defaultPaginationPageOption(15)
            ->striped()
            ->columns([
                TextColumn::make('sales.nama')
                    ->label('Nama Sales')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn(Presensi $record): string => $record->sales?->area ?? '-'),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tepat_waktu' => 'success',
                        'terlambat'   => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string =>
                        $state === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat'
                    ),

                TextColumn::make('jam_masuk')
                    ->label('Masuk')
                    ->time('H:i')
                    ->description(fn(Presensi $record): ?string =>
                        $record->jam_perangkat_masuk
                            ? "HP: {$record->jam_perangkat_masuk}"
                            : null
                    )
                    ->alignCenter(),

                ImageColumn::make('foto_masuk')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn(Presensi $record): string =>
                        'https://ui-avatars.com/api/?name='
                        . urlencode($record->sales?->nama ?? 'S')
                        . '&color=7F9CF5&background=EBF4FF&size=64'
                    ),

                IconColumn::make('is_suspicious')
                    ->label('GPS')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->alignCenter(),

                TextColumn::make('jam_pulang')
                    ->label('Pulang')
                    ->time('H:i')
                    ->placeholder('--:--')
                    ->color('info')
                    ->alignCenter(),
            ])
            ->filters([
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari')->label('Dari Tanggal')->native(false),
                        DatePicker::make('sampai')->label('Sampai Tanggal')->native(false),
                    ])
                    ->query(fn(Builder $query, array $data) => $query
                        ->when($data['dari'],   fn($q, $d) => $q->whereDate('tanggal', '>=', Carbon::parse($d)->toDateString()))
                        ->when($data['sampai'], fn($q, $d) => $q->whereDate('tanggal', '<=', Carbon::parse($d)->toDateString()))
                    ),

                SelectFilter::make('sales')
                    ->relationship('sales', 'nama')
                    ->label('Filter Sales')
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'tepat_waktu' => 'Tepat Waktu',
                        'terlambat'   => 'Terlambat',
                    ]),
            ])
            // headerActions SUDAH DIHAPUS (Tombol Download Hilang)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPresensis::route('/'),
            'view'  => Pages\ViewPresensi::route('/{record}'),
        ];
    }
}