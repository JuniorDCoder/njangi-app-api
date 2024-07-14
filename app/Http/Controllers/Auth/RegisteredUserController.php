<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'host_number' => ['nullable', 'numeric', 'unique:users,host_number'],
            'phone_number' => ['required', 'string', 'max:13', 'min:9'],
            'location' => ['string', 'required'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'type' => ['required', 'string']
        ]);

        $user = User::create([
            'name' => $request->name,
            'unique_id' => Str::upper(User::generateUniqueId()),
            'host_number' => $request->host_number,
            'phone_number' => $request->phone_number,
            'location' => $request->location,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        // Correctly retrieve the admin role
        $role = Role::where('name', $request->type)->firstOrFail();
        $user->assignRole($role);

        // Generate token
        // $token = $user->createToken('auth-token')->plainTextToken;

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            "message" => "User Created Successfully",
            "user" => $user,
            "role" => $role
        ], 201);
    }
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
