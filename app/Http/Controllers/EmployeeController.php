<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax())
        {
            $employees = Employee::orderBy('id', 'desc')->get();

            return DataTables::of($employees)
                ->addColumn('action', function ($employee) {
                    return '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit_employee" data-id="'.$employee->id.'">Edit</a>&nbsp;&nbsp;<a href="javascript:void(0)" class="btn btn-sm btn-danger delete_employee" data-id="'.$employee->id.'">Delete</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return view('employees.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:employees,email,' . $request->employee_id,
            'phone' => 'required',
            'department' => 'required',
        ]);

        if($validator->fails())
        {
            return response()->json(['success'=> false, 'message' => $validator->errors()->first()]);
        }

        if($request->employee_id)
        {
            $employee = Employee::find($request->employee_id);
            if(!$employee)
            {
                return response()->json(['success'=> false, 'message' => 'Employee not found.']);
            }
            $employee->update($request->all());
            return response()->json(['success'=> true, 'message' => 'Employee updated successfully.']);
        }
        else
        {
            if(Employee::create($request->all()))
            {
                return response()->json(['success'=> true, 'message' => 'Employee added successfully.']);
            }
        }   
        
        return response()->json(['success'=> false, 'message' => 'Failed to add employee.']);
    }

    public function get_employee(Request $request)
    {
        $employee = Employee::find($request->id);
        if(!$employee)
        {
            return response()->json(['success'=> false, 'message' => 'Employee not found.']);
        }
        return response()->json(['success'=> true, 'data' => $employee]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);
        if(!$employee)
        {
            return response()->json(['success'=> false, 'message' => 'Employee not found.']);
        }   
    
        if($employee->delete())
        {
            LeaveRequest::where('employee_id', $id)->delete();
            return response()->json(['success'=> true, 'message' => 'Employee deleted successfully.']);
        }
        return response()->json(['success'=> false, 'message' => 'Failed to delete employee.']);
    }
}
