<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AbsenRequest;
use App\Services\AttendanceService;
use App\Services\IzinService;
use App\Models\Presensi;
use App\Models\KunjunganToko;
use App\Models\Sales;
use App\Http\Resources\Api\SalesResource;
use App\Http\Resources\Api\PresensiResource;
use App\Http\Resources\Api\IzinResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected AttendanceService $service;
    protected IzinService $izinService;

    public function __construct(AttendanceService $service, IzinService $izinService)
    {
        $this->service     = $service;
        $this->izinService = $izinService;
    }

    // ═══════════════════════════════════════════════════════════
    // LOGIN
    // ═══════════════════════════════════════════════════════════

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'nik'       => 'required|string',
            'password'  => 'required|string',
            'device_id' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        $sales = Sales::where('nik', $request->nik)->first();

        if (!$sales || !Hash::check($request->password, $sales->password)) {
            return response()->json([
                'success' => false,
                'message' => 'NIK atau Password salah',
            ], 401);
        }

        $sales->device_id = $request->device_id;
        if ($request->fcm_token) {
            $sales->fcm_token = $request->fcm_token;
        }
        $sales->save();

        $token = $sales->createToken('sales_auth_token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'Login Berhasil',
            'access_token' => $token,
            'user'         => new SalesResource($sales->refresh()),
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // LOGOUT
    // ═══════════════════════════════════════════════════════════

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['success' => true, 'message' => 'Logout berhasil']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal logout'], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ABSEN MASUK
    // ✅ Cek izin aktif sebelum proses
    // ═══════════════════════════════════════════════════════════

    public function absenMasuk(AbsenRequest $request): JsonResponse
    {
        try {
            $sales = $request->user();

            // ✅ Cek izin aktif — tolak jika sakit/cuti
            $cekIzin = $this->izinService->cekBolehAbsenMasuk($sales->id);
            if (!$cekIzin['boleh']) {
                return response()->json([
                    'success'    => false,
                    'message'    => $cekIzin['alasan'],
                    'izin_aktif' => $cekIzin['izin']
                        ? new IzinResource($cekIzin['izin'])
                        : null,
                    'kode'       => 'IZIN_AKTIF',
                ], 403);
            }

            // ✅ Tentukan status berdasarkan izin terlambat
            $jamMasuk = $request->jam_perangkat ?? Carbon::now('Asia/Jakarta')->format('H:i:s');
            $status   = $this->izinService->tentukanStatusAbsen($sales->id, $jamMasuk);

            $data = [
                'location_masuk'      => $request->latitude . ',' . $request->longitude,
                'jam_perangkat_masuk' => $jamMasuk,
                'keterangan'          => $request->keterangan ?? null,
                'status_override'     => $status,
            ];

            $presensi = $this->service->handleMasuk(
                $sales->id,
                $data,
                $request->file('foto_masuk')
            );

            $message = match($status) {
                'terlambat_izin' => 'Absen masuk berhasil (Terlambat - Ada Izin)',
                'terlambat'      => 'Absen masuk berhasil (Terlambat)',
                default          => 'Absen masuk berhasil',
            };

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => new PresensiResource($presensi),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ABSEN PULANG
    // ✅ Cek izin pulang cepat
    // ═══════════════════════════════════════════════════════════

    public function absenPulang(AbsenRequest $request): JsonResponse
    {
        try {
            $sales = $request->user();
            $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

            $presensi = Presensi::where('sales_id', $sales->id)
                ->where('tanggal', $today)
                ->first();

            if (!$presensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum absen masuk',
                ], 400);
            }

            $data = [
                'location_pulang'      => $request->latitude . ',' . $request->longitude,
                'jam_perangkat_pulang' => $request->jam_perangkat ?? now()->format('H:i:s'),
                'pulang_cepat_izin'    => $this->izinService->bolehPulangCepat($sales->id),
            ];

            $updated = $this->service->handlePulang(
                $presensi,
                $data,
                $request->file('foto_pulang')
            );

            return response()->json([
                'success' => true,
                'message' => $data['pulang_cepat_izin']
                    ? 'Absen pulang berhasil (Pulang Cepat - Ada Izin)'
                    : 'Absen pulang berhasil',
                'data'    => new PresensiResource($updated),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // STATUS HARI INI
    // ✅ Tambah info izin aktif untuk Flutter
    // ═══════════════════════════════════════════════════════════

    public function presensiHariIni(Request $request): JsonResponse
    {
        $sales = $request->user();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        $presensi        = Presensi::where('sales_id', $sales->id)->where('tanggal', $today)->first();
        $jumlahKunjungan = KunjunganToko::where('sales_id', $sales->id)
            ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
            ->count();

        $sudahMasuk  = $presensi !== null;
        $sudahPulang = $presensi?->jam_pulang !== null;

        // ✅ Cek izin aktif hari ini
        $cekIzin   = $this->izinService->cekBolehAbsenMasuk($sales->id);
        $izinAktif = $this->izinService->getIzinAktif($sales->id);

        $bisaMasuk = !$sudahMasuk && $cekIzin['boleh'];

        return response()->json([
            'success' => true,
            'message' => $presensi ? 'Data ditemukan' : 'Belum presensi hari ini',
            'data'    => $presensi ? new PresensiResource($presensi) : null,
            'izin_aktif' => $izinAktif ? new IzinResource($izinAktif) : null,
            'progress' => [
                'sudah_masuk'       => $sudahMasuk,
                'sudah_pulang'      => $sudahPulang,
                'jumlah_kunjungan'  => $jumlahKunjungan,
                'minimal_kunjungan' => 3,
                'bisa_masuk'        => $bisaMasuk,
                'bisa_kunjungan'    => $sudahMasuk && !$sudahPulang,
                'bisa_pulang'       => $sudahMasuk && !$sudahPulang && $jumlahKunjungan >= 3,
                'sedang_izin'       => !$cekIzin['boleh'],
                'keterangan_izin'   => $cekIzin['alasan'],
                'jenis_izin_aktif'  => $izinAktif?->jenis_izin,
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // RIWAYAT
    // ═══════════════════════════════════════════════════════════

    public function riwayat(Request $request): JsonResponse
    {
        $sales = $request->user();
        $query = Presensi::where('sales_id', $sales->id)->orderBy('tanggal', 'desc');

        if ($request->tanggal_mulai) $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        if ($request->tanggal_akhir) $query->whereDate('tanggal', '<=', $request->tanggal_akhir);

        return response()->json([
            'success' => true,
            'data'    => PresensiResource::collection($query->get()),
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // NOTIFIKASI
    // ═══════════════════════════════════════════════════════════

    public function getNotifikasi(Request $request): JsonResponse
    {
        try {
            $sales = $request->user();
            $notif = DB::table('notifikasi_sales')
                ->where('sales_id', $sales->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $notif,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal muat data',
            ], 500);
        }
    }
}