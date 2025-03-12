<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

         // Ensure the admin role exists
         $adminRole = Role::firstOrCreate(['name' => 'admin']);

         // Create the administrator user
         User::firstOrCreate(
             ['username' => 'administrator'],
             [
                 'name' => 'Administrator',
                 'role_id' => $adminRole->id,
                 'email' => 'admin@example.com', // Change if needed
                 'password' => Hash::make('password'),
             ]
         );
    }
}
