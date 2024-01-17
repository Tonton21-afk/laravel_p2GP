<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{

    //-------------REGISTER-------------------
    public function register(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate a token for the registered user
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status'=>true,
            'message'=>'created successfully',
            'token' => $token], 201);
    }


    //-------------------LOG IN ---------------------
    public function login(Request $request)
    {
   
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    // Attempt to authenticate the user
    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        
        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

       
        return response()->json(['user' => $user, 'token' => $token], 200)
            ->withCookie(cookie('token', $token, 60));
    }

    
    return response()->json(['error' => 'Invalid credentials'], 401);
    }


    //----------------------GET-----------------------
    public function show(Request $request)
    {
    $user = $request->user();

    if ($user) {
        return response()->json(['user' => $user], 200);
    } else {
        return response()->json(['error' => 'User not authenticated'], 401);
    }
    }

    public function getUserById($id)
    {
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    return response()->json(['user' => $user], 200);
    }

    //--------------UPDATE---------------
    public function updateUserById(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

   
    $request->validate([
        'name' => 'required|string',
        'email' => [
            'required',
            'email',
            Rule::unique('users')->ignore($user->id),
        ],
        'password' => 'nullable|string|min:6',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return response()->json(['status' => true, 'message' => 'User updated successfully', 'data' => $user], 200);
}


    

    //----------------DELETE------------------
    public function deleteUserById($id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
    
        try {
            $user->delete();
            return response()->json(['status' => true, 'message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete user', 'message' => $e->getMessage()], 500);
        }
    }


    
}