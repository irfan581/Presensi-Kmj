<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sales;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KunjunganToko>
 */
class KunjunganTokoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $is_suspicious = $this->faker->boolean(15); // 15% curang
        
        return [
            'sales_id' => Sales::factory(),
            'nama_toko' => $this->faker->company() . ' Cell',
            'location' => $this->faker->latitude(-7.0, -6.0) . ',' . $this->faker->longitude(106.0, 108.0),
            'keterangan' => $this->faker->sentence(),
            'foto_kunjungan' => 'kunjungan-toko/dummy-toko.jpg',
            'is_suspicious' => $is_suspicious,
            'suspicious_reason' => $is_suspicious ? $this->faker->randomElement(['Fake GPS Detected', 'Rooted Device', 'Mock Location Active']) : null,
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}