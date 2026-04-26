@extends('layout')
@section('content')
    <div class="mb-4">
       <h2>Admin - Manage Leave Requests</h2>
    </div>
    <table class="table table-striped" id="leave-requests-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>From Date</th>
                <th>To Date</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Admin Notes</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Leave Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateStatusForm" method="POST">
                    <div class="modal-body">
                        <div id="errorMessage" class="alert alert-danger d-none mb-3"></div>
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Notes</label>
                            <input type="text" class="form-control" id="admin_notes" name="admin_notes" required>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="leave_request_id" id="leave_request_id" value="">
                        <input type="hidden" name="leave_request_status" id="leave_request_status" value="">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
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
        $(document).ready(function() {
            
            jQuery('#leave-requests-table').DataTable({
                processing: true,
                serverSide: true,
                order:[],
                ajax: {
                    url: "{{ route('admin.leaves') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'employee_name', name: 'employee_name' },
                    { data: 'from_date', name: 'from_date',orderable: false,sortable: false },
                    { data: 'to_date', name: 'to_date',orderable: false,sortable: false },
                    { data: 'reason', name: 'reason',orderable: false,sortable: false },
                    { data: 'status', name: 'status', className: 'text-capitalize dt-center' },
                    { data: 'admin_notes', name: 'admin_notes', orderable: false,sortable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false,className: 'dt-center' },
                ]
            });

            $(document).on('click', '.update_status', function() {
                let id = $(this).data('id');
                let status = $(this).data('status');
                if(status === 'approved') 
                {
                    if(!confirm('Are you sure you want to approve this leave request?')) {
                        return;
                    }
                } else {
                    if(!confirm('Are you sure you want to reject this leave request?')) {
                        return;
                    }
                }
                $('#leave_request_id').val(id);
                $('#leave_request_status').val(status);
                $('#updateStatusModal').modal('show');
            });

            $('#updateStatusForm').validate({
                rules: {
                    admin_notes: {
                        required: true,
                        maxlength: 255
                    }
                },
                messages: {
                    admin_notes: {
                        required: "Please enter notes",
                        maxlength: "Notes cannot exceed 255 characters"
                    }
                },
                submitHandler: function(form) {

                    jQuery('#errorMessage').addClass('d-none').html('');

                    $.ajax({
                        url: '{{ route("admin.leave.status") }}',
                        method: 'POST',
                        data: $(form).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                $('#leave-requests-table').DataTable().ajax.reload();
                                jQuery('#errorMessage').addClass('d-none').html('');
                                jQuery('#updateStatusModal').modal('hide');
                                alert(response.message);
                            } else {
                                jQuery('#errorMessage').removeClass('d-none').html(response.message);
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = "An error occurred while updating the leave status. Please try again.";
                            jQuery('#errorMessage').removeClass('d-none').html(errorMessage);
                        }
                    });
                }
            });

            jQuery('#updateStatusModal').on('hidden.bs.modal', function () {
                jQuery('#updateStatusForm')[0].reset();
                jQuery('#leave_request_id').val('');
                jQuery('#leave_request_status').val('');
                jQuery('#updateStatusForm').validate().resetForm();
                jQuery('#errorMessage').addClass('d-none').html('');
            });
        });
    </script>
@endpush