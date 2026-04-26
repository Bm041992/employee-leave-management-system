<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
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
                ->addColumn('action', function ($leaveRequest) {
                    if($leaveRequest->status == 'pending') {
                        return '<button class="btn btn-sm btn-success update_status" data-id="'.$leaveRequest->id.'" data-status="approved">Approve</button>&nbsp;&nbsp;<button class="btn btn-sm btn-danger update_status" data-id="'.$leaveRequest->id.'" data-status="rejected">Reject</button>';
                    } 
                    else if($leaveRequest->status == 'approved')
                    {
                        return '<button class="btn btn-sm btn-danger update_status" data-id="'.$leaveRequest->id.'" data-status="rejected">Reject</button>';
                    }
                    else if($leaveRequest->status == 'rejected') 
                    {
                        return '<button class="btn btn-sm btn-success update_status" data-id="'.$leaveRequest->id.'" data-status="approved">Approve</button>';
                    }
                    else {
                        return '';
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    
        return view('admin.leaves');
    }

    public function updateStatus(Request $request)
    {
        $leaveRequest = LeaveRequest::findOrFail($request->leave_request_id);
        $leaveRequest->status = $request->leave_request_status;
        $leaveRequest->admin_notes = $request->admin_notes;
        $leaveRequest->save();
        if($leaveRequest->wasChanged('status'))
        {
            if($request->leave_request_status == 'approved') {
                return response()->json(['success' => true, 'message' => 'Leave request approved successfully']);
            } else {
                return response()->json(['success' => true, 'message' => 'Leave request rejected successfully']);
            }
        }
        return response()->json(['success' => false, 'message' => 'Failed to update leave request status']);
    }
}
