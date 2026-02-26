<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotifikasiSales;
use App\Http\Resources\Api\NotificationResource; 
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Ambil daftar notifikasi untuk sales yang login
     */
    public function index(): JsonResponse
    {
        try {
            $salesId = (int) Auth::id(); 

            $notifications = NotifikasiSales::where('sales_id', $salesId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar notifikasi berhasil diambil',
                'data'    => NotificationResource::collection($notifications) 
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => [] 
            ], 500);
        }
    }

    /**
     * Tandai satu notif sebagai sudah dibaca
     */
    public function markAsRead($id): JsonResponse
    {
        try {
            $currentSalesId = (int) Auth::id();

            $notification = NotifikasiSales::where('id', $id)
                ->where('sales_id', $currentSalesId)
                ->first();

            if ($notification) {
                $notification->update(['is_read' => true]);
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Notifikasi berhasil ditandai sudah dibaca'
                ], 200);
            }

            return response()->json([
                'success' => false, 
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal update status'
            ], 500);
        }
    }

    /**
     * Tandai SEMUA notif sebagai sudah dibaca
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $currentSalesId = (int) Auth::id();

            $updatedCount = NotifikasiSales::where('sales_id', $currentSalesId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => $updatedCount . ' notifikasi berhasil ditandai sudah dibaca'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menandai semua'
            ], 500);
        }
    }

    /**
     * Hapus satu notifikasi tertentu (Baru)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $currentSalesId = (int) Auth::id();

            $notification = NotifikasiSales::where('id', $id)
                ->where('sales_id', $currentSalesId)
                ->first();

            if ($notification) {
                $notification->delete();
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Notifikasi berhasil dihapus'
                ], 200);
            }

            return response()->json([
                'success' => false, 
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus SEMUA notifikasi (Baru)
     */
    public function deleteAll(): JsonResponse
    {
        try {
            $currentSalesId = (int) Auth::id();

            $deletedCount = NotifikasiSales::where('sales_id', $currentSalesId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi (' . $deletedCount . ') berhasil dibersihkan'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal membersihkan notifikasi'
            ], 500);
        }
    }

    /**
     * Ambil jumlah belum dibaca
     */
    public function getUnreadCount(): JsonResponse
    {
        try {
            $count = NotifikasiSales::where('sales_id', (int) Auth::id())
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * Update Token FCM
     */
    // Bagian updateToken tetap sama, tapi pastikan kolom di tabel users adalah 'fcm_token'
    public function updateToken(Request $request): JsonResponse
        {
            try {
                $request->validate(['fcm_token' => 'required|string']);
        
                $user = Auth::user(); 
                    $user->update(['fcm_token' => $request->fcm_token]);

                return response()->json([
                    'success' => true,
                        'message' => 'FCM Token berhasil diperbarui'
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                        'message' => 'Gagal memperbarui token: ' . $e->getMessage()
            ], 500);
        }
    }
}