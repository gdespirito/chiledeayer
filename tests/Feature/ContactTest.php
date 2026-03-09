<?php

use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('displays the contact form', function () {
    $this->get('/contacto')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Contact'));
});

it('stores a contact message and sends emails', function () {
    Mail::fake();

    $admin = User::factory()->create(['is_admin' => true]);

    $this->post('/contacto', [
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'phone' => '+56 9 1234 5678',
        'subject' => 'Consulta',
        'body' => 'Hola, quiero saber más sobre el proyecto.',
    ])->assertRedirect();

    expect(ContactMessage::count())->toBe(1);

    $message = ContactMessage::first();
    expect($message->name)->toBe('Juan Pérez');
    expect($message->email)->toBe('juan@example.com');
    expect($message->phone)->toBe('+56 9 1234 5678');
    expect($message->subject)->toBe('Consulta');

    Mail::assertQueued(ContactMessageReceived::class, fn ($mail) => $mail->hasTo($admin->email));

    Mail::assertQueued(ContactMessageConfirmation::class, fn ($mail) => $mail->hasTo('juan@example.com'));
});

it('validates required fields', function () {
    $this->post('/contacto', [])
        ->assertSessionHasErrors(['name', 'email', 'subject', 'body']);
});

it('allows phone to be optional', function () {
    Mail::fake();

    User::factory()->create(['is_admin' => true]);

    $this->post('/contacto', [
        'name' => 'María',
        'email' => 'maria@example.com',
        'subject' => 'Sugerencia',
        'body' => 'Me encanta el proyecto.',
    ])->assertRedirect();

    expect(ContactMessage::count())->toBe(1);
    expect(ContactMessage::first()->phone)->toBeNull();
});
