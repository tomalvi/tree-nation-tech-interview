<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;


/**
 * @extends Factory<Customer>
 */
class VisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::today();
        $date->setHour(rand(8, 9));
        $date->setMinute(rand(0, 59));
        $date->setSecond(rand(0, 59));
        
        return [
            'visited_at' => $date,
        ];
    }
}
