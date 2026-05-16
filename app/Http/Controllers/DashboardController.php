<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = $this->getDashboardData($user, 'month', 'all');

        return view('dashboard', [
            'dashboardData' => $data,
            'currentRange' => 'month',
        ]);
    }

    public function filter(Request $request): JsonResponse
    {
        $request->validate([
            'range' => 'required|in:today,week,month,all',
            'wallet_type' => 'nullable|in:all,personal,shared',
        ]);

        $user = Auth::user();
        $range = $request->input('range');
        $walletType = $request->input('wallet_type', 'all');

        return response()->json($this->getDashboardData($user, $range, $walletType));
    }

    private function getDashboardData($user, string $range, string $walletType): array
    {
        [$startDate, $endDate] = $this->getDateRange($range);
        $walletIds = $this->getAccessibleWalletIds($user, $walletType);

        $transactions = Transaction::with(['category.fixedCategory', 'wallet'])
            ->whereIn('wallet_id', $walletIds)
            ->whereBetween('transacted_at', [$startDate, $endDate])
            ->expense()
            ->latest()
            ->get();

        $totalExpenses = $transactions->sum('amount');
        $totalBalance = $user->wallets()->whereIn('id', $walletIds)->sum('balance');
        $filteredCount = $transactions->count();
        $averagePerTransaction = $filteredCount > 0 ? $totalExpenses / $filteredCount : 0;
        $walletCount = count($walletIds);

        $categoryData = DB::table('transactions')
            ->join('custom_categories', 'transactions.category_id', '=', 'custom_categories.id')
            ->join('fixed_categories', 'custom_categories.fixed_category_id', '=', 'fixed_categories.id')
            ->whereIn('transactions.wallet_id', $walletIds)
            ->whereBetween('transactions.transacted_at', [$startDate, $endDate])
            ->where('transactions.type', 'expense')
            ->select(
                'fixed_categories.id',
                'fixed_categories.name',
                'fixed_categories.color',
                'fixed_categories.icon',
                DB::raw('SUM(transactions.amount) as value')
            )
            ->groupBy('fixed_categories.id', 'fixed_categories.name', 'fixed_categories.color', 'fixed_categories.icon')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'value' => (float) $row->value,
                'color' => $row->color,
                'icon' => $row->icon,
            ])
            ->values()
            ->all();

        $topCategoryData = null;
        if (count($categoryData) > 0) {
            $topCategoryData = $categoryData[0];
        }

        $sevenDaySpending = $this->getSevenDaySpending($walletIds);

        $recentTransactions = Transaction::with(['category.fixedCategory', 'wallet'])
            ->whereIn('wallet_id', $walletIds)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'recipient' => $t->recipient ?? '-',
                'amount' => $t->amount,
                'type' => $t->type->value,
                'category' => $t->category?->fixedCategory?->name ?? 'อื่น ๆ',
                'category_color' => $t->category?->fixedCategory?->color ?? '#64748b',
                'icon' => $t->category?->fixedCategory?->icon ?? '📌',
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

    private function getAccessibleWalletIds($user, string $walletType): array
    {
        $ownedQuery = $user->wallets();

        if ($walletType === 'shared') {
            $ownedQuery->shared();
        } elseif ($walletType === 'personal') {
            $ownedQuery->personal();
        }

        $ownedIds = $ownedQuery->pluck('id')->toArray();

        $sharedIds = [];
        if ($walletType !== 'personal') {
            $sharedIds = $user->sharedWallets()
                ->when($walletType === 'shared', fn ($q) => $q->shared())
                ->pluck('wallets.id')
                ->toArray();
        }

        return array_values(array_unique(array_merge($ownedIds, $sharedIds)));
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

    private function getSevenDaySpending(array $walletIds): array
    {
        $days = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
        $today = Carbon::now();
        $startDate = $today->copy()->subDays(6)->startOfDay();
        $endDate = $today->copy()->endOfDay();

        $transactions = Transaction::whereIn('wallet_id', $walletIds)
            ->whereBetween('transacted_at', [$startDate, $endDate])
            ->expense()
            ->selectRaw('DATE(transacted_at) as date, SUM(amount) as total')
            ->groupByRaw('DATE(transacted_at)')
            ->pluck('total', 'date')
            ->toArray();

        $spending = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i)->startOfDay();
            $dateKey = $date->format('Y-m-d');
            $total = $transactions[$dateKey] ?? 0;

            $spending[] = [
                'day' => $days[$date->dayOfWeek],
                'amount' => (float) $total,
            ];
        }

        return $spending;
    }
}
