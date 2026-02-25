<div class="fi-breezy-update-password flex flex-col gap-4 mt-8">

    {{-- JUDUL DI ATAS CARD --}}
    <div class="flex items-center gap-3 px-1">
        <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-500/10">
            <x-filament::icon icon="heroicon-m-lock-closed" class="h-6 w-6 text-amber-600 dark:text-amber-400" />
        </div>
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                Ganti Kata Sandi
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Pastikan kata sandi Anda kuat dan aman agar akun tetap terlindungi.
            </p>
        </div>
    </div>

    {{-- CARD UTAMA --}}
    <x-filament::section shadow
        class="fi-section border-none ring-1 ring-gray-950/5 dark:ring-white/10 dark:bg-gray-900 w-full">
        <form wire:submit.prevent="submit" class="space-y-6">

            {{-- Form Filament Dibatasi Lebarnya pakai max-w-2xl --}}
            <div class="fi-form-container grid gap-6 max-w-2xl">
                {{ $this->form }}
            </div>

            {{-- ✅ NOTIFIKASI PASSWORD BARU (Muncul 5 Detik) --}}
            @if($showPasswordTemporarily)
            <div x-data="{ show: true }"
                x-init="setTimeout(() => { show = false; $wire.set('showPasswordTemporarily', null) }, 5000)"
                x-show="show" x-transition.duration.500ms
                class="max-w-2xl p-4 mt-6 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-green-500/10 dark:text-green-400 ring-1 ring-green-400/30">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-m-check-circle"
                        class="h-5 w-5 text-green-600 dark:text-green-400" />
                    <p>
                        <strong>Sukses Disimpan!</strong> Password baru Anda:
                        <span
                            class="font-mono bg-white dark:bg-gray-800 px-2 py-1 rounded border dark:border-gray-700 ml-1 text-gray-900 dark:text-gray-100 select-all tracking-wide">
                            {{ $showPasswordTemporarily }}
                        </span>
                    </p>
                </div>
                <p class="mt-1 text-xs text-green-600/80 dark:text-green-400/80 ml-7">
                    Pesan ini akan hilang otomatis dalam 5 detik...
                </p>
            </div>
            @endif

            <div
                class="flex flex-wrap items-center gap-3 pt-6 mt-6 border-t border-gray-100 dark:border-white/5 max-w-2xl">
                {{-- Tombol Simpan --}}
                <x-filament::button type="submit" size="md" wire:loading.attr="disabled" class="shadow-sm">
                    <span wire:loading.remove>Simpan Perubahan</span>
                    <span wire:loading>Memproses...</span>
                </x-filament::button>

                {{-- Tombol Generate Password --}}
                <x-filament::button color="warning" icon="heroicon-m-key" size="md" outlined
                    class="dark:hover:bg-warning-500/10" x-on:click="
                        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
                        let pass = '';
                        for (let i = 0; i < 16; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
                        
                        const inputs = $el.closest('form').querySelectorAll('input[type=password], input[type=text]');
                        
                        if (inputs.length >= 3) {
                            [1, 2].forEach(index => {
                                inputs[index].value = pass;
                                inputs[index].type = 'text';
                                inputs[index].dispatchEvent(new Event('input'));
                            });
                            
                            navigator.clipboard.writeText(pass);
                            
                            new FilamentNotification()
                                .title('Password Kuat Berhasil Dibuat & Disalin!')
                                .success()
                                .send();
                        }
                    ">
                    Generate Password
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    <style>
    /* Styling Card Dark Mode */
    .fi-section {
        transition: all 0.3s ease;
        border-radius: 1rem !important;
    }

    .dark .fi-section {
        background-color: #18181b !important;
    }

    /* Memberi warna pada label input di dark mode */
    .dark .fi-fo-field-wrp-label label {
        color: #e5e7eb !important;
    }
    </style>
</div>