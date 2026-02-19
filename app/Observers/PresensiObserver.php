<?php

namespace App\Observers;

use App\Models\Presensi;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class PresensiObserver implements ShouldQueue
{
    public string $queue = 'notifications';

    public function created(Presensi $presensi): void
    {
        $presensi->loadMissing('sales');
        $admins = User::where('is_admin', true)->orWhere('role', 'admin')->get();

        Notification::make()
            ->title('Sales Masuk')
            ->body("**{$presensi->sales->nama}** baru saja absen masuk.")
            ->success()
            ->sendToDatabase($admins);
    }

    public function updated(Presensi $presensi): void
    {
        if ($presensi->wasChanged('jam_pulang') && !empty($presensi->jam_pulang)) {
            $presensi->loadMissing('sales');
            $admins = User::where('is_admin', true)->orWhere('role', 'admin')->get();

            Notification::make()
                ->title('Sales Pulang')
                ->body("**{$presensi->sales->nama}** sudah absen pulang.")
                ->info()
                ->sendToDatabase($admins);
        }
    }

    public function deleted(Presensi $presensi): void
    {
        $files = array_filter([$presensi->foto_masuk, $presensi->foto_pulang]);
        if (!empty($files)) Storage::disk('public')->delete($files);
    }
}