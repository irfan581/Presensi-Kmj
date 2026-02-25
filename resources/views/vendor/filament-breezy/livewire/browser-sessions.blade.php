<x-filament-breezy::grid-section md=2 title="Sesi Browser"
    description="Lihat dan kelola sesi aktif Anda di browser dan perangkat lain.">

    <x-filament::card>
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Jika perlu, Anda dapat keluar dari semua sesi browser Anda di semua perangkat. Beberapa sesi terbaru Anda
            tercantum di bawah ini; namun, daftar ini mungkin tidak lengkap. Jika Anda merasa akun Anda telah disusupi,
            Anda juga harus memperbarui kata sandi Anda.
        </div>

        <x-filament-panels::form>
            {{ $this->form }}
        </x-filament-panels::form>

        <x-filament-actions::modals />
    </x-filament::card>
</x-filament-breezy::grid-section>