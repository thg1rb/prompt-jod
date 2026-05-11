<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

test('user can redirect to google oauth', function () {
    Socialite::shouldReceive('driver->redirect')
        ->once()
        ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    $response = $this->get('/auth/redirect');

    $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('unverified google email cannot login', function () {
    $googleUser = (object) [
        'id' => 'google_id_123',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'user' => ['email_verified' => false],
        'token' => 'access_token',
        'refreshToken' => 'refresh_token',
    ];

    Socialite::shouldReceive('driver->user')
        ->once()
        ->andReturn($googleUser);

    $response = $this->get('/auth/callback');

    $response->assertRedirect('/login');
    $response->assertSessionHas('error');
});

test('verified google email creates new user and logs in', function () {
    $googleUser = (object) [
        'id' => 'google_id_123',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'user' => ['email_verified' => true],
        'token' => 'access_token',
        'refreshToken' => 'refresh_token',
    ];

    Socialite::shouldReceive('driver->user')
        ->once()
        ->andReturn($googleUser);

    $response = $this->get('/auth/callback');

    $response->assertRedirect('/dashboard');

    $this->assertDatabaseHas('users', [
        'google_id' => 'google_id_123',
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);

    $user = User::where('google_id', 'google_id_123')->first();
    expect($user->google_token)->toBe('access_token');
    expect($user->google_refresh_token)->toBe('refresh_token');

    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->id)->toBe($user->id);
});

test('verified google email updates existing user and logs in', function () {
    $user = User::factory()->create([
        'google_id' => 'google_id_123',
        'email' => 'john@example.com',
        'name' => 'Old Name',
    ]);

    $googleUser = (object) [
        'id' => 'google_id_123',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'user' => ['email_verified' => true],
        'token' => 'new_access_token',
        'refreshToken' => 'new_refresh_token',
    ];

    Socialite::shouldReceive('driver->user')
        ->once()
        ->andReturn($googleUser);

    $response = $this->get('/auth/callback');

    $response->assertRedirect('/dashboard');

    $user->refresh();
    expect($user->name)->toBe('John Doe');
    expect($user->google_token)->toBe('new_access_token');
    expect($user->google_refresh_token)->toBe('new_refresh_token');
});

test('oauth tokens are encrypted', function () {
    $user = User::factory()->create([
        'google_token' => 'test_token',
        'google_refresh_token' => 'test_refresh',
    ]);

    $user->linkGoogleAccount('google_id_456', 'new_token', 'new_refresh');

    $user->refresh();

    $rawToken = $user->getRawOriginal('google_token');
    $rawRefresh = $user->getRawOriginal('google_refresh_token');

    expect($rawToken)->not->toBe('new_token');
    expect($rawRefresh)->not->toBe('new_refresh');
    expect($user->google_id)->toBe('google_id_456');
    expect($user->google_token)->toBe('new_token');
    expect($user->google_refresh_token)->toBe('new_refresh');
});

test('oauth tokens are hidden from json serialization', function () {
    $user = User::factory()->create([
        'google_id' => 'google_123',
        'google_token' => 'test_token',
        'google_refresh_token' => 'test_refresh',
    ]);

    $json = $user->toArray();

    expect($json)->not->toHaveKey('google_token');
    expect($json)->not->toHaveKey('google_refresh_token');
    expect($json)->toHaveKey('google_id');
    expect($json['google_id'])->toBe('google_123');
});
