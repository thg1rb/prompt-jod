<x-app-layout>
    @php
        $subscriptionJson = $subscription ? json_encode([
            'id' => $subscription->id,
            'status' => $subscription->status->value,
            'status_label' => $subscription->status_label,
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
            'billing_day' => $subscription->billing_day,
            'current_period_start' => $subscription->current_period_start?->toIso8601String(),
            'current_period_end' => $subscription->current_period_end?->toIso8601String(),
        ]) : 'null';
        $paymentsJson = $payments->map(fn ($p) => [
            'id' => $p->id,
            'amount' => $p->amount,
            'status' => $p->status,
            'paid_at' => $p->paid_at?->toIso8601String(),
        ])->toJson();
    @endphp

    <div x-data="subscription({{ $subscriptionJson }}, {{ $paymentsJson }})"
         @subscribe="handleSubscribe($event.detail.token)"
         @close-card-form="showCardForm = false"
         class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div>
                <h1 class="text-2xl font-bold">การสมัครสมาชิก</h1>
                <p class="text-text-muted text-sm">จัดการแผนบัญชีเรียบร้อยของคุณ</p>
            </div>

            <template x-if="subscription && subscription.status !== 'canceled'">
                <div class="bg-card border border-border rounded-lg p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': subscription.status === 'active',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': subscription.status === 'past_due',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100': subscription.status === 'canceled' || subscription.status === 'expired'
                                    }"
                                    x-text="getStatusLabel(subscription.status)">
                                </span>
                                <span class="text-sm text-text-muted">PromptJod Premium</span>
                            </div>
                            <p class="text-3xl font-bold">฿99.00<span class="text-sm font-normal text-text-muted">/เดือน</span></p>
                            <p class="text-sm text-text-muted mt-1">
                                วันที่เรียกเงิน: ทุกวันที่ <span x-text="subscription.billing_day"></span>
                            </p>
                            <p class="text-sm text-text-muted">
                                รอบบวงถัดไป: <span x-text="formatDate(subscription.current_period_end)"></span>
                            </p>
                        </div>
                        <button @click="cancelSubscription"
                                class="px-4 py-2 bg-destructive text-destructive-foreground rounded-lg text-sm font-medium hover:bg-destructive/90 transition-colors">
                            ยกเลิกการสมัครสมาชิก
                        </button>
                    </div>

                    <div class="mt-6 pt-6 border-t border-border">
                        <h3 class="text-lg font-semibold mb-4">ประวัติการชำระเงิน</h3>
                        <div class="space-y-3">
                            <template x-for="payment in payments" :key="payment.id">
                                <div class="flex items-center justify-between py-2 border-b border-border last:border-0">
                                    <div>
                                        <p class="font-medium">฿<span x-text="formatAmount(payment.amount)"></span></p>
                                        <p class="text-xs text-text-muted" x-text="formatDate(payment.paid_at)"></p>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                          :class="{
                                              'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': payment.status === 'successful',
                                              'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': payment.status === 'failed',
                                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': payment.status === 'pending'
                                          }"
                                          x-text="getPaymentStatusLabel(payment.status)">
                                    </span>
                                </div>
                            </template>
                            <div x-show="payments.length === 0" class="text-center py-8 text-text-muted">
                                ยังไม่มีประวัติการชำระเงิน
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="!subscription">
                <div class="space-y-6">
                    <div class="bg-card border border-border rounded-lg p-8 text-center">
                        <div class="max-w-md mx-auto">
                            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold mb-2">เริ่มใช้งาน PromptJod Premium</h2>
                            <p class="text-text-muted mb-6">
                                เข้าถึงฟีเจอร์พิเศษทั้งหมด เพียง฿99.00/เดือน
                            </p>
                            <button @click="showCardForm = true"
                                    class="w-full px-6 py-3 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors">
                                สมัครสมาชิกเลย
                            </button>
                        </div>
                    </div>

                    <div x-show="showCardForm" x-transition>
                        @include('subscription.partials.card-form')
                    </div>
                </div>
            </template>

            <template x-if="subscription && subscription.status === 'canceled'">
                <div class="space-y-6">
                    <div class="bg-card border border-border rounded-lg p-6 text-center">
                        <h3 class="text-lg font-semibold mb-2">การสมัครสมาชิกของคุณถูกยกเลิกแล้ว</h3>
                        <p class="text-text-muted mb-4">คุณสามารถเริ่มใช้งานได้อีกครั้งได้ตลอดเวลา</p>
                        <button @click="resumeSubscription"
                                class="px-6 py-3 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors">
                            เริ่มใช้งานการสมัครสมาชิก
                        </button>
                    </div>

                    <div x-show="showCardForm" x-transition>
                        @include('subscription.partials.card-form')
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-app-layout>
