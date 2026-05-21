<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptions = Subscription::with('user')->get();

        foreach ($subscriptions as $subscription) {
            $status = fake()->randomElement(['paid', 'paid', 'paid', 'pending', 'failed']);

            Payment::create([
                'user_id'            => $subscription->user_id,
                'subscription_id'    => $subscription->id,
                'amount'             => $subscription->package?->price ?? 4.99,
                'currency'           => 'USD',
                'provider'           => fake()->randomElement(['stripe', 'paypal', 'tap']),
                'provider_reference' => strtoupper(fake()->bothify('PAY-########')),
                'status'             => $status,
                'paid_at'            => $status === 'paid' ? $subscription->starts_at : null,
            ]);
        }
    }
}
