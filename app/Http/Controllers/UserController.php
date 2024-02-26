<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use DB;

class UserController extends Controller
{
    public function Me()
    {
        return response()->json([
            'user' => Auth::user()
        ], 200);
    }

    public function Update(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::find($id);

        $validatedData = Validator::make($request->all(),
            [
                'first_name' => 'string|max:255',
                'last_name' => 'string|max:255',
                'phone_number' => 'numeric|unique:users',
                'email' => 'email|unique:users', 
            ]
        );

        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        if($request['first_name'])
            $user->first_name = $request['first_name'];
        if($request['last_name'])
            $user->last_name = $request['last_name'];
        if($request['phone_number'])
            $user->phone_number = $request['phone_number'];
        if($request['email'])
            $user->email = $request['email'];

        $user->save();

        return response()->json(['data' => $user], 200);
    }

    public function getEmployees() 
    {
        if (Auth::user()->role != 2) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Employees'], 403);
        }

        $employees = User::where('role', 1)->orderBy('created_at', 'desc')->get();

        return response()->json($employees, 200);
    }

    public function searchEmployees(Request $request)
    {
        if (Auth::user()->role != 2) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Employees'], 403);
        }

        $employees = User::where([
            ['role', '=', 1],
            [DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', '%' . $request['query'] . '%'],
        ])
        ->orderBy('created_at', 'desc')->get();

        return response()->json($employees, 200);
    }

    public function updateEmployee(Request $request, $id)
    {
        if (Auth::user()->role != 2) {
            return response()->json(['message'=>'Access Denied, You Can Not Update Employees'], 403);
        }

        $employee = User::where('role', '=', 1)->find($id);

        if(!$employee){
            return response()->json(['errors' => 'There is no Employee with this id !'], 400);
        }

        $validatedData = Validator::make($request->all(),
            [
                'first_name' => 'string|max:255',
                'last_name' => 'string|max:255',
                'phone_number' => 'numeric|unique:users',
                'email' => 'email|unique:users', 
            ]
        );

        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        if($request['first_name'])
            $employee->first_name = $request['first_name'];
        if($request['last_name'])
            $employee->last_name = $request['last_name'];
        if($request['phone_number'])
            $employee->phone_number = $request['phone_number'];
        if($request['email'])
            $employee->email = $request['email'];

        $employee->save();

        return response()->json(['data' => $employee], 200);
    }

    public function deleteEmployee($id)
    {
        if (Auth::user()->role != 2) {
            return response()->json(['message'=>'Access Denied, You Can Not Delete Employees'], 403);
        }

        $employee = User::where('role', '=', 1)->find($id);

        if(!$employee){
            return response()->json(['errors' => 'There is no Employee with this id !'], 400);
        }

        $employee->delete();
        return response()->json(['message' => "Employee Deleted"], 200);
    }

}
