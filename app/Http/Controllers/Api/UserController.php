<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\notification;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required',
            'password' => 'required',
            'type_id' => 'required',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'status' => false,
                'message' => "email is taken"
            ], 200);
        }

        $validatedData['password'] = bcrypt($request->password);

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        $user_data = User::where('id', $user->id)->first();

        return response()->json([
            'status' => true,
            'access_token' => $accessToken,
            'user_data' => $user_data
        ]);
    }

    public function login(Request $request)
    {
        $accessToken = $this->userService->checkCredential($request->password, $request->email);

        if ($accessToken) {
            $user_data = User::where('id', auth()->user()->id)->first();
            return response()->json([
                'status' => true,
                'access_token' => $accessToken,
                'user_data' => $user_data
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'something went error',
            ]);
        }
    }

    public function showUsers()
    {

        $users = User::where('id', '!=', auth()->user()->id)->get(['id', 'name', 'email']);

        return response()->json([
            'status' => true,
            'users' => $users
        ]);
    }

    public function showNotification()
    {
        notification::where('user_id', auth()->user()->id)->update(['viewed' => 1]);
        $data = notification::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'status' => true,
            'users' => $data
        ]);
    }
}
