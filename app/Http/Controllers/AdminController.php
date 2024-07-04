<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lcobucci\JWT\Builder;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
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
                'avatar' => 'required|image|max:2048',
            ]);

            $user = new User([
                'uuid' => (string) Str::uuid(),
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'is_admin' => true,
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
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Admin account created successfully'], 201);
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
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $user->update([
                'last_logged_in' => Carbon::now(),
            ]);

            // Generate user token here
            // $token = $this->issueToken($user->id);

            $tokenController = new AuthController();
            $token = $tokenController->issueToken($user->id);
            dd($token);
            return response()->json([
                'message' => 'Successfully logged in.',
                'token' => $token,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Invalid email or password.',
        ], 401);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function userListing()
    {
        try {
            $users = User::where('is_admin', false)->paginate(10);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($users, 200);
    }

    public function userEdit(Request $request, $uuid)
    {
        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'address' => 'required|string',
                'phone_number' => 'required|string|max:15',
                'avatar' => 'nullable|image|max:2048',
            ]);

            $user = User::where('uuid', $uuid)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user->first_name = $validatedData['first_name'];
            $user->last_name = $validatedData['last_name'];
            $user->email = $validatedData['email'];
            $user->address = $validatedData['address'];
            $user->phone_number = $validatedData['phone_number'];

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
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function userDelete($uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
