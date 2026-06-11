<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\UpdateUserProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangePasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Contributor;
use App\Models\Reader;
use App\Models\User;
use App\Models\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $user = User::create([
                'name'        => trim((string) $data['name']),
                'email'       => strtolower((string) $data['email']),
                'password'    => Hash::make((string) $data['password']),
                'locale'      => $data['locale'] ?? 'ar',
                'country'     => $data['country'] ?? null,
                'language'    => $data['language'] ?? null,
                'is_active'   => true,
                'is_verified' => false,
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            match ($data['type']) {
                'contributor' => tap(Contributor::create([
                    'user_id'        => $user->id,
                    'bio'            => $data['bio'] ?? null,
                    'portfolio_link' => $data['portfolio_link'] ?? null,
                    'profile_photo'  => $data['profile_photo'],
                ]), function (Contributor $contributor) use ($data): void {
                    $contributor->categories()->attach($data['categories']);
                }),
                'writer' => tap(Writer::create([
                    'user_id'               => $user->id,
                    'display_name'          => $data['display_name'] ?? $data['name'],
                    'bio'                   => $data['bio'] ?? null,
                    'portfolio_link'        => $data['portfolio_link'] ?? null,
                    'profile_photo'         => $data['profile_photo'],
                    'experience_level'      => $data['experience_level'],
                    'languages'             => $data['languages'],
                    'editorial_specialties' => $data['editorial_specialties'],
                    'application_status'    => 'submitted',
                ]), function (Writer $writer) use ($data): void {
                    $writer->categories()->attach($data['categories']);
                }),
                default => Reader::create(['user_id' => $user->id]),
            };

            $token = $user->createToken('api-client');

            return $this->success([
                'token'      => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user'       => UserResource::makeLoaded($user->refresh()),
            ], 'Account created successfully.', 201);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to create account.');
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            /** @var User|null $user */
            $user = User::query()->where('email', strtolower((string) $data['email']))->first();

            if (! $user || ! Hash::check((string) $data['password'], (string) $user->password)) {
                return $this->error(null, 'Invalid email or password.', 401);
            }

            if (! (bool) $user->is_active) {
                return $this->error(null, 'This account has been deactivated.', 403);
            }

            $token = $user->createToken((string) ($data['device_name'] ?? 'api-client'));

            return $this->success([
                'token'      => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user'       => UserResource::makeLoaded($user),
            ], 'Logged in successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Login failed.');
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->user()?->currentAccessToken();

            if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
                $token->delete();
            }

            return $this->success(null, 'Logged out successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Logout failed.');
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            return $this->success(
                UserResource::makeLoaded($user),
                'User data retrieved successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve user data.');
        }
    }

    public function updateProfile(UpdateProfileRequest $request, UpdateUserProfileAction $updateProfile): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            $user = $updateProfile->execute($user, $request->validated());

            return $this->success(
                UserResource::makeLoaded($user),
                'Profile updated successfully.'
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to update profile.');
        }
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            $data = $request->validated();

            if (! Hash::check((string) $data['current_password'], (string) $user->password)) {
                return $this->error(null, 'Current password is incorrect.', 422);
            }

            $user->password = Hash::make((string) $data['password']);
            $user->save();

            return $this->success(null, 'Password changed successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to change password.');
        }
    }
}
