<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class GantiPassword extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    
    // ✅ Variabel untuk menampung password sementara agar bisa ditampilkan
    public ?string $showPasswordTemporarily = null;

    // Syarat 1: Izin Tampil
    public static function canView(): bool
    {
        return true; 
    }

    // Syarat 2: Urutan Tampil
    public static function getSort(): int
    {
        return 10;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('current_password')
                    ->label('Kata Sandi Saat Ini')
                    ->password()
                    ->revealable() 
                    ->required()
                    ->currentPassword(),

                TextInput::make('new_password')
                    ->label('Kata Sandi Baru')
                    ->password()
                    ->revealable() 
                    ->required()
                    ->rule(Password::default()->mixedCase()->numbers()->uncompromised())
                    ->same('new_password_confirmation'),

                TextInput::make('new_password_confirmation')
                    ->label('Konfirmasi Kata Sandi Baru')
                    ->password()
                    ->revealable() 
                    ->required()
                    ->dehydrated(false),
            ])
            ->statePath('data');
    }

    // ✅ NAMA FUNGSI SUDAH DIPERBAIKI MENJADI 'submit'
    public function submit(): void
    {
        $state = $this->form->getState();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Simpan password teks asli ke variabel ini sebelum di-hash
        $this->showPasswordTemporarily = $state['new_password'];

        $user->update([
            'password' => Hash::make($state['new_password']),
        ]);

        // Kosongkan form setelah berhasil disimpan
        $this->form->fill();

        Notification::make()
            ->title('Password Berhasil Diperbarui')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('vendor.filament-breezy.livewire.update-password');
    }
}