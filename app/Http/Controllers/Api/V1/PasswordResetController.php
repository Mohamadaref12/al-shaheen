<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use App\Support\SafeMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Throwable;

class PasswordResetController extends Controller
{
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $email = strtolower((string) $request->validated('email'));

            /** @var User $user */
            $user = User::query()->where('email', $email)->firstOrFail();

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(15);

            Cache::put($this->cacheKey($email), $code, $expiresAt);

            SafeMail::queue($user->email, new PasswordResetCodeMail(
                name: $user->name,
                code: $code,
                expiresAt: $expiresAt,
                locale: $user->locale ?? 'ar',
            ));

            return $this->success(null, 'Password reset code sent to your email.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to send password reset code.');
        }
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $email = strtolower((string) $data['email']);
            $cachedCode = Cache::get($this->cacheKey($email));

            if (! is_string($cachedCode) || $cachedCode !== (string) $data['code']) {
                return $this->error(null, 'Invalid or expired reset code.', 422);
            }

            /** @var User $user */
            $user = User::query()->where('email', $email)->firstOrFail();
            $user->password = Hash::make((string) $data['password']);
            $user->save();

            Cache::forget($this->cacheKey($email));

            return $this->success(null, 'Password reset successfully.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to reset password.');
        }
    }

    private function cacheKey(string $email): string
    {
        return 'password_reset_code:'.sha1($email);
    }
}
