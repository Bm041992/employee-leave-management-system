@extends('layout')
@section('content')
    <h2>Leave Requests</h2>
    <div class="mb-3 text-end">
        <a href="javascript:void(0)" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">Apply Leave</a>
    </div>
    <table class="table table-striped" id="leavesTable">
        <thead>
            <tr>
                <th>Employee</th>
                <th>From</th>
                <th>To</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Admin Notes</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
    <div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="applyLeaveModalLabel">Apply for Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="leaveForm" method="POST">
                    <div class="modal-body">
                        <div id="errorMessage" class="alert alert-danger d-none mb-3"></div>
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Employee</label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="from_date" class="form-label">From Date</label>
                            <input type="text" class="form-control" id="from_date" name="from_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="to_date" class="form-label">To Date</label>
                            <input type="text" class="form-control" id="to_date" name="to_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Apply Leave</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#from_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        $('#to_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        let employeeLeaves = [];

        $('#employee_id').on('change', function () {
            let employeeId = $(this).val();

            if (!employeeId) return;

            $.ajax({
                url: '{{ route("employee.leaves") }}',
                type: 'POST',
                data: { employee_id: employeeId },
                success: function (disabledDates) {

                    employeeLeaves = disabledDates;

                    $('#from_date').datepicker('destroy');
                    $('#to_date').datepicker('destroy');

                    $('#from_date').datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true,
                        datesDisabled: disabledDates
                    });
                    $('#to_date').datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true,
                        datesDisabled: disabledDates
                    });
                }
            });
        });

        function hasOverlap(fromDate, toDate, leaveDates) {
            let current = new Date(fromDate);
            let end = new Date(toDate);

            while (current <= end) {
                let d = current.toISOString().split('T')[0];

                if (leaveDates.includes(d)) {
                    return true;
                }

                current.setDate(current.getDate() + 1);
            }

            return false;
        }

        $(document).ready(function() {
            jQuery('#leavesTable').DataTable({
                processing: true,
                serverSide: true,
                order:[],
                ajax: {
                    url: "{{ route('leave-requests.index') }}",
                    type: 'GET',
                },
                columns: [
                    { data: 'employee.name', name: 'employee.name' },
                    { data: 'from_date', name: 'from_date',orderable: false,sortable: false,className: 'dt-center' },
                    { data: 'to_date', name: 'to_date',orderable: false,sortable: false,className: 'dt-center' },
                    { data: 'reason', name: 'reason', orderable: false,sortable: false },
                    { data: 'status', name: 'status', className: 'text-capitalize dt-center' },
                    { data: 'admin_notes', name: 'admin_notes', orderable: false,sortable: false }
                ]
            });

            $('#leaveForm').validate({
                rules: {
                    employee_id: {
                        required: true
                    },
                    from_date: {
                        required: true,
                        date: true,
                        max: function() {
                            if($('#to_date').val()) {
                                return $('#to_date').val();
                            }
                            return;
                        }
                    },
                    to_date: {
                        required: true,
                        date: true,
                        min: function() {
                            if($('#from_date').val()) {
                                return $('#from_date').val();
                            }
                            return;
                        }
                    },
                    reason: {
                        required: true
                    }
                },
                messages: {
                    employee_id: {
                        required: "Please select an employee"
                    },
                    from_date: {
                        required: "Please select a start date",
                        date: "Please enter a valid date"
                    },
                    to_date: {
                        required: "Please select an end date",
                        date: "Please enter a valid date"
                    },
                    reason: {
                        required: "Please provide a reason for leave"
                    }
                },
                submitHandler: function(form) {
                    
                    let fromDate = $('#from_date').val();
                    let toDate = $('#to_date').val();

                    if (hasOverlap(fromDate, toDate, employeeLeaves)) {
                        jQuery('#errorMessage').html('Selected dates overlap with existing leave.').removeClass('d-none ');
                        return false;
                    }

                    $.ajax({
                        url: "{{ route('leave-requests.store') }}",
                        method: 'POST',
                        data: $('#leaveForm').serialize(),
                        dataType: 'json',
                        success: function(response) {
                            jQuery('#errorMessage').html('').addClass('d-none');
                            if(response.success) {
                                $('#applyLeaveModal').modal('hide');
                                $('#leaveForm')[0].reset();
                                $('#leavesTable').DataTable().ajax.reload();
                                alert(response.message);
                            }
                            else {
                                jQuery('#errorMessage').html(response.message).removeClass('d-none');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = "An error occurred. Please try again.";
                            $('#errorMessage').html(errorMessage).removeClass('d-none');
                        }
                    });
                }
            });
        });

        jQuery('#applyLeaveModal').on('hidden.bs.modal', function () {
            jQuery('#errorMessage').html('').addClass('d-none');
            $('#leaveForm')[0].reset();
            jQuery('#leaveForm').validate().resetForm();
            $('#leave_id').val('');
        });
    </script>
@endpush