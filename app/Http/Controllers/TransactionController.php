<?php

namespace App\Http\Controllers;

use App\Http\Requests\SlipVerifyRequest;
use App\Http\Requests\TransactionStoreRequest;
use App\Services\EasySlipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function __construct(
        private EasySlipService $easySlipService
    ) {}

    public function index()
    {
        $user = Auth::user();

        return view('transactions', [
            'transactions' => $this->getTransactions($user),
            'categories' => $user->categories()->orderBy('name')->get(),
            'wallets' => $user->wallets()->orderBy('name')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $user = Auth::user();
        $q = $request->input('q', '');
        $cat = $request->input('category', 'all');
        $wal = $request->input('wallet', 'all');

        $transactions = $user->transactions()
            ->with(['category', 'wallet'])
            ->latest('transacted_at');

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

        return response()->json([
            'transactions' => $transactions->map(fn ($t) => [
                'id' => $t->id,
                'description' => $t->recipient ?? $t->note ?? '-',
                'amount' => (float) $t->amount,
                'type' => $t->type->value,
                'category_id' => $t->category_id,
                'category' => $t->category?->name ?? '-',
                'category_icon' => $t->category?->icon ?? '📌',
                'wallet_id' => $t->wallet_id,
                'wallet' => $t->wallet?->name ?? '-',
                'transacted_at' => $t->transacted_at->format('Y-m-d H:i:s'),
                'recipient' => $t->recipient,
            ])->all(),
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $user = Auth::user();
        $q = $request->input('q', '');
        $cat = $request->input('category', 'all');
        $wal = $request->input('wallet', 'all');

        $transactions = $user->transactions()
            ->with(['category', 'wallet'])
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
                $t->category?->name ?? '-',
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
            'categories' => $user->categories()->orderBy('name')->get(),
        ]);
    }

    public function store(TransactionStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        $transaction = $user->transactions()->create([
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
        $transaction = $user->transactions()
            ->with(['category', 'wallet'])
            ->findOrFail($id);

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
            ],
        ]);
    }

    public function update(TransactionStoreRequest $request, $id): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $transaction = $user->transactions()->findOrFail($id);

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
        $transaction = $user->transactions()->findOrFail($id);
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบธุรกรรมเรียบร้อย',
        ]);
    }

    private function getTransactions($user)
    {
        return $user->transactions()
            ->with(['category', 'wallet'])
            ->latest('transacted_at')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'description' => $t->recipient ?? $t->note ?? '-',
                'amount' => (float) $t->amount,
                'type' => $t->type->value,
                'category_id' => $t->category_id,
                'category' => $t->category?->name ?? '-',
                'category_icon' => $t->category?->icon ?? '📌',
                'wallet_id' => $t->wallet_id,
                'wallet' => $t->wallet?->name ?? '-',
                'transacted_at' => $t->transacted_at->format('Y-m-d H:i:s'),
                'recipient' => $t->recipient,
            ])
            ->all();
    }
}
