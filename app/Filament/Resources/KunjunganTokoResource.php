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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;

class KunjunganTokoResource extends Resource
{
    protected static ?string $model = KunjunganToko::class;
    protected static ?string $navigationIcon   = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel  = 'Kunjungan Toko';
    protected static ?string $pluralModelLabel = 'Kunjungan Toko';
    protected static ?int    $navigationSort   = 3;

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Kunjungan')
                ->schema([
                    Forms\Components\Select::make('sales_id')->relationship('sales', 'nama')->disabled(),
                    Forms\Components\TextInput::make('nama_toko')->disabled(),
                    Forms\Components\TextInput::make('location')->label('Koordinat GPS')->disabled(),
                    Forms\Components\Textarea::make('keterangan')->disabled()->columnSpanFull(),
                ])->columns(2),
            Forms\Components\Section::make('Bukti & Validasi')
                ->schema([
                    Forms\Components\FileUpload::make('foto_kunjungan')->image()->disk('public')->disabled(),
                    Forms\Components\Toggle::make('is_suspicious')->label('Mencurigakan'),
                    Forms\Components\TextInput::make('suspicious_reason')->label('Alasan Kecurigaan'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with('sales'))
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(15)
            
            // --- FIXED GROUPING ---
            ->groups([
                Group::make('sales.nama')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (KunjunganToko $record): string => $record->sales->nama),
            ])
            ->defaultGroup('sales.nama') 
            
            ->columns([
                TextColumn::make('nama_toko')
                    ->label('Toko')
                    ->searchable()
                    ->weight('bold')
                    ->color('warning'),

                IconColumn::make('foto_kunjungan')
                    ->label('Foto')
                    ->boolean()
                    ->trueIcon('heroicon-o-camera')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->alignCenter(),

                IconColumn::make('is_suspicious')
                    ->label('GPS Aman?')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle') 
                    ->falseIcon('heroicon-o-check-circle')       
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Jam Kunjungan')
                    ->dateTime('H:i') 
                    ->description(fn (KunjunganToko $record): string => $record->created_at->format('d M Y'))
                    ->alignEnd()
                    ->sortable(false), 
            ])
            ->filters([
                TernaryFilter::make('is_suspicious')->label('Status Kecurangan'),
                SelectFilter::make('sales_id')->relationship('sales', 'nama')->label('Filter Per Sales'),
                Tables\Filters\Filter::make('created_at')
                    ->form([Forms\Components\DatePicker::make('tanggal')])
                    ->query(fn($query, $data) => $query->when($data['tanggal'], fn($q) => $q->whereDate('created_at', $data['tanggal'])))
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail')->color('gray')->icon('heroicon-m-eye'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
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