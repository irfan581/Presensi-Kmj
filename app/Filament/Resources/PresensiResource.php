<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresensiResource\Pages;
use App\Models\Presensi;
use App\Models\KunjunganToko;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
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
        // ✅ OPT: Cache 60s — badge query setiap sidebar render = boros
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
                // ✅ OPT: select kolom spesifik — tidak load semua
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

                // ✅ OPT: Gunakan foto_masuk (path) bukan foto_masuk_url (accessor)
                // ImageColumn handle URL sendiri via disk — tidak perlu accessor
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
            ->headerActions([
                Tables\Actions\Action::make('export_combined_pdf')
                    ->label('Download Laporan')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn(HasTable $livewire) => self::_generatePdf($livewire)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    // ─── PAGES ────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPresensis::route('/'),
            'view'  => Pages\ViewPresensi::route('/{record}'),
        ];
    }

    // ─── PDF GENERATOR ────────────────────────────────────────

    private static function _generatePdf(HasTable $livewire): mixed
    {
        // ✅ OPT: select kolom yang dibutuhkan saja di query PDF
        $presensi = $livewire->getFilteredTableQuery()
            ->select([
                'id', 'sales_id', 'tanggal', 'status',
                'jam_masuk', 'jam_pulang', 'jam_perangkat_masuk',
                'location_masuk', 'location_pulang',
                'keterangan', 'is_suspicious',
            ])
            ->with('sales:id,nama')
            ->get();

        if ($presensi->isEmpty()) {
            Notification::make()->warning()->title('Data tidak ditemukan')->send();
            return null;
        }

        $filterData = $livewire->tableFilters;
        $tglDari    = $filterData['tanggal']['dari']   ?? $presensi->min('tanggal');
        $tglSampai  = $filterData['tanggal']['sampai'] ?? $presensi->max('tanggal');
        $range      = Carbon::parse($tglDari)->format('d/m/Y')
                    . ' - '
                    . Carbon::parse($tglSampai)->format('d/m/Y');

        $salesIds = $presensi->pluck('sales_id')->unique();
        $kunjungan = KunjunganToko::whereIn('sales_id', $salesIds)
            ->whereBetween('created_at', [
                Carbon::parse($tglDari)->startOfDay(),
                Carbon::parse($tglSampai)->endOfDay(),
            ])
            ->select(['id', 'sales_id', 'nama_toko', 'location', 'keterangan', 'is_suspicious', 'suspicious_reason', 'created_at'])
            ->with('sales:id,nama') 
            ->get();

        $combined = collect();

        foreach ($presensi as $p) {
            $tglBersih = Carbon::parse($p->tanggal)->toDateString();
            $isTimeSuspicious = ($p->jam_masuk && $p->jam_perangkat_masuk)
                && Carbon::parse($p->jam_masuk)->diffInMinutes(Carbon::parse($p->jam_perangkat_masuk)) > 10;

            $combined->push([
                'sales_name' => $p->sales->nama,
                'waktu'      => $tglBersih . ' ' . $p->jam_masuk,
                'type'       => 'Absen',
                'detail'     => 'MASUK ' . ($p->status === 'terlambat' ? '(Telat)' : '(Tepat Waktu)'),
                'location'   => $p->location_masuk,
                'keterangan' => $p->keterangan,
                'suspicious' => $p->is_suspicious || $isTimeSuspicious,
                'reason'     => $p->is_suspicious ? 'Fake GPS' : ($isTimeSuspicious ? 'Jam HP Tidak Sinkron' : ''),
            ]);

            if ($p->jam_pulang) {
                $combined->push([
                    'sales_name' => $p->sales->nama,
                    'waktu'      => $tglBersih . ' ' . $p->jam_pulang,
                    'type'       => 'Absen',
                    'detail'     => 'PULANG',
                    'location'   => $p->location_pulang,
                    'keterangan' => 'Selesai tugas',
                    'suspicious' => false,
                    'reason'     => '',
                ]);
            }
        }

        foreach ($kunjungan as $k) {
            $combined->push([
                'sales_name' => $k->sales->nama, // ✅ Sudah eager loaded — tidak N+1
                'waktu'      => $k->created_at->format('Y-m-d H:i:s'),
                'type'       => 'Toko',
                'detail'     => 'Kunjungan: ' . ($k->nama_toko ?? 'Toko'),
                'location'   => $k->location,
                'keterangan' => $k->keterangan,
                'suspicious' => $k->is_suspicious,
                'reason'     => $k->suspicious_reason,
            ]);
        }

        $sorted   = $combined->sortBy('waktu');
        $selected = $livewire->tableFilters['sales']['values'] ?? [];
        $isSingle = count($selected) === 1;
        $fname    = $isSingle
            ? 'Laporan-' . str_replace(' ', '-', $presensi->first()->sales->nama)
            : 'Laporan-Tim';

        return response()->streamDownload(
            function () use ($sorted, $presensi, $isSingle, $range) {
                echo Pdf::loadView('pdf.recap-presensi', [
                    'activities' => $sorted,
                    'sales'      => $isSingle ? $presensi->first()->sales : null,
                    'date_range' => $range,
                ])
                ->setPaper('a4', 'landscape')
                ->setOption(['isRemoteEnabled' => true])
                ->output();
            },
            $fname . '-' . date('YmdHi') . '.pdf'
        );
    }
}