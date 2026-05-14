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
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-5">
            <!-- Page Header -->
            <div>
                <h1 class="text-[22px] font-bold tracking-tight">การสมัครสมาชิก</h1>
                <p class="text-text-muted text-sm">จัดการแผนบัญชีเรียบร้อยของคุณ</p>
            </div>

            <template x-if="subscription && subscription.status !== 'canceled'">
                <div class="bg-card rounded-2xl border border-border overflow-hidden shadow-card">
                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold"
                                        :class="{
                                            'bg-success-light text-success': subscription.status === 'active',
                                            'bg-warning-light text-warning': subscription.status === 'past_due',
                                            'bg-surface-subtle text-text-muted': subscription.status === 'canceled' || subscription.status === 'expired'
                                        }"
                                        x-text="getStatusLabel(subscription.status)">
                                    </span>
                                    <span class="text-sm text-text-muted font-medium">PromptJod Premium</span>
                                </div>
                                <p class="text-3xl font-bold tracking-tight">฿99.00<span class="text-sm font-normal text-text-muted">/เดือน</span></p>
                                <p class="text-sm text-text-muted mt-1">
                                    วันที่เรียกเงิน: ทุกวันที่ <span x-text="subscription.billing_day"></span>
                                </p>
                                <p class="text-sm text-text-muted">
                                    รอบบวงถัดไป: <span x-text="formatDate(subscription.current_period_end)"></span>
                                </p>
                            </div>
                            <button @click="cancelSubscription"
                                    class="px-4 py-2.5 bg-destructive text-destructive-foreground rounded-xl text-sm font-semibold hover:bg-destructive/90 transition-colors shadow-sm active:scale-[0.98]">
                                ยกเลิกการสมัครสมาชิก
                            </button>
                        </div>

                        <div class="pt-5 border-t border-border">
                            <h3 class="font-semibold text-[15px] mb-4">ประวัติการชำระเงิน</h3>
                            <div class="space-y-3">
                                <template x-for="payment in payments" :key="payment.id">
                                    <div class="flex items-center justify-between py-2 border-b border-border last:border-0">
                                        <div>
                                            <p class="font-semibold text-[14px]">฿<span x-text="formatAmount(payment.amount)"></span></p>
                                            <p class="text-xs text-text-muted" x-text="formatDate(payment.paid_at)"></p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold"
                                              :class="{
                                                  'bg-success-light text-success': payment.status === 'successful',
                                                  'bg-destructive-light text-destructive': payment.status === 'failed',
                                                  'bg-warning-light text-warning': payment.status === 'pending'
                                              }"
                                              x-text="getPaymentStatusLabel(payment.status)">
                                        </span>
                                    </div>
                                </template>
                                <div x-show="payments.length === 0" class="text-center py-6 text-text-muted text-sm">
                                    ยังไม่มีประวัติการชำระเงิน
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="!subscription">
                <div class="space-y-5">
                    <div class="bg-card rounded-2xl border border-border p-8 text-center shadow-card">
                        <div class="max-w-md mx-auto">
                            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-foreground tracking-tight mb-2">เริ่มใช้งาน PromptJod Premium</h2>
                            <p class="text-text-muted mb-6">
                                เข้าถึงฟีเจอร์พิเศษทั้งหมด เพียง฿99.00/เดือน
                            </p>
                            <button @click="showCardForm = true"
                                    class="w-full px-6 py-3 bg-primary text-primary-foreground rounded-xl font-semibold text-sm hover:bg-primary-hover active:scale-[0.98] transition-all shadow-sm">
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
                <div class="space-y-5">
                    <div class="bg-card rounded-2xl border border-border p-6 text-center shadow-card">
                        <h3 class="text-[16px] font-semibold text-foreground mb-2">การสมัครสมาชิกของคุณถูกยกเลิกแล้ว</h3>
                        <p class="text-text-muted text-sm mb-4">คุณสามารถเริ่มใช้งานได้อีกครั้งได้ตลอดเวลา</p>
                        <button @click="resumeSubscription"
                                class="px-6 py-3 bg-primary text-primary-foreground rounded-xl font-semibold text-sm hover:bg-primary-hover active:scale-[0.98] transition-all shadow-sm">
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