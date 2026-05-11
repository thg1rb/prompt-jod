<?php

namespace App\Http\Controllers;

use App\Http\Requests\BalanceAdjustmentRequest;
use App\Http\Requests\WalletStoreRequest;
use App\Http\Requests\WalletUpdateRequest;
use App\Models\BalanceAdjustment;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Display a listing of the user's wallets.
     */
    public function index(): View
    {
        $wallets = auth()->user()
            ->wallets()
            ->with(['transactions' => function ($query) {
                $query->latest()->limit(3);
            }])
            ->withCount(['transactions', 'balanceAdjustments'])
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        $totalBalance = $wallets->sum('balance');

        return view('wallets.index', compact('wallets', 'totalBalance'));
    }

    /**
     * Show the form for creating a new wallet.
     */
    public function create(): View
    {
        return view('wallets.create');
    }

    /**
     * Store a newly created wallet in storage.
     */
    public function store(WalletStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['balance'] = $data['opening_balance'] ?? 0;
        $data['is_default'] = $request->boolean('is_default');

        // If this is set as default, remove default from other wallets
        if ($data['is_default']) {
            auth()->user()->wallets()->update(['is_default' => false]);
        }

        $wallet = auth()->user()->wallets()->create($data);

        // Create opening balance adjustment if provided
        if (isset($data['opening_balance']) && $data['opening_balance'] > 0) {
            BalanceAdjustment::create([
                'wallet_id' => $wallet->id,
                'user_id' => auth()->id(),
                'previous_balance' => 0,
                'new_balance' => $data['opening_balance'],
                'adjustment_amount' => $data['opening_balance'],
                'reason' => 'opening_balance',
                'notes' => 'ยอดเงินเริ่มต้น',
                'adjusted_at' => now(),
            ]);
        }

        return redirect()
            ->route('wallets.index')
            ->with('success', 'เพิ่มบัญชีเรียบร้อยแล้ว');
    }

    /**
     * Display the specified wallet with transaction history.
     */
    public function show(Wallet $wallet): View
    {
        $this->authorizeWallet($wallet);

        $wallet->load(['transactions' => function ($query) {
            $query->with('category')
                ->latest()
                ->limit(10);
        }]);

        $wallet->load(['balanceAdjustments' => function ($query) {
            $query->latest('adjusted_at')
                ->limit(5);
        }]);

        return view('wallets.show', compact('wallet'));
    }

    /**
     * Display all balance adjustments for the specified wallet.
     */
    public function adjustments(Wallet $wallet): View
    {
        $this->authorizeWallet($wallet);

        $adjustments = $wallet->balanceAdjustments()
            ->latest('adjusted_at')
            ->paginate(20);

        return view('wallets.adjustments', compact('wallet', 'adjustments'));
    }

    /**
     * Show the form for editing the specified wallet.
     */
    public function edit(Wallet $wallet): View
    {
        $this->authorizeWallet($wallet);

        return view('wallets.edit', compact('wallet'));
    }

    /**
     * Update the specified wallet in storage.
     */
    public function update(WalletUpdateRequest $request, Wallet $wallet): RedirectResponse
    {
        $this->authorizeWallet($wallet);

        $data = $request->validated();
        $data['is_default'] = $request->boolean('is_default');

        // Handle default wallet change
        if ($data['is_default'] && ! $wallet->is_default) {
            auth()->user()->wallets()->where('id', '!=', $wallet->id)->update(['is_default' => false]);
        }

        $wallet->update($data);

        return redirect()
            ->route('wallets.index')
            ->with('success', 'อัปเดตบัญชีเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified wallet from storage (soft delete).
     */
    public function destroy(Wallet $wallet): JsonResponse
    {
        $this->authorizeWallet($wallet);

        if ($wallet->transactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถลบกระเป๋าเงินที่มีธุรกรรมได้',
            ], 400);
        }

        $wallet->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบบัญชีเรียบร้อยแล้ว',
        ]);
    }

    /**
     * Adjust wallet balance manually.
     */
    public function adjustBalance(BalanceAdjustmentRequest $request, Wallet $wallet): RedirectResponse
    {
        $this->authorizeWallet($wallet);

        $data = $request->validated();
        $previousBalance = $wallet->balance;
        $newBalance = $data['new_balance'];
        $adjustmentAmount = $newBalance - $previousBalance;

        // Create balance adjustment record
        BalanceAdjustment::create([
            'wallet_id' => $wallet->id,
            'user_id' => auth()->id(),
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
            'adjustment_amount' => $adjustmentAmount,
            'reason' => 'manual_adjustment',
            'notes' => $data['notes'],
            'adjusted_at' => now(),
        ]);

        // Update wallet balance
        $wallet->update(['balance' => $newBalance]);

        return redirect()
            ->route('wallets.show', $wallet)
            ->with('success', 'ปรับยอดเงินเรียบร้อยแล้ว');
    }

    /**
     * Set wallet as default wallet.
     */
    public function setDefault(Wallet $wallet): RedirectResponse
    {
        $this->authorizeWallet($wallet);

        auth()->user()->wallets()->update(['is_default' => false]);
        $wallet->update(['is_default' => true]);

        return redirect()
            ->route('wallets.index')
            ->with('success', 'ตั้งเป็นบัญชีหลักเรียบร้อยแล้ว');
    }

    /**
     * Authorize that the user owns the wallet.
     */
    protected function authorizeWallet(Wallet $wallet): void
    {
        abort_if($wallet->user_id !== auth()->id(), 403);
    }
}
