<x-filament-breezy::grid-section md=2 title="Otentikasi Dua Faktor"
    description="Kelola keamanan tambahan untuk akun Anda menggunakan otentikasi dua langkah.">

    <x-filament::card>

        @if($this->showRequiresTwoFactorAlert())
        <div class="p-4 rounded bg-danger-500/10 border border-danger-500/50">
            <div class="flex">
                <div class="shrink-0"> {{-- ✅ Diubah dari flex-shrink-0 jadi shrink-0 --}}
                    @svg('heroicon-s-shield-exclamation', 'w-5 h-5 text-danger-600')
                </div>
                <div class="ml-3">
                    <p class="text-sm text-danger-600 font-medium">
                        Anda wajib mengaktifkan Otentikasi Dua Faktor untuk mengakses panel ini.
                    </p>
                </div>
            </div>
        </div>
        @endif

        @unless ($user->hasEnabledTwoFactor())
        <h3 class="flex items-center gap-2 text-lg font-medium">
            @svg('heroicon-o-exclamation-circle', 'w-6')
            Otentikasi Dua Faktor Belum Aktif
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Gunakan aplikasi otentikasi (seperti Google Authenticator)
            untuk memindai kode QR saat login
            guna meningkatkan keamanan akun Anda.</p>

        <div class="flex justify-between mt-3">
            {{ $this->enableAction->label('Aktifkan Sekarang') }}
        </div>

        @else
        @if ($user->hasConfirmedTwoFactor())
        <h3 class="flex items-center gap-2 text-lg font-medium text-success-600">
            @svg('heroicon-o-shield-check', 'w-6')
            Otentikasi Dua Faktor Telah Aktif
        </h3>
        <p class="text-sm">Akun Anda sekarang jauh lebih aman. Jangan lupa simpan kode pemulihan di bawah ini.</p>

        @if($showRecoveryCodes)
        <div class="px-4 space-y-3 mt-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Kode Pemulihan Cadangan</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($this->recoveryCodes->toArray() as $code )
                <span
                    class="inline-flex items-center p-2 text-xs font-mono font-medium text-gray-800 dark:text-gray-400 bg-gray-100 rounded-lg dark:bg-gray-900 justify-center border border-gray-200 dark:border-gray-800">
                    {{ $code }}
                </span>
                @endforeach
            </div>
            <div class="inline-block text-xs mt-2">
                <x-filament-breezy::clipboard-link :data="$this->recoveryCodes->join(',')"
                    label="Salin Semua Kode Pemulihan" />
            </div>
        </div>
        @endif

        <div class="flex justify-between mt-6 pt-4 border-t dark:border-gray-800">
            {{ $this->regenerateCodesAction->label('Buat Ulang Kode') }}
            {{ $this->disableAction()->color('danger')->label('Nonaktifkan 2FA') }}
        </div>
        @else
        <h3 class="flex items-center gap-2 text-lg font-medium text-amber-600">
            @svg('heroicon-o-question-mark-circle', 'w-6')
            Selesaikan Pengaktifan 2FA
        </h3>
        <p class="text-sm">Pindai kode QR di bawah ini dengan aplikasi otentikasi ponsel Anda, lalu masukkan kode
            konfirmasi yang muncul.</p>

        <div
            class="flex flex-col mt-4 space-y-4 md:flex-row md:space-x-6 md:space-y-0 md:divide-x dark:divide-gray-700">
            <div class="flex flex-col items-center shrink-0">
                <div class="p-2 bg-white rounded-lg shadow-sm border border-gray-200">
                    {!! $this->getTwoFactorQrCode() !!}
                </div>
                <p class="pt-3 text-[10px] font-mono text-gray-500 uppercase tracking-widest">KODE MANUAL</p>
                <p class="font-bold text-gray-700 dark:text-gray-300 tracking-wider">
                    {{ decrypt($this->user->two_factor_secret) }}
                </p>
            </div>
            <div class="px-4 space-y-3 flex-1">
                <p class="text-xs font-bold text-danger-600 uppercase">Penting: Simpan kode ini sebelum konfirmasi!</p>
                <div class="grid grid-cols-2 gap-2 text-center">
                    @foreach ($this->recoveryCodes->toArray() as $code )
                    <span
                        class="p-1 text-xs font-mono font-medium text-gray-800 dark:text-gray-400 bg-gray-100 rounded-lg dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                        {{ $code }}
                    </span>
                    @endforeach
                </div>
                <div class="inline-block text-xs mt-2">
                    <x-filament-breezy::clipboard-link :data="$this->recoveryCodes->join(',')"
                        label="Salin Kode Pemulihan" />
                </div>
            </div>
        </div>

        <div class="flex justify-between mt-6 pt-4 border-t dark:border-gray-800">
            {{ $this->confirmAction->label('Konfirmasi & Simpan') }}
            {{ $this->disableAction->label('Batalkan')->color('gray') }}
        </div>

        @endif

        @endunless
    </x-filament::card>
    <x-filament-actions::modals />
</x-filament-breezy::grid-section>