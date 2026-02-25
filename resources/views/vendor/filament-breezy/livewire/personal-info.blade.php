<div class="fi-breezy-personal-info flex flex-col gap-4">

    {{-- JUDUL DI ATAS CARD --}}
    <div class="flex items-center gap-2 px-1">
        <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-500/10">
            <x-filament::icon icon="heroicon-m-user-circle" class="h-6 w-6 text-amber-600 dark:text-amber-400" />
        </div>
        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
            Informasi Profil
        </h2>
    </div>

    {{-- CARD UTAMA --}}
    <x-filament::section shadow
        class="fi-section border-none ring-1 ring-gray-950/5 dark:ring-white/10 dark:bg-gray-900 w-full">

        {{-- ✅ UDAH DIGANTI JADI submit --}}
        <form wire:submit.prevent="submit" class="space-y-6">

            <div class="fi-form-inner-container">
                {{ $this->form }}
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-100 dark:border-white/5">
                {{-- ✅ UDAH DIGANTI JADI submit --}}
                <x-filament::button type="submit" size="md" icon="heroicon-m-check-badge" class="shadow-sm"
                    wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Simpan Perubahan</span>
                    <span wire:loading wire:target="submit">Menyimpan...</span>
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    <style>
    .fi-form-inner-container {
        display: grid;
        gap: 1.5rem;
    }

    .fi-section {
        border-radius: 1rem !important;
        transition: all 0.3s ease;
    }

    .dark .fi-section {
        background-color: #18181b !important;
    }

    .dark .fi-fo-field-wrp-label label {
        color: #e5e7eb !important;
    }

    .fi-fo-file-upload>div {
        border-radius: 9999px !important;
        max-width: 140px !important;
        margin: 0 auto 1rem auto !important;
        overflow: hidden !important;
        border: 2px dashed #e5e7eb !important;
        aspect-ratio: 1/1 !important;
        background-color: #fcfcfc;
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
    }

    .dark .fi-fo-file-upload>div {
        background-color: #18181b !important;
        border-color: #3f3f46 !important;
    }

    .fi-fo-file-upload-badge {
        font-size: 11px !important;
        font-weight: 500;
    }

    .fi-section:hover {
        box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.2) !important;
    }
    </style>
</div>