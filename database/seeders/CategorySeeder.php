<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the five default categories.
     */
    public function run(): void
    {
        $categories = [
            'COS' => 'Costume',
            'WIG' => 'Wig',
            'PRP' => 'Prop',
            'ACC' => 'Accessory',
            'SHO' => 'Footwear',
        ];

        foreach ($categories as $code => $name) {
            Category::firstOrCreate(['code' => $code], ['name' => $name]);
        }
    }
}
