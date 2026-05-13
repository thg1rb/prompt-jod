<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionStoreRequest;
use App\Http\Requests\SubscriptionUpdateCardRequest;
use App\Models\Subscription;
use App\Services\OmiseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    private array $plans = [
        'monthly' => ['amount' => 9900, 'label' => 'รายเดือน'],
        'yearly' => ['amount' => 99900, 'label' => 'รายปี'],
    ];

    private string $subscriptionCurrency = 'THB';

    public function __construct(private OmiseService $omise) {}

    public function store(SubscriptionStoreRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->subscription) {
            return response()->json([
                'success' => false,
                'message' => 'คุณมีการสมัครสมาชิกอยู่แล้ว',
            ], 400);
        }

        $plan = $request->plan;
        $amount = $this->plans[$plan]['amount'];

        $customerResult = $this->omise->createCustomer(
            $user->email,
            "Subscription for user {$user->id}",
            $request->omise_token
        );

        if (! $customerResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถสร้างข้อมูลลูกค้าได้',
                'error' => $customerResult['error'],
            ], 500);
        }

        $billingDay = now()->day;
        $scheduleResult = $this->omise->createChargeSchedule(
            $customerResult['customer_id'],
            $customerResult['card_id'],
            $amount,
            $billingDay,
            'PromptJod Premium Subscription'
        );

        if (! $scheduleResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถสร้างตารางการชำระเงินได้',
                'error' => $scheduleResult['error'],
            ], 500);
        }

        $periodEnd = $plan === 'yearly' ? now()->addYear() : now()->addMonth();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'omise_customer_id' => $customerResult['customer_id'],
            'omise_card_id' => $customerResult['card_id'],
            'omise_default_card_id' => $customerResult['default_card_id'],
            'omise_schedule_id' => $scheduleResult['schedule_id'],
            'status' => 'active',
            'plan' => $plan,
            'amount' => $amount / 100,
            'currency' => $this->subscriptionCurrency,
            'billing_day' => $billingDay,
            'current_period_start' => now(),
            'current_period_end' => $periodEnd,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'สมัครสมาชิกสำเร็จ',
            'subscription' => $subscription,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $subscription = auth()->user()->subscription;

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลการสมัครสมาชิก',
            ], 404);
        }

        if ($subscription->omise_schedule_id) {
            $result = $this->omise->destroySchedule($subscription->omise_schedule_id);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถยกเลิกการสมัครสมาชิกได้',
                    'error' => $result['error'],
                ], 500);
            }
        }

        $subscription->markAsCanceled();

        return response()->json([
            'success' => true,
            'message' => 'ยกเลิกการสมัครสมาชิกสำเร็จ',
        ]);
    }

    public function updateCard(SubscriptionUpdateCardRequest $request): JsonResponse
    {
        $subscription = auth()->user()->subscription;

        if (! $subscription || ! $subscription->omise_customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลการสมัครสมาชิก',
            ], 404);
        }

        $result = $this->omise->updateCustomerCard(
            $subscription->omise_customer_id,
            $request->omise_token
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถอัปเดตบัตรเครดิตได้',
                'error' => $result['error'],
            ], 500);
        }

        $subscription->update([
            'omise_card_id' => $result['card_id'],
            'omise_default_card_id' => $result['default_card_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตบัตรเครดิตสำเร็จ',
        ]);
    }

    public function resume(Request $request): JsonResponse
    {
        $subscription = auth()->user()->subscription;

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลการสมัครสมาชิก',
            ], 404);
        }

        if ($subscription->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'การสมัครสมาชิกของคุณยังคงใช้งานอยู่',
            ], 400);
        }

        $plan = $subscription->plan ?? 'monthly';
        $amount = $this->plans[$plan]['amount'];
        $billingDay = now()->day;
        $periodEnd = $plan === 'yearly' ? now()->addYear() : now()->addMonth();

        $scheduleResult = $this->omise->createChargeSchedule(
            $subscription->omise_customer_id,
            $subscription->omise_card_id,
            $amount,
            $billingDay,
            'PromptJod Premium Subscription'
        );

        if (! $scheduleResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถสร้างตารางการชำระเงินได้',
                'error' => $scheduleResult['error'],
            ], 500);
        }

        $subscription->update([
            'omise_schedule_id' => $scheduleResult['schedule_id'],
            'status' => 'active',
            'billing_day' => $billingDay,
            'current_period_start' => now(),
            'current_period_end' => $periodEnd,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'เริ่มใช้งานการสมัครสมาชิกสำเร็จ',
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $payments = auth()->user()
            ->payments()
            ->with('subscription')
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json($payments);
    }
}
