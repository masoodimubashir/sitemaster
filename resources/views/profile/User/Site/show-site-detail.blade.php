<x-app-layout>
    <x-breadcrumb :names="['Sites', $site->site_name]" :urls="['admin/sites', 'admin/sites/' . base64_encode($site->id)]" />

  

    <!-- Site Info Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center">
                        <i class="fas fa-building text-info fs-3 me-3"></i>
                        <div>
                            <h6 class="text-muted mb-1">Site Name</h6>
                            <h5 class="mb-0">{{ ucwords($site->site_name) }}</h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user text-info fs-3 me-3"></i>
                        <div>
                            <h6 class="text-muted mb-1">Owner</h6>
                            <h5 class="mb-0">{{ ucwords($site->site_owner_name) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center">
                        <i class="fa-solid fa-phone text-info fs-3 me-3"></i>
                        <div>
                            <h6 class="text-muted mb-1">Contact</h6>
                            <h5 class="mb-0">
                                <a href="tel:+91-{{ $site->contact_no }}"
                                    class="text-decoration-none">+91-{{ $site->contact_no }}</a>
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-map-marker-alt text-info fs-3 me-3"></i>
                        <div>
                            <h6 class="text-muted mb-1">Location</h6>
                            <h5 class="mb-0">{{ ucwords($site->location) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Site Financial Summary -->
    <div class="card mb-4 border-info">

        <!-- Phase Tabs -->
        @if (count($phaseData) > 0)

            <div class="card-body mt-3">
                <ul class="nav nav-pills mb-4">
                    @foreach ($phaseData as $key => $phase)
                        <li class="nav-item">
                            <a class="nav-link {{ $key === 0 ? 'active' : '' }}" href="#phase-{{ $key }}"
                                data-bs-toggle="tab">{{ ucfirst($phase['phase']) }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="tab-content">
                @foreach ($phaseData as $key => $phase)
                    <div class="tab-pane fade {{ $key === 0 ? 'show active' : '' }}" id="phase-{{ $key }}">
                        <!-- Phase Summary Card -->
                        <div class="card mb-4 border-info">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">{{ ucfirst($phase['phase']) }} Phase Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-bold">Construction:</span>
                                            <span>₹{{ number_format($phase['construction_total_amount'], 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-bold">Contractor:</span>
                                            <span>₹{{ number_format($phase['square_footage_total_amount'], 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-bold">Expenses:</span>
                                            <span>₹{{ number_format($phase['daily_expenses_total_amount'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-bold">Wasta:</span>
                                            <span>₹{{ number_format($phase['daily_wastas_total_amount'], 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-bold">Labour:</span>
                                            <span>₹{{ number_format($phase['daily_labours_total_amount'], 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">Subtotal:</span>
                                            <span>₹{{ number_format($phase['phase_total'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-bold">Service Charge (10%):</span>
                                            <span>₹{{ number_format($phase['phase_total_with_service_charge'] - $phase['phase_total'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">Total Amount:</span>
                                            <span
                                                class="fw-bold">₹{{ number_format($phase['phase_total_with_service_charge'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-bold">Total Paid:</span>
                                            <span>₹{{ number_format($phase['total_paid'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-bold">Total Due:</span>
                                            <span>₹{{ number_format($phase['total_due'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">Effective Balance:</span>
                                            <span
                                                class="fw-bold">₹{{ number_format($phase['effective_balance'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Phase Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 mb-3">

                

                            <a href="{{ url('user/download-phase/report', ['id' => base64_encode($phase['phase_id'])]) }}"
                                class="btn btn-success btn-sm text-white">
                                Generate PDF
                            </a>

                        </div>

                        <!-- Phase Data Tables -->
                        @php
                            $tables = [
                                'construction_material_billings' => [
                                    'label' => 'Materials',
                                    'data' => $phase['construction_material_billings'],
                                ],
                                'square_footage_bills' => [
                                    'label' => 'Contractor',
                                    'data' => $phase['square_footage_bills'],
                                ],
                                'daily_expenses' => ['label' => 'Expenses', 'data' => $phase['daily_expenses']],
                                'daily_wastas' => ['label' => 'Wasta', 'data' => $phase['daily_wastas']],
                                'daily_labours' => ['label' => 'Labour', 'data' => $phase['daily_labours']],
                            ];
                        @endphp

                        @foreach ($tables as $tableKey => $table)
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white fw-bold text-uppercase">
                                    {{ $table['label'] }}
                                    {{-- <span class="float-end">Total: ₹{{ number_format($phase["{$tableKey}"], 2) }}</span> --}}
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Description</th>
                                                    <th>Supplier</th>
                                                    <th>Amount</th>
                                                    <th>Total (with SC)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($table['data'] as $entry)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($entry['created_at'])->format('d-M-Y') }}
                                                        </td>
                                                        <td>{{ $entry['description'] ?? '-' }}</td>
                                                        <td>{{ $entry['supplier'] ?? '-' }}</td>
                                                        <td>₹{{ number_format($entry['debit'], 2) }}</td>
                                                        <td>₹{{ number_format($entry['total_amount_with_service_charge'], 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                No phase data available for this site.
            </div>
        @endif



        {{-- <div id="modal-construction-billings{{ $phase->id }}" class="modal fade" aria-hidden="true"
            aria-labelledby="exampleModalToggleLabel" tabindex="-1">

            <div class="modal-dialog modal-dialog-centered modal-lg">

                <div class="modal-content">

                    <div class="modal-body">

                        <form enctype="multipart/form-data" class="forms-sample material-form"
                            id="constructionBillingForm">

                            @csrf


                            <div class="form-group">
                                <input type="number" name="amount" id="amount" />
                                <label for="amount" class="control-label">Material Price</label>
                                <i class="bar"></i>
                                <p class=" mt-1 text-danger" id="amount-error"></p>
                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-select text-black form-select-sm"
                                        id="exampleFormControlSelect3" name="item_name" style="cursor: pointer">
                                        <option value="">Select Item
                                        </option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->item_name }}">
                                                {{ $item->item_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class=" mt-1 text-danger" id="item_name-error"></p>
                                </div>

                                <div class="col-md-6">
                                    <select class="form-select text-black form-select-sm"
                                        id="exampleFormControlSelect3" name="supplier_id" style="cursor: pointer">
                                        <option value="">Select Supplier
                                        </option>
                                        @foreach ($raw_material_providers as $supplier)
                                            <option value="{{ $supplier->id }}">
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class=" mt-1 text-danger" id="supplier_id-error"></p>
                                </div>

                                <div class=" col-md-6 mt-3">
                                    <input id="phase_id" type="hidden" name="phase_id" placeholder="Phase"
                                        value="{{ $phase->id }}" />
                                </div>
                            </div>

                            <div class="mt-3">
                                <input class="form-control form-control-md" id="image" type="file"
                                    name="image">
                                <p class=" mt-1 text-danger" id="image-error"></p>
                            </div>

                            <x-primary-button>
                                {{ __('Create Billing') }}
                            </x-primary-button>


                        </form>

                    </div>
                </div>
            </div>

        </div>

        <div id="modal-square-footage-bills{{ $phase->id }}" class="modal fade" aria-hidden="true"
            aria-labelledby="exampleModalToggleLabel" tabindex="-1">

            <div class="modal-dialog modal-dialog-centered modal-lg">

                <div class="modal-content">

                    <div class="modal-body">

                        <form id="squareFootageBills" enctype="multipart/form-data"
                            class="forms-sample material-form">

                            @csrf

                            <div class="form-group">
                                <input id="wager_name" type="text" name="wager_name" />
                                <label for="wager_name" class="control-label" />Work
                                Type</label><i class="bar"></i>
                                <p class="text-danger" id="wager_name-error"></p>
                            </div>

                            <div class="form-group">
                                <input id="price" type="number" name="price" />
                                <label for="price" class="control-label" />Price</label><i class="bar"></i>
                                <p class="text-danger" id="price-error"></p>
                            </div>

                            <div class="form-group">
                                <input id="multiplier" type="number" name="multiplier" />
                                <label for="multiplier" class="control-label">Multiplier</label><i
                                    class="bar"></i>

                                <p class="text-danger" id="multiplier-error"></p>
                            </div>

                            <div class="row">

                                <div class="col-md-6">
                                    <select class="form-select text-black form-select-sm"
                                        id="exampleFormControlSelect3" name="type" style="cursor: pointer">
                                        <option value="">Select Type</option>
                                        <option value="per_sqr_ft">Per Square Feet</option>
                                        <option value="per_unit">Per Unit</option>
                                        <option value="full_contract">Full Contract
                                        </option>
                                    </select>
                                    <p class="text-danger" id="type-error"></p>
                                </div>

                                <div class="col-md-6">
                                    <select class="form-select text-black form-select-sm" id="supplier_id"
                                        name="supplier_id" style="cursor: pointer">
                                        <option value="">Select Supplier</option>
                                        @foreach ($workforce_suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <p class="text-danger" id="supplier_id-error"></p>

                                </div>

                                <div class=" col-md-6 mt-3">
                                    <input id="phase_id" type="hidden" name="phase_id" placeholder="Phase"
                                        value="{{ $phase->id }}" />
                                </div>
                            </div>


                            <div class="mt-3">
                                <label for="image">Item Bill</label>
                                <input class="form-control form-control-md" id="image" type="file"
                                    name="image_path">
                                <p class="text-danger" id="image_path-error"></p>

                            </div>


                            <div class="mt-3">
                                <x-primary-button>
                                    {{ __('Create Bill') }}
                                </x-primary-button>
                            </div>



                        </form>

                    </div>
                </div>
            </div>
        </div>

        <div id="modal-daily-expenses{{ $phase->id }}" class="modal fade" aria-hidden="true"
            aria-labelledby="exampleModalToggleLabel" tabindex="-1">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <form id="dailyExpenses" class="forms-sample material-form">

                            @csrf

                            <div class="form-group">
                                <input id="item_name" type="text" name="item_name" />
                                <label for="item_name" class="control-label">Item
                                    Name</label><i class="bar"></i>
                                <p class="text-danger" id="date-error"></p>
                            </div>

                            <div class="form-group">
                                <input id="price" type="number" name="price" />
                                <label for="price" class="control-label">Price</label><i class="bar"></i>
                                <p class="text-danger" id="description-error"></p>
                            </div>

                            <div class="form-group">
                                <input id="site_id" type="number" name="site_id" value="{{ $site->id }}" />
                            </div>

                            <div class=" col-md-6 mt-3">
                                <input id="phase_id" type="hidden" name="phase_id" placeholder="Phase"
                                    value="{{ $phase->id }}" />
                                <p class="text-danger" id="amount-error"></p>
                            </div>


                            <div class="col-12 mt-3">

                                <input class="form-control" type="file" id="formFile" name="bill_photo">

                                <p class="text-danger" id="category_id-error"></p>

                            </div>


                            <x-primary-button class="mt-3">
                                {{ __('Create Bill') }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>

            </div>
        </div> --}}

    </div>

    {{-- 
    <div id="phase" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
        tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-body">

                    <form class="forms-sample material-form" id="phaseForm">

                        @csrf

                        <div class="form-group">
                            <input type="text" name="phase_name" id="phase_name" />
                            <label for="phase_name" class="control-label">Phase Name</label>
                            <i class="bar"></i>
                            <x-input-error :messages="$errors->get('phase_name')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="hidden" name="site_id" value="{{ $site->id }}" />
                            <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Create Phase') }}
                            </x-primary-button>
                        </div>

                    </form>

                </div>
            </div>

        </div>

    </div> --}}



    {{-- <div id="payment-supplier" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
        tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-body">

                    <form id="payment_supplierForm" class="forms-sample material-form" enctype="multipart/form-data">

                        @csrf

                        <div class="form-group">
                            <input type="number" min="0" name="amount" step="0.01" />
                            <label for="input" class="control-label">Amount</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="hidden" name="site_id" value="{{ $site->id }}" />
                            <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                        </div>

                        <select name="payment_initiator" id="payment_initiator"
                            class="form-select text-black form-select-sm" style="cursor: pointer"
                            onchange="togglePayOptions()">
                            <option value="" selected>Select Payee</option>
                            <option value="1">Supplier</option>
                            <option value="0">Admin</option>
                        </select>

                        <div id="supplierOptions" style="display: none;" class="mt-3">
                            <select name="supplier_id" id="supplier_id" class="form-select text-black form-select-sm"
                                style="cursor: pointer">
                                <option for="supplier_id" value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="mt-3">
                                <input class="form-control form-control-md" id="image" type="file"
                                    name="screenshot">
                            </div>
                        </div>

                        <div id="adminOptions" style="display: none;" class="mt-4">
                            <div class="row g-3">
                                <div class="col-auto">
                                    <label for="transaction_sent">
                                        <input type="radio" name="transaction_type" id="transaction_sent"
                                            value="1"> Sent
                                    </label>
                                </div>
                                <div class="col-auto">
                                    <label for="transaction_received">
                                        <input type="radio" name="transaction_type" id="transaction_received"
                                            value="0"> Received
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">

                            <x-primary-button>
                                {{ __('Pay') }}
                            </x-primary-button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div> --}}

    {{-- @push('scripts')
        <script>
            // Delete functionality
            $(document).on('click', '.delete-link', function(e) {
                e.preventDefault();

                const button = $(this);
                const id = button.data('id');
                const type = button.data('type');
                const row = button.closest('tr');

                const routes = {
                    'Materials': 'construction-material-billings',
                    'Contractor': 'square-footage-bills',
                    'Expenses': 'daily-expenses',
                    'Wasta': 'dailywager',
                    'Labour': 'daily-wager-attendance'
                };

                if (!routes[type]) {
                    showAlert('error', 'Invalid operation type');
                    return;
                }

                if (!confirm('Are you sure you want to delete this item?')) {
                    return;
                }

                $.ajax({
                    url: `{{ url('admin') }}/${routes[type]}/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        showAlert('success', response.message);
                        row.fadeOut(400, function() {
                            $(this).remove();
                        });
                    },
                    error: function(error) {
                        const message = error.status === 404 ?
                            (error.responseJSON?.error || 'Resource not found') :
                            'An error occurred. Please try again.';
                        showAlert('error', message);
                    }
                });
            });



            // Helper function for showing alerts
            function showAlert(type, message) {
                const container = $('#messageContainer');
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                container.empty().append(`
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="fas ${icon} me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);

                setTimeout(() => {
                    container.find('.alert').fadeOut(400, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        </script>
    @endpush --}}

</x-app-layout>
