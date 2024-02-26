<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone_number' => 'required|numeric|unique:users',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:password',
        ]);
    }

    public function Register(Request $request)
    {
        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $user = new User;

        $user->first_name = $request['first_name'];
        $user->last_name = $request['last_name'];
        $user->email = $request['email'];
        $user->phone_number = $request['phone_number'];
        $user->password = Hash::make($request['password']);
        $user->role = 0;

        $user->save();

        return response()->json(['data' => $user], 200);
    }

    public function addEmployee(Request $request)
    {
        if (Auth::user()->role != 2){
            return response()->json(['message'=>'Access Denied, You Can Not Add Employee'], 403);
        }

        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $user = new User;

        $user->first_name = $request['first_name'];
        $user->last_name = $request['last_name'];
        $user->email = $request['email'];
        $user->phone_number = $request['phone_number'];
        $user->password = Hash::make($request['password']);
        $user->role = 1;

        $user->save();

        return response()->json(['data' => $user], 200);
    }

    public function addAdmin(Request $request)
    {
        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $user = new User;

        $user->first_name = $request['first_name'];
        $user->last_name = $request['last_name'];
        $user->email = $request['email'];
        $user->phone_number = $request['phone_number'];
        $user->password = Hash::make($request['password']);
        $user->role = 2;

        $user->save();

        return response()->json(['data' => $user], 200);
    }

    public function Login(Request $request)
    {
        if(Auth::attempt($request->only('phone_number', 'password'))){

            $user = User::where('phone_number', $request['phone_number'])->firstOrFail();

        }else{
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $token = $user->createToken('User Password')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role,
        ], 200);
    }

    public function Logout()
    {
        $token = Auth::user()->token();
        $token->revoke();

        return response()->json(['message' => 'You Have Been Successfully Logged Out!'], 200);
    }
}
