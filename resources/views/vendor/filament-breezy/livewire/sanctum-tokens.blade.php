<x-filament-breezy::grid-section md=2 title="Token API"
    description="Kelola token akses pribadi yang memungkinkan aplikasi pihak ketiga mengakses akun Anda secara aman.">

    <div class="space-y-4">
        @if($plainTextToken)
        <div
            class="space-y-3 bg-amber-50 dark:bg-amber-950 p-4 rounded-lg border border-amber-200 dark:border-amber-800">
            <p class="text-sm text-amber-800 dark:text-amber-200 font-medium">
                Harap salin token API baru Anda. Demi keamanan, token ini tidak akan ditampilkan lagi setelah Anda
                menutup halaman ini.
            </p>

            <input type="text" disabled
                class="w-full py-2 px-3 rounded-lg bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-sm font-mono shadow-sm"
                name="plain_text_token" value="{{$plainTextToken}}" />

            <div class="flex items-center justify-between">
                <div class="inline-block text-xs text-gray-500 font-medium">
                    <x-filament-breezy::clipboard-link :data="$plainTextToken" label="Salin ke papan klip" />
                </div>
                <x-filament::button size="sm" color="warning" type="button" wire:click="$set('plainTextToken',null)">
                    Selesai & Tutup
                </x-filament::button>
            </div>
        </div>
        @endif

        {{-- Menggunakan logic Blade daripada inline style CSS agar VS Code tidak error --}}
        @if(!$plainTextToken)
        <x-filament::card>
            {{ $this->table }}
        </x-filament::card>
        @endif
    </div>

</x-filament-breezy::grid-section>