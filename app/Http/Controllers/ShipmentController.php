<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Shipment;
use App\Models\User;
use DB;

class ShipmentController extends Controller
{
    // Student
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'image' => ['required', 'image','mimes:jpeg,jpg,png'],
            'amount' => 'required|numeric',
        ]);
    }

    public function addShipment(Request $request)
    {
        if (Auth::user()->role != 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Add Shipment'], 403);
        }

        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $shipment = new Shipment;

        $shipment->user_id = Auth::user()->id;
        $shipment->amount = $request['amount'];

        if ($request->hasFile('image')) {

            // Get filename with extension
            $filenameWithExt = $request->file('image')->getClientOriginalName();

            // Get just the filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

            // Get extension
            $extension = $request->file('image')->getClientOriginalExtension();

            // Create new filename
            $filenameToStore = $filename.'_'.time().'.'.$extension;

            // Uplaod image
            $path = $request->file('image')->storeAs('public/Shipment_images/', $filenameToStore);

            $shipment->image = $filenameToStore;
        }

        $shipment->save();

        return response()->json(['data' => $shipment], 200);
    }

    public function getStudentShipments()
    {
        if (Auth::user()->role != 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Shipments'], 403);
        }

        $shipments = Shipment::where('user_id', '=', Auth::user()->id)
                        ->orderBy('created_at', 'desc')->get();

        return response()->json($shipments, 200);
    }

    // Resposible
    public function getShipments()
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Shipments'], 403);
        }

        $shipments = Shipment::with('User:id,first_name,last_name')
                        ->orderBy('created_at', 'desc')->get();

        return response()->json($shipments, 200);
    }

    public function searchShipments(Request $request)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Shipments'], 403);
        }

        $shipments = Shipment::with('User:id,first_name,last_name')
                        ->whereHas('User', function($q) use($request) {
                            $q->where(DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', '%' . $request['query'] . '%');
                        })
                        ->orderBy('created_at', 'desc')->get();

        return response()->json($shipments, 200);
    }

    public function acceptShipment(Request $request, $id)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Accept Shipment'], 403);
        }

        $shipment = Shipment::find($id);

        if(!$shipment){
            return response()->json(['errors' => 'There is no Shipment with this id !'], 400);
        }

        if ($shipment->state != 0) {
            return response()->json(['errors'=>'You Can Not Accept This Shipment'], 400);
        }
        
        // Increase User's Balance
        $user = User::find($shipment->user_id);
        $user->balance += $shipment->amount;
        $user->save();

        $shipment->state = 1;
        $shipment->save();

        return response()->json(['message' => "Shipment Accepted"], 200);
    }

    public function cancelShipment(Request $request, $id)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Cancel Shipment'], 403);
        }

        $shipment = Shipment::find($id);

        if(!$shipment){
            return response()->json(['errors' => 'There is no Shipment with this id !'], 400);
        }

        if ($shipment->state != 0) {
            return response()->json(['errors'=>'You Can Not Cancel This Shipment'], 400);
        }

        $shipment->state = 2;
        $shipment->save();

        return response()->json(['message' => "Shipment Canceled"], 200);
    }
}
