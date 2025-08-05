<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample inventory items
        Inventory::create([
            'name' => 'Laptop',
            'quantity' => 10,
            'price' => 1500.00,
            'location' => 'Warehouse A',
        ]);

        Inventory::create([
            'name' => 'Keyboard',
            'quantity' => 50,
            'price' => 25.50,
            'location' => 'Warehouse B',
        ]);

        Inventory::create([
            'name' => 'Mouse',
            'quantity' => 75,
            'price' => 15.00,
            'location' => 'Warehouse B',
        ]);

        Inventory::create([
            'name' => 'Monitor',
            'quantity' => 20,
            'price' => 200.00,
            'location' => 'Warehouse A',
        ]);
    }
}
