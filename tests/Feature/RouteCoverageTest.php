<?php

use App\Models\User;
use Illuminate\Support\Str;

describe('Auth Routes Coverage', function () {
    test('guest can access register page', function () {
        $response = $this->get('/register');

        $response->assertStatus(200);
    });

    test('guest can access forgot password page', function () {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    });

    test('guest can access reset password page with valid token', function () {
        $user = User::factory()->create();

        $response = $this->get(route('password.reset', [
            'token' => Str::random(64),
            'email' => $user->email,
        ]));

        $response->assertStatus(200);
    });

    test('authenticated user can access confirm password page', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
    });

    test('authenticated user can access verify email page', function () {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    });

    test('authenticated user can resend verification notification', function () {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertStatus(302);
    });
});

describe('Web Routes Coverage', function () {
    test('root route redirects guest to login', function () {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    });

    test('root route redirects authenticated user to dashboard', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/dashboard');
    });

    test('authenticated user can access terms page', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/terms');

        $response->assertStatus(200);
    });

    test('authenticated user can access privacy page', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/privacy');

        $response->assertStatus(200);
    });

    test('subscription page redirects to profile', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subscription');

        $response->assertRedirect(route('profile.edit'));
    });

    test('guest cannot access dashboard', function () {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    });

    test('verified user can access dashboard', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    });

    test('guest cannot access wallets index', function () {
        $response = $this->get('/wallets');

        $response->assertRedirect('/login');
    });

    test('guest cannot access categories index', function () {
        $response = $this->get('/categories');

        $response->assertRedirect('/login');
    });

    test('guest cannot access transactions index', function () {
        $response = $this->get('/transactions');

        $response->assertRedirect('/login');
    });

    test('guest cannot access profile', function () {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    });
});
