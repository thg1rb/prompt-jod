<?php

namespace App\Http\Controllers;

use App\Enums\WalletAccess;
use App\Http\Requests\SlipVerifyRequest;
use App\Http\Requests\TransactionStoreRequest;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\EasySlipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function __construct(
        private EasySlipService $easySlipService
    ) {}

    private function sharedWalletIds($user)
    {
        return $user->sharedWallets()->where('access_type', 'shared')->pluck('wallets.id')
            ->merge(
                $user->wallets()->where('access_type', 'shared')->pluck('id')
            )->unique();
    }

    public function index()
    {
        $user = Auth::user();

        return view('transactions', [
            'transactions' => $this->getTransactions($user),
            'categories' => $user->customCategories()->with('fixedCategory')->orderBy('name')->get(),
            'wallets' => $user->allWallets(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $user = Auth::user();
        $q = $request->input('q', '');
        $cat = $request->input('category', 'all');
        $wal = $request->input('wallet', 'all');
        $walType = $request->input('wallet_type', 'all');
        $txFilter = $request->input('transaction_filter', 'all');

        $query = Transaction::with(['category.fixedCategory', 'wallet', 'creator'])
            ->where(function ($query) use ($user, $walType, $txFilter) {
                if ($walType === 'personal') {
                    $query->where('user_id', $user->id)
                        ->whereHas('wallet', fn ($q) => $q->where('access_type', WalletAccess::Personal));
                } elseif ($walType === 'shared') {
                    $sharedWalletIds = $this->sharedWalletIds($user);
                    $query->whereIn('wallet_id', $sharedWalletIds);
                    if ($txFilter === 'shared') {
                        $query->where('user_id', $user->id);
                    }
                } else {
                    $sharedWalletIds = $this->sharedWalletIds($user);
                    $query->where(function ($q) use ($user, $sharedWalletIds) {
                        $q->where('user_id', $user->id)
                            ->orWhereIn('wallet_id', $sharedWalletIds);
                    });
                }
            })
            ->latest('transacted_at');

        if ($cat !== 'all') {
            $transactions->where('category_id', $cat);
        }

        if ($wal !== 'all') {
            $query->where('wallet_id', $wal);
        }

        if ($q) {
            $search = '%'.addcslashes(strtolower($q), '%_').'%';
            $query->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(recipient) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(note) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(sender) LIKE ?', [$search]);
            });
        }

        $transactions = $query->get();

        return response()->json([
            'transactions' => $transactions->map(fn ($t) => [
                'id' => $t->id,
                'description' => $t->recipient ?? $t->note ?? '-',
                'amount' => (float) $t->amount,
                'type' => $t->type->value,
                'category_id' => $t->category_id,
                'category' => $t->category?->fixedCategory?->name ?? '-',
                'category_icon' => $t->category?->fixedCategory?->icon ?? '📌',
                'category_color' => $t->category?->fixedCategory?->color ?? '#64748b',
                'wallet_id' => $t->wallet_id,
                'wallet' => $t->wallet?->name ?? '-',
                'wallet_access_type' => $t->wallet?->access_type?->value,
                'transacted_at' => $t->transacted_at->format('Y-m-d H:i:s'),
                'recipient' => $t->recipient,
                'created_by' => $t->created_by,
                'creator_name' => $t->creator?->name ?? null,
            ])->all(),
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $user = Auth::user();
        $q = $request->input('q', '');
        $cat = $request->input('category', 'all');
        $wal = $request->input('wallet', 'all');

        $sharedWalletIds = $this->sharedWalletIds($user);

        $transactions = Transaction::with(['category.fixedCategory', 'wallet'])
            ->where(function ($query) use ($sharedWalletIds, $user) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('wallet_id', $sharedWalletIds);
            })
            ->orderBy('transacted_at', 'desc');

        if ($cat !== 'all') {
            $transactions->where('category_id', $cat);
        }

        if ($wal !== 'all') {
            $transactions->where('wallet_id', $wal);
        }

        if ($q) {
            $search = '%'.addcslashes(strtolower($q), '%_').'%';
            $transactions->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(recipient) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(note) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(sender) LIKE ?', [$search]);
            });
        }

        $transactions = $transactions->get();

        $rows = [['วันเวลา', 'รายการ', 'หมวดหมู่', 'กระเป๋า', 'ประเภท', 'จำนวน']];
        foreach ($transactions as $t) {
            $rows[] = [
                $t->transacted_at->format('d/m/Y H:i'),
                $t->description ?? '-',
                $t->category?->fixedCategory?->name ?? '-',
                $t->wallet?->name ?? '-',
                $t->type->value === 'expense' ? 'รายจ่าย' : ($t->type->value === 'income' ? 'รายรับ' : 'ปรับ'),
                number_format((float) $t->amount, 2),
            ];
        }

        return response()->json(['rows' => $rows]);
    }

    public function create()
    {
        $user = Auth::user();

        return view('transactions.create', [
            'wallets' => $user->wallets()->orderBy('is_default', 'desc')->orderBy('name')->get(),
            'categories' => $user->customCategories()->with('fixedCategory')->orderBy('name')->get(),
        ]);
    }

    public function store(TransactionStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        $wallet = Wallet::find($validated['wallet_id']);

        if ($wallet && $wallet->access_type->value === 'shared' && ! $wallet->isOwner($user) && $user->isFree()) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาสมัครสมาชิก Premium เพื่อใช้งานธุรกรรมในกระเป๋าแชร์',
                'requires_subscription' => true,
            ], 403);
        }

        $transaction = $user->transactions()->create([
            'wallet_id' => $validated['wallet_id'],
            'category_id' => $validated['category_id'],
            'transaction_ref' => $validated['transaction_ref'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'sender' => $validated['sender'] ?? null,
            'recipient' => $validated['recipient'] ?? null,
            'note' => $validated['note'] ?? null,
            'transacted_at' => $validated['transacted_at'],
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'บันทึกธุรกรรมเรียบร้อย',
            'transaction' => [
                'id' => $transaction->id,
                'amount' => (float) $transaction->amount,
                'type' => $transaction->type->value,
                'transacted_at' => $transaction->transacted_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function verifySlip(SlipVerifyRequest $request): JsonResponse
    {
        if (Auth::user()->isFree()) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาสมัครสมาชิก Premium เพื่อใช้งาน OCR สลิป',
                'requires_subscription' => true,
            ], 403);
        }

        $image = $request->file('image');

        $result = $this->easySlipService->verifyBankSlip($image);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'slip' => [
                'amount' => $result['data']['amount'],
                'date' => $result['data']['date'],
                'sender_name' => $result['data']['sender_name'],
                'sender_bank' => $result['data']['sender_bank'],
                'receiver_name' => $result['data']['receiver_name'],
                'transaction_ref' => $result['data']['transaction_ref'],
                'ref1' => $result['data']['ref1'],
                'ref2' => $result['data']['ref2'],
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $transaction = $this->findAccessibleTransaction($user, $id);

        if (! $transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if (! $user->canAccessTransaction($transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาสมัครสมาชิก Premium เพื่อเข้าถึงธุรกรรมในกระเป๋าแชร์',
                'requires_subscription' => true,
            ], 403);
        }

        return response()->json([
            'transaction' => [
                'id' => $transaction->id,
                'wallet_id' => $transaction->wallet_id,
                'category_id' => $transaction->category_id,
                'type' => $transaction->type->value,
                'amount' => (float) $transaction->amount,
                'sender' => $transaction->sender,
                'recipient' => $transaction->recipient,
                'note' => $transaction->note,
                'transacted_at' => $transaction->transacted_at->format('Y-m-d\TH:i'),
                'created_by' => $transaction->created_by,
            ],
        ]);
    }

    public function update(TransactionStoreRequest $request, $id): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $transaction = $this->findAccessibleTransaction($user, $id);

        if (! $transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if (! $user->canAccessTransaction($transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาสมัครสมาชิก Premium เพื่อแก้ไขธุรกรรมในกระเป๋าแชร์',
                'requires_subscription' => true,
            ], 403);
        }

        $transaction->update([
            'wallet_id' => $validated['wallet_id'],
            'category_id' => $validated['category_id'],
            'transaction_ref' => $validated['transaction_ref'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'sender' => $validated['sender'],
            'recipient' => $validated['recipient'],
            'note' => $validated['note'],
            'transacted_at' => $validated['transacted_at'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'แก้ไขธุรกรรมเรียบร้อย',
            'transaction' => [
                'id' => $transaction->id,
                'amount' => (float) $transaction->amount,
                'type' => $transaction->type->value,
                'transacted_at' => $transaction->transacted_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $transaction = $this->findAccessibleTransaction($user, $id);

        if (! $transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if (! $user->canAccessTransaction($transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาสมัครสมาชิก Premium เพื่อลบธุรกรรมในกระเป๋าแชร์',
                'requires_subscription' => true,
            ], 403);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบธุรกรรมเรียบร้อย',
        ]);
    }

    private function findAccessibleTransaction($user, string $id)
    {
        $sharedWalletIds = $this->sharedWalletIds($user);

        return Transaction::where(function ($query) use ($user, $sharedWalletIds) {
            $query->where('user_id', $user->id)
                ->orWhereIn('wallet_id', $sharedWalletIds);
        })->find($id);
    }

    public function searchNames(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'nullable|string',
            'field' => 'required|in:sender,recipient',
        ]);

        $user = Auth::user();
        $query = $request->input('q', '');
        $field = $request->input('field');

        $transactionQuery = $user->transactions()
            ->whereNotNull($field);

        if (! empty($query)) {
            $transactionQuery->where($field, 'like', '%'.$query.'%');
        }

        $names = $transactionQuery
            ->orderByDesc('transacted_at')
            ->limit(10)
            ->pluck($field)
            ->unique()
            ->values()
            ->filter(fn ($name) => ! empty($name))
            ->take(8)
            ->values();

        return response()->json([
            'names' => $names,
        ]);
    }

    private function getTransactions($user)
    {
        $sharedWalletIds = $this->sharedWalletIds($user);

        return Transaction::with(['category.fixedCategory', 'wallet', 'creator'])
            ->where(function ($query) use ($sharedWalletIds, $user) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('wallet_id', $sharedWalletIds);
            })
            ->latest('transacted_at')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'description' => $t->recipient ?? $t->note ?? '-',
                'amount' => (float) $t->amount,
                'type' => $t->type->value,
                'category_id' => $t->category_id,
                'category' => $t->category?->fixedCategory?->name ?? '-',
                'category_icon' => $t->category?->fixedCategory?->icon ?? '📌',
                'category_color' => $t->category?->fixedCategory?->color ?? '#64748b',
                'wallet_id' => $t->wallet_id,
                'wallet' => $t->wallet?->name ?? '-',
                'wallet_access_type' => $t->wallet?->access_type?->value,
                'transacted_at' => $t->transacted_at->format('Y-m-d H:i:s'),
                'recipient' => $t->recipient,
                'created_by' => $t->created_by,
                'creator_name' => $t->creator?->name ?? null,
            ])
            ->all();
    }
}
