<x-filament-panels::page>
    {{-- Menggunakan gap-y-8 agar jarak antar card (Profil, Password, 2FA) lebih rapi dan lega --}}
    <div class="flex flex-col gap-y-8">
        @foreach ($this->getRegisteredMyProfileComponents() as $component)
        @if($component)
        {{-- ✅ WAJIB ADA key($component) AGAR LIVEWIRE TIDAK BINGUNG/ERROR --}}
        @livewire($component, key($component))
        @endif
        @endforeach
    </div>
</x-filament-panels::page>