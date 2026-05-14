<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSubscribed;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnsureSubscribedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(EnsureSubscribed::class)->get('/test-protected', fn () => response('ok'));
    }

    #[Test]
    public function guest_is_redirected_to_subscription(): void
    {
        $response = $this->get('/test-protected');

        $response->assertRedirect();
    }

    #[Test]
    public function free_user_is_redirected_to_subscription(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/test-protected');

        $response->assertRedirect();
    }

    #[Test]
    public function premium_user_can_access(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->active()->create();
        $this->actingAs($user);

        $response = $this->get('/test-protected');

        $response->assertOk();
        $response->assertSee('ok');
    }
}
