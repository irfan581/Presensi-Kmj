<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $sales_id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property \Illuminate\Support\Carbon|null $sampai_tanggal
 * @property string $jenis_izin
 * @property string $keterangan
 * @property string|null $bukti_foto
 * @property string $status
 * @property string|null $alasan_tolak
 * @property string|null $rejection_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property-read \App\Models\User|null $admin
 * @property-read string|null $bukti_foto_url
 * @property-read int $durasi_hari
 * @property-read string $status_label
 * @property-read \App\Models\Sales $sales
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin approved()
 * @method static \Database\Factories\IzinFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereAlasanTolak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereBuktiFoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereJenisIzin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereSalesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereSampaiTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Izin whereUpdatedAt($value)
 */
	class Izin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sales_id
 * @property string $nama_toko
 * @property string $location
 * @property string $foto_kunjungan
 * @property string|null $keterangan
 * @property bool $is_suspicious
 * @property string|null $suspicious_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $foto_kunjungan_url
 * @property-read \App\Models\Sales $sales
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko bySales($salesId)
 * @method static \Database\Factories\KunjunganTokoFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko hariIni()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereFotoKunjungan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereIsSuspicious($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereNamaToko($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereSalesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereSuspiciousReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KunjunganToko whereUpdatedAt($value)
 */
	class KunjunganToko extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sales_id
 * @property string $title
 * @property string $message
 * @property int $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Sales $sales
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales whereSalesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifikasiSales whereUpdatedAt($value)
 */
	class NotifikasiSales extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sales_id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string $jam_masuk
 * @property string|null $status
 * @property string|null $jam_perangkat_masuk
 * @property string|null $jam_pulang
 * @property string|null $jam_perangkat_pulang
 * @property string $foto_masuk
 * @property string|null $foto_pulang
 * @property string $location_masuk
 * @property string|null $location_pulang
 * @property string|null $keterangan
 * @property bool $is_suspicious
 * @property string|null $suspicious_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $foto_masuk_url
 * @property-read string|null $foto_pulang_url
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KunjunganToko> $kunjungan
 * @property-read int|null $kunjungan_count
 * @property-read \App\Models\Sales $sales
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi bySales(int $salesId)
 * @method static \Database\Factories\PresensiFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi hariIni()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereFotoMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereFotoPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereIsSuspicious($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereJamMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereJamPerangkatMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereJamPerangkatPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereJamPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereLocationMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereLocationPulang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereSalesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereSuspiciousReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Presensi whereUpdatedAt($value)
 */
	class Presensi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nik
 * @property string $nama
 * @property string|null $no_hp
 * @property string|null $area
 * @property string|null $alamat
 * @property string $password
 * @property string|null $fcm_token
 * @property string|null $foto_profil
 * @property string|null $device_id
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $foto_profil_url
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NotifikasiSales> $notifikasi
 * @property-read int|null $notifikasi_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Presensi> $presensi
 * @property-read int|null $presensi_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\SalesFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereFcmToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereFotoProfil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sales withoutTrashed()
 */
	class Sales extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property bool $is_admin
 * @property string|null $allowed_ip
 * @property int $session_version
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $breezy_session
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Jeffgreco13\FilamentBreezy\Models\BreezySession> $breezySessions
 * @property-read int|null $breezy_sessions_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read mixed $two_factor_recovery_codes
 * @property-read mixed $two_factor_secret
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAllowedIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSessionVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser, \Filament\Models\Contracts\HasAvatar {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $type
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property array<array-key, mixed> $data
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $notifiable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|notifications whereUpdatedAt($value)
 */
	class notifications extends \Eloquent {}
}

