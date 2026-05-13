<?php

namespace App\Http\Controllers;

use App\Http\Requests\BalanceAdjustmentRequest;
use App\Http\Requests\WalletStoreRequest;
use App\Http\Requests\WalletUpdateRequest;
use App\Models\BalanceAdjustment;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Display a listing of the user's wallets.
     */
    public function index(): View
    {
        $user = auth()->user();

        $ownedWallets = $user->wallets()
            ->with(['transactions' => function ($query) {
                $query->latest()->limit(3);
            }])
            ->withCount(['transactions', 'balanceAdjustments'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $sharedWallets = $user->sharedWallets()
            ->with(['transactions' => function ($query) {
                $query->latest()->limit(3);
            }])
            ->withCount('transactions')
            ->orderBy('name')
            ->get();

        $wallets = $ownedWallets->merge($sharedWallets);

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
    public function store(WalletStoreRequest $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if (! $user->canCreateWallet()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาสมัครสมาชิก Premium เพื่อสร้างกระเป๋าเงินเพิ่มเติม',
                    'requires_subscription' => true,
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'กรุณาสมัครสมาชิก Premium เพื่อสร้างกระเป๋าเงินเพิ่มเติม');
        }

        $data = $request->validated();

        if ($user->isFree() && ($data['access_type'] ?? null) === 'shared') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาสมัครสมาชิก Premium เพื่อสร้างกระเป๋าเงินแชร์',
                    'requires_subscription' => true,
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'กรุณาสมัครสมาชิก Premium เพื่อสร้างกระเป๋าเงินแชร์');
        }

        $data['user_id'] = $user->id;
        $data['balance'] = $data['opening_balance'] ?? 0;
        $data['is_default'] = $request->boolean('is_default');

        if ($data['is_default']) {
            $user->wallets()->update(['is_default' => false]);
        }

        $wallet = $user->wallets()->create($data);

        if (isset($data['opening_balance']) && $data['opening_balance'] > 0) {
            BalanceAdjustment::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
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
    public function show(Wallet $wallet): View|RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if (! $wallet->hasAccess($user)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่มีสิทธิ์เข้าถึงกระเป๋าเงินนี้',
                ], 403);
            }

            abort(403);
        }

        if (! $user->canAccessWallet($wallet)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาสมัครสมาชิก Premium เพื่อเข้าถึงกระเป๋าเงินแชร์',
                    'requires_subscription' => true,
                ], 403);
            }

            return redirect()->route('wallets.index')
                ->with('error', 'กรุณาสมัครสมาชิก Premium เพื่อเข้าถึงกระเป๋าเงินแชร์');
        }

        $wallet->load(['transactions' => function ($query) {
            $query->with(['category', 'creator'])
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
    public function adjustments(Wallet $wallet): View|RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if (! $wallet->hasAccess($user)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่มีสิทธิ์เข้าถึงกระเป๋าเงินนี้',
                ], 403);
            }

            abort(403);
        }

        if (! $user->canAccessWallet($wallet)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาสมัครสมาชิก Premium เพื่อเข้าถึงกระเป๋าเงินแชร์',
                    'requires_subscription' => true,
                ], 403);
            }

            return redirect()->route('wallets.index')
                ->with('error', 'กรุณาสมัครสมาชิก Premium เพื่อเข้าถึงกระเป๋าเงินแชร์');
        }

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
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'uuid'],
        ]);

        $userIds = auth()->user()->wallets()->pluck('id')->toArray();

        $submittedIds = $data['ids'];

        if (count(array_diff($submittedIds, $userIds)) > 0) {
            abort(403);
        }

        foreach ($submittedIds as $index => $id) {
            Wallet::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'จัดเรียงกระเป๋าเงินเรียบร้อยแล้ว',
        ]);
    }

    protected function authorizeWallet(Wallet $wallet): void
    {
        abort_if(! $wallet->isOwner(auth()->user()), 403);
    }
}
