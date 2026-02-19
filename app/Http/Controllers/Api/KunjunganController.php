<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\KunjunganService;
use App\Http\Requests\Api\KunjunganRequest;
use App\Http\Resources\Api\KunjunganResource;
use App\Models\KunjunganToko;
use App\Models\Presensi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KunjunganController extends Controller
{
    protected KunjunganService $kunjunganService;

    public function __construct(KunjunganService $kunjunganService)
    {
        $this->kunjunganService = $kunjunganService;
    }

    // ─── RIWAYAT ──────────────────────────────────────────────

    public function history(Request $request): JsonResponse
    {
        try {
            $salesId = Auth::id();

            if (!$salesId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi anda telah berakhir, silakan login kembali',
                ], 401);
            }

            $data = $this->kunjunganService->getRiwayat(
                (int) $salesId,
                $request->query('tanggal_mulai'),
                $request->query('tanggal_akhir'),
            );

            return response()->json([
                'success' => true,
                'message' => 'Data riwayat berhasil diambil',
                'data'    => KunjunganResource::collection($data),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── STORE ────────────────────────────────────────────────

    public function store(KunjunganRequest $request): JsonResponse
    {
        try {
            $salesId = (int) Auth::id();

            if (!$salesId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

            // ✅ Proteksi: wajib absen masuk dulu
            $sudahMasuk = Presensi::where('sales_id', $salesId)
                ->where('tanggal', $today)
                ->exists();

            if (!$sudahMasuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus absen masuk terlebih dahulu sebelum melakukan kunjungan.',
                ], 400);
            }

            // ✅ Proteksi: tidak bisa kunjungan setelah pulang
            $sudahPulang = Presensi::where('sales_id', $salesId)
                ->where('tanggal', $today)
                ->whereNotNull('jam_pulang')
                ->exists();

            if ($sudahPulang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah absen pulang. Kunjungan tidak dapat ditambah.',
                ], 400);
            }

            // Proteksi double submit
            $isDuplicate = KunjunganToko::where('sales_id', $salesId)
                ->where('nama_toko', $request->nama_toko)
                ->where('created_at', '>=', now()->subMinute())
                ->exists();

            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan kunjungan serupa sedang diproses, mohon tunggu.',
                ], 422);
            }

            $kunjungan = $this->kunjunganService->storeKunjungan(
                $salesId,
                $request->validated(),
                $request->file('foto_kunjungan'),
            );

            // Hitung total kunjungan hari ini setelah simpan
            $totalKunjungan = KunjunganToko::where('sales_id', $salesId)
                ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Kunjungan toko berhasil disimpan',
                'data'    => new KunjunganResource($kunjungan),
                // ✅ Info progress kunjungan untuk Flutter update UI langsung
                'progress' => [
                    'jumlah_kunjungan'  => $totalKunjungan,
                    'minimal_kunjungan' => 3,
                    'bisa_pulang'       => $totalKunjungan >= 3,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }
}