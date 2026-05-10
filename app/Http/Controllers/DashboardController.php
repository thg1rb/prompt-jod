<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = $this->getDashboardData($user, 'month');

        return view('dashboard', [
            'dashboardData' => $data,
            'currentRange' => 'month',
        ]);
    }

    public function filter(Request $request): JsonResponse
    {
        $request->validate([
            'range' => 'required|in:today,week,month,all',
        ]);

        $user = Auth::user();
        $range = $request->input('range');

        return response()->json($this->getDashboardData($user, $range));
    }

    private function getDashboardData($user, string $range): array
    {
        [$startDate, $endDate] = $this->getDateRange($range);

        $transactions = $user->transactions()
            ->with('category')
            ->whereBetween('transacted_at', [$startDate, $endDate])
            ->expense()
            ->latest()
            ->get();

        $totalExpenses = $transactions->sum('amount');
        $totalBalance = $user->wallets()->sum('balance');
        $filteredCount = $transactions->count();
        $averagePerTransaction = $filteredCount > 0 ? $totalExpenses / $filteredCount : 0;
        $walletCount = $user->wallets()->count();

        $topCategoryData = null;
        if ($filteredCount > 0) {
            $grouped = $transactions->groupBy('category_id');
            $maxCategory = $grouped->map(fn ($items) => $items->sum('amount'))->sortDesc()->first();

            if ($maxCategory) {
                $categoryId = $grouped->keys()->first();
                $category = Category::find($categoryId);
                if ($category) {
                    $topCategoryData = [
                        'id' => $category->id,
                        'name' => $category->name,
                        'value' => $maxCategory,
                        'color' => $category->color ?? '#6366f1',
                        'icon' => $category->icon ?? '📌',
                    ];
                }
            }
        }

        $categoryData = $transactions->groupBy('category_id')
            ->map(fn ($items) => [
                'id' => $items->first()->category_id,
                'name' => $items->first()->category->name ?? 'อื่น ๆ',
                'value' => $items->sum('amount'),
                'color' => $items->first()->category->color ?? '#64748b',
                'icon' => $items->first()->category->icon ?? '📌',
            ])
            ->values()
            ->sortByDesc('value')
            ->values()
            ->all();

        $sevenDaySpending = $this->getSevenDaySpending($user);

        $recentTransactions = $user->transactions()
            ->with(['category', 'wallet'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'recipient' => $t->recipient ?? $t->description ?? '-',
                'description' => $t->description ?? '-',
                'amount' => $t->amount,
                'type' => $t->type->value,
                'category' => $t->category?->name ?? 'อื่น ๆ',
                'category_color' => $t->category?->color ?? '#64748b',
                'icon' => $t->category?->icon ?? '📌',
                'wallet' => $t->wallet?->name ?? '-',
                'transacted_at' => $t->transacted_at->format('Y-m-d H:i:s'),
            ])
            ->all();

        return [
            'totalExpenses' => $totalExpenses,
            'totalBalance' => $totalBalance,
            'averagePerTransaction' => $averagePerTransaction,
            'topCategory' => $topCategoryData,
            'categoryData' => $categoryData,
            'sevenDaySpending' => $sevenDaySpending,
            'recentTransactions' => $recentTransactions,
            'filteredCount' => $filteredCount,
            'walletCount' => $walletCount,
        ];
    }

    private function getDateRange(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'today' => [$now->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->startOfMonth(), $now->copy()->endOfMonth()],
            'all' => [Carbon::parse('2000-01-01'), $now->copy()->endOfDay()],
            default => [$now->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    private function getSevenDaySpending($user): array
    {
        $days = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
        $today = Carbon::now();
        $spending = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i)->startOfDay();
            $endDate = $date->copy()->endOfDay();

            $total = $user->transactions()
                ->whereBetween('transacted_at', [$date, $endDate])
                ->expense()
                ->sum('amount');

            $spending[] = [
                'day' => $days[$date->dayOfWeek],
                'amount' => (float) $total,
            ];
        }

        return $spending;
    }
}
