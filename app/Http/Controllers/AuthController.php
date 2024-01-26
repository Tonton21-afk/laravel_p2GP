<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{

    //-------------get all---------

    public function index()
    {
        $users = User::all();

        if ($users->count() > 0) {
            return response()->json([
                'status' => 200,
                'user' => $users
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'status_message' => 'No Matches Found'
            ], 404);
        }
    }

    //-------------REGISTER-------------------
    public function register(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6', 
        ]);

    
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

      
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message'=>'created successfully',], 201);
    }



    //-------------------LOG IN ---------------------
    public function login(Request $request)
    {
   
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

   
    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        
        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

       
        return response()->json([
            'message' => 'You are logged in successfully!',
            'user' => $user,
            'token' => $token,
        ], 200)->withCookie(cookie('token', $token, 60));
    }

    
    return response()->json(['error' => 'Invalid credentials'], 401);
    }


    //----------------------GET-----------------------
  
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
    
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:250',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages()
            ], 422);
        }
    
        try {
            $userData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
            ];
    
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->input('password'));
            }
    
            $user->update($userData);
        } catch (\Exception $e) {
            \Log::error('Update User Error: ' . $e->getMessage());
    
            return response()->json([
                'status' => 500,
                'error' => 'Failed to update user data. Check the logs for details.',
            ], 500);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }
    

    //----------------DELETE------------------
    public function deleteUserById($id)
    {
        $user = user::find($id);

        if ($user) {
            if ($user->delete()) {
                return response()->json([
                    'status' => 200,
                    'status_message' => "User Data successfully deleted!"
                ], 200);
            } else {
                return response()->json([
                    
                    'error_message' => "Failed to delete user data."
                ], 500);
            }
        } else {
            return response()->json([
    
                'error_message' => "No Such Data Found!"
            ], 404);
        }
    }

    // check stats

    public function checkAuthStatus(Request $request)
{
    $user = $request->user();

    if ($user) {
        return response()->json([
            'message' => 'Logged In!',
            'user' => $user,
        ], 200);
    } else {
        $token = $request->cookie('token');

        if ($token) {
            return response()->json([
                'status' => false,
                'message' => 'Logged In!',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Logged Out!',
            ], 401);
        }
    }
}

    //---------------LOGOUT------------------
    public function logout(Request $request)
{
    $user = $request->user();

    if ($user) {
        $user->tokens()->delete();

        
        return response()->json(['message' => 'User logged out successfully'], 200)
            ->withoutCookie('token')
            ;
    } else {
        return response()->json(['error' => 'User not authenticated'], 401);
    }
}
    

   


}