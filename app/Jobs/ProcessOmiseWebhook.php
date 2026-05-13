<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\OmiseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessOmiseWebhook implements ShouldQueue
{
    use Queueable;

    private array $payload;

    private OmiseService $omise;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->omise = app(OmiseService::class);
    }

    public function handle(): void
    {
        $event = $this->omise->parseWebhookEvent($this->payload);

        match ($event['event']) {
            'charge.create' => $this->handleChargeCreate($event['data']),
            'charge.complete' => $this->handleChargeComplete($event['data']),
            'schedule.suspend' => $this->handleScheduleSuspend($event['data']),
            'schedule.expire' => $this->handleScheduleExpire($event['data']),
            'schedule.destroy' => $this->handleScheduleDestroy($event['data']),
            default => Log::info('Unhandled webhook event: '.$event['event']),
        };
    }

    private function handleChargeCreate(array $data): void
    {
        if (! isset($data['customer'])) {
            return;
        }

        $subscription = Subscription::where('omise_customer_id', $data['customer'])->first();
        if (! $subscription) {
            return;
        }

        Payment::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'omise_charge_id' => $data['id'],
            'amount' => $data['amount'] / 100,
            'currency' => $data['currency'],
            'status' => 'pending',
            'omise_response' => $data,
        ]);
    }

    private function handleChargeComplete(array $data): void
    {
        $payment = Payment::where('omise_charge_id', $data['id'])->first();
        if (! $payment) {
            return;
        }

        $status = $data['status'] === 'successful' ? 'successful' : 'failed';

        $payment->update([
            'status' => $status,
            'paid_at' => $status === 'successful' ? now() : null,
            'omise_response' => $data,
        ]);

        $subscription = $payment->subscription;
        if (! $subscription) {
            return;
        }

        if ($status === 'successful') {
            $subscription->update([
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);
        } else {
            $subscription->markAsPastDue();
        }
    }

    private function handleScheduleSuspend(array $data): void
    {
        $subscription = Subscription::where('omise_schedule_id', $data['id'])->first();
        if (! $subscription) {
            return;
        }

        $subscription->markAsPastDue();
    }

    private function handleScheduleExpire(array $data): void
    {
        $subscription = Subscription::where('omise_schedule_id', $data['id'])->first();
        if (! $subscription) {
            return;
        }

        $subscription->update([
            'status' => 'expired',
        ]);
    }

    private function handleScheduleDestroy(array $data): void
    {
        $subscription = Subscription::where('omise_schedule_id', $data['id'])->first();
        if (! $subscription) {
            return;
        }

        $subscription->markAsCanceled();
    }
}
