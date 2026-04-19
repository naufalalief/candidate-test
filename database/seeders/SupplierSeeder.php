<?php

namespace Database\Seeders;

use App\Models\Layer;
use App\Models\Layup;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Nordic Timber Co.', 'layups' => 24, 'created_at' => '2023-10-24'],
            ['name' => 'Alpine CLT Solutions', 'layups' => 12, 'created_at' => '2023-11-02'],
            ['name' => 'MassivWood Ltd.', 'layups' => 156, 'created_at' => '2024-01-15'],
            ['name' => 'TimberStruct Inc.', 'layups' => 89, 'created_at' => '2024-02-10'],
            ['name' => 'EuroLam Systems', 'layups' => 45, 'created_at' => '2024-02-28'],
        ];

        foreach ($suppliers as $data) {
            $supplier = Supplier::create([
                'name' => $data['name'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['created_at'],
            ]);

            for ($i = 1; $i <= $data['layups']; $i++) {
                $layup = Layup::create([
                    'supplier_id' => $supplier->id,
                    'name' => $data['name'] . ' Layup ' . $i,
                ]);

                $layerCount = fake()->numberBetween(3, 7);
                for ($j = 1; $j <= $layerCount; $j++) {
                    Layer::create([
                        'layup_id' => $layup->id,
                        'layer_order' => $j,
                        'thickness' => fake()->randomFloat(2, 10, 40),
                        'width' => fake()->randomFloat(2, 50, 200),
                        'angle' => fake()->randomElement([0, 90]),
                    ]);
                }
            }
        }
    }
}
