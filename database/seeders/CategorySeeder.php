<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement('SET CONSTRAINTS ALL DEFERRED');
        }

        $defaultCategories = [
            [
                'name' => 'อาหาร & เครื่องดื่ม',
                'description' => 'ร้านอาหาร คาเฟ่ ซูเปอร์มาร์เก็ต และอาหารส่ง',
                'color' => '#EC4899',
                'icon' => '🍜',
                'sort_order' => 1,
            ],
            [
                'name' => 'ช้อปปิ้ง',
                'description' => 'เสื้อผ้า อิเล็กทรอนิกส์ และการซื้อของ',
                'color' => '#F59E0B',
                'icon' => '🛍️',
                'sort_order' => 2,
            ],
            [
                'name' => 'เดินทาง',
                'description' => 'น้ำมัน การเดินทางสาธารณะ และที่จอดรถ',
                'color' => '#EC4899',
                'icon' => '🚗',
                'sort_order' => 3,
            ],
            [
                'name' => 'ค่าสาธารณูปโภค',
                'description' => 'ไฟฟ้า น้ำ อินเทอร์เน็ต และโทรศัพท์',
                'color' => '#EF4444',
                'icon' => '💡',
                'sort_order' => 4,
            ],
            [
                'name' => 'บันเทิง',
                'description' => 'ภาพยนตร์ เกม สตรีมมิ่ง และงานอดิเรก',
                'color' => '#F59E0B',
                'icon' => '🎬',
                'sort_order' => 5,
            ],
            [
                'name' => 'สุขภาพ',
                'description' => 'การแพทย์ ร้านขายยา ฟิตเนส และสุขภาพ',
                'color' => '#F59E0B',
                'icon' => '🏥',
                'sort_order' => 6,
            ],
            [
                'name' => 'การเงิน',
                'description' => 'ประกัน การลงทุน และค่าธรรมเนียมธนาคาร',
                'color' => '#10B981',
                'icon' => '🏦',
                'sort_order' => 7,
            ],
            [
                'name' => 'อื่น ๆ',
                'description' => 'ค่าใช้จ่ายอื่นๆ',
                'color' => '#3B82F6',
                'icon' => '📌',
                'sort_order' => 99,
            ],
        ];

        foreach (Category::withTrashed()->get()->pluck('user_id')->unique() as $userId) {
            foreach ($defaultCategories as $category) {
                $existing = Category::withTrashed()
                    ->where('user_id', $userId)
                    ->where('name', $category['name'])
                    ->first();

                if (! $existing) {
                    Category::create([
                        'user_id' => $userId,
                        'name' => $category['name'],
                        'description' => $category['description'],
                        'color' => $category['color'],
                        'icon' => $category['icon'],
                        'is_active' => true,
                        'sort_order' => $category['sort_order'],
                    ]);
                }
            }
        }

        if (config('database.default') !== 'sqlite') {
            DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
        }
    }
}
