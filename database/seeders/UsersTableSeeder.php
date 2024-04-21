<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Murtada Luqman',
            'email' => 'murtada@gmail.com',
            'password' => bcrypt('12345678'),
        ])->roles()->attach(1); // Attach Admin role to the user
        User::create([
            'name' => 'Ahmed Luqman',
            'email' => 'ahmed@gmail.com',
            'password' => bcrypt('12345678'),
        ])->roles()->attach(3); // Attach Admin role to the user
        User::create([
            'name' => 'Omar Mohammed',
            'email' => 'omar@gmail.com',
            'password' => bcrypt('12345678'),
        ])->roles()->attach(4); // Attach Admin role to the user
    }
}
