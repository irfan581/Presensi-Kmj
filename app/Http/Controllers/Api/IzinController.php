<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IzinService;
use App\Http\Requests\Api\IzinRequest;
use App\Http\Resources\Api\IzinResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IzinController extends Controller
{
    protected $izinService;

    public function __construct(IzinService $izinService)
    {
        $this->izinService = $izinService;
    }

    /**
     * Riwayat izin dengan filter
     */
    public function riwayat(Request $request): JsonResponse
    {
        try {
            $salesId = Auth::id();

            if (!$salesId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi anda telah berakhir, silakan login kembali',
                ], 401);
            }

            $riwayat = $this->izinService->getRiwayatIzin(
                (int) $salesId,
                $request->query('status'),
                $request->query('start_date'),
                $request->query('end_date'),
            );

            return response()->json([
                'success' => true,
                'message' => 'Data riwayat berhasil diambil',
                'data'    => IzinResource::collection($riwayat),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ajukan izin baru
     */
    public function ajukanIzin(IzinRequest $request): JsonResponse
    {
        try {
            // 1. Ambil data yang sudah lolos validasi
            $validatedData = $request->validated();
            
            // 2. TAMBAHAN PENTING: Sisipkan sales_id dari token Auth
            $validatedData['sales_id'] = Auth::id();

            // 3. Lempar ke service
            $izin = $this->izinService->ajukanIzin(
                $validatedData,
                $request->file('bukti_foto')
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan izin berhasil dikirim',
                'data'    => new IzinResource($izin),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Cek izin aktif hari ini
     * Dipakai Flutter untuk update UI tombol absen
     */
    public function cekIzinHariIni(Request $request): JsonResponse
    {
        try {
            $salesId  = Auth::id();
            $izinAktif = $this->izinService->getIzinAktif($salesId);
            $cekBoleh  = $this->izinService->cekBolehAbsenMasuk($salesId);

            return response()->json([
                'success'    => true,
                'izin_aktif' => $izinAktif ? new IzinResource($izinAktif) : null,
                'boleh_absen' => $cekBoleh['boleh'],
                'keterangan'  => $cekBoleh['alasan'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        return $this->riwayat($request);
    }

    public function store(IzinRequest $request): JsonResponse
    {
        return $this->ajukanIzin($request);
    }
}