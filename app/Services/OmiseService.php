<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OmiseCharge;
use OmiseCustomer;
use OmiseSchedule;

class OmiseService
{
    private string $publicKey;

    private string $secretKey;

    private string $apiVersion;

    private string $webhookSecret;

    public function __construct()
    {
        $this->publicKey = config('services.omise.public_key');
        $this->secretKey = config('services.omise.secret_key');
        $this->apiVersion = config('services.omise.api_version', '2019-05-29');
        $this->webhookSecret = config('services.omise.webhook_secret');
    }

    /**
     * Create an Omise customer with a card token.
     */
    public function createCustomer(string $email, string $description, string $token): array
    {
        try {
            $customer = OmiseCustomer::create([
                'email' => $email,
                'description' => $description,
                'card' => $token,
            ]);

            $cardId = $customer->cards->data[0]['id'] ?? null;
            $defaultCardId = $customer->default_card ?? null;

            return [
                'success' => true,
                'customer_id' => $customer['id'],
                'card_id' => $cardId,
                'default_card_id' => $defaultCardId,
                'customer' => $customer,
            ];
        } catch (\Exception $e) {
            Log::error('Omise Customer Creation Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a charge schedule (recurring payment).
     */
    public function createChargeSchedule(
        string $customerId,
        ?string $cardId,
        int $amount,
        int $billingDay,
        string $description,
        ?string $endDate = null
    ): array {
        try {
            $chargeParams = [
                'customer' => $customerId,
                'amount' => $amount,
                'description' => $description,
            ];

            if ($cardId) {
                $chargeParams['card'] = $cardId;
            }

            $scheduler = OmiseCharge::schedule($chargeParams);

            $scheduler = $scheduler->every(1)
                ->months([$billingDay])
                ->startDate(now()->format('Y-m-d'));

            if ($endDate) {
                $scheduler = $scheduler->endDate($endDate);
            } else {
                $scheduler = $scheduler->endDate(now()->addYears(5)->format('Y-m-d'));
            }

            $schedule = $scheduler->start();

            return [
                'success' => true,
                'schedule_id' => $schedule['id'],
                'status' => $schedule['status'],
                'next_occurrences_on' => $schedule['next_occurrences_on'] ?? [],
                'schedule' => $schedule,
            ];
        } catch (\Exception $e) {
            Log::error('Omise Schedule Creation Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve a schedule by ID.
     */
    public function retrieveSchedule(string $scheduleId): array
    {
        try {
            $schedule = OmiseSchedule::retrieve($scheduleId);

            return [
                'success' => true,
                'schedule' => $schedule,
                'status' => $schedule['status'],
            ];
        } catch (\Exception $e) {
            Log::error('Omise Schedule Retrieval Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Destroy a schedule by ID.
     */
    public function destroySchedule(string $scheduleId): array
    {
        try {
            $schedule = OmiseSchedule::retrieve($scheduleId);
            $schedule->destroy();

            return [
                'success' => true,
                'deleted' => $schedule['deleted'],
            ];
        } catch (\Exception $e) {
            Log::error('Omise Schedule Destruction Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update customer card.
     */
    public function updateCustomerCard(string $customerId, string $token): array
    {
        try {
            $customer = OmiseCustomer::retrieve($customerId);
            $customer->update(['card' => $token]);

            $cardId = $customer->cards->data[0]['id'] ?? null;
            $defaultCardId = $customer->default_card ?? null;

            return [
                'success' => true,
                'card_id' => $cardId,
                'default_card_id' => $defaultCardId,
                'customer' => $customer,
            ];
        } catch (\Exception $e) {
            Log::error('Omise Customer Card Update Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve customer cards.
     */
    public function retrieveCustomerCards(string $customerId): array
    {
        try {
            $customer = OmiseCustomer::retrieve($customerId);

            return [
                'success' => true,
                'cards' => $customer->cards->data ?? [],
                'default_card' => $customer->default_card ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Omise Customer Cards Retrieval Error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $timestamp): bool
    {
        if (empty($this->webhookSecret)) {
            Log::error('Webhook secret not configured, rejecting webhook request');

            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, base64_decode($this->webhookSecret));

        $signatures = explode(',', $signature);

        foreach ($signatures as $sig) {
            if (hash_equals($expectedSignature, $sig)) {
                return true;
            }
        }

        Log::warning('Webhook signature verification failed');

        return false;
    }

    /**
     * Parse webhook event.
     */
    public function parseWebhookEvent(array $payload): array
    {
        return [
            'event' => $payload['key'] ?? null,
            'data' => $payload['data'] ?? null,
            'object' => $payload['data']['object'] ?? null,
        ];
    }
}
