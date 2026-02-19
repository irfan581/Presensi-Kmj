<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;

    /**
     * Redirect setelah berhasil membuat data
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Manipulasi data sebelum disimpan ke database
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Contoh: Jika admin tidak mengisi password, berikan password default 'sales123'
        if (empty($data['password'])) {
            $data['password'] = Hash::make('sales123');
        }

        // Contoh: Memastikan NIK selalu huruf kapital
        $data['nik'] = strtoupper($data['nik']);

        return $data;
    }

    /**
     * Memberikan pesan notifikasi kustom saat berhasil
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Akun Sales Berhasil Dibuat!';
    }
}