<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'    => 'required|string|max:255',
                'company' => 'nullable|string|max:255',
                'phone'   => 'nullable|string|max:30',
                'email'   => 'required|email|max:255',
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:5000',
            ]);

            $message = ContactMessage::create([
                'user_id'    => $request->user()?->id,
                'name'       => $data['name'],
                'company'    => $data['company'] ?? null,
                'phone'      => $data['phone'] ?? null,
                'email'      => strtolower($data['email']),
                'subject'    => $data['subject'],
                'message'    => $data['message'],
                'status'     => 'new',
                'ip_address' => $request->ip(),
            ]);

            return $this->success([
                'id'         => $message->id,
                'created_at' => $message->created_at?->toIso8601String(),
            ], 'Your message has been sent successfully. We will get back to you soon.', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to send contact message.');
        }
    }
}
