<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Users;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Validator;

class UserController extends BaseController
{
    public function __construct()
    {
        //$this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $users = User::all();

            return $this->sendResponse(Users::collection($users), 'User Retrieved Successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }



     /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = User::find($id);

            if (is_null($user)) {
                return $this->sendError('User not found.');
            }

            return $this->sendResponse(new Users($user), 'User Retrieved Successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $input = $request->all();

            $validator = Validator::make($input, [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
                'dob' => 'required|date_format:d/m/Y'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }
            $user = User::find($id);
            $user->firstname = $input['firstname'];
            $user->lastname = $input['lastname'];
            $user->email = $input['email'];
            $user->password = Hash::make($user['password']);
            $user->dob = $input['dob'];

            $user->save();

            return $this->sendResponse(new Users($user), 'Users Updated Successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $ids = explode(',', $id);
            $user = User::whereIn('id', explode(',', $id))->get();
            $useDetails= $user;
            if (!empty($user)) {
                $user = User::whereIn('id', explode(',', $id))->delete();
                return $this->sendResponse(Users::collection($useDetails), 'User Deleted Successfully.');
              
            } else {
                return $this->sendError('User not found.');
            }

           // echo "<pre>";printf($userCollection);die;
            //$user->delete();

            return $this->sendResponse([], 'User Deleted Successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }
}
