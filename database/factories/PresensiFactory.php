<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sales;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Presensi>
 */
class PresensiFactory extends Factory
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
            'jam_absen' => now()->subHours(rand(1, 8)),
            'latitude' => $this->faker->latitude(-7.0, -6.0),
            'longitude' => $this->faker->longitude(106.0, 108.0),
            'foto_absensi' => 'presensi/dummy.jpg',
            'status' => $this->faker->randomElement(['hadir', 'terlambat']),
            'is_suspicious' => $this->faker->boolean(10), // 10% probabilitas terdeteksi fake GPS
        ];
    }
}