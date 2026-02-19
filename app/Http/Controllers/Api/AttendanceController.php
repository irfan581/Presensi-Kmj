<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AbsenRequest;
use App\Services\AttendanceService;
use App\Models\Presensi;
use App\Models\KunjunganToko;
use App\Models\Sales;
use App\Http\Resources\Api\SalesResource;
use App\Http\Resources\Api\PresensiResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected AttendanceService $service;

    public function __construct(AttendanceService $service)
    {
        $this->service = $service;
    }
    // --- LOGIN ---
    // --- LOGIN ---
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
        return response()->json(['success' => false, 'message' => 'NIK atau Password salah'], 401);
    }

    // UPDATE DATA WAJIB ✅
    $sales->device_id = $request->device_id;
    if ($request->fcm_token) {
        $sales->fcm_token = $request->fcm_token;
    }
    $sales->save(); // Pastikan tersimpan ke DB

    $token = $sales->createToken('sales_auth_token')->plainTextToken;

    return response()->json([
        'success'      => true,
        'message'      => 'Login Berhasil',
        'access_token' => $token,
        'user'         => new SalesResource($sales->refresh()),
    ]);
}
    // --- LOGOUT ---
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['success' => true, 'message' => 'Logout berhasil']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal logout'], 500);
        }
    }

    // --- ABSEN MASUK ---
    public function absenMasuk(AbsenRequest $request): JsonResponse
    {
        try {
            $sales = $request->user();
            
            $data = [
                'location_masuk'      => $request->latitude . ',' . $request->longitude,
                'jam_perangkat_masuk' => $request->jam_perangkat ?? now()->format('H:i:s'),
                'keterangan'          => $request->keterangan ?? null,
            ];

            $presensi = $this->service->handleMasuk(
                $sales->id,
                $data,
                $request->file('foto_masuk')
            );

            return response()->json([
                'success' => true,
                'message' => 'Absen masuk berhasil',
                'data'    => new PresensiResource($presensi),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- ABSEN PULANG ---
    public function absenPulang(AbsenRequest $request): JsonResponse
    {
        try {
            $sales = $request->user();
            $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $presensi = Presensi::where('sales_id', $sales->id)->where('tanggal', $today)->first();

            if (!$presensi) {
                return response()->json(['success' => false, 'message' => 'Anda belum absen masuk'], 400);
            }

            $data = [
                'location_pulang'      => $request->latitude . ',' . $request->longitude,
                'jam_perangkat_pulang' => $request->jam_perangkat ?? now()->format('H:i:s'),
            ];

            $updated = $this->service->handlePulang(
                $presensi,
                $data,
                $request->file('foto_pulang')
            );

            return response()->json([
                'success' => true,
                'message' => 'Absen pulang berhasil',
                'data'    => new PresensiResource($updated),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- STATUS PROGRES HARI INI ---
    public function presensiHariIni(Request $request): JsonResponse
    {
        $sales = $request->user();
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        
        $presensi = Presensi::where('sales_id', $sales->id)->where('tanggal', $today)->first();

        $jumlahKunjungan = KunjunganToko::where('sales_id', $sales->id)
            ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
            ->count();

        $sudahMasuk  = $presensi !== null;
        $sudahPulang = $presensi?->jam_pulang !== null;

        // Logic buat kunci/buka tombol di Flutter
        return response()->json([
            'success' => true,
            'message' => $presensi ? 'Data ditemukan' : 'Belum presensi hari ini',
            'data'    => $presensi ? new PresensiResource($presensi) : null,
            'progress' => [
                'sudah_masuk'       => $sudahMasuk,
                'sudah_pulang'      => $sudahPulang,
                'jumlah_kunjungan'  => $jumlahKunjungan,
                'minimal_kunjungan' => 3,
                'bisa_kunjungan'    => ($sudahMasuk && !$sudahPulang), // Tombol kunjungan nyala kalo udah absen masuk
                'bisa_pulang'       => ($sudahMasuk && !$sudahPulang && $jumlahKunjungan >= 3), // Tombol pulang nyala kalo kunjungan >= 3
            ],
        ]);
    }

    // --- RIWAYAT ---
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

    public function getNotifikasi(Request $request): JsonResponse
{
    try {
        $sales = $request->user();
        // Mengambil data dari tabel notifications (asumsi Bos pakai tabel ini)
        $notif = \DB::table('notifications')
            ->where('notifiable_id', $sales->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $notif
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Gagal muat data'], 500);
    }
}
}