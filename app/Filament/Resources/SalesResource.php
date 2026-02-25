<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Models\Sales;
use App\Models\Presensi;
use App\Models\KunjunganToko;
use App\Models\Izin;
use App\Models\NotifikasiSales;
use App\Services\FcmService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\{TextInput, Toggle, FileUpload, Textarea, Section, Grid, Group, DatePicker, CheckboxList};
use Filament\Tables\Columns\{TextColumn, ImageColumn, IconColumn};
use Filament\Tables\Actions\{Action, ActionGroup};
use Filament\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Data Karyawan';
    protected static ?int    $navigationSort  = 1;

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
                    ->label('Area Tugas')
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

                    // ─── ACTION: KIRIM NOTIFIKASI ───────────────────────────
                    Action::make('kirimNotifikasi')
                        ->label('Kirim Pesan')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->form([
                            TextInput::make('title')
                                ->label('Judul Notifikasi')
                                ->required()
                                ->maxLength(100),
                            Textarea::make('message')
                                ->label('Isi Pesan')
                                ->rows(3)
                                ->required()
                                ->maxLength(500),
                        ])
                        ->action(function (Sales $record, array $data) {
                            // ✅ FIX: Refresh dulu agar fcm_token terbaru
                            $record->refresh();

                            // 1. Simpan ke database notifikasi
                            NotifikasiSales::create([
                                'sales_id' => $record->id,
                                'title'    => $data['title'],
                                'message'  => $data['message'],
                                'is_read'  => false,
                            ]);

                            // 2. ✅ FIX: Langsung kirim FCM (tidak andalkan Observer)
                            $token = $record->fcm_token;

                            if (empty($token)) {
                                Notification::make()
                                    ->warning()
                                    ->title('Notifikasi disimpan, tapi FCM token belum terdaftar.')
                                    ->body('Sales belum login atau token belum tersinkron.')
                                    ->send();
                                return;
                            }

                            $berhasil = FcmService::sendNotification(
                                $token,
                                $data['title'],
                                $data['message'],
                                [
                                    'type'     => 'admin_notif',
                                    'sales_id' => (string) $record->id,
                                ]
                            );

                            if ($berhasil) {
                                Notification::make()
                                    ->success()
                                    ->title('Notifikasi berhasil dikirim!')
                                    ->send();
                            } else {
                                Notification::make()
                                    ->warning()
                                    ->title('Notifikasi tersimpan, tapi pengiriman FCM gagal.')
                                    ->body('Cek log Laravel untuk detail error.')
                                    ->send();
                            }
                        }),

                    // ─── ACTION: REKAP PDF ──────────────────────────────────
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
                            CheckboxList::make('filter_tipe')
                                ->label('Pilih Data yang Ingin Ditampilkan')
                                ->options([
                                    'absen' => 'Presensi (Masuk & Pulang)',
                                    'toko'  => 'Kunjungan Toko',
                                    'izin'  => 'Izin/Sakit (Approved)',
                                ])
                                ->default(['absen', 'toko', 'izin'])
                                ->columns(3)
                                ->required(),
                        ])
                        ->action(function (Sales $record, array $data) {
                            $dari   = Carbon::parse($data['dari'])->toDateString();
                            $sampai = Carbon::parse($data['sampai'])->toDateString();
                            $tipeTerpilih = $data['filter_tipe'];

                            $combined = collect();
                            $totalHadir = 0;
                            $totalKunjungan = 0;

                            if (in_array('absen', $tipeTerpilih)) {
                                $presensi = Presensi::where('sales_id', $record->id)
                                    ->whereBetween('tanggal', [$dari, $sampai])
                                    ->get();

                                $totalHadir = $presensi->count();

                                $presensi->each(function ($p) use ($combined) {
                                    $tgl = Carbon::parse($p->tanggal)->toDateString();
                                    $combined->push([
                                        'waktu'      => $tgl . ' ' . $p->jam_masuk,
                                        'type'       => 'Absen',
                                        'detail'     => 'MASUK ' . ($p->status === 'terlambat' ? '(Telat)' : '(On Time)'),
                                        'location'   => $p->location_masuk,
                                        'keterangan' => $p->keterangan ?? '-',
                                        'sort_key'   => Carbon::parse($tgl . ' ' . $p->jam_masuk),
                                    ]);
                                    if ($p->jam_pulang) {
                                        $combined->push([
                                            'waktu'      => $tgl . ' ' . $p->jam_pulang,
                                            'type'       => 'Absen',
                                            'detail'     => 'PULANG',
                                            'location'   => $p->location_pulang,
                                            'keterangan' => 'Selesai Tugas',
                                            'sort_key'   => Carbon::parse($tgl . ' ' . $p->jam_pulang),
                                        ]);
                                    }
                                });
                            }

                            if (in_array('toko', $tipeTerpilih)) {
                                $kunjungan = KunjunganToko::where('sales_id', $record->id)
                                    ->whereBetween('created_at', [
                                        Carbon::parse($dari)->startOfDay(),
                                        Carbon::parse($sampai)->endOfDay()
                                    ])
                                    ->get();

                                $totalKunjungan = $kunjungan->count();

                                $kunjungan->each(function ($k) use ($combined) {
                                    $combined->push([
                                        'waktu'      => $k->created_at->format('Y-m-d H:i:s'),
                                        'type'       => 'Toko',
                                        'detail'     => 'Kunjungan: ' . ($k->nama_toko ?? 'Toko'),
                                        'location'   => $k->location,
                                        'keterangan' => $k->keterangan ?? '-',
                                        'sort_key'   => $k->created_at,
                                    ]);
                                });
                            }

                            if (in_array('izin', $tipeTerpilih)) {
                                Izin::where('sales_id', $record->id)
                                    ->where('status', 'approved')
                                    ->whereBetween('tanggal', [$dari, $sampai])
                                    ->get()
                                    ->each(function ($i) use ($combined) {
                                        $combined->push([
                                            'waktu'      => Carbon::parse($i->tanggal)->format('Y-m-d'),
                                            'type'       => 'Izin',
                                            'detail'     => 'IZIN: ' . strtoupper($i->jenis_izin),
                                            'location'   => '-',
                                            'keterangan' => $i->keterangan,
                                            'sort_key'   => Carbon::parse($i->tanggal)->startOfDay(),
                                        ]);
                                    });
                            }

                            if ($combined->isEmpty()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Data tidak ditemukan untuk kriteria ini')
                                    ->send();
                                return;
                            }

                            $activities = $combined->sortBy('sort_key');

                            return response()->streamDownload(
                                function () use ($record, $activities, $dari, $sampai, $totalHadir, $totalKunjungan) {
                                    echo Pdf::loadView('pdf.recap-presensi', [
                                        'sales'           => $record,
                                        'activities'      => $activities,
                                        'total_hadir'     => $totalHadir,
                                        'total_kunjungan' => $totalKunjungan,
                                        'date_range'      => Carbon::parse($dari)->format('d/m/Y') . ' - ' . Carbon::parse($sampai)->format('d/m/Y'),
                                    ])->setPaper('a4', 'landscape')->output();
                                },
                                "Rekap_{$record->nama}_" . date('Ymd_Hi') . ".pdf"
                            );
                        }),

                    // ─── ACTION: RESET DEVICE ───────────────────────────────
                    Action::make('resetDevice')
                        ->label('Reset Device ID')
                        ->icon('heroicon-o-arrow-path')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Sales $record) {
                            $record->updateQuietly(['device_id' => null]);
                            Notification::make()
                                ->success()
                                ->title('Device ID berhasil dilepas!')
                                ->send();
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