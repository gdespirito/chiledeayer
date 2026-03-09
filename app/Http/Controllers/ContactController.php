<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Contact');
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $message = ContactMessage::create($request->validated());

        $adminEmails = User::where('is_admin', true)->pluck('email');

        foreach ($adminEmails as $email) {
            Mail::to($email)->queue(new ContactMessageReceived($message));
        }

        Mail::to($message->email)->queue(new ContactMessageConfirmation($message));

        return back()->with('success', '¡Mensaje enviado! Te responderemos lo antes posible.');
    }
}
