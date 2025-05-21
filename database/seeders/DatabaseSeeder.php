<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\InventoryStock;
use App\Models\Material;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Uom;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
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
        $roles = [
            ['name' => 'admin'],
            ['name' => 'stocker'],
            ['name' => 'cashier'],
            ['name' => 'manager'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

        $warehouses = [
            ['name' => 'warehouse 1'],
            ['name' => 'warehouse 2'],
            ['name' => 'warehouse 3'],
            ['name' => 'warehouse 4'],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::firstOrCreate(['name' => $warehouse['name']], $warehouse);
        }

        // Then fetch the admin role to get its ID
        $adminRole = Role::where('name', 'admin')->first();
        // $adminWarehouse = Role::where('id', 1)->first();

        // $adminRole = Role::firstOrCreate(
        //     ['name' => 'admin'],
        //     ['name' => 'stocker'],
        //     ['name' => 'cashier'],
        //     ['name' => 'manager']
        // );

        // Create the administrator user
        User::firstOrCreate(
            ['username' => 'administrator'],
            [
                'name' => 'Administrator',
                'role_id' => $adminRole->id,
                // 'role_id' => $adminWarehouse->id,
                'email' => 'admin@example.com', // Change if needed
                'password' => Hash::make('password'),
            ]
        );

        Customer::firstOrCreate(
            ['name' => 'sample customer'],
            [
                'phone_no' => '09123456789',
                'street' => '1st Street',
                'municipality' => 'Carmona',
                'city' => 'Cavite',
                'email' => 'customer@sample.com',
                'tin_no' => '000 - 123 - 456 - 001',
                'created_by' => 1,
            ]
        );

        Supplier::firstOrCreate(
            ['name' => 'sample supplier'],
            [
                'phone_no' => '09123456789',
                'street' => '1st Street',
                'municipality' => 'Carmona',
                'city' => 'Cavite',
                'email' => 'supplier@sample.com',
                'tin_no' => '000 - 123 - 456 - 001',
                'created_by' => 1,
            ]
        );

        InventoryStock::firstOrCreate(
            ['item_code' => 'WhiteStandardPlasticCup100ML',],
            [
                'name' => 'Sample Item',
                // 'item_qty' => 100,
                'category' => 'Cup',
                'type' => 'Standard',
                'material' => 'Plastic',
                'color' => 'White',
                'size' => '100',
                'uom' => 'ml',
                'price' => 20,
                'min_stock' => 10,
                'max_stock' => 100,
                'status' => 'normal',
                'created_by' => 1,
            ]
        );

        WarehouseStock::firstOrCreate(
            [
                'inventory_id' => 1,
                'warehouse_id' => 1,
            ],
            [
                'item_code' => 'WhiteStandardPlasticCup100ML',
                'item_qty' => 50,
                'status' => 'normal',
                'price' => 20,
                'min_stock' => 10,
                'max_stock' => 100,
                'created_by' => 1,
            ]
        );

        WarehouseStock::firstOrCreate(
            [
                'inventory_id' => 1,
                'warehouse_id' => 2,
            ],
            [
                'item_code' => 'WhiteStandardPlasticCup100ML',
                'item_qty' => 30,
                'status' => 'normal',
                'price' => 20,
                'min_stock' => 10,
                'max_stock' => 100,
                'created_by' => 1,
            ]
        );

        WarehouseStock::firstOrCreate(
            [
                'inventory_id' => 1,
                'warehouse_id' => 3,
            ],
            [
                'item_code' => 'WhiteStandardPlasticCup100ML',
                'item_qty' => 20,
                'status' => 'normal',
                'price' => 20,
                'min_stock' => 10,
                'max_stock' => 100,
                'created_by' => 1,
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

        Discount::firstOrCreate(
            ['name' => 'Student Discount'],
            [
                'type' => 'Percentage',
                'amount' => 20,
                'created_by' => 1,
            ]
        );
    }
}
