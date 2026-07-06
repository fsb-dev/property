<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return Inertia::render('Client/Auth/Profile', [
            'user' => $request->user("client"),
        ]);
    }

    public function update(Request $request)
    {
        /** @var Client $client */
        $client = $request->user("client");

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($client->id)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $client->update($validated);

        return back()->with('success', 'Profile updated successfully.')
            ->with('toast', ['type' => 'success', 'message' => "Profile updated successfully."]);
    }

    public function updatePassword(Request $request)
    {
        /** @var Client $client */
        $client = $request->user("client");

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:client'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $client->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.')
            ->with('toast', ['type' => 'success', 'message' => "Password changed successfully."]);
    }
}
