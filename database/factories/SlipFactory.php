<?php

namespace Database\Factories;

use App\Enums\SlipStatus;
use App\Enums\VerificationStatus;
use App\Models\Slip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlipFactory extends Factory
{
    protected $model = Slip::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ocr_status' => SlipStatus::from(fake()->randomElement(['pending', 'processing', 'done', 'failed'])),
            'ocr_raw_text' => fake()->text(),
            'transaction_ref' => fake()->numerify('##########'),
            'sender' => fake()->name(),
            'recipient' => fake()->name(),
            'bank' => fake()->randomElement(['KBANK', 'SCB', 'KTB', 'BBL']),
            'amount' => fake()->randomFloat(2, 1, 100000),
            'transferred_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'verification_status' => VerificationStatus::from(fake()->randomElement(['verified', 'unverified', 'mismatch'])),
            'is_duplicate' => false,
            'duplicate_of' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'ocr_status' => SlipStatus::Pending,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'ocr_status' => SlipStatus::Processing,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'ocr_status' => SlipStatus::Done,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'ocr_status' => SlipStatus::Failed,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VerificationStatus::Verified,
        ]);
    }

    public function duplicate(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_duplicate' => true,
        ]);
    }
}
