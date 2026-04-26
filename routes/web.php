<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AdminController;

Route::get('/', [EmployeeController::class, 'index'])->name('employees');
Route::post('get_employee', [EmployeeController::class, 'get_employee'])->name('employees.get_employee');
Route::resource('employees', EmployeeController::class);

Route::post('/employee-leaves', [LeaveRequestController::class, 'employeeLeaves'])->name('employee.leaves');
Route::resource('leave-requests', LeaveRequestController::class)->only(['index','create','store']);

Route::get('/admin/leaves', [AdminController::class, 'index'])->name('admin.leaves');
Route::post('/admin/update-leave-status', [AdminController::class, 'updateStatus'])->name('admin.leave.status');