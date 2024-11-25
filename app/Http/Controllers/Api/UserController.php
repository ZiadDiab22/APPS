<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\file;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function addFile(Request $request)
    {
        $validatedData = $request->validate([
            'file' => 'required',
        ]);

        $file = $request->file('file');
        $validatedData['name'] = $file->getClientOriginalName();
        $validatedData['type'] = $file->getClientOriginalExtension();
        $validatedData['content'] = file_get_contents($file->getRealPath());
        $validatedData['creater_id'] = auth()->user()->id;

        $file = file::create($validatedData);

        Storage::put('uploads/' . $validatedData['name'], $validatedData['content']);

        return response()->json([
            'message' => 'done successfully',
            'file' => $file,
        ]);
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
        $loginData = $request->validate([
            'password' => 'required',
            'email' => 'required'
        ]);

        if (!Auth::guard('web')->attempt(['password' => $loginData['password'], 'email' => $loginData['email']])) {
            return response()->json(['status' => false, 'message' => 'Invalid User'], 404);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        $user_data = User::where('email', $request->email)->first();

        return response()->json([
            'status' => true,
            'access_token' => $accessToken,
            'user_data' => $user_data
        ]);
    }
}
