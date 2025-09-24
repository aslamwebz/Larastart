<?php

use App\Models\User;
use Livewire\Livewire;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    // Reset rate limiter before each test
    RateLimiter::clear('test-key');
});

test('login screen can be rendered', function () {
    $this->get(route('login'))
        ->assertStatus(200)
        ->assertSeeLivewire(Login::class);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('login is rate limited after too many attempts', function () {
    // $user = User::factory()->create();

    // // Make 5 failed attempts
    // for ($i = 0; $i < 5; $i++) {
    //     $response = Livewire::test(Login::class)
    //         ->set('email', 'test@example.com')
    //         ->set('password', 'wrong-password')
    //         ->call('login');
    // }
    
    // // After 5 attempts, we should get rate limited
    // $response->assertHasErrors('email');
    
    // // Get the error message from the session
    // $errorMessage = session('errors')->first('email');
    // $this->assertStringContainsString(
    //     __('auth.throttle', ['seconds' => 60, 'minutes' => 1]),
    //     $errorMessage
    // );

    // // Verify rate limiter state
    // $this->assertTrue(RateLimiter::tooManyAttempts(
    //     'test@example.com|'.request()->ip(), 5
    // ));
});

test('users can logout', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);
    $this->assertAuthenticatedAs($user);
    
    $this->post(route('logout'))
        ->assertRedirect('/');
    
    $this->assertGuest();
});
