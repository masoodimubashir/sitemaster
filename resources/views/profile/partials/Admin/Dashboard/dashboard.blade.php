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
        <div class="row mb-4">
            <!-- Statistics Cards -->
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1 text-white">Sites Open</h6>
                                        <h2 class="mb-0">{{ $ongoingSites }}</h2>
                                    </div>
                                    <div class="icon-shape bg-white text-primary rounded-circle p-3">
                                        <i class="fas fa-building fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-secondary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1 text-white">Closed Sites</h6>
                                        <h2 class="mb-0">{{ $completedSites }}</h2>
                                    </div>
                                    <div class="icon-shape bg-white text-danger rounded-circle p-3">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-4 mt-3 mt-md-0">
                <div class="d-flex flex-column h-100 gap-2">
                    <a class="btn btn-outline-info btn-sm w-100 d-flex align-items-center justify-content-center"
                        href="{{ route('suppliers.dashboard') }}">
                        <i class="fas fa-exchange-alt me-2"></i> Switch Suppliers
                    </a>
                    <button class="btn  btn-success btn-sm w-100 d-flex align-items-center justify-content-center"
                        data-bs-toggle="modal" data-bs-target="#create-site-modal">
                        <i class="fas fa-plus me-2"></i> Create Site
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <!-- Search and Filters -->
                    <div class="p-3 ">
                        <form method="GET" action="{{ url()->current() }}">
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Search for customers or sites..."
                                            value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Search</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ url()->current() }}"
                                        class="btn btn-sm btn-outline-secondary text-black w-100">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Sites List -->
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Site Name</th>
                                    <th>Client</th>
                                    <th>Created</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sites as $site)
                                    @php
                                        $baseCost = 0;
                                        foreach ($site->phases as $phase) {
                                            $baseCost +=
                                                ($phase->total_material_billing ?? 0) +
                                                ($phase->total_square_footage ?? 0) +
                                                ($phase->total_daily_expenses ?? 0) +
                                                ($phase->total_labour_cost ?? 0) +
                                                ($phase->total_wasta_cost ?? 0);
                                        }

                                        $servicePercentage = $site->service_charge ?? 0;
                                        $serviceAmount = ($baseCost * $servicePercentage) / 100;
                                        $totalCost = $baseCost + $serviceAmount;

                                        $paid = $site->total_payments ?? 0;
                                        $balance = $totalCost - $paid;
                                        $status = $balance <= 0 ? 'Completed' : 'In Progress';
                                    @endphp

                                    <tr>
                                        <td>
                                            <a href="{{ url('/admin/sites/' . base64_encode($site->id)) }}"
                                                class="fw-bold text-decoration-none">
                                                {{ $site->site_name }}
                                            </a>
                                        </td>
                                        <td>{{ $site->client->name }}</td>
                                        <td>{{ $site->created_at->diffForHumans() }}</td>
                                        <td class="text-end text-success fw-bold">{{ number_format($paid, 2) }}</td>
                                        <td class="text-end text-danger fw-bold">{{ number_format($balance, 2) }}</td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($sites->hasPages())
                        <div class="p-3 border-top">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($sites->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">&laquo;</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $sites->previousPageUrl() }}"
                                                rel="prev">&laquo;</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($sites->getUrlRange(1, $sites->lastPage()) as $page => $url)
                                        @if ($page == $sites->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($sites->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $sites->nextPageUrl() }}"
                                                rel="next">&raquo;</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">&raquo;</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>



    <!-- Create Site Modal -->
    <div id="create-site-modal" class="modal fade" aria-hidden="true" aria-labelledby="createSiteModalLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
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
                            <div id="site_name-error" class="error-message invalid-feedback"></div>
                        </div>

                        <!-- Service Charge -->
                        <div class="form-group">
                            <input type="number" min="0" name="service_charge" id="service_charge"
                                step="0.01" />
                            <label for="service_charge" class="control-label">Service Charge</label><i
                                class="bar"></i>
                            <div id="service_charge-error" class="error-message invalid-feedback"></div>
                        </div>

                        <!-- Service Charge -->
                        <div class="form-group">
                            <input type="number" min="0" name="contact_no" id="contact_no"
                                step="0.01" />
                            <label for="contact_no" class="control-label">Contact No</label><i class="bar"></i>
                            <div id="contact_no-error" class="error-message invalid-feedback"></div>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <input type="text" name="location" id="location" />
                            <label for="location" class="control-label">Location</label><i class="bar"></i>
                            <div id="location-error" class="error-message invalid-feedback"></div>
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
                                <div id="user_id-error" class="error-message invalid-feedback"></div>
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
                                <div id="client_id-error" class="error-message invalid-feedback"></div>
                            </div>


                            <div class="flex items-center justify-end mt-4">
                                <button type="button" class="btn btn-sm btn-secondary me-2"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-success" id="submitSiteBtn">
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
