<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        if($request->ajax())
        {
            $leaveRequests = LeaveRequest::with('employee')->orderBy('id', 'desc')->get();

            return DataTables::of($leaveRequests)
                ->addColumn('employee_name', function ($leaveRequest) {
                    return $leaveRequest->employee->name;
                })
                ->addColumn('from_date', function ($leaveRequest) {
                    return date('d M Y', strtotime($leaveRequest->from_date));
                })
                ->addColumn('to_date', function ($leaveRequest) {
                    return date('d M Y', strtotime($leaveRequest->to_date));
                })
                // ->addColumn('action', function ($leaveRequest) {
                //     return '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit_leave" data-id="'.$leaveRequest->id.'">Edit</a>&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-sm btn-danger delete_leave" data-id="'.$leaveRequest->id.'">Delete</a>';
                // })
                // ->rawColumns(['action'])
                ->make(true);
        }

        $employees = Employee::all();
        return view('leave_requests.index', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=> false, 'message' => $validator->errors()->first()]);
        }

        if(LeaveRequest::create(array_merge($request->all(), ['status' => 'pending']))) {
            return response()->json(['success'=> true, 'message' => 'Leave request created successfully']);
        }
        return response()->json(['success'=> false, 'message' => 'Failed to create leave request']);
    }

    public function employeeLeaves(Request $request)
    {
        $leaves = LeaveRequest::where('employee_id', $request->employee_id)
            ->whereNotIn('status', ['rejected'])
            ->get(['from_date', 'to_date']);

        $disabledDates = [];

        foreach ($leaves as $leave) {
            $current = strtotime($leave->from_date);
            $end = strtotime($leave->to_date);

            while ($current <= $end) {
                $disabledDates[] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
            }
        }

        return response()->json(array_values(array_unique($disabledDates)));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        //
    }
}
