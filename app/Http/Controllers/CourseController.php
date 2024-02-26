<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\User;
use App\Models\Mark;
use DB;

class CourseController extends Controller
{
    // Responsible
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'image' => ['required', 'image','mimes:jpeg,jpg,png'],
            'course_name' => 'required|string|max:255',
            'teacher_name' => 'required|string|max:255',
            'cost' => 'required|numeric',
            'total_student' => 'required|numeric',
            'total_hours' => 'required|numeric',
            'description' => 'required|string',
        ]);
    }

    public function addCourse(Request $request)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Add Course'], 403);
        }

        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $course = new Course;

        $course->user_id = Auth::user()->id;
        $course->course_name = $request['course_name'];
        $course->teacher_name = $request['teacher_name'];
        $course->cost = $request['cost'];
        $course->total_student = $request['total_student'];
        $course->total_hours = $request['total_hours'];
        $course->description = $request['description'];

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
            $path = $request->file('image')->storeAs('public/Course_images/', $filenameToStore);

            $course->image = $filenameToStore;
        }

        $course->save();

        return response()->json(['data' => $course], 200);
    }

    public function getCourse($id)
    {
        $course = Course::find($id);

        $super_student = null;
        $super_mark = -1;
        if($course->super_student){
            $super_mark = Mark::where([['user_id', '=', $course->super_student],
                                        ['course_id', '=', $id]])
                                        ->with('User:id,first_name,last_name')
                                        ->first();
        }

        return response()->json(['course' => $course, 'super_mark' => $super_mark], 200);
    }

    public function getCourses()
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Courses'], 403);
        }

        $courses = Course::orderBy('created_at', 'desc')->get();

        return response()->json($courses, 200);
    }

    public function searchCourses(Request $request)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Courses'], 403);
        }

        $courses = Course::where('course_name', 'LIKE', '%' . $request['query'] . '%')
                        ->orWhere('teacher_name', 'LIKE', '%' . $request['query'] . '%')
                        ->orderBy('created_at', 'desc')->get();

        return response()->json($courses, 200);
    }

    public function deleteCourse($id)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Delete Course'], 403);
        }

        $course = Course::find($id);
        if(!$course){
            return response()->json(['errors' => 'There is no course with this id !'], 400);
        }

        if ($course->state == 0){
            foreach ($course->Reservations as $res){
                $student = User::find($res->user_id);
                $student->balance += $course->cost;
                $student->save();
            }
        } else if ($course->state == 1 or $course->state == 2){
            return response()->json(['errors' => 'You can not delete the course right now ! !'], 400);
        }

        $course->delete();
        return response()->json(['message' => "Course Deleted"], 200);
    }

    public function getCourseStudents($id){
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Get Course Students'], 403);
        }

        $course = Course::find($id);
        if(!$course){
            return response()->json(['errors' => 'There is no course with this id !'], 400);
        }

        $students = array();
        foreach ($course->Reservations as $res){
            $student = User::find($res->user_id);
            $students[] = $student;
        }

        return response()->json($students, 200);
    }

    public function startCourse(Request $request, $id)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Start Course'], 403);
        }

        $course = Course::find($id);
        if(!$course){
            return response()->json(['errors' => 'There is no Course with this id !'], 400);
        }

        if (count($course->Reservations) == 0){
            return response()->json(['errors'=>'The course does not have any student !'], 400);
        }

        if ($course->state != 0) {
            return response()->json(['errors'=>'You Can Not Start This Course'], 400);
        }

        $course->state = 1;
        $course->save();

        return response()->json(['message' => "Course Started"], 200);
    }

    public function finishCourse(Request $request, $id)
    {
        if (Auth::user()->role == 0) {
            return response()->json(['message'=>'Access Denied, You Can Not Finish Course'], 403);
        }

        $course = Course::find($id);

        if(!$course){
            return response()->json(['errors' => 'There is no Course with this id !'], 400);
        }

        if ($course->state != 1) {
            return response()->json(['errors'=>'You Can Not Finish This Course'], 400);
        }

        $course->state = 2;
        $course->save();

        return response()->json(['message' => "Course Finished"], 200);
    }
    
    // Student
    public function getAvailableCourses()
    {
        $courses = Course::where('state', '=', 0)
                    ->orderBy('created_at', 'desc')->get();

        return response()->json($courses, 200);
    }

    public function searchAvailableCourses(Request $request)
    {
        $courses = Course::where([['state', '=', 0],
                                ['course_name', 'LIKE', '%' . $request['query'] . '%']
                            ])
                            ->orWhere([['state', '=', 0],
                                ['teacher_name', 'LIKE', '%' . $request['query'] . '%']
                            ])
                            ->orderBy('created_at', 'desc')->get();

        return response()->json($courses, 200);
    }
}
