<?php

return [
    /*
    | Jam batas toleransi masuk (format 24 jam).
    | Jika absen masuk >= jam ini, status jadi 'terlambat'.
    */
    'batas_jam_terlambat' => env('ABSENSI_BATAS_JAM', 8),
];