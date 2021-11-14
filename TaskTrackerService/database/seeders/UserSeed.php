<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::query()->updateOrCreate(['id' => 1], [
            'name' => 'admin',
            'email' => 'admin@admin.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('123456'),
            'public_id' => '123123'
        ]);
    }
}
