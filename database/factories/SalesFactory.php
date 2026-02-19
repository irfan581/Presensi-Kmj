<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sales>
 */
class SalesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'no_hp' => '08' . $this->faker->numerify('##########'),
            'area' => $this->faker->randomElement(['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Makassar']),
            'target_kunjungan' => 10,
            'password' => bcrypt('password'), // password default: password
        ];
    }
}