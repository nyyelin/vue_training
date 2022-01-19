<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Model\Inquire;
use App\Model\Section;
use App\Model\Township;
use App\Model\Course;
use App\Model\Student;
use App\Model\Duration;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\InquireResource;
use App\Http\Resources\StudentResource;
use Carbon\Carbon;


class InquireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $townships = Township::all();
        $sections = Section::all();

        // $inquires = Inquire::all();

        // $inquires = Inquire::with(['section' => function($q1){
        //     $q1->with(['duration' => function($q2){
        //         $q2->where('course_id', 1);
        //     }]);
        // }])->get();

        $hr_ygn_inquires = Inquire::whereHas('section', function($q1){
            $q1->whereHas('duration', function($q2){
                $q2->where('course_id', 1);
            });
        })->get();

        $hr_mdy_inquires = Inquire::whereHas('section', function($q1){
            $q1->whereHas('duration', function($q2){
                $q2->where('course_id', 2);
            });
        })->get();

        $php_inquires = Inquire::whereHas('section', function($q1){
            $q1->whereHas('duration', function($q2){
                $q2->where('course_id', 3);
            });
        })->get();

        $ios_inquires = Inquire::whereHas('section', function($q1){
            $q1->whereHas('duration', function($q2){
                $q2->where('course_id', 4);
            });
        })->get();

        $php_mdy_inquires = Inquire::whereHas('section', function($q1){
            $q1->whereHas('duration', function($q2){
                $q2->where('course_id', 5);
            });
        })->get();

        $inquireAll=Inquire::all();
        $inquiresAlls=InquireResource::collection($inquireAll);

            // $titles = DB::table('inquires')->pluck('name');


        // $php_inquires_new = Inquire::with('section', 'section.duration', 'section.duration.course')->where('section.duration.courseid','=',3)->get();
        // dd($php_inquires_new);

        $hr_ygn_inquires =  InquireResource::collection($hr_ygn_inquires);
        $hr_mdy_inquires =  InquireResource::collection($hr_mdy_inquires);
        $php_inquires =  InquireResource::collection($php_inquires);
        $ios_inquires =  InquireResource::collection($ios_inquires);
        $php_mdy_inquires = InquireResource::collection($php_mdy_inquires);

        return response()->json([
            'hr_ygn_inquires' => $hr_ygn_inquires,
            'hr_mdy_inquires'   => $hr_mdy_inquires,
            'php_inquires'  =>  $php_inquires,
            'ios_inquires'  =>  $ios_inquires,
            'php_mdy_inquires' => $php_mdy_inquires,
            'inquires'=>$inquiresAlls
        ],200);


    }

    public function print($id)
    {
        $inquire = Inquire::find($id)->toArray();

        $design = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title>Page Title</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

        </head>
        <body>

            <div class="container">
                <div class="row">
                    <div class="col-md-12">

                        <div class="card shadow mb-4">

                            <div class="card-header py-3">

                            <h3 class="m-0 font-weight-bold text-danger">

                                '.$inquire["name"].'

                            </h3>



                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </body>
        </html>
        ';

        return response()->json([
            'inquires' => $design,
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // dd($request);
         $this->validate($request, [
            'name'  => 'required',
        ]);

        $course_id = request('course');

        $course = Course::find($course_id);

        $course_fees = $course->fees;
        $payment = request('paymentamount');

        $request_paymentdate = request('paymentdate');

        if ($payment > 0) 
        {
            $paymentdate = Carbon::parse($request_paymentdate)->format('dmy');
            $day = Carbon::parse($request_paymentdate)->format('d');

            $course_codeno = $course->codeno;
            $city_zipcode = $course->location->city->zipcode;

            $temp_receiveno = $paymentdate.$course_codeno.$city_zipcode;

            $lastinquire=Inquire::where('receiveno','like', '%' .$temp_receiveno. '%')->orderBy('receiveno','desc')
                ->first();

            if($lastinquire == null)
            {
                $receiveno = $temp_receiveno.'001';
            }

            else
            {
                $lastinquire_receiveno = $lastinquire->receiveno;

                if($day < 10)
                {
                    $receiveno = '0'.++$lastinquire_receiveno;
                }
                else
                {
                    $receiveno = ++$lastinquire_receiveno;
                }
                
            }

        }
        else
        {
            $receiveno = 0;
        }

        // dd($receiveno);

        $inquire = Inquire::create([
            'name'  =>  request('name'),
            'inquireno' =>  request('inquireno'),
            'receiveno' =>  $receiveno,
            'dob' => request('dob'),
            'age'  =>  request('age'),
            'gender'  =>  request('gender'),
            'address' => request('address'),
            'phno' => request('phoneno'),
            'email' => request('email'),
            'installmentdate' => $request_paymentdate,
            'installmentamount' => $payment,
            'remark' => request('remark'),
            'position' => request('position'),
            'camp' => request('camp'),
            'education' => request('education'),
            'acceptedyear' => request('acceptedyear'),
            'section_id' => request('section_id'),
            'township_id' => request('township_id'),
            // 'status' => request('status'),
            //'course_id' => request('course_id'),
            'user_id'    =>  Auth::user()->id

        ]);

        if ($course_fees == $payment)
        {
            $student = Student::create([
                'secinstallmentdate'=>request('paymentdate'),
                'secinstallmentamount'=>0,
                'remark'=>request('remark'),
                'resume'=>'no resume',
                'status'=>1,
                'inquire_id'=>$inquire->id,
                'user_id'=>Auth::user()->id
            ]);
        }



        $id = $inquire->id;
        // dd($id);
        // $new_inquire = Inquire::find($id);

        // $inquire_resource = new InquireResource($inquire);

        $inquires =  DB::table('inquires')
        ->select(
                'inquires.*',
                'townships.name as townshipname',
                'sections.title as sectiontitle',
                'sections.codeno as codeno',
                'courses.name as course',
                'courses.fees as fees',
                'courses.id as courseid',
                'sections.startdate as startdate',
                'sections.id as s_id',
                'sections.codeno as s_codeno',
                'sections.title as s_title',
                'sections.startdate as s_startdate',
                'sections.enddate as s_enddate',
                'durations.time as d_time',
                'durations.days as d_days',
                'durations.during as d_during',
                'courses.name as c_name',
                'cities.name as city_name')
            ->join('townships', 'townships.id', '=', 'inquires.township_id')
            ->join('sections', 'sections.id', '=', 'inquires.section_id')
            ->join('durations', 'durations.id', '=', 'sections.duration_id')
            ->join('courses','courses.id','=','durations.course_id')
            ->join('locations','locations.id','=','courses.location_id')
            ->join('cities','cities.id','=','locations.city_id')
            ->where('inquires.id','=',$id)
            ->orderBy('inquires.id','desc')
            ->get();

        // dd($inquires);

        $inquire =  new InquireResource($inquires[0]);

        return response()->json([
            'inquire'  =>  $inquire,
            'id'    =>   0,
            'course_id' =>  $course_id,
            'message'   =>  'Successfully Added!'
        ],200);


   }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $inquires =  DB::table('inquires')
        //     ->select(
        //         'inquires.*',
        //         'townships.name as townshipname',
        //         'sections.title as sectiontitle',
        //         'sections.codeno as codeno',
        //         'courses.name as course',
        //         'courses.fees as fees',
        //         'sections.startdate as startdate',
        //         'sections.id as s_id',
        //         'sections.codeno as s_codeno',
        //         'sections.title as s_title',
        //         'sections.startdate as s_startdate',
        //         'sections.enddate as s_enddate',
        //         'durations.time as d_time',
        //         'durations.days as d_days',
        //         'durations.during as d_during',
        //         'courses.name as c_name',
        //         'cities.name as city_name',
        //     )
        //     ->join('townships', 'townships.id', '=', 'inquires.township_id')
        //     ->join('sections', 'sections.id', '=', 'inquires.section_id')
        //     ->join('durations', 'durations.id', '=', 'sections.duration_id')
        //     ->join('courses','courses.id','=','durations.course_id')
        //     ->join('locations','locations.id','=','courses.location_id')
        //     ->join('cities','cities.id','=','locations.city_id')
        //     ->orderBy('inquires.id','desc')
        //     ->where('inquires.section_id',$id)
        //     ->get();

        $inquires = Inquire::doesntHave('student')
            ->where('section_id',$id)
            ->get();


            // dd($inquires);

        $inquires =  InquireResource::collection($inquires);

        return response()->json([
            'inquires' => $inquires,
        ],200);
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
        //

        $inquire = Inquire::find($id);

        $inquire->name = request('name');
        $inquire->receiveno = request('receiveno');
        $inquire->dob = request('dob');
        $inquire->age = request('age');
        $inquire->gender = request('gender');
        $inquire->address = request('address');
        $inquire->phno = request('phno');
        $inquire->installmentdate = request('installmentdate');
        $inquire->installmentamount = request('installmentamount');
        $inquire->remark = request('remark');
        $inquire->position = request('position');
        $inquire->camp = request('camp');
        $inquire->education = request('education');
        $inquire->acceptedyear = request('acceptedyear');
        $inquire->section_id = request('section_id');
        $inquire->township_id = request('township_id');
        $inquire->actionstatus = request('actionstatus');
        $inquire->user_id=  Auth::user()->id;
        $inquire->save();

        return response()->json([
            'message'   =>  'Inquire updated successfully!'
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //

        $inquire = Inquire::find($id);
        $inquire->delete();

        return response()->json([
            'message'   =>  'Staff deleted successfully!'
        ],200);
    }

    public function getInquire($id){
       $inquire =  DB::table('inquires')
            ->select(
                'inquires.*',
                'townships.name as townshipname',
                'sections.title as sectiontitle',
                'sections.codeno as codeno',
                'courses.name as course',
                'courses.fees as fees',
                'courses.id as courseid',
                'sections.startdate as startdate',
                'sections.id as s_id',
                'sections.codeno as s_codeno',
                'sections.title as s_title',
                'sections.startdate as s_startdate',
                'sections.enddate as s_enddate',
                'durations.time as d_time',
                'durations.days as d_days',
                'durations.during as d_during',
                'courses.name as c_name',
                'cities.name as city_name')
            ->join('townships', 'townships.id', '=', 'inquires.township_id')
            ->join('sections', 'sections.id', '=', 'inquires.section_id')
            ->join('durations', 'durations.id', '=', 'sections.duration_id')
            ->join('courses','courses.id','=','durations.course_id')
            ->join('locations','locations.id','=','courses.location_id')
            ->join('cities','cities.id','=','locations.city_id')
            ->orderBy('inquires.id','desc')
            ->where('inquires.id',$id)
            ->get();


            // dd($inquires);

        $inquires =  InquireResource::collection($inquire);

        return response()->json([
            'inquires' => $inquires,
        ],200);
    }

    public function lastinquire(){
        $inquire=Inquire::orderBy('id','desc')
                ->first();
        // dd($inquire);


        // if (empty($inquire)) {
        //     return response()->json([
        //         'inquire'  =>  $inquire['receiveno'=>date('dmY').'0001'],
        //         'message'   =>  'Successfully selected Last Inquired!'
        //     ],200);
        // }else{
        //     return response()->json([
        //         'inquire'  =>  $inquire,
        //         'message'   =>  'Successfully selected Last Inquired!'
        //     ],200);
        // }

        if ($inquire == null) {
            $inquire = [
                "inquireno" => 0
            ];
            return response()->json([
                'inquire'  =>  $inquire,
                'message'   =>  'Successfully selected Last Inquired!'
            ],200);
        }else{
            $inquire = new InquireResource($inquire);

            return response()->json([
                'inquire'  =>  $inquire,
                'message'   =>  'Successfully selected Last Inquired!'
            ],200);
        }
        return response()->json([
            'inquire'  =>  $inquire,
            'message'   =>  'Successfully selected Last Inquired!'
        ],200);


       // echo request('courseid')." and ".request('sectionid')." and ".request('durationid');
        $id=request('sectionid');
        $inquires = Inquire::doesntHave('student')
            ->where('section_id',$id)
            ->get();
            
    }



}
