<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'loan'],
            ['name' => 'saving'],
            ['name' => 'contribution'],
            ['name' => 'sinking fund']
        ];

        foreach ($types as $type) {
            TransactionType::firstOrCreate($type);
        }
    }
}
