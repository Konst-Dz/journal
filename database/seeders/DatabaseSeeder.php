<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Админ',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Преподаватель',
                'role' => 'teacher',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Сотрудник',
                'role' => 'employee',
                'password' => Hash::make('password'),
            ]
        );
        User::firstOrCreate(
            ['email' => 'kdemidenko@gmail.com'],
            [
                'name' => 'Константин Демиденко',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );User::firstOrCreate(
            ['email' => 'kdemidenko2@gmail.com'],
            [
                'name' => 'Константин Демиденко',
                'role' => 'teacher',
                'password' => Hash::make('password'),
            ]
        );

        $existingEmployeesCount = User::where('role', 'employee')->count();
        if ($existingEmployeesCount < 51) {
            User::factory()->count(51 - $existingEmployeesCount)->create([
                'role' => 'employee',
            ]);
        }

        $existingTeachersCount = User::where('role', 'teacher')->count();
        if ($existingTeachersCount < 6) {
            User::factory()->count(6 - $existingTeachersCount)->create([
                'role' => 'teacher',
            ]);
        }
    }
}
