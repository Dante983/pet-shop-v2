<?php

namespace App\Http\Controllers;

use App\Handlers\AuthHandler;
use App\Models\File;
use App\Models\JwtToken;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserController extends APIController
{
    /**
     * @OA\Get(
     *     path="/api/v1/user",
     *     summary="Get user profile",
     *     tags={"User"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        $fileRecord = $user->avatar ? File::where('uuid', $user->avatar)->first() : null;
        $avatarUrl = $fileRecord ? config('app.app_url') . '/storage/' . $fileRecord->path : null;

        $userData = $user->toArray();
        $userData['avatar'] = $avatarUrl;

        return response()->json($userData, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/create",
     *     summary="Create user",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","password","password_confirmation"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User account created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|string|same:password',
            ]);

            $user = new User([
                'uuid' => (string) Str::uuid(),
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'is_admin' => false,
                'address' => $request->get('address', ''),
                'phone_number' => $request->get('phone_number', ''),
                'is_marketing' => $request->get('is_marketing', false),
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

    /**
     * @OA\Post(
     *     path="/api/v1/user/login",
     *     summary="Login user",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
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

            if ($user->avatar) {
                $avatar = File::where('uuid', $user->avatar)->first();
                $avatar_url = config('app.app_url') . '/storage/' . $avatar->path;
            } else {
                $avatar_url = null;
            }

            $success = [
                'user' => $user,
                'token' => $token,
                'avatar_url' => $avatar_url,
            ];

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

    /**
     * @OA\Post(
     *     path="/api/v1/user/logout",
     *     summary="Logout user",
     *     tags={"User"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/v1/user/edit",
     *     summary="Edit user profile",
     *     tags={"User"},
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","address","phone_number"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="avatar", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User account updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/v1/user",
     *     summary="Delete user account",
     *     tags={"User"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User account deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/user/forgot-password",
     *     summary="Forgot password",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recovery link generated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        $token = Password::createToken($user);
        $url = config('app.vue_app_base_url') . '/forgot-password?token=' . $token;

        return response()->json(['recovery_link' => $url, 'message' => 'Recovery link generated.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/reset-password-token",
     *     summary="Reset password using token",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","password","password_confirmation"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function resetPasswordToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ]);
        Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return response()->json(['message' => 'Password reset successfully'], 200);
    }
}
