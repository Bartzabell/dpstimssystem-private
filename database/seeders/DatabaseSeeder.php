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
            ['name' => 'kg', 'created_by' => 1],
            ['name' => 'g', 'created_by' => 1],
            ['name' => 'l', 'created_by' => 1],
            ['name' => 'pc', 'created_by' => 1],
            ['name' => 'm', 'created_by' => 1],
            ['name' => 'ml', 'created_by' => 1],
            ['name' => 'm', 'created_by' => 1],
        ];

        foreach ($uoms as $uom) {
            Uom::firstOrCreate(['name' => $uom['name']], $uom);
        }

        $colors = [
            ['name' => 'Clear', 'hex' => null, 'created_by' => 1],
            ['name' => 'White', 'hex' => '#FFFFFF', 'created_by' => 1],
            ['name' => 'Black', 'hex' => '#000000', 'created_by' => 1],
            ['name' => 'Blue', 'hex' => '#0000FF', 'created_by' => 1],
            ['name' => 'Red', 'hex' => '#FF0000', 'created_by' => 1],
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
