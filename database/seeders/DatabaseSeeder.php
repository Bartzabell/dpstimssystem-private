<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Color;
use App\Models\Material;
use App\Models\Role;
use App\Models\Uom;
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

        $uoms = [
            ['name' => 'Kilogram', 'created_by' => 1],
            ['name' => 'Gram', 'created_by' => 1],
            ['name' => 'Liter', 'created_by' => 1],
            ['name' => 'Piece', 'created_by' => 1],
            ['name' => 'Meter', 'created_by' => 1],
        ];

        foreach ($uoms as $uom) {
            Uom::firstOrCreate(['name' => $uom['name']], $uom);
        }

        $colors = [
            ['name' => 'Clear', 'created_by' => 1],
            ['name' => 'White', 'created_by' => 1],
            ['name' => 'Black', 'created_by' => 1],
            ['name' => 'Blue', 'created_by' => 1],
            ['name' => 'Red', 'created_by' => 1],
        ];

        foreach ($colors as $color) {
            Color::firstOrCreate(['name' => $color['name']], $color);
        }

        $materials = [
            ['name' => 'Plastic', 'created_by' => 1],
            ['name' => 'Paper', 'created_by' => 1],
        ];

        foreach ($materials as $material) {
            Material::firstOrCreate(['name' => $material['name']], $material);
        }

        $categories = [
            ['name' => 'Bottle', 'created_by' => 1],
            ['name' => 'Cup', 'created_by' => 1],
            ['name' => 'Cap', 'created_by' => 1],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
