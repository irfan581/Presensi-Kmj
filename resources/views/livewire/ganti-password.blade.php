<x-filament-panels::form wire:submit="updatePassword">
    <x-filament::section>
        <x-slot name="heading">
            Perbarui Password
        </x-slot>

        <x-slot name="description">
            Pastikan akun Anda menggunakan password yang panjang dan acak untuk menjaga keamanan.
        </x-slot>

        <div class="grid grid-cols-1 gap-6">
            {{-- Password Sekarang --}}
            <div>
                <label class="text-sm font-medium text-gray-950 dark:text-white">Password Sekarang</label>
                <x-filament::input.wrapper :valid="!$errors->has('current_password')">
                    <x-filament::input type="password" viewable {{-- Menambahkan ikon mata --}}
                        wire:model="current_password" required />
                </x-filament::input.wrapper>
                <x-input-error for="current_password" class="mt-2 text-danger-600" />
            </div>

            {{-- Password Baru --}}
            <div>
                <label class="text-sm font-medium text-gray-950 dark:text-white">Password Baru</label>
                <x-filament::input.wrapper :valid="!$errors->has('new_password')">
                    <x-filament::input type="password" viewable {{-- Menambahkan ikon mata --}}
                        wire:model="new_password" required />
                </x-filament::input.wrapper>
                <x-input-error for="new_password" class="mt-2 text-danger-600" />
            </div>

            {{-- Konfirmasi Password --}}
            <div>
                <label class="text-sm font-medium text-gray-950 dark:text-white">Konfirmasi Password Baru</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="password" viewable {{-- Menambahkan ikon mata --}}
                        wire:model="new_password_confirmation" required />
                </x-filament::input.wrapper>
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button type="submit" color="primary">
                Simpan Perubahan
            </x-filament::button>
        </x-slot>
    </x-filament::section>
</x-filament-panels::form>