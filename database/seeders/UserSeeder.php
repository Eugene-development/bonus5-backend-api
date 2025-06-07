<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем тестового пользователя
        User::create([
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'city' => 'Москва',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Создаем администратора
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@bonus5.com',
            'city' => 'Санкт-Петербург',
            'password' => Hash::make('admin123456'),
            'email_verified_at' => now(),
        ]);

        // Создаем несколько дополнительных тестовых пользователей
        User::factory(5)->create();
    }
}
