<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AdResource;
use App\Models\Ad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AdController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'placement'    => 'nullable|in:' . implode(',', Ad::PLACEMENTS),
                'ad_category'  => 'nullable|string|max:100',
                'limit'        => 'nullable|integer|min:1|max:20',
            ]);

            $query = Ad::query()->active()->orderByDesc('starts_at')->orderByDesc('id');

            if ($request->filled('placement')) {
                $query->where('placement', $request->input('placement'));
            }

            if ($request->filled('ad_category')) {
                $query->where('ad_category', $request->input('ad_category'));
            }

            $ads = $query
                ->limit((int) $request->input('limit', 10))
                ->get();

            return $this->success(
                AdResource::collection($ads)->resolve(),
                'Ads retrieved successfully.'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->errors(), 'Validation failed.', 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve ads.');
        }
    }
}
