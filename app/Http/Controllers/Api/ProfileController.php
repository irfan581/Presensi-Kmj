<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileRequest;
use App\Services\ProfileService;
use App\Http\Resources\Api\SalesResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    protected ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    // ─── GET PROFIL ───────────────────────────────────────────

    public function getProfile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new SalesResource($request->user()),
        ]);
    }

    // ─── UPDATE PROFIL ────────────────────────────────────────

    public function updateProfile(ProfileRequest $request): JsonResponse
    {
        try {
            $updatedUser = $this->profileService->update(
                $request->user(),
                $request->validated(),
                $request->file('foto_profil'),
            );

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data'    => new SalesResource($updatedUser),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update profil: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── GANTI PASSWORD ───────────────────────────────────────

    public function changePassword(ProfileRequest $request): JsonResponse
    {
        try {
            $this->profileService->updatePassword(
                $request->user(),
                $request->validated(),
            );

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ─── RESET PASSWORD ───────────────────────────────────────
    // Reset ke password default (misal NIK sales) — hanya admin atau sales sendiri

    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Reset ke NIK sebagai password default
            $passwordBaru = $this->profileService->resetPassword($user);

            return response()->json([
                'success'       => true,
                'message'       => 'Password berhasil direset',
                'password_baru' => $passwordBaru, // Tampilkan sekali ke user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset password: ' . $e->getMessage(),
            ], 500);
        }
    }
}