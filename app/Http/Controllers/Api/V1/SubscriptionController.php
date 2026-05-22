<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SubscriptionController extends Controller
{
    public function packages(): JsonResponse
    {
        try {
            $packages = SubscriptionPackage::where('is_active', true)->orderBy('price')->get();

            return $this->success($packages, 'Packages retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve packages.');
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $subscriptions = Subscription::with('package')
                ->where('user_id', $request->user()->id)
                ->orderByDesc('created_at')
                ->get();

            return $this->success($subscriptions, 'Subscriptions retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve subscriptions.');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'package_id' => 'required|exists:subscription_packages,id',
                'plan'       => 'required|string|in:monthly,yearly,lifetime',
            ]);

            $package = SubscriptionPackage::find($data['package_id']);

            $subscription = Subscription::create([
                'user_id'    => $request->user()->id,
                'package_id' => $package->id,
                'plan'       => $data['plan'],
                'starts_at'  => now(),
                'ends_at'    => match ($data['plan']) {
                    'monthly'  => now()->addMonth(),
                    'yearly'   => now()->addYear(),
                    'lifetime' => null,
                },
                'status' => 'active',
            ]);

            return $this->success($subscription->load('package'), 'Subscribed successfully.', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Subscription failed.');
        }
    }
}
