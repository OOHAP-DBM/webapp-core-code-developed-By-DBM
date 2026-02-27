<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // public function rules(): array
    // {
    //     // return [
    //     //     'name' => ['required', 'string', 'max:255'],
    //     //     'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
    //     //     'phone' => ['nullable', 'string', 'max:15', 'unique:users,phone'],
    //     //     'password' => ['required', 'string', 'confirmed', Password::defaults()],
    //     //     'role' => ['nullable', 'string', 'in:customer,vendor'],
    //     //     'address' => ['nullable', 'string'],
    //     //     'city' => ['nullable', 'string', 'max:100'],
    //     //     'state' => ['nullable', 'string', 'max:100'],
    //     //     'pincode' => ['nullable', 'string', 'max:10'],
    //     // ];
    //     return [
    //         'name'     => 'required|string|max:100',
    //         'email'    => 'required_without:phone|nullable|email|unique:users,email',
    //         'phone'    => 'required_without:email|nullable|string|unique:users,phone',
    //         'password' => 'required|string|min:8|confirmed',
    //         'role'     => 'sometimes|string|in:customer,vendor',
    //     ];
    // }

    // public function messages(): array
    // {
    //     return [
    //         'name.required' => 'Name is required',
    //         'email.required' => 'Email is required',
    //         'email.email' => 'Please provide a valid email address',
    //         'email.unique' => 'This email is already registered',
    //         'phone.unique' => 'This phone number is already registered',
    //         'password.required' => 'Password is required',
    //         'password.confirmed' => 'Passwords do not match',
    //     ];
    // }

    public function rules(): array
    {
        return [
            'email' => [
                'required_without:phone',
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    $user = User::where('email', $value)->first();
                    // If user exists and already has a name, they are already registered
                    if ($user && !is_null($user->password)) {
                        $fail('This email is already registered. Please login.');
                    }
                },
            ],
            'phone' => [
                'required_without:email',
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    $user = User::where('phone', $value)->first();
                    if ($user && !is_null($user->name)) {
                        $fail('This phone number is already registered. Please login.');
                    }
                },
            ],
            'name' => 'required|string|max:100',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|in:customer,vendor',
            'fcm_token' => 'nullable|string',
        ];
    }
}
