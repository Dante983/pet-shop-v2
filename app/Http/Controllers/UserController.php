<?php

namespace App\Http\Controllers;

use App\Handlers\AuthHandler;
use App\Models\File;
use App\Models\JwtToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserController extends APIController
{
    public function profile(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|string|same:password',
                'address' => 'required|string',
                'phone_number' => 'required|string|max:15',
                'avatar' => 'image|max:2048',
            ]);

            $user = new User([
                'uuid' => (string) Str::uuid(),
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'is_admin' => false,
                'address' => $validatedData['address'],
                'phone_number' => $validatedData['phone_number'],
            ]);

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $path = $file->store('avatars', 'public');

                $fileRecord = File::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getMimeType(),
                ]);

                $user->avatar = $fileRecord->uuid;
            }

            $user->save();

            if ($user) {
                $authHandler = new AuthHandler;
                $token = $authHandler->GenerateToken($user);

                $success = [
                    'user' => $user,
                    'token' => $token,
                ];

                return $this->sendResponse($success, 'User account created successfully', 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'User account created successfully'], 201);
    }

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|string|email|max:255',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $validatedData['email'])->first();

            if (!$user || !Hash::check($validatedData['password'], $user->password)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user->update([
                'last_logged_in' => Carbon::now(),
            ]);

            $authHandler = new AuthHandler;
            $token = $authHandler->GenerateToken($user);

            $success = ['user' => $user, 'token' => $token];

            JwtToken::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'unique_id' => $token,
                    'token_title' => 'User Login',
                    'restrictions' => json_encode([]),
                    'permissions' => json_encode([]),
                    'expires_at' => now()->addHours(1),
                    'last_used_at' => now(),
                    'refreshed_at' => now(),
                ]
            );

            return $this->sendResponse($success, 'Logged In');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = JwtToken::where('user_id', $request->user()->id)->first();
            if ($token) {
                $token->delete();
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function edit(Request $request)
    {
        try {
            $user = $request->user();

            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'address' => 'required|string',
                'phone_number' => 'required|string|max:15',
                'avatar' => 'image|max:2048',
            ]);

            $user->first_name = $validatedData['first_name'];
            $user->last_name = $validatedData['last_name'];
            $user->email = $validatedData['email'];
            $user->address = $validatedData['address'];
            $user->phone_number = $validatedData['phone_number'];

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $path = $file->store('avatars', 'public');

                if ($user->avatar) {
                    $oldFile = File::where('uuid', $user->avatar)->first();
                    $oldFile->delete();
                }

                $fileRecord = File::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getMimeType(),
                ]);

                $user->avatar = $fileRecord->uuid;
            }

            $user->save();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'User account updated successfully'], 200);
    }

    public function delete(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->avatar) {
                $file = File::where('uuid', $user->avatar)->first();
                $file->delete();
            }

            $user->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'User account deleted successfully'], 200);
    }
}
