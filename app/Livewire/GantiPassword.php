<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Models\User;

class GantiPassword extends Component  // ✅ class name sesuai filename
{
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password'     => ['required', 'min:8', 'confirmed'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            $user->update([
                'password' => Hash::make($this->new_password),
            ]);

            $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

            Notification::make()
                ->title('Password Berhasil Diperbarui')
                ->success()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.ganti-password');
    }
}