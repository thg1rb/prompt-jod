<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryRuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET CONSTRAINTS ALL DEFERRED');

        $categoryRules = [
            'อาหาร & เครื่องดื่ม' => [
                ['keyword' => '7-eleven', 'priority' => 10],
                ['keyword' => '7-11', 'priority' => 10],
                ['keyword' => 'mk', 'priority' => 10],
                ['keyword' => 'grab food', 'priority' => 10],
            ],
            'ช้อปปิ้ง' => [
                ['keyword' => 'lazada', 'priority' => 10],
                ['keyword' => 'shopee', 'priority' => 10],
                ['keyword' => 'central', 'priority' => 10],
                ['keyword' => 'robinson', 'priority' => 10],
            ],
            'เดินทาง' => [
                ['keyword' => 'grab', 'priority' => 10],
                ['keyword' => 'bolt', 'priority' => 10],
                ['keyword' => 'bts', 'priority' => 10],
                ['keyword' => 'mrt', 'priority' => 10],
            ],
            'ค่าสาธารณูปโภค' => [
                ['keyword' => 'mea', 'priority' => 10],
                ['keyword' => 'pwa', 'priority' => 10],
                ['keyword' => 'ais', 'priority' => 10],
                ['keyword' => 'dtac', 'priority' => 10],
            ],
            'บันเทิง' => [
                ['keyword' => 'netflix', 'priority' => 10],
                ['keyword' => 'spotify', 'priority' => 10],
                ['keyword' => 'youtube', 'priority' => 10],
                ['keyword' => 'sf cinema', 'priority' => 10],
            ],
            'สุขภาพ' => [
                ['keyword' => 'โรงพยาบาล', 'priority' => 10],
                ['keyword' => 'hospital', 'priority' => 10],
                ['keyword' => 'pharmacy', 'priority' => 10],
                ['keyword' => 'watsons', 'priority' => 10],
            ],
            'การเงิน' => [
                ['keyword' => 'ประกัน', 'priority' => 10],
                ['keyword' => 'กองทุน', 'priority' => 10],
                ['keyword' => 'เงินกู้', 'priority' => 10],
                ['keyword' => 'aia', 'priority' => 10],
            ],
            'อื่น ๆ' => [],
        ];

        foreach ($categoryRules as $categoryName => $rules) {
            $categories = Category::withTrashed()
                ->where('name', $categoryName)
                ->get();

            foreach ($categories as $category) {
                foreach ($rules as $rule) {
                    $existing = CategoryRule::withTrashed()
                        ->where('category_id', $category->id)
                        ->where('keyword', $rule['keyword'])
                        ->first();

                    if (!$existing) {
                        CategoryRule::create([
                            'category_id' => $category->id,
                            'keyword' => $rule['keyword'],
                            'priority' => $rule['priority'],
                            'is_active' => true,
                            'case_sensitive' => false,
                        ]);
                    }
                }
            }
        }

        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
    }
}
