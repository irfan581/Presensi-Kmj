<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Batas Jam Masuk
    |--------------------------------------------------------------------------
    | Format: HH:mm
    | Sales yang masuk setelah jam ini akan dianggap terlambat.
    | Nilai diambil dari .env → ABSENSI_BATAS_JAM
    */
    'batas_jam' => env('ABSENSI_BATAS_JAM', '08:00'),
];