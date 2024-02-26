<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\User;
use App\Models\Reservation;
use DB;

class ReservationController extends Controller
{
    // Student
    public function addReservation(Request $request, $course_id)
    {
        if (Auth::user()->role != 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Add Reservation'], 403);
        }

        $user = User::find(Auth::user()->id);
        $course = Course::find($course_id);
        if(!$course){
            return response()->json(['errors' => 'There is no Course with this id !'], 400);
        }

        foreach ($course->Reservations as $res){
            if ($user->id == $res->user_id){
                return response()->json(['errors' => 'You can not reserve twice !'], 400);
            }
        }

        // User Validation
        if ($user->balance < $course->cost){
            return response()->json(['errors' => 'There is no enough money !'], 400);
        }
        // Course Validation
        if ($course->state != 0){
            return response()->json(['errors' => 'You can not register in this course !'], 400);
        }
        if ($course->curr_student == $course->total_student){
            return response()->json(['errors' => 'The Course Is Full !'], 400);
        }

        $reservation = new Reservation;

        $reservation->user_id = $user->id;
        $reservation->course_id = $course_id;
        $reservation->save();

        // Update User
        $user->balance -= $course->cost;
        $user->save();

        // Update Course
        $course->curr_student += 1;
        $course->save();

        return response()->json(['data' => $reservation], 200);
    }

    public function getStudentReservations()
    {
        if (Auth::user()->role != 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Reservations'], 403);
        }

        $reservations = Reservation::where('user_id', '=', Auth::user()->id)
                            ->with('Course:id,course_name,teacher_name,cost,state')
                            ->orderBy('created_at', 'desc')->get();

        return response()->json($reservations, 200);
    }

    public function searchStudentReservations(Request $request)
    {
        if (Auth::user()->role != 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Reservations'], 403);
        }

        $reservations = Reservation::where('user_id', '=', Auth::user()->id)
                            ->with('Course:id,course_name,teacher_name,cost,state')
                            ->whereHas('Course', function($q) use($request) {
                                $q->where('course_name', 'LIKE', '%' . $request['query'] . '%');
                            })
                            ->orderBy('created_at', 'desc')->get();

        return response()->json($reservations, 200);
    }

    // Responsible
    public function getReservations()
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Reservations'], 403);
        }

        $reservations = Reservation::with([
                                'User:id,first_name,last_name',
                                'Course:id,course_name,teacher_name,cost,state'
                            ])
                            ->orderBy('created_at', 'desc')->get();

        return response()->json($reservations, 200);
    }

    public function searchReservations(Request $request)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Reservations'], 403);
        }

        $reservations = Reservation::with([
                                'User:id,first_name,last_name',
                                'Course:id,course_name,teacher_name,cost,state'
                            ])
                            ->whereHas('User', function($q) use($request) {
                                $q->where(DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', '%' . $request['query'] . '%');
                            })
                            ->orWhereHas('Course', function($q) use($request) {
                                $q->where('course_name', 'LIKE', '%' . $request['query'] . '%');
                            })
                            ->orderBy('created_at', 'desc')->get();

        return response()->json($reservations, 200);
    }
}
