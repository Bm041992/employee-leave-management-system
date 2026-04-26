@extends('layout')

@section('content')
    <h1>Employees</h1>
    <div class="mb-3 text-end">
        <a href="javascript:void(0)" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#employeeModal">Add Employee</a>
    </div>
    <table class="table table-striped" id="employeesTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>

    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="employeeForm" method="POST">
                    <div class="modal-body">
                        <div id="errorMessage" class="alert alert-danger d-none mb-3"></div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="employee_id" id="employee_id" value="">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).ready(function() {
                $('#employeesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    order: [],
                    ajax: {
                        url:'{{ route("employees") }}',
                        type: 'GET'
                    },
                    columns: [
                        { data: 'name', name: 'name' },
                        { data: 'email', name: 'email' },
                        { data: 'department', name: 'department', className: 'dt-center' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'dt-center' }
                    ]
                });
            });

            jQuery('#employeeForm').validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 2,
                        maxlength: 255
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    phone: {
                        required: true,
                        number: true,
                        minlength: 10,
                        maxlength: 15
                    },
                    department: {
                        required: true,
                        minlength: 2,
                        maxlength: 255
                    }
                },
                messages: {
                    name: {
                        required: "Please enter employee name",
                        minlength: "Name must be at least 2 characters",
                        maxlength: "Name cannot exceed 255 characters"
                    },
                    email: {
                        required: "Please enter employee email",
                        email: "Please enter a valid email address"
                    },
                    phone: {
                        required: "Please enter employee phone number",
                        number: "Please enter a valid phone number",
                        minlength: "Phone number must be at least 10 digits",
                        maxlength: "Phone number cannot exceed 15 digits"
                    },
                    department: {
                        required: "Please enter employee department",
                        minlength: "Department name must be at least 2 characters",
                        maxlength: "Department name cannot exceed 255 characters"
                    }
                },
                submitHandler: function(form){

                    jQuery('#errorMessage').addClass('d-none').html('');

                    $.ajax({
                        url: '{{ route("employees.store") }}',
                        method: 'POST',
                        data: $('#employeeForm').serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                alert(response.message);
                                $('#employee_id').val('');
                                $('#employeeModal').modal('hide');
                                $('#employeesTable').DataTable().ajax.reload();
                                $('#employeeForm')[0].reset();
                            } else {
                                jQuery('#errorMessage').removeClass('d-none').html(response.message);
                            }
                        },
                        error: function(xhr) {
                            
                            jQuery('#errorMessage').html("An error occurred while saving the employee. Please try again.").removeClass('d-none');
                        }
                    });
                }
            });

            jQuery('#employeeModal').on('hidden.bs.modal', function () {
                jQuery('#employeeForm')[0].reset();
                $('#employee_id').val('');
                jQuery('#employeeForm').validate().resetForm();
                jQuery('#errorMessage').addClass('d-none').html('');
            });

            jQuery(document).on('click', '.edit_employee', function(){
                let employee_id = jQuery(this).data('id');
                jQuery('#errorMessage').addClass('d-none').html('');

                $.ajax({
                    url: '{{ route("employees.get_employee") }}',
                    method: 'POST',
                    data: { id: employee_id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            let employee = response.data;
                            jQuery('#employee_id').val(employee.id);
                            jQuery('#name').val(employee.name);
                            jQuery('#email').val(employee.email);
                            jQuery('#phone').val(employee.phone);
                            jQuery('#department').val(employee.department);
                            jQuery('#employeeModalLabel').text('Edit Employee');
                            jQuery('#employeeModal').modal('show');
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        alert("An error occurred while fetching employee details. Please try again.");
                    }
                });
            });

            jQuery(document).on('click', '.delete_employee', function(){
                if(!confirm("Are you sure you want to delete this employee?")) {
                    return false;
                }

                let employee_id = jQuery(this).data('id');

                $.ajax({
                    
                    url: '{{ route("employees.destroy", ":id") }}'.replace(':id', employee_id),
                    method: 'DELETE',
                    // data: { id: employee_id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            alert(response.message);
                            $('#employeesTable').DataTable().ajax.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        alert("An error occurred while deleting the employee. Please try again.");
                    }
                });
            });
        </script>
    @endpush
@endsection     