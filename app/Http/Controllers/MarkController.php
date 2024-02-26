<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Mark;
use DB;

class MarkController extends Controller
{
    // Responsible
    public function getCourseMarks($course_id)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Course Marks'], 403);
        }
        
        $course = Course::find($course_id);
        if(!$course){
            return response()->json(['errors' => 'There is no Course with this id !'], 400);
        }

        $marks = Mark::where('course_id', '=', $course_id)
                        ->with('User:id,first_name,last_name')
                        ->orderBy('created_at', 'desc')->get();

        return response()->json($marks, 200);
    }

    public function addCourseMarks(Request $request)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Add Course Marks'], 403);
        }

        $validatedData = Validator::make($request->all(),
            [
                'course_id' => 'required|numeric|exists:courses,id',
                'students' => 'required|array',
                'students.*' => 'required|numeric|exists:users,id',
                'marks' => 'required|array',
                'marks.*' => 'required|numeric|min:0|max:100',                
            ]
        );
        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        $course = Course::find($request['course_id']);
        if ($course->state == 3){
            return response()->json(['errors' => 'The marks is uploded before for this course!'], 400);
        }else if ($course->state != 2){
            return response()->json(['errors' => 'The course is not finished!'], 400);
        }

        if (count($request['marks']) != count($request['students'])){
            return response()->json(['errors' => 'The size of marks array does not equal students array!'], 400);
        }

        $super_student = 0;
        $super_mark = 0;
        for ($i = 0; $i < count($request['students']); $i+=1){
            $mark = new Mark;
            $mark->user_id = $request['students'][$i];
            $mark->course_id = $request['course_id'];
            $mark->rate = $request['marks'][$i];
            $mark->save();

            if ($mark->rate > $super_mark){
                $super_mark = $mark->rate;
                $super_student = $mark->user_id;
            }
        }

        $course->super_student = $super_student;
        $course->state = 3;
        $course->save();

        return response()->json(['message' => "Marks Added"], 200);
    }

    // User
    public function getStudentMark($course_id)
    {
        if (Auth::user()->role != 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Student Mark'], 403);
        }

        $course = Course::find($course_id);
        if(!$course){
            return response()->json(['errors' => 'There is no Course with this id !'], 400);
        }
        if ($course->state != 3){
            return response()->json(['errors' => 'You can not see your mark now!'], 400);
        }

        $reservation = Reservation::where([
                                    ['course_id', '=', $course_id],
                                    ['user_id', '=', Auth::user()->id]])
                                    ->first();
        if(!$reservation){
            return response()->json(['errors' => 'You are not registed in this course !'], 400);
        }

        $mark = Mark::where([['course_id', '=', $course_id],
                            ['user_id', '=', Auth::user()->id]])
                        ->first();

        return response()->json($mark, 200);
    }
}
