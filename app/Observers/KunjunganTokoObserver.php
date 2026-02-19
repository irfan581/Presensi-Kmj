<?php

namespace App\Observers;

use App\Models\KunjunganToko;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class KunjunganTokoObserver implements ShouldQueue
{
    public string $queue = 'notifications';

    public function created(KunjunganToko $kunjungan): void
    {
        $kunjungan->loadMissing('sales');

        $admins = User::query()
            ->where(fn($q) => $q
                ->where('is_admin', true)
                ->orWhere('role', 'admin')
            )
            ->select(['id'])
            ->get();

        if ($admins->isEmpty()) return;

        \Filament\Notifications\Notification::make()
            ->title('Kunjungan Toko Baru')
            ->body("**{$kunjungan->sales->nama}** di **{$kunjungan->nama_toko}**.")
            ->info()
            ->icon('heroicon-o-map-pin')
            ->sendToDatabase($admins);
    }

    public function updated(KunjunganToko $kunjungan): void {}
    public function deleted(KunjunganToko $kunjungan): void {}
    public function restored(KunjunganToko $kunjungan): void {}
    public function forceDeleted(KunjunganToko $kunjungan): void {}
}