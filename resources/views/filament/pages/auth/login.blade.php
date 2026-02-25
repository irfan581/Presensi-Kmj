<x-filament-panels::page.simple>
    @push('styles')
    <style>
    /* KONFIGURASI LIGHT MODE (Default) */
    :root {
        --bg: #f3f4f6;
        --card: #ffffff;
        --bd: rgba(0, 0, 0, 0.08);
        --inp: #ffffff;
        --txt: #111827;
        --mut: #6b7280;
        --acc: #ea580c;
        --shadow: rgba(0, 0, 0, 0.1);
    }

    /* KONFIGURASI DARK MODE */
    .dark {
        --bg: #09090b;
        --card: #18181b;
        --bd: rgba(255, 255, 255, 0.1);
        --inp: #27272a;
        --txt: #fafafa;
        --mut: #a1a1aa;
        --acc: #f97316;
        /* Sedikit lebih terang di dark mode agar kontras */
        --shadow: rgba(0, 0, 0, 0.5);
    }

    body {
        background: var(--bg) !important;
        color: var(--txt) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 100vh !important;
        margin: 0 !important;
        transition: background 0.3s ease, color 0.3s ease;
    }

    /* Box Login */
    .fi-simple-main {
        background: var(--card) !important;
        border: 1px solid var(--bd) !important;
        border-radius: 20px !important;
        padding: 2.5rem !important;
        box-shadow: 0 25px 50px -12px var(--shadow) !important;
        transition: all 0.3s ease;
    }

    .fi-simple-main-ctn {
        max-width: 450px !important;
    }

    /* Branding */
    .kj-brand {
        text-align: center;
        margin-bottom: 2rem;
    }

    .kj-brand-name {
        font-weight: 700;
        font-size: 1.25rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--txt);
    }

    .kj-brand-name em {
        color: var(--acc);
        font-style: normal;
    }

    .kj-brand-sub {
        font-size: 0.7rem;
        color: var(--mut);
        letter-spacing: 0.2em;
        margin-top: 0.4rem;
        font-weight: 500;
    }

    /* Input Styling */
    .fi-input-wrp {
        background-color: var(--inp) !important;
        border: 1px solid var(--bd) !important;
        border-radius: 12px !important;
        transition: border-color 0.2s;
    }

    /* Menyesuaikan warna label Filament agar terlihat di kedua mode */
    .fi-fo-field-wrp-label label {
        color: var(--txt) !important;
    }

    /* Button Styling */
    button[type="submit"] {
        background-color: var(--acc) !important;
        border-radius: 12px !important;
        height: 48px !important;
        font-weight: 700 !important;
        color: white !important;
        box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.3) !important;
    }

    button[type="submit"]:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    /* Sembunyikan Header Default */
    .fi-simple-header,
    .fi-simple-header-heading {
        display: none !important;
    }
    </style>
    @endpush

    <div class="kj-brand">
        <img src="{{ asset('images/login.png') }}" style="height: 4.5rem; margin: 0 auto 1.2rem; display: block;"
            alt="Logo KMJ">

        <div class="kj-brand-name">Admin Sales <em>KMJ</em></div>
        <div class="kj-brand-sub">MANAGEMENT SYSTEM</div>
    </div>

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="true" />
        </div>
    </x-filament-panels::form>
</x-filament-panels::page.simple>