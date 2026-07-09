<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Support\TransactionalMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => 'required|email|max:255',
                'name'  => 'nullable|string|max:255',
            ]);

            $email      = strtolower($data['email']);
            $subscriber = NewsletterSubscriber::where('email', $email)->first();

            if ($subscriber) {
                if ($subscriber->status === 'unsubscribed') {
                    $subscriber->update([
                        'status'          => 'active',
                        'subscribed_at'   => now(),
                        'unsubscribed_at' => null,
                    ]);
                }

                TransactionalMailer::send(
                    email: $subscriber->email,
                    name: $subscriber->name ?? $subscriber->email,
                    locale: 'ar',
                    key: 'reader.newsletter_subscribed',
                    replace: ['name' => $subscriber->name ?? $subscriber->email],
                );

                return $this->success($subscriber, 'Subscribed to newsletter successfully.');
            }

            $subscriber = NewsletterSubscriber::create([
                'email'         => $email,
                'name'          => $data['name'] ?? null,
                'user_id'       => $request->user()?->id,
                'status'        => 'active',
                'subscribed_at' => now(),
            ]);

            TransactionalMailer::send(
                email: $subscriber->email,
                name: $subscriber->name ?? $subscriber->email,
                locale: 'ar',
                key: 'reader.newsletter_subscribed',
                replace: ['name' => $subscriber->name ?? $subscriber->email],
            );

            return $this->success($subscriber, 'Subscribed to newsletter successfully.', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Newsletter subscription failed.');
        }
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
            ]);

            $subscriber = NewsletterSubscriber::where('email', strtolower($data['email']))->first();

            if (! $subscriber) {
                return $this->error(null, 'Email address is not subscribed.', 404);
            }

            $subscriber->update([
                'status'          => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);

            TransactionalMailer::send(
                email: $subscriber->email,
                name: $subscriber->name ?? $subscriber->email,
                locale: 'ar',
                key: 'reader.newsletter_unsubscribed',
                replace: ['name' => $subscriber->name ?? $subscriber->email],
            );

            return $this->success(null, 'Unsubscribed from newsletter successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Newsletter unsubscription failed.');
        }
    }
}
