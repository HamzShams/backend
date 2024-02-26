<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Event;

class EventController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'image' => ['required', 'image','mimes:jpeg,jpg,png'],
            'event_name' => 'required|string|max:255',
            'place' => 'required|string|max:255',
            'date' => 'required|date_format:Y-m-d',
            'description' => 'required|string',
        ]);
    }

    public function addEvent(Request $request)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Add Event'], 403);
        }

        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $event = new Event;

        $event->user_id = Auth::user()->id;
        $event->event_name = $request['event_name'];
        $event->place = $request['place'];
        $event->date = $request['date'];
        $event->description = $request['description'];

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
            $path = $request->file('image')->storeAs('public/Event_images/', $filenameToStore);

            $event->image = $filenameToStore;
        }

        $event->save();

        return response()->json(['data' => $event], 200);
    }

    public function getEvents() 
    {

        $events = Event::orderBy('created_at', 'desc')->get();

        return response()->json($events, 200);
    }

    public function searchEvents(Request $request)
    {
        $events = Event::where('event_name', 'LIKE', '%' . $request['query'] . '%')
                        ->orderBy('created_at', 'desc')->get();

        return response()->json($events, 200);
    }

    public function updateEvent(Request $request, $id)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Update Event'], 403);
        }

        $event = Event::find($id);

        if(!$event){
            return response()->json(['errors' => 'There is no Event with this id !'], 400);
        }

        $validatedData = Validator::make($request->all(),
            [
                'image' => ['image','mimes:jpeg,jpg,png'],
                'event_name' => 'string|max:255',
                'place' => 'string|max:255',
                'date' => 'date_format:Y-m-d',
                'description' => 'string',
            ]
        );

        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        if($request['event_name'])
            $event->event_name = $request['event_name'];
        if($request['place'])
            $event->place = $request['place'];
        if($request['date'])
            $event->date = $request['date'];
        if($request['description'])
            $event->description = $request['description'];

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
            $path = $request->file('image')->storeAs('public/Event_images/', $filenameToStore);

            $event->image = $filenameToStore;
        }

        $event->save();

        return response()->json(['data' => $event], 200);
    }

    public function deleteEvent($id)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Delete Event'], 403);
        }

        $event = Event::find($id);

        if(!$event){
            return response()->json(['errors' => 'There is no Event with this id !'], 400);
        }

        $event->delete();
        return response()->json(['message' => "Event Deleted"], 200);
    }
}
