<?php

use App\Models\User;
use Livewire\Livewire;
use App\Livewire\Auth\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

test('reset password screen can be rendered', function () {
    $user = User::factory()->create();
    
    $token = Password::createToken($user);
    
    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertStatus(200)
        ->assertSeeLivewire(ResetPassword::class);
});

test('can reset password with valid token', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);
    
    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('resetPassword')
        ->assertRedirect(route('login'));
    
    $this->assertTrue(Auth::attempt([
        'email' => $user->email,
        'password' => 'new-password',
    ]));
});

test('validates required fields', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);
    
    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', '')
        ->set('password', '')
        ->call('resetPassword')
        ->assertHasErrors(['email', 'password']);
});

test('validates password confirmation', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);
    
    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', $user->email)
        ->set('password', 'password')
        ->set('password_confirmation', 'wrong-password')
        ->call('resetPassword')
        ->assertHasErrors('password');
});

test('shows error for invalid token', function () {
    $user = User::factory()->create();
    $invalidToken = 'invalid-token';
    
    Livewire::test(ResetPassword::class, ['token' => $invalidToken])
        ->set('email', $user->email)
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('resetPassword')
        ->assertHasErrors('email');
});

test('validates password strength', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);
    
    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', $user->email)
        ->set('password', '123')
        ->set('password_confirmation', '123')
        ->call('resetPassword')
        ->assertHasErrors('password');
});
