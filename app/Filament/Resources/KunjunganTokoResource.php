<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KunjunganTokoResource\Pages;
use App\Models\KunjunganToko;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;

class KunjunganTokoResource extends Resource
{
    protected static ?string $model = KunjunganToko::class;
    protected static ?string $navigationIcon  = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Kunjungan Toko';
    protected static ?string $pluralModelLabel = 'Kunjungan Toko';
    protected static ?int    $navigationSort  = 3;

    public static function canCreate(): bool { return false; }

    // ─── FORM ─────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kunjungan')
                    ->schema([
                        Forms\Components\Select::make('sales_id')
                            ->relationship('sales', 'nama')
                            ->disabled(),
                        Forms\Components\TextInput::make('nama_toko')
                            ->disabled(),
                        Forms\Components\TextInput::make('location')
                            ->label('Koordinat GPS')
                            ->disabled(),
                        Forms\Components\Textarea::make('keterangan')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Bukti & Validasi')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_kunjungan')
                            ->image()
                            ->disk('public')
                            ->disabled(),
                        Forms\Components\Toggle::make('is_suspicious')
                            ->label('Mencurigakan'),
                        Forms\Components\TextInput::make('suspicious_reason')
                            ->label('Alasan Kecurigaan'),
                    ])->columns(2),
            ]);
    }

    // ─── TABLE ────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            // ✅ FIX N+1: eager load sales — sebelumnya tidak ada!
            // sales.nama di kolom TextColumn::make('sales.nama') trigger N+1
            ->modifyQueryUsing(fn($query) => $query
                ->select([
                    'id', 'sales_id', 'nama_toko', 'foto_kunjungan',
                    'is_suspicious', 'created_at',
                ])
                ->with('sales:id,nama')
            )
            ->defaultSort('created_at', 'desc')
            // ✅ OPT: Default 15 record per halaman — tidak load semua sekaligus
            ->defaultPaginationPageOption(15)
            ->columns([
                TextColumn::make('sales.nama')
                    ->label('Sales')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('nama_toko')
                    ->label('Toko')
                    ->searchable(),

                // ✅ OPT: ImageColumn hanya tampil di view detail
                // Di list pakai icon saja — tidak load semua URL foto sekaligus
                IconColumn::make('foto_kunjungan')
                    ->label('Foto')
                    ->boolean()
                    ->trueIcon('heroicon-o-camera')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success'),

                IconColumn::make('is_suspicious')
                    ->label('Fake GPS?')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle'),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_suspicious')
                    ->label('Status Kecurangan'),
                SelectFilter::make('sales_id')
                    ->relationship('sales', 'nama')
                    ->label('Filter Per Sales'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKunjunganTokos::route('/'),
            'view'  => Pages\ViewKunjunganToko::route('/{record}'),
        ];
    }
}