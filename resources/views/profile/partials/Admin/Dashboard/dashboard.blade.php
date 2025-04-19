<x-app-layout>


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

        <!-- Stats Overview -->
        <div class="row mb-4 align-items-center">
            <!-- Statistics -->
            <div class="col-md-8">
                <div class="d-flex gap-3">
                    <div class="bg-white px-4 py-3 rounded">
                        <p class="statistics-title text-info fw-bold">Sites Open</p>
                        <h3 class="rate-percentage text-info">{{ $ongoingSites }}</h3>
                    </div>
                    <div class="bg-white px-4 py-3 rounded">
                        <p class="statistics-title text-danger fw-bold">Closed Sites</p>
                        <h3 class="rate-percentage text-danger">{{ $completedSites }}</h3>
                    </div>
                </div>
            </div>

            <!-- Button aligned right -->
            <div class="col-md-2 text-end">
                <a class="btn btn-primary w-100" href="">
                    <i class="menu-icon fa fa-inbox"></i> Suppliers
                </a>
            </div>

            <!-- Create Site Button -->
            <div class="col-md-2 text-end">
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#create-site-modal">
                    + Create Site
                </button>
            </div>

        </div>




        <!-- Summary + Charts -->
        <div class="col-12">
            <div class="card card-rounded mb-4">
                <div class="card-body p-0 d-flex flex-column">

                    <!-- Top Summary -->
                    <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
                        <div><strong>You’ll Give:</strong> ₹0</div>
                        <div><strong>You’ll Get:</strong> ₹1,000 <span class="text-danger">↓</span></div>
                        <a href="{{ url('/admin/payments') }}" class="btn btn-outline-primary btn-sm">View Report</a>
                    </div>

                    <!-- Filters -->
                    <div class="p-3 border-bottom d-flex gap-2">
                        <input type="text" class="form-control w-25" placeholder="Search for customers">
                        <select class="form-select w-25">
                            <option selected>Filter By</option>
                        </select>
                        <select class="form-select w-25">
                            <option selected>Sort By</option>
                        </select>
                    </div>

                    <!-- Customer List -->
                    <div class="p-3  bg-white rounded shadow-sm">
                        @foreach ($sites as $site)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong>
                                        <a href="{{ url('/admin/sites/' . base64_encode($site->id)) }}">
                                            {{ $site->site_name }}
                                        </a>
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $site->client->name }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        {{ $site->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-success">
                                        {{ $site->total_payments ? $site->total_payments : 0 }}
                                    </strong><br>
                                    <small class="text-muted">Credit</small>
                                </div>
                            </div>
                        @endforeach
                    </div>




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

                // Reset previous error messages and alerts
                $('.error-message').text('');
                messageContainer.html('');

                // Disable button and show spinner
                const submitBtn = $('#submitSiteBtn');
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...'
                );

                // Prepare form data
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

                            $('#create-site-modal').modal('hide');
                            $('#createSiteForm')[0].reset();

                            messageContainer.html(`
                                <div class="alert alert-success mt-3 alert-dismissible fade show" role="alert">
                                    ${response.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `);
                        }

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 2000);

                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {


                            messageContainer.html(`
                                <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                    ${xhr.responseJSON.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `);
                        } else {
                            messageContainer.html(`
                                <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                    ${xhr.responseJSON?.message || 'An unexpected error occurred.'}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `);
                        }

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 2000);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text('Create Site');
                    }
                });
            });
        });
    </script>



</x-app-layout>
