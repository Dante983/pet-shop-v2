<?php

namespace App\Http\Controllers;

use App\Handlers\AuthHandler;
use App\Models\File;
use App\Models\JwtToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends APIController
{
    /**
     * @OA\Post(
     *     path="/api/v1/admin/create",
     *     summary="Create admin user",
     *     tags={"Admin"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","password","password_confirmation","address","phone_number","avatar"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="avatar", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Admin account created successfully"
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

            if ($user) {
                $authHandler = new AuthHandler;
                $token = $authHandler->GenerateToken($user);

                $success = [
                    'user' => $user,
                    'token' => $token,
                ];

                return $this->sendResponse($success, 'Admin account created successfully', 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Admin account created successfully'], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/login",
     *     summary="Login admin user",
     *     tags={"Admin"},
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

    /**
     * @OA\Post(
     *     path="/api/v1/admin/logout",
     *     summary="Logout admin user",
     *     tags={"Admin"},
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
     * @OA\Get(
     *     path="/api/v1/admin/user-listing",
     *     summary="Get user listing",
     *     tags={"Admin"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function userListing(Request $request)
    {
        try {
            if ($request->user()->is_admin) {
                $query = User::where('is_admin', false);

                if ($request->has('name')) {
                    $query->where(function ($q) use ($request) {
                        $q
                            ->where('first_name', 'like', '%' . $request->name . '%')
                            ->orWhere('last_name', 'like', '%' . $request->name . '%');
                    });
                }

                if ($request->has('email')) {
                    $query->where('email', 'like', '%' . $request->email . '%');
                }

                if ($request->has('phone')) {
                    $query->where('phone_number', 'like', '%' . $request->phone . '%');
                }

                if ($request->has('address')) {
                    $query->where('address', 'like', '%' . $request->address . '%');
                }

                if ($request->has('date_created')) {
                    $query->whereDate('created_at', $request->date_created);
                }

                if ($request->has('marketing_preferences')) {
                    $query->where('is_marketing', $request->marketing_preferences === 'yes');
                }

                $users = $query->paginate(10);
                foreach ($users as $user) {
                    if ($user->avatar) {
                        $avatar = File::where('uuid', $user->avatar)->first();
                        $avatar_url = config('app.app_url') . '/storage/' . $avatar->path;
                        $user->avatar = $avatar_url;
                    } else {
                        $avatar_url = null;
                    }
                }

                return response()->json($users, 200);
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/user-edit/{uuid}",
     *     summary="Edit user details",
     *     tags={"Admin"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
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
     *         description="User updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function userEdit(Request $request, $uuid)
    {
        if ($request->hasFile('avatar')) {
            Log::info('Avatar file is included in the request');
        } else {
            Log::info('No avatar file in the request');
        }

        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'address' => 'required|string',
                'phone_number' => 'required|string|max:16',
                'avatar' => 'nullable|',
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
            Log::error('Error updating user:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return response()->json(['message' => 'User updated successfully'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/user-delete/{uuid}",
     *     summary="Delete user",
     *     tags={"Admin"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
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
