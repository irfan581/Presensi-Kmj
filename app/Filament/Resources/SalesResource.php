<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Models\Sales;
use App\Models\Presensi;
use App\Models\KunjunganToko;
use App\Models\NotifikasiSales; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\{TextInput, Toggle, FileUpload, Textarea, Section, Grid, Group, DatePicker};
use Filament\Tables\Columns\{TextColumn, ImageColumn, IconColumn};
use Filament\Tables\Actions\{Action, ActionGroup};
use Filament\Notifications\Notification; 
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // Penting untuk Auth::user()

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Data Karyawan';
    protected static ?int    $navigationSort  = 1;

    // ─── FORM ─────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Profil Sales')
                            ->schema([
                                FileUpload::make('foto_profil')
                                    ->image()
                                    ->avatar()
                                    ->imageEditor()
                                    ->directory('foto-sales')
                                    ->columnSpanFull(),
                                Toggle::make('is_active')
                                    ->label('Status Akun Aktif')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->default(true),
                            ]),
                    ])->columnSpan(['lg' => 1]),

                Group::make()
                    ->schema([
                        Section::make('Informasi Pribadi')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('nik')
                                        ->label('NIK (User ID Login)')
                                        ->required()
                                        ->numeric()
                                        ->unique(Sales::class, 'nik', ignoreRecord: true),
                                    TextInput::make('nama')
                                        ->label('Nama Lengkap')
                                        ->required(),
                                    TextInput::make('no_hp')
                                        ->label('No. WhatsApp')
                                        ->tel()
                                        ->required(),
                                    TextInput::make('area')
                                        ->label('Area Tugas')
                                        ->required(),
                                ]),
                                Textarea::make('alamat')
                                    ->label('Alamat Domisili')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Keamanan Akun')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('password')
                                        ->label('Password Baru')
                                        ->password()
                                        ->revealable()
                                        ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                                        ->dehydrated(fn($state) => filled($state))
                                        ->required(fn(string $context): bool => $context === 'create')
                                        ->placeholder(fn(string $context): string =>
                                            $context === 'edit' ? 'Kosongkan jika tidak diubah' : 'Masukkan password'
                                        ),
                                    TextInput::make('device_id')
                                        ->label('Device ID Terdaftar')
                                        ->disabled()
                                        ->placeholder('Terisi otomatis saat login'),
                                ]),
                            ])
                            ->collapsed(fn(string $context): bool => $context === 'edit'),
                    ])->columnSpan(['lg' => 2]),
            ])->columns(3);
    }

    // ─── TABLE ────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query
                ->select(['id', 'nama', 'nik', 'no_hp', 'area', 'foto_profil', 'is_active', 'last_login_at', 'device_id', 'fcm_token'])
                ->withTrashed(false)
            )
            ->columns([
                ImageColumn::make('foto_profil')
                    ->disk('public')
                    ->circular()
                    ->label('Foto')
                    ->defaultImageUrl(fn(Sales $record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->nama)
                        . '&background=EA580C&color=fff&bold=true'
                    ),

                TextColumn::make('nama')
                    ->label('Nama Sales')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn(Sales $record) => "NIK: {$record->nik}"),

                TextColumn::make('no_hp')
                    ->label('WhatsApp')
                    ->copyable()
                    ->color('success'),

                TextColumn::make('area')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Akun'),
                Tables\Filters\SelectFilter::make('area')->label('Filter Area'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    // ─── ACTION: KIRIM NOTIFIKASI (FIXED) ───
                    Action::make('kirimNotifikasi')
                        ->label('Kirim Notifikasi')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->form([
                            TextInput::make('title')
                                ->label('Judul Notifikasi')
                                ->placeholder('Contoh: Pengumuman Penting')
                                ->required(),
                            Textarea::make('message')
                                ->label('Isi Pesan')
                                ->placeholder('Tulis pesan Anda di sini...')
                                ->rows(3)
                                ->required(),
                        ])
                        ->action(function (Sales $record, array $data) {
                            // PAKSA REFRESH DATA DARI DATABASE AGAR TOKEN TERBARU TERBACA ✅
                            $record->refresh();

                            // 1. Simpan ke tabel riwayat notifikasi (Database)
                            NotifikasiSales::create([
                                'sales_id' => $record->id,
                                'title'    => $data['title'],
                                'message'  => $data['message'],
                                'is_read'  => false,
                            ]);

                            // 2. KIRIM REAL-TIME VIA FCM ✅
                            if ($record->fcm_token) {
                                try {
                                    \App\Services\FcmService::sendNotification(
                                        $record->fcm_token,
                                        $data['title'],
                                        $data['message'],
                                        ['type' => 'manual_admin'] // Data tambahan untuk Flutter
                                    );
                                    
                                    $statusNotif = "Notifikasi berhasil terkirim ke HP {$record->nama}.";
                                    $statusIcon = 'success';
                                } catch (\Exception $e) {
                                    $statusNotif = "Data disimpan ke riwayat, tapi FCM gagal: " . $e->getMessage();
                                    $statusIcon = 'danger';
                                }
                            } else {
                                $statusNotif = "Data disimpan ke riwayat, tapi gagal kirim ke HP karena Sales belum login/tidak ada token di sistem.";
                                $statusIcon = 'warning';
                            }

                            // 3. Notifikasi untuk Admin (Dashboard Web)
                            Notification::make()
                                ->title($statusNotif)
                                ->status($statusIcon)
                                ->send();
                        }),

                    // ─── ACTION: REKAP PDF ───
                    Action::make('downloadReport')
                        ->label('Rekap Aktivitas (PDF)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->form([
                            Grid::make(2)->schema([
                                DatePicker::make('dari')
                                    ->label('Dari Tanggal')
                                    ->default(now()->startOfMonth())
                                    ->required(),
                                DatePicker::make('sampai')
                                    ->label('Sampai Tanggal')
                                    ->default(now())
                                    ->required(),
                            ]),
                        ])
                        ->action(function (Sales $record, array $data) {
                            $dari   = Carbon::parse($data['dari'])->toDateString();
                            $sampai = Carbon::parse($data['sampai'])->toDateString();

                            $combined = collect();

                            Presensi::where('sales_id', $record->id)
                                ->whereBetween('tanggal', [$dari, $sampai])
                                ->orderBy('tanggal')
                                ->each(function ($p) use ($combined) {
                                    $tglBersih = Carbon::parse($p->tanggal)->toDateString();
                                    $combined->push([
                                        'waktu'      => $tglBersih . ' ' . $p->jam_masuk,
                                        'type'       => 'Absen',
                                        'detail'     => 'MASUK ' . ($p->status === 'terlambat' ? '(Telat)' : '(On Time)'),
                                        'location'   => $p->location_masuk,
                                        'keterangan' => $p->keterangan,
                                        'suspicious' => $p->is_suspicious,
                                        'sort_key'   => Carbon::parse($tglBersih . ' ' . $p->jam_masuk),
                                    ]);

                                    if ($p->jam_pulang) {
                                        $combined->push([
                                            'waktu'      => $tglBersih . ' ' . $p->jam_pulang,
                                            'type'       => 'Absen',
                                            'detail'     => 'PULANG',
                                            'location'   => $p->location_pulang,
                                            'keterangan' => 'Tugas Selesai',
                                            'suspicious' => false,
                                            'sort_key'   => Carbon::parse($tglBersih . ' ' . $p->jam_pulang),
                                        ]);
                                    }
                                });

                            KunjunganToko::where('sales_id', $record->id)
                                ->whereBetween('created_at', [Carbon::parse($dari)->startOfDay(), Carbon::parse($sampai)->endOfDay()])
                                ->orderBy('created_at')
                                ->each(function ($k) use ($combined) {
                                    $combined->push([
                                        'waktu'      => $k->created_at->format('Y-m-d H:i:s'),
                                        'type'       => 'Toko',
                                        'detail'     => 'Kunjungan: ' . $k->nama_toko,
                                        'location'   => $k->location,
                                        'keterangan' => $k->keterangan,
                                        'suspicious' => $k->is_suspicious,
                                        'sort_key'   => $k->created_at,
                                    ]);
                                });

                            if ($combined->isEmpty()) {
                                Notification::make()->warning()->title('Tidak ada data pada periode ini')->send();
                                return;
                            }

                            $activities = $combined->sortBy('sort_key');

                            return response()->streamDownload(
                                function () use ($record, $activities, $dari, $sampai) {
                                    echo Pdf::loadView('pdf.recap-presensi', [
                                        'sales'      => $record,
                                        'activities' => $activities,
                                        'date_range' => Carbon::parse($dari)->format('d/m/Y') . ' - ' . Carbon::parse($sampai)->format('d/m/Y'),
                                    ])->setPaper('a4', 'landscape')->output();
                                },
                                "Rekap_{$record->nama}_" . date('Ymd') . ".pdf"
                            );
                        }),

                    // ─── ACTION: RESET DEVICE ───
                    Action::make('resetDevice')
                        ->label('Reset Device ID')
                        ->icon('heroicon-o-arrow-path')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Sales $record) {
                            $record->updateQuietly(['device_id' => null]);
                            Notification::make()->success()->title('Perangkat berhasil dilepas!')->send();
                        }),

                ])->label('Menu')->button()->color('gray'),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            'edit'   => Pages\EditSales::route('/{record}/edit'),
        ];
    }
}