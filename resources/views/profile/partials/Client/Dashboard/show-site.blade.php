<x-app-layout>
    <x-breadcrumb :names="['Sites']" :urls="['client/dashboard']" />


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




    </div>

    @push('scripts')
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
    @endpush

</x-app-layout>
