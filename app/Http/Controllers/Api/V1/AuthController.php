<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangePasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
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
                'contributor' => Contributor::create([
                    'user_id'        => $user->id,
                    'bio'            => $data['bio'] ?? null,
                    'portfolio_link' => $data['portfolio_link'] ?? null,
                ]),
                'writer' => Writer::create([
                    'user_id'            => $user->id,
                    'display_name'       => $data['display_name'] ?? $data['name'],
                    'bio'                => $data['bio'] ?? null,
                    'portfolio_link'     => $data['portfolio_link'] ?? null,
                    'application_status' => 'pending',
                ]),
                default => Reader::create(['user_id' => $user->id]),
            };

            $token = $user->createToken('api-client');

            return $this->success([
                'token'      => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user'       => $this->userData($user),
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
                'user'       => $this->userData($user),
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

            return $this->success($this->userData($user), 'User data retrieved successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve user data.');
        }
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            $data = $request->validated();

            if (isset($data['email'])) {
                $data['email'] = strtolower((string) $data['email']);
            }

            $user->fill($data)->save();
            $user->refresh();

            return $this->success($this->userData($user), 'Profile updated successfully.');
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

    private function userData(User $user): array
    {
        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $this->resolveRole($user),
            'locale'    => $user->locale,
            'country'   => $user->country,
            'language'  => $user->language,
            'is_active' => (bool) $user->is_active,
        ];
    }

    private function resolveRole(User $user): string
    {
        if ($user->writer()->exists()) return 'writer';
        if ($user->contributor()->exists()) return 'contributor';
        if ($user->editor()->exists()) return 'editor';
        if ($user->admin()->exists()) return 'admin';
        return 'reader';
    }
}
