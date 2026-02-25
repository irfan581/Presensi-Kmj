<?php

namespace App\Livewire;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Livewire\WithFileUploads; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification; // Tambahan agar lebih rapi

class MyPersonalInfo extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public ?array $data = [];

    public function mount(): void
    {
        // Mengisi form dengan data user yang sedang login
        $this->form->fill(Auth::user()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Logic untuk Foto Profil
                FileUpload::make('avatar_url')
                    ->label('Foto Profil')
                    ->image()
                    ->avatar() // Membuat tampilan bulat di preview
                    ->directory('avatars') // Folder penyimpanan di storage/app/public/avatars
                    ->imageEditor()
                    ->circleAppearance(),

                // Logic untuk Nama
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                // Logic untuk Email
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
            ])
            ->statePath('data');
    }

    // ✅ FIX: Ubah nama fungsi dari 'save' menjadi 'submit'
    public function submit(): void
    {
        try {
            $state = $this->form->getState();
            
            // Eksekusi update ke database
            Auth::user()->update($state);

            // Munculkan notifikasi ala Filament
            Notification::make()
                ->title('Profil Berhasil Diperbarui')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Memperbarui Profil')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        // Pastikan view-nya mengarah ke file blade Abang yang tadi kita update
        return view('vendor.filament-breezy.livewire.personal-info');
    }
}