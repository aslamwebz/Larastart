<?php

declare(strict_types=1);

use App\Livewire\Settings\Profile;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
});

test('resend verification sends notification for unverified user', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class, ['user' => $user])
        ->call('resendVerificationNotification');

    Notification::assertSentTo($user, VerifyEmail::class);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email_verified_at' => null,
    ]);
});

test('resend verification redirects if email already verified', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);

    $response = Livewire::test(Profile::class, ['user' => $user])
        ->call('resendVerificationNotification')
        ->assertRedirect(route('dashboard', absolute: false));

    Notification::assertNothingSent();

    $this->assertNotNull($user->fresh()->email_verified_at);
});
