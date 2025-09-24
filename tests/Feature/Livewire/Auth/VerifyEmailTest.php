<?php

use App\Models\User;
use Livewire\Livewire;
use App\Livewire\Auth\VerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;

beforeEach(function () {
    Notification::fake();
});

test('verify email component renders', function () {
    $user = User::factory()->unverified()->create();
    
    $this->actingAs($user);
    
    $this->get(route('verification.notice'))
        ->assertStatus(200)
        ->assertSeeLivewire(VerifyEmail::class);
});

test('sends verification email for unverified users', function () {
    $user = User::factory()->unverified()->create();
    
    $this->actingAs($user);
    
    // Test the component
    $test = Livewire::test(VerifyEmail::class);
    
    // Call the method
    $test->call('sendVerification');
    
    // Verify the notification was sent to the user
    Notification::assertSentTo($user, VerifyEmailNotification::class);
    
    // Check if the component has the success status
    $test->assertSet('sent', true);
});

test('redirects if email already verified', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    
    $this->actingAs($user);
    
    Livewire::test(VerifyEmail::class)
        ->call('sendVerification')
        ->assertRedirect(route('dashboard'));
    
    Notification::assertNothingSent();
});

test('user can logout', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);
    $this->assertAuthenticatedAs($user);
    
    $this->post(route('logout'))
        ->assertRedirect('/');
    
    $this->assertGuest();
});
