<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'exists:users,phone_number'],
            'unique_id' => ['required', 'string', 'exists:users,unique_id']
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function authenticate(): User
    {
        Log::info('Attempting to authenticate user', [
            'phone_number' => $this->input('phone_number'),
            'unique_id' => $this->input('unique_id')
        ]);

        $this->ensureIsNotRateLimited();

        $userExists = User::where([
            ['phone_number', $this->input('phone_number')],
            ['unique_id', $this->input('unique_id')],
        ])->exists();

        if (!$userExists) {
            Log::warning('Authentication failed for user', [
                'phone_number' => $this->input('phone_number'),
                'unique_id' => $this->input('unique_id')
            ]);

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'phone_number' => __('auth.failed'),
                'unique_id' => __('auth.failed'),
            ])->status(Response::HTTP_UNAUTHORIZED);
        }

        Log::info('Authentication successful for user', [
            'phone_number' => $this->input('phone_number'),
            'unique_id' => $this->input('unique_id')
        ]);

        RateLimiter::clear($this->throttleKey());

        return User::where('phone_number', $this->input('phone_number'))
               ->where('unique_id', $this->input('unique_id'))
               ->firstOrFail();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'phone_number' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ])->status(Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('phone_number')).'|'.$this->ip());
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Invalid Credentials',
                'errors' => $validator->errors(),
            ], 401)
        );
    }
}
