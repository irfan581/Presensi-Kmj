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
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
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
                    ->label('Waktu Kejadian')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label('Nama User')
                    ->searchable()
                    ->default('System')
                    ->description(fn (Activity $record): string => $record->causer->role ?? 'System'),

                TextColumn::make('description')
                    ->label('Aktivitas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login'   => 'success',
                        'logout'  => 'gray',
                        default   => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'login'   => 'Masuk (Login)',
                        'logout'  => 'Keluar (Logout)',
                        default   => ucfirst($state),
                    }),

                // Menampilkan informasi browser/perangkat jika tersedia
                TextColumn::make('properties.user_agent')
                    ->label('Perangkat / Browser')
                    ->wrap()
                    ->limit(50)
                    ->size('xs')
                    ->color('gray')
                    ->default('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('description')
                    ->label('Jenis Aktivitas')
                    ->options([
                        'login'   => 'Login',
                        'logout'  => 'Logout',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Sesi Login')
                    ->description('Detail waktu masuk dan keluar pengguna')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('causer.name')
                            ->label('Nama Pengguna')
                            ->weight('bold'),
                        
                        TextEntry::make('description')
                            ->label('Status Aktivitas')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'login'   => 'success',
                                'logout'  => 'gray',
                                default   => 'info',
                            }),

                        TextEntry::make('created_at')
                            ->label('Waktu (Jam & Tanggal)')
                            ->dateTime('d F Y, H:i:s'),

                        TextEntry::make('causer.email')
                            ->label('Email Akun')
                            ->color('gray'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'view' => Pages\ViewActivity::route('/{record}'),
        ];
    }

    // Hanya Owner yang bisa akses, dan hanya bisa VIEW (tidak bisa Edit/Hapus/Tambah)
    public static function can(string $action, ?Model $record = null): bool
    {
        $user = Auth::user();
        /** @var \App\Models\User|null $user */
        if (!$user || $user->role !== 'owner') return false;

        if (in_array($action, ['create', 'update', 'delete', 'deleteAny'])) return false;

        return parent::can($action, $record);
    }
}