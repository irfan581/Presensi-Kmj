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

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    
    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?string $label = 'Log Aktivitas';

    /**
     * 🔒 GUARD: Menentukan siapa yang bisa melihat menu ini di sidebar.
     */
    public static function canViewAny(): bool
    {
        // Menggunakan Facade Auth langsung agar Intelephense tidak bingung
        $user = Auth::user();

        /** @var \App\Models\User|null $user */
        // 🔥 HANYA OWNER YANG BISA LIHAT MENU INI
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
                    ->label('Pelaku')
                    ->searchable()
                    ->default('System'),

                TextColumn::make('description')
                    ->label('Aktivitas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('subject_type')
                    ->label('Modul')
                    ->formatStateUsing(fn ($state) => str_replace('App\Models\\', '', $state ?? '')),

                TextColumn::make('properties')
                    ->label('Detail Perubahan')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('description')
                    ->label('Jenis Kejadian')
                    ->options([
                        'created' => 'Data Baru',
                        'updated' => 'Perubahan',
                        'deleted' => 'Penghapusan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }

    /**
     * Memastikan keamanan di level akses Resource.
     */
    public static function can(string $action, ?Model $record = null): bool
    {
        $user = Auth::user();

        /** @var \App\Models\User|null $user */
        // 🔥 HANYA OWNER YANG BISA AKSES RESOURCE INI
        if (!$user || $user->role !== 'owner') {
            return false;
        }

        // Log bersifat Read-Only
        if (in_array($action, ['create', 'update', 'delete', 'deleteAny'])) {
            return false;
        }

        return parent::can($action, $record);
    }
}