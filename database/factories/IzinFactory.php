<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sales;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Izin>
 */
class IzinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sales_id' => Sales::factory(),
            'tanggal' => $this->faker->dateTimeBetween('now', '+1 month'),
            'jenis_izin' => $this->faker->randomElement(['sakit', 'izin', 'keperluan_lain']),
            'keterangan' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'disetujui', 'ditolak']),
            'bukti_foto' => 'izin/dummy-bukti.jpg',
        ];
    }
}