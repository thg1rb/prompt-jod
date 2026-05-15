<?php

namespace Database\Seeders;

use App\Models\FixedCategory;
use Illuminate\Database\Seeder;

class FixedCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'อาหาร & เครื่องดื่ม', 'color' => '#EC4899', 'icon' => '🍜', 'sort_order' => 1, 'type' => 'expense'],
            ['name' => 'ช้อปปิ้ง', 'color' => '#F59E0B', 'icon' => '🛍️', 'sort_order' => 2, 'type' => 'expense'],
            ['name' => 'เดินทาง', 'color' => '#EC4899', 'icon' => '🚗', 'sort_order' => 3, 'type' => 'expense'],
            ['name' => 'ค่าสาธารณูปโภค', 'color' => '#EF4444', 'icon' => '💡', 'sort_order' => 4, 'type' => 'expense'],
            ['name' => 'บันเทิง', 'color' => '#F59E0B', 'icon' => '🎬', 'sort_order' => 5, 'type' => 'expense'],
            ['name' => 'สุขภาพ', 'color' => '#F59E0B', 'icon' => '🏥', 'sort_order' => 6, 'type' => 'expense'],
            ['name' => 'การเงิน', 'color' => '#10B981', 'icon' => '🏦', 'sort_order' => 7, 'type' => 'expense'],
            ['name' => 'อื่น ๆ', 'color' => '#3B82F6', 'icon' => '📌', 'sort_order' => 99, 'type' => 'expense'],
        ];

        foreach ($categories as $category) {
            FixedCategory::create($category);
        }
    }
}
