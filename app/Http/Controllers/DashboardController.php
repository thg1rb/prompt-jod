<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get date range (default: this month)
        [$startDate, $endDate] = $this->getDateRange('month');

        // Get transactions for the date range
        $transactions = $user->transactions()
            ->whereBetween('transacted_at', [$startDate, $endDate])
            ->expense()
            ->latest()
            ->get();

        // Calculate summary statistics
        $totalExpenses = $transactions->sum('amount');
        $totalBalance = $user->wallets()->sum('balance');
        $averagePerTransaction = $transactions->count() > 0
            ? $totalExpenses / $transactions->count()
            : 0;

        // Get top category
        $topCategory = $transactions->groupBy('category_id')
            ->map(fn ($items) => $items->sum('amount'))
            ->sortDesc()
            ->first();

        $topCategoryData = null;
        if ($topCategory) {
            $categoryId = $transactions->groupBy('category_id')
                ->keys()
                ->first();
            $category = Category::find($categoryId);
            if ($category) {
                $topCategoryData = [
                    'name' => $category->name,
                    'amount' => $topCategory,
                    'color' => $category->color ?? '#6366f1',
                ];
            }
        }

        // Group expenses by category for pie chart
        $categoryData = $transactions->groupBy('category_id')
            ->map(fn ($items) => [
                'name' => $items->first()->category->name ?? 'Other',
                'value' => $items->sum('amount'),
                'color' => $items->first()->category->color ?? '#94a3b8',
                'icon' => $items->first()->category->icon ?? 'circle',
            ])
            ->values()
            ->sortByDesc('value')
            ->values()
            ->all();

        // Calculate 7-day spending for bar chart
        $sevenDaySpending = $this->getSevenDaySpending($user);

        // Get recent transactions (last 5)
        $recentTransactions = $user->transactions()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'description' => $t->description,
                'amount' => $t->amount,
                'type' => $t->type->value,
                'category' => $t->category?->name ?? '-',
                'category_color' => $t->category?->color ?? '#94a3b8',
                'wallet' => $t->wallet?->name ?? '-',
                'transacted_at' => $t->transacted_at->format('Y-m-d H:i:s'),
            ])
            ->all();

        return view('dashboard', [
            'totalExpenses' => number_format($totalExpenses, 2),
            'totalBalance' => number_format($totalBalance, 2),
            'averagePerTransaction' => number_format($averagePerTransaction, 2),
            'topCategory' => $topCategoryData,
            'categoryData' => $categoryData,
            'sevenDaySpending' => $sevenDaySpending,
            'recentTransactions' => $recentTransactions,
            'currentRange' => 'month',
        ]);
    }

    /**
     * Filter dashboard data by date range.
     */
    public function filter(Request $request): JsonResponse
    {
        $request->validate([
            'range' => 'required|in:today,week,month,all',
        ]);

        $user = Auth::user();
        $range = $request->input('range');

        // Get date range
        [$startDate, $endDate] = $this->getDateRange($range);

        // Get transactions for the date range
        $transactions = $user->transactions()
            ->whereBetween('transacted_at', [$startDate, $endDate])
            ->expense()
            ->latest()
            ->get();

        // Calculate summary statistics
        $totalExpenses = $transactions->sum('amount');
        $totalBalance = $user->wallets()->sum('balance');
        $averagePerTransaction = $transactions->count() > 0
            ? $totalExpenses / $transactions->count()
            : 0;

        // Get top category
        $topCategory = $transactions->groupBy('category_id')
            ->map(fn ($items) => $items->sum('amount'))
            ->sortDesc()
            ->first();

        $topCategoryData = null;
        if ($topCategory) {
            $categoryId = $transactions->groupBy('category_id')
                ->keys()
                ->first();
            $category = Category::find($categoryId);
            if ($category) {
                $topCategoryData = [
                    'name' => $category->name,
                    'amount' => $topCategory,
                    'color' => $category->color ?? '#6366f1',
                ];
            }
        }

        // Group expenses by category for pie chart
        $categoryData = $transactions->groupBy('category_id')
            ->map(fn ($items) => [
                'name' => $items->first()->category->name ?? 'Other',
                'value' => $items->sum('amount'),
                'color' => $items->first()->category->color ?? '#94a3b8',
                'icon' => $items->first()->category->icon ?? 'circle',
            ])
            ->values()
            ->sortByDesc('value')
            ->values()
            ->all();

        // Calculate 7-day spending for bar chart
        $sevenDaySpending = $this->getSevenDaySpending($user);

        // Get recent transactions (last 5)
        $recentTransactions = $user->transactions()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'description' => $t->description,
                'amount' => $t->amount,
                'type' => $t->type->value,
                'category' => $t->category?->name ?? '-',
                'category_color' => $t->category?->color ?? '#94a3b8',
                'wallet' => $t->wallet?->name ?? '-',
                'transacted_at' => $t->transacted_at->format('Y-m-d H:i:s'),
            ])
            ->all();

        return response()->json([
            'totalExpenses' => number_format($totalExpenses, 2),
            'totalBalance' => number_format($totalBalance, 2),
            'averagePerTransaction' => number_format($averagePerTransaction, 2),
            'topCategory' => $topCategoryData,
            'categoryData' => $categoryData,
            'sevenDaySpending' => $sevenDaySpending,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Get date range based on filter type.
     */
    private function getDateRange(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'today' => [
                $now->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'week' => [
                $now->startOfWeek(),
                $now->copy()->endOfWeek(),
            ],
            'month' => [
                $now->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'all' => [
                Carbon::parse('2000-01-01'),
                $now->copy()->endOfDay(),
            ],
            default => [
                $now->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
        };
    }

    /**
     * Get 7-day spending data.
     */
    private function getSevenDaySpending($user): array
    {
        $days = [
            ['label' => 'อา', 'key' => 0],
            ['label' => 'จ', 'key' => 1],
            ['label' => 'อ', 'key' => 2],
            ['label' => 'พ', 'key' => 3],
            ['label' => 'พฤ', 'key' => 4],
            ['label' => 'ศ', 'key' => 5],
            ['label' => 'ส', 'key' => 6],
        ];

        $today = Carbon::now();
        $currentDayOfWeek = $today->dayOfWeek;

        $spending = [];

        foreach ($days as $day) {
            $daysAgo = ($currentDayOfWeek - $day['key'] + 7) % 7;
            $date = $today->copy()->subDays($daysAgo)->startOfDay();
            $endDate = $date->copy()->endOfDay();

            $total = $user->transactions()
                ->whereBetween('transacted_at', [$date, $endDate])
                ->expense()
                ->sum('amount');

            $spending[] = [
                'day' => $day['label'],
                'amount' => (float) $total,
            ];
        }

        return $spending;
    }
}
