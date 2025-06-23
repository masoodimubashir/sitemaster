<x-app-layout>

    <x-breadcrumb :names="['Sites']" :urls="['admin/sites']" />

    @if (session('status') === 'create')
        <x-success-message message="Site Created..." />
    @endif

    @if (session('status') === 'update')
        <x-success-message message="Site Verification Updated..." />
    @endif

    @if (session('status') === 'verify')
        <x-success-message message="Site Verification Updated..." />
    @endif

    @if (session('status') === 'delete')
        <x-error-message message="Site Deleted...." />
    @endif

    @if (session('status') === 'error')
        <x-error-message message="Site Cannot Be Deleted...." />
    @endif


    @if (session('status') === 'hasPaymentRecords')
        <x-error-message message="Site Cannot Be Deleted...." />
    @endif


    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
        }
    </style>

    <div class="row">

        <div class="col-lg-12">

            <div class="card-body">


                <div class="d-flex justify-content-end">



                    <!-- Create Site Button -->
                    <div class="col-md-2 text-end">
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#create-site-modal">
                            + Create Site
                        </button>
                    </div>

                </div>

                <div class="table-responsive mt-4">

                    @if (count($sites))


                        <table class="table table-bordered">

                            <thead>

                                <tr>
                                    <th class="bg-info text-white fw-bold"> Site Status </th>
                                    <th class="bg-info text-white fw-bold">Date</th>
                                    <th class="bg-info text-white fw-bold">Site Name</th>
                                    <th class="bg-info text-white fw-bold"> Location </th>
                                    <th class="bg-info text-white fw-bold">Contact No</th>
                                    <th class="bg-info text-white fw-bold"> Site Owner Name </th>
                                    <th class="bg-info text-white fw-bold"> Service Charge(%) </th>
                                    <th class="bg-info text-white fw-bold">Actions</th>
                                </tr>

                            </thead>

                            <tbody>
                                @foreach ($sites as $site)
                                    <tr>

                                        <td class="fw-bold {{ $site->is_on_going ? 'text-success' : 'text-danger' }}">

                                            {{ $site->is_on_going ? 'Open' : 'Closed' }}

                                        </td>

                                        <td> {{ $site->created_at->format('d-M-Y') }} </td>

                                        <td title=" View {{ $site->site_name }} details...">

                                            @if ($site->is_on_going)
                                                <a href="{{ route('sites.show', [base64_encode($site->id)]) }}"
                                                    class="fw-bold link-offset-2 link-underline link-underline-opacity-0">
                                                    {{ ucfirst($site->site_name) }}
                                                </a>
                                            @else
                                                <p>
                                                    {{ $site->site_name }}
                                                </p>
                                            @endif

                                        </td>

                                        <td>

                                            {{ ucfirst($site->location) }} </td>

                                        <td>

                                            <a href="tel:{{ $site->contact_no }}">
                                                +91-{{ $site->contact_no }}
                                            </a>

                                        </td>

                                        <td>
                                            {{ ucfirst($site->site_owner_name) }}
                                        </td>

                                        <td>
                                            {{ $site->service_charge }}
                                        </td>

                                        <td class="space-x-4">

                                            <a href="{{ url('admin/sites/' . $site->id . '/edit') }}"
                                                class="text-black" title="Edit Site" aria-label="Edit Site">
                                                <i class="fas fa-edit me-1"></i>
                                            </a>


                                            <form id="delete-form-{{ $site->id }}"
                                                action="{{ route('sites.destroy', [base64_encode($site->id)]) }}"
                                                method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                            <a href="#"
                                                onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $site->id }}').submit();">
                                                <i
                                                    class="fa-solid fa-trash-o text-xl text-red-600 bg-white rounded-full px-2 py-1"></i>
                                            </a>

                                            <form action="{{ url('/admin/sites/update-on-going', $site->id) }}"
                                                method="POST" class="d-inline">

                                                @csrf

                                                @method('POST')

                                                <button type="submit"
                                                    class="badge badge-pill btn text-white {{ $site->is_on_going ? 'text-bg-success' : 'text-bg-danger' }}">
                                                    {{ $site->is_on_going ? 'Verified' : 'Verify' }}
                                                </button>
                                            </form>

                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>


                        </table>
                    @else
                        <table class="table table-bordered">
                            <thead></thead>
                            <tbody>
                                <tr>
                                    <td class="text-danger fw-bold text-center">No Sites Found...</td>
                                </tr>
                            </tbody>
                        </table>


                    @endif
                </div>


                <div class="mt-4">

                    {{ $sites->links() }}

                </div>

            </div>

        </div>
    </div>



    <!-- Create Site Modal -->
    <div id="create-site-modal" class="modal fade" aria-hidden="true" aria-labelledby="createSiteModalLabel"
        tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSiteModalLabel">Create New Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createSiteForm" class="forms-sample material-form" enctype="multipart/form-data">
                        @csrf

                        <!-- Site Name -->
                        <div class="form-group">
                            <input type="text" name="site_name" id="site_name" />
                            <label for="site_name" class="control-label">Site Name</label><i class="bar"></i>
                        </div>

                        <!-- Service Charge -->
                        <div class="form-group">
                            <input type="number" min="0" name="service_charge" id="service_charge"
                                step="0.01" />
                            <label for="service_charge" class="control-label">Service Charge</label><i
                                class="bar"></i>
                        </div>

                        <!-- Service Charge -->
                        <div class="form-group">
                            <input type="number" min="0" name="contact_no" id="contact_no" step="0.01" />
                            <label for="contact_no" class="control-label">Contact No</label><i class="bar"></i>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <input type="text" name="location" id="location" />
                            <label for="location" class="control-label">Location</label><i class="bar"></i>
                        </div>

                        <div class="row">
                            <!-- Select User -->
                            <div class="col-md-6">
                                <select name="user_id" id="user_id" class="form-select text-black form-select-sm"
                                    style="cursor: pointer">
                                    <option value="" selected>Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select Client -->
                            <div class="col-md-6">
                                <select name="client_id" id="client_id" class="form-select text-black form-select-sm"
                                    style="cursor: pointer">
                                    <option value="" selected>Select Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="flex items-center justify-end mt-4">
                                <button type="button" class="btn btn-secondary me-2"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="submitSiteBtn">
                                    Create Site
                                </button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="messageContainer"></div>



    <script>
        $(document).ready(function() {
            const messageContainer = $('#messageContainer');

            $('#createSiteForm').submit(function(e) {
                e.preventDefault();

                // Clear previous messages and errors
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');
                messageContainer.html('');

                // Button state
                const submitBtn = $('#submitSiteBtn');
                const originalBtnText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...'
                );

                // Form data
                const formData = new FormData(this);

                // AJAX request
                $.ajax({
                    url: '/admin/sites',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status) {
                            // Show success message
                            messageContainer.html(`
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                ${response.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `);

                            // Reset form and hide modal
                            $('#createSiteForm')[0].reset();
                            $('#create-site-modal').modal('hide');

                            // Optional: reload after delay
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'An unexpected error occurred.';

                        if (xhr.responseJSON) {
                            errorMsg = xhr.responseJSON.message || errorMsg;

                            // Handle validation errors (422 status)
                            if (xhr.status === 422 && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                $.each(errors, function(field, messages) {
                                    const input = $(`[name="${field}"]`);
                                    const formGroup = input.closest('.form-group');

                                    if (input.length) {
                                        input.addClass('is-invalid');
                                        if (formGroup.length) {
                                            formGroup.append(
                                                `<div class="invalid-feedback">${messages.join('<br>')}</div>`
                                                );
                                        } else {
                                            input.after(
                                                `<div class="invalid-feedback">${messages.join('<br>')}</div>`
                                                );
                                        }
                                    }
                                });
                            }
                        }

                     
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });

            // Clear validation errors when modal is hidden
            $('#create-site-modal').on('hidden.bs.modal', function() {
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');
                messageContainer.html('');
            });
        });
    </script>




</x-app-layout>
