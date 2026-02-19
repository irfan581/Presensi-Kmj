<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class EditSales extends EditRecord
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Tombol Reset Device ID
            Actions\Action::make('resetDevice')
                ->label('Reset Device ID')
                ->color('warning')
                ->icon('heroicon-o-device-phone-mobile')
                ->requiresConfirmation()
                ->modalHeading('Reset Device ID Sales?')
                ->modalDescription('Setelah direset, sales bisa login kembali menggunakan perangkat baru.')
                ->action(function () {
                    $this->record->update(['device_id' => null]);

                    Notification::make()
                        ->title('Device ID berhasil direset')
                        ->success()
                        ->send();
                }),

            // 2. Tombol Reset Password ke Default
            Actions\Action::make('resetPassword')
                ->label('Reset Password')
                ->color('danger')
                ->icon('heroicon-o-key')
                ->requiresConfirmation()
                ->modalHeading('Reset Password ke Default?')
                ->modalDescription('Password akan diubah menjadi "sales123". Pastikan beritahu sales setelah ini.')
                ->action(function () {
                    $this->record->update([
                        'password' => Hash::make('sales123')
                    ]);

                    Notification::make()
                        ->title('Password berhasil direset menjadi: sales123')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}