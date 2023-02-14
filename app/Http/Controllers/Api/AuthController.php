<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Validator;

class AuthController extends BaseController
{
    /**
    * Create a new AuthController instance.
    *
    * @return void
    */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

   /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $credentials = $request->only('email', 'password');
            $token = Auth::attempt($credentials);
            if (!$token) {
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
            }

            $user = Auth::user();
            $success['user'] =  $user;
            $success['user']['token'] =  $token;
            $success['user']['type'] =  'bearer';
            return $this->sendResponse($success, 'User Login successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
                'dob' => 'required|date_format:d/m/Y'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'dob' => $request->dob,
            ]);
            $success["user_id"] = $user->id;
            return $this->sendResponse($success, 'User register successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            Auth::logout();
            $success["user_id"] = '';
            return $this->sendResponse($success, 'Successfully logged out');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

     /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $success['user'] =  Auth::user();
            $success['user']['token'] =  Auth::refresh();
            $success['user']['type'] =  'bearer';
            return $this->sendResponse($success, 'Refresh Token');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }
}
