<?php

return [
    'password_confirm' => [
        'heading' => 'Konfirmasi kata sandi',
        'description' => 'Silakan konfirmasi kata sandi Anda untuk menyelesaikan tindakan ini.',
        'current_password' => 'Kata sandi saat ini',
    ],
    'two_factor' => [
        'heading' => 'Tantangan Dua Faktor',
        'description' => 'Silakan konfirmasi akses ke akun Anda dengan memasukkan kode yang diberikan oleh aplikasi otentikator Anda.',
        'code_placeholder' => 'XXX-XXX',
        'recovery' => [
            'heading' => 'Tantangan Dua Faktor',
            'description' => 'Silakan konfirmasi akses ke akun Anda dengan memasukkan salah satu kode pemulihan darurat Anda.',
        ],
        'recovery_code_placeholder' => 'abcdef-98765',
        'recovery_code_text' => 'Kehilangan perangkat?',
        'recovery_code_link' => 'Gunakan kode pemulihan',
        'back_to_login_link' => 'Kembali ke halaman masuk',
    ],
    'profile' => [
        'account' => 'Akun',
        'profile' => 'Profil',
        'my_profile' => 'Profil Saya',
        'subheading' => 'Kelola profil pengguna Anda di sini.',
        'personal_info' => [
            'heading' => 'Informasi Pribadi',
            'subheading' => 'Kelola informasi pribadi dasar Anda.',
            'submit' => [
                'label' => 'Perbarui',
            ],
            'notify' => 'Profil berhasil diperbarui!',
        ],
        'password' => [
            'heading' => 'Kata Sandi',
            'subheading' => 'Pastikan kata sandi Anda minimal 8 karakter.',
            'submit' => [
                'label' => 'Perbarui',
            ],
            'notify' => 'Kata sandi berhasil diperbarui!',
        ],
        '2fa' => [
            'title' => 'Otentikasi Dua Faktor (2FA)',
            'description' => 'Kelola otentikasi dua faktor untuk akun Anda (sangat disarankan).',
            'actions' => [
                'enable' => 'Aktifkan',
                'regenerate_codes' => 'Buat Ulang Kode Pemulihan',
                'disable' => 'Nonaktifkan',
                'confirm_finish' => 'Konfirmasi & Selesai',
                'cancel_setup' => 'Batal',
            ],
            'setup_key' => 'Kunci Pengaturan',
            'must_enable' => 'Anda harus mengaktifkan Otentikasi Dua Faktor untuk menggunakan aplikasi ini.',
            'not_enabled' => [
                'title' => 'Anda belum mengaktifkan otentikasi dua faktor.',
                'description' => 'Saat otentikasi dua faktor diaktifkan, Anda akan diminta memasukkan token acak yang aman saat proses login. Anda dapat menggunakan aplikasi seperti Google Authenticator atau Microsoft Authenticator di ponsel Anda untuk menghasilkan token tersebut.',
            ],
            'finish_enabling' => [
                'title' => 'Selesaikan pengaturan otentikasi dua faktor.',
                'description' => "Untuk menyelesaikan pengaturan, pindai kode QR berikut menggunakan aplikasi otentikator di ponsel Anda, atau masukkan kunci pengaturan secara manual beserta kode OTP yang dihasilkan.",
            ],
            'enabled' => [
                'notify' => 'Otentikasi dua faktor telah diaktifkan.',
                'title' => 'Anda telah mengaktifkan otentikasi dua faktor!',
                'description' => 'Otentikasi dua faktor sekarang aktif. Ini akan membuat akun Anda jauh lebih aman.',
                'store_codes' => 'Kode-kode ini dapat digunakan untuk memulihkan akses ke akun Anda jika perangkat Anda hilang. Peringatan! Kode ini hanya akan ditampilkan satu kali. Simpan di tempat yang aman.',
            ],
            'disabling' => [
                'notify' => 'Otentikasi dua faktor telah dinonaktifkan.',
            ],
            'regenerate_codes' => [
                'notify' => 'Kode pemulihan baru berhasil dibuat.',
            ],
            'confirmation' => [
                'success_notification' => 'Kode terverifikasi. Otentikasi dua faktor diaktifkan.',
                'invalid_code' => 'Kode yang Anda masukkan tidak valid.',
            ],
        ],
        'sanctum' => [
            'title' => 'Token API',
            'description' => 'Kelola token API yang mengizinkan layanan pihak ketiga untuk mengakses aplikasi ini atas nama Anda.',
            'create' => [
                'notify' => 'Token berhasil dibuat!',
                'message' => 'Token Anda hanya akan ditampilkan satu kali setelah dibuat. Jika Anda kehilangan token ini, Anda harus menghapusnya dan membuat yang baru.',
                'submit' => [
                    'label' => 'Buat Token',
                ],
            ],
            'update' => [
                'notify' => 'Token berhasil diperbarui!',
                'submit' => [
                    'label' => 'Perbarui',
                ],
            ],
            'copied' => [
                'label' => 'Saya telah menyalin token',
            ],
        ],
        'browser_sessions' => [
            'heading' => 'Sesi Browser',
            'subheading' => 'Kelola sesi aktif Anda di berbagai perangkat.',
            'label' => 'Sesi Browser',
            'content' => 'Jika perlu, Anda dapat keluar (log out) dari semua sesi browser Anda yang lain di semua perangkat Anda. Beberapa sesi terbaru Anda tercantum di bawah ini; namun, daftar ini mungkin tidak lengkap. Jika Anda merasa akun Anda telah disusupi, Anda juga harus memperbarui kata sandi Anda.',
            'device' => 'Perangkat ini',
            'last_active' => 'Terakhir aktif',
            'logout_other_sessions' => 'Keluar dari Sesi Browser Lainnya',
            'logout_heading' => 'Keluar dari Sesi Browser Lainnya',
            'logout_description' => 'Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin keluar dari sesi browser Anda yang lain di semua perangkat.',
            'logout_action' => 'Keluar dari Sesi Lain',
            'incorrect_password' => 'Kata sandi yang Anda masukkan salah. Silakan coba lagi.',
            'logout_success' => 'Berhasil keluar dari semua sesi browser lainnya.',
        ],
    ],
    'clipboard' => [
        'link' => 'Salin',
        'tooltip' => 'Tersalin!',
    ],
    'fields' => [
        'avatar' => 'Foto Profil',
        'email' => 'Email',
        'login' => 'Masuk',
        'name' => 'Nama Lengkap',
        'password' => 'Kata Sandi',
        'password_confirm' => 'Konfirmasi Kata Sandi',
        'new_password' => 'Kata Sandi Baru',
        'new_password_confirmation' => 'Konfirmasi Kata Sandi Baru',
        'token_name' => 'Nama Token',
        'token_expiry' => 'Masa Berlaku Token',
        'abilities' => 'Hak Akses (Abilities)',
        '2fa_code' => 'Kode OTP',
        '2fa_recovery_code' => 'Kode Pemulihan',
        'created' => 'Dibuat pada',
        'expires' => 'Berakhir pada',
    ],
    'or' => 'Atau',
    'cancel' => 'Batal',
];