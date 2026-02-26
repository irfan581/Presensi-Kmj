<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path'; // Icon yang lebih cocok untuk log
    
    protected static ?string $navigationGroup = 'Sistem';

    protected static ?string $label = 'Log Aktivitas';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        /** @var \App\Models\User|null $user */
        return $user && $user->role === 'owner';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable()
                    ->default('System')
                    ->description(fn (Activity $record): string => $record->causer->role ?? 'System'),

                TextColumn::make('description')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'login'   => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Tambah',
                        'updated' => 'Ubah',
                        'deleted' => 'Hapus',
                        default => ucfirst($state),
                    }),

                TextColumn::make('subject_type')
                    ->label('Modul / Data')
                    ->formatStateUsing(fn ($state) => str_replace('App\Models\\', '', $state ?? 'System'))
                    ->description(fn (Activity $record): string => "ID: {$record->subject_id}"),

                // Detail Ringkas Perubahan (Hanya menampilkan key apa yang berubah)
                TextColumn::make('properties.attributes')
                    ->label('Yang Diubah')
                    ->formatStateUsing(fn ($state) => $state ? implode(', ', array_keys($state)) : '-')
                    ->limit(30)
                    ->color('gray')
                    ->size('xs'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('description')
                    ->label('Jenis Kejadian')
                    ->options([
                        'created' => 'Penambahan',
                        'updated' => 'Perubahan',
                        'deleted' => 'Penghapusan',
                    ]),
                SelectFilter::make('subject_type')
                    ->label('Modul')
                    ->options(fn () => Activity::groupBy('subject_type')
                        ->pluck('subject_type', 'subject_type')
                        ->mapWithKeys(fn ($item) => [$item => str_replace('App\Models\\', '', $item)])
                        ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail'),
            ])
            ->bulkActions([]);
    }

    /**
     * 🔥 POLESAN MAUT: Membuat tampilan detail (View) jadi cantik
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Aktivitas')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('causer.name')->label('Pelaku'),
                        TextEntry::make('description')->label('Jenis Aksi')->badge(),
                        TextEntry::make('created_at')->label('Waktu Kejadian')->dateTime(),
                        TextEntry::make('subject_type')->label('Modul')->formatStateUsing(fn($state) => str_replace('App\Models\\', '', $state)),
                        TextEntry::make('subject_id')->label('ID Data'),
                    ]),
                
                Section::make('Detail Perubahan Data')
                    ->description('Perbandingan data sebelum dan sesudah perubahan')
                    ->schema([
                        // Menampilkan data baru
                        KeyValueEntry::make('properties.attributes')
                            ->label('Data Sekarang / Baru')
                            ->columns(2)
                            ->keyLabel('Kolom')
                            ->valueLabel('Nilai Baru'),
                        
                        // Menampilkan data lama (jika ada)
                        KeyValueEntry::make('properties.old')
                            ->label('Data Sebelumnya')
                            ->columns(2)
                            ->keyLabel('Kolom')
                            ->valueLabel('Nilai Lama')
                            ->visible(fn ($record) => isset($record->properties['old'])),
                    ])->columns(2)
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }

    public static function can(string $action, ?Model $record = null): bool
    {
        $user = Auth::user();
        /** @var \App\Models\User|null $user */
        if (!$user || $user->role !== 'owner') return false;

        // Log tidak boleh diedit/dihapus sama sekali
        if (in_array($action, ['create', 'update', 'delete', 'deleteAny'])) return false;

        return parent::can($action, $record);
    }
}