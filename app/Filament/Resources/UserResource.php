<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{Select, TextInput, FileUpload, Section, Toggle, CheckboxList, Grid};
use Filament\Tables\Columns\{TextColumn, ImageColumn, IconColumn};
use Illuminate\Support\Facades\{Hash, Auth};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    public static function getEloquentQuery(): Builder
    {
        // ✅ Owner tidak tampil di list
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->where('role', '!=', 'owner');
    }

    public static function canViewAny(): bool
    {
        return in_array(Auth::user()?->role, ['boss', 'owner']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Akun')
                                    ->description('Data utama login dan kontrol akses dashboard.')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Lengkap')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true),

                                        Select::make('role')
                                            ->options([
                                                'boss'  => 'Boss',
                                                'admin' => 'Admin',
                                            ])
                                            ->required()
                                            ->native(false)
                                            ->live()
                                            ->afterStateUpdated(fn ($state, $set) =>
                                                $set('is_admin', $state === 'boss')
                                            ),

                                        Toggle::make('is_admin')
                                            ->label('Akses Panel Admin')
                                            ->onColor('success')
                                            ->inline(false)
                                            ->disabled(fn ($get) => $get('role') === 'boss')
                                            ->dehydrated(),
                                    ])->columns(2),

                                Section::make('Hak Akses Spesifik')
                                    ->description('Tentukan izin fitur khusus untuk Role Admin.')
                                    ->schema([
                                        CheckboxList::make('permissions')
                                            ->label('')
                                            ->options([
                                                'view_absensi'    => 'Lihat Data Absensi',
                                                'delete_absensi'  => 'Hapus Data Absensi',
                                                'view_kunjungan'  => 'Lihat Kunjungan Toko',
                                                'delete_kunjungan'=> 'Hapus Kunjungan Toko',
                                                'view_izin'       => 'Lihat Izin Karyawan',
                                                'export_pdf'      => 'Cetak Laporan PDF',
                                            ])
                                            ->columns(2)
                                            ->bulkToggleable()
                                            ->hidden(fn ($get) => in_array($get('role'), ['boss', 'owner']))
                                    ])->collapsible(),
                            ])->columnSpan(['lg' => 2]),

                        Grid::make(1)
                            ->schema([
                                Section::make('Foto Profil')
                                    ->schema([
                                        FileUpload::make('avatar_url')
                                            ->label('')
                                            ->image()
                                            ->avatar()
                                            ->directory('avatars')
                                            ->imageEditor()
                                            ->alignCenter(),
                                    ]),

                                Section::make('Keamanan Akun')
                                    ->description('Update password secara berkala.')
                                    ->schema([
                                        TextInput::make('password')
                                            ->label('Kata Sandi Baru')
                                            ->password()
                                            ->revealable()
                                            ->required(fn ($context) => $context === 'create')
                                            ->rule(Password::default()->mixedCase()->numbers())
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                            ->same('password_confirmation'),

                                        TextInput::make('password_confirmation')
                                            ->label('Ulangi Kata Sandi')
                                            ->password()
                                            ->revealable()
                                            ->required(fn ($context) => $context === 'create')
                                            ->dehydrated(false),
                                    ]),
                            ])->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record) => $record->email),

                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'boss'  => 'danger',
                        'admin' => 'success',
                        default => 'gray',
                    }),

                IconColumn::make('is_admin')
                    ->boolean()
                    ->label('Admin')
                    ->alignCenter(),
            ])
            ->filters([
                // ✅ Owner tidak ada di pilihan filter
                Tables\Filters\SelectFilter::make('role')
                    ->options(['boss' => 'Boss', 'admin' => 'Admin']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (User $record, Tables\Actions\DeleteAction $action) {
                        if ($record->id === Auth::id()) $action->cancel();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}