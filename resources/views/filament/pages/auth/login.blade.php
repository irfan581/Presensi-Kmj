<x-filament-panels::page.simple>
    @push('styles')
    <style>
    /* Definisi Variabel Warna */
    :root {
        /* Light Mode */
        --bg: #f4f6fb;
        --card: #ffffff;
        --bd: rgba(0, 0, 0, .08);
        --inp: #f9fafb;
        --txt: #111827;
        --mut: #6b7280;
        --acc: #ea580c;
    }

    /* Dark Mode (Otomatis saat class .dark aktif di HTML) */
    .dark {
        --bg: #09090b;
        --card: #18181b;
        --bd: rgba(255, 255, 255, 0.1);
        --inp: #27272a;
        --txt: #fafafa;
        --mut: #a1a1aa;
    }

    /* Layout Lock Center & No Scroll */
    body {
        background: var(--bg) !important;
        color: var(--txt) !important;
        height: 100vh !important;
        overflow: hidden !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        transition: background 0.3s ease;
        /* Transisi halus saat ganti mode */
    }

    .fi-simple-layout {
        width: 100% !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Ukuran Wide (650px) */
    .fi-simple-main-ctn {
        width: 100% !important;
        max-width: 650px !important;
        margin: 0 !important;
    }

    .fi-simple-main {
        background: var(--card) !important;
        border: 1px solid var(--bd) !important;
        border-radius: 16px !important;
        padding: 2.5rem 3.5rem !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2) !important;
        transition: background 0.3s ease, border 0.3s ease;
    }

    /* Branding */
    .kj-brand {
        text-align: center;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--bd);
        padding-bottom: 1.2rem;
    }

    .kj-brand-name {
        font-weight: 800;
        font-size: 1.6rem;
        color: var(--txt);
    }

    .kj-brand-name em {
        color: var(--acc);
        font-style: normal;
    }

    .kj-brand-sub {
        font-size: 0.75rem;
        color: var(--mut);
        letter-spacing: 0.2em;
        text-transform: uppercase;
    }

    /* Input Styling */
    .fi-fo-field-wrp label {
        color: var(--txt) !important;
        font-weight: 500 !important;
    }

    .fi-input-wrp {
        background-color: var(--inp) !important;
        border: 1px solid var(--bd) !important;
        height: 44px !important;
        border-radius: 10px !important;
    }

    /* Button */
    .fi-btn-primary,
    button[type="submit"] {
        background-color: var(--acc) !important;
        height: 44px !important;
        border-radius: 10px !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3) !important;
    }

    /* Footer */
    .kj-footer {
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid var(--bd);
        font-size: 0.75rem;
        color: var(--mut);
        display: flex;
        justify-content: space-between;
    }

    .fi-simple-header,
    .fi-simple-header-heading {
        display: none !important;
    }
    </style>
    @endpush

    <div class="kj-brand">
        <div class="kj-brand-name">Kembar <em>Jaya</em></div>
        <div class="kj-brand-sub">Sales Management System</div>
    </div>

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}
        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="true" />
    </x-filament-panels::form>

    <div class="kj-footer">
        <span>&copy; {{ date('Y') }} Kembar Jaya</span>
        <span>v1.0.0</span>
    </div>
</x-filament-panels::page.simple>