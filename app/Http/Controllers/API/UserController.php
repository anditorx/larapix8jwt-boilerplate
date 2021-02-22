<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use PasswordValidationRules;

    public function login(Request $request)
    {
        try {
            // input validation
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            // credentials login
            $credentials = request(['email','password']);

            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized',
                ], 'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();
            
            // send error, if hash password not match
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid credentials');
            }
            
            // login if success
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token'  => $tokenResult,
                'token_type'    => 'Bearer',
                'user'          => $user, 
            ],'Authenticated');


        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
            // return 'salah';
        }
    }

    public function register(Request $request)
    {
        try {
             // input validation
            $request->validate([
                'name' => ['required','string','max:255'],
                'email' => ['required','string','email', 'max:255','unique:users'],
                'password' => $this->passwordRules(),
            ]);

            User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            
            return ResponseFormatter::success([
                'access_token'  => $tokenResult,
                'token_type'    => 'Bearer',
                'user'          => $user, 
            ],'Authenticated');

        } catch (Exception $err) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $err
            ], 'Authentication Failed', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token revoked');
    }

    // get data user login
    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Successfully get user'); 
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();
        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile updated');
    }

    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2048'
        ]);

        if ($validator->fails()) 
        {
            return ResponseFormatter::error([
                'error' => $validator->errors()
            ], 'Update photo failed', 401);
        }

        if($request->file('file'))
        {
            $file = $request->file->store('assets/user','public');

            // save photo url to db
            $user = Auth::user();
            $user->profile_photo_path = $file;
            $user->update();

            return ResponseFormatter::success([$file], 'File successfully uploaded');
        }
    }
    

}
