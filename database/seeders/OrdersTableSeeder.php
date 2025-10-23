<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Order::factory()->count(rand(1, 5))->create([
                'user_id' => $user->id,
                'created_at' => now(),
            ]);
        }
    }
}
