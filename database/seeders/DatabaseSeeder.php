<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Asha', 'Ravi', 'Meera'] as $name) {
            User::factory()->create([
                'name' => $name,
                'email' => strtolower($name).'@example.com',
            ]);
        }
    }
}
