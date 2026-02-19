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

    /**
     * Dependency Injection untuk IzinService.
     */
    public function __construct(IzinService $izinService)
    {
        $this->izinService = $izinService;
    }

    /**
     * Mengambil riwayat izin sales yang sedang login.
     * Mendukung filter status, start_date, dan end_date.
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

            // Menggunakan query params untuk filter di Flutter
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
     * Memproses pengajuan izin baru.
     * Validasi dilakukan otomatis oleh IzinRequest.
     */
    public function ajukanIzin(IzinRequest $request): JsonResponse
    {
        try {
            // Service akan menangani penyimpanan dan dispatch Job foto
            $izin = $this->izinService->ajukanIzin(
                $request->validated(),
                $request->file('bukti_foto'),
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
     * Boilerplate untuk standard API Resource (Index & Store)
     */
    public function index(Request $request): JsonResponse
    {
        return $this->riwayat($request);
    }

    public function store(IzinRequest $request): JsonResponse
    {
        return $this->ajukanIzin($request);
    }
}