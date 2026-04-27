<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET CONSTRAINTS ALL DEFERRED');

        // Define keyword rules for each category
        $categoryRules = [
            'Food' => [
                ['keyword' => '7-eleven', 'priority' => 10],
                ['keyword' => 'mcdonald', 'priority' => 10],
                ['keyword' => 'starbucks', 'priority' => 10],
                ['keyword' => 'kfc', 'priority' => 10],
                ['keyword' => 'pizza', 'priority' => 9],
                ['keyword' => 'burger', 'priority' => 9],
                ['keyword' => 'sushi', 'priority' => 9],
                ['keyword' => 'foodpanda', 'priority' => 8],
                ['keyword' => 'grabfood', 'priority' => 8],
                ['keyword' => 'robinhood', 'priority' => 8],
                ['keyword' => 'restaurant', 'priority' => 5],
                ['keyword' => 'cafe', 'priority' => 5],
                ['keyword' => 'coffee', 'priority' => 5],
                ['keyword' => 'meal', 'priority' => 4],
                ['keyword' => 'grocery', 'priority' => 4],
                ['keyword' => 'mart', 'priority' => 3],
            ],
            'Shopping' => [
                ['keyword' => 'shopee', 'priority' => 10],
                ['keyword' => 'lazada', 'priority' => 10],
                ['keyword' => 'amazon', 'priority' => 10],
                ['keyword' => 'clothing', 'priority' => 8],
                ['keyword' => 'fashion', 'priority' => 8],
                ['keyword' => 'shoes', 'priority' => 7],
                ['keyword' => 'store', 'priority' => 5],
                ['keyword' => 'mall', 'priority' => 5],
                ['keyword' => 'shop', 'priority' => 4],
            ],
            'Transport' => [
                ['keyword' => 'ptt', 'priority' => 10],
                ['keyword' => 'bangchak', 'priority' => 10],
                ['keyword' => 'shell', 'priority' => 10],
                ['keyword' => 'esso', 'priority' => 10],
                ['keyword' => 'grab', 'priority' => 8],
                ['keyword' => 'bolt', 'priority' => 8],
                ['keyword' => 'taxi', 'priority' => 7],
                ['keyword' => 'parking', 'priority' => 6],
                ['keyword' => 'fuel', 'priority' => 5],
                ['keyword' => 'gas', 'priority' => 5],
                ['keyword' => 'bts', 'priority' => 5],
                ['keyword' => 'mrt', 'priority' => 5],
            ],
            'Utilities' => [
                ['keyword' => 'mea', 'priority' => 10],
                ['keyword' => 'pea', 'priority' => 10],
                ['keyword' => 'true', 'priority' => 10],
                ['keyword' => 'ais', 'priority' => 10],
                ['keyword' => 'dtac', 'priority' => 10],
                ['keyword' => 'jas', 'priority' => 10],
                ['keyword' => 'water', 'priority' => 8],
                ['keyword' => 'electric', 'priority' => 8],
                ['keyword' => 'internet', 'priority' => 7],
                ['keyword' => 'phone', 'priority' => 7],
                ['keyword' => 'bill', 'priority' => 5],
            ],
            'Entertainment' => [
                ['keyword' => 'netflix', 'priority' => 10],
                ['keyword' => 'disney', 'priority' => 10],
                ['keyword' => 'spotify', 'priority' => 10],
                ['keyword' => 'youtube', 'priority' => 9],
                ['keyword' => 'game', 'priority' => 8],
                ['keyword' => 'movie', 'priority' => 7],
                ['keyword' => 'cinema', 'priority' => 7],
                ['keyword' => 'theater', 'priority' => 6],
                ['keyword' => 'concert', 'priority' => 6],
                ['keyword' => 'steam', 'priority' => 5],
            ],
            'Health' => [
                ['keyword' => 'pharmacy', 'priority' => 10],
                ['keyword' => 'hospital', 'priority' => 10],
                ['keyword' => 'clinic', 'priority' => 10],
                ['keyword' => 'fitness', 'priority' => 8],
                ['keyword' => 'gym', 'priority' => 8],
                ['keyword' => 'medical', 'priority' => 7],
                ['keyword' => 'doctor', 'priority' => 7],
                ['keyword' => 'dental', 'priority' => 6],
                ['keyword' => 'vitamin', 'priority' => 5],
                ['keyword' => 'supplement', 'priority' => 5],
            ],
            'Finance' => [
                ['keyword' => 'insurance', 'priority' => 10],
                ['keyword' => 'premium', 'priority' => 9],
                ['keyword' => 'investment', 'priority' => 8],
                ['keyword' => 'mutual fund', 'priority' => 8],
                ['keyword' => 'stock', 'priority' => 7],
                ['keyword' => 'crypto', 'priority' => 7],
                ['keyword' => 'bank', 'priority' => 5],
                ['keyword' => 'loan', 'priority' => 5],
                ['keyword' => 'interest', 'priority' => 5],
                ['keyword' => 'fee', 'priority' => 4],
            ],
            'Other' => [
                ['keyword' => 'transfer', 'priority' => 5],
                ['keyword' => 'atm', 'priority' => 4],
                ['keyword' => 'withdraw', 'priority' => 4],
            ],
        ];

        // Create rules for all categories across all users
        foreach ($categoryRules as $categoryName => $rules) {
            $categories = Category::withTrashed()
                ->where('name', $categoryName)
                ->get();

            foreach ($categories as $category) {
                foreach ($rules as $rule) {
                    // Check if rule already exists for this category
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
