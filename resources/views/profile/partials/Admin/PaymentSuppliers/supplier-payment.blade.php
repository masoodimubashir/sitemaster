<x-app-layout>
    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    @if ($user === 'admin')
        <x-breadcrumb :names="['Dashboard', 'View ' . $supplier->name, 'Payments']" 
                     :urls="['admin/dashboard', 'admin/suppliers/' . $supplier->id, 'admin/suppliers/' . $supplier->id . '/payments']" />
    @else
        <x-breadcrumb :names="['Suppliers', $supplier->name, 'Payments']" 
                     :urls="['user/dashboard', 'user/suppliers/' . $supplier->id, 'user/suppliers/' . $supplier->id . '/payments']" />
    @endif

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <!-- Supplier Info Card -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-opacity-10 rounded p-3 me-3">
                                <i class="fas fa-user-tie text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Supplier Information</h6>
                                <h4 class="mb-2">{{ ucwords($supplier->name) }}</h4>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-phone me-2"></i>
                                    <a href="tel:{{ $supplier->contact_no }}" class="text-decoration-none">
                                        {{ $supplier->contact_no }}
                                    </a>
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>{{ ucwords($supplier->address) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Summary Card -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-opacity-10 rounded p-3 me-3">
                                <i class="fas fa-money-bill-wave text-success fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Payment Summary</h6>
                                @if ($payments->count() > 0)
                                    <h4 class="mb-2">{{ Number::currency($payments->sum('amount'), 'INR') }}</h4>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-list me-2"></i>{{ $payments->total() }} payment records
                                    </p>
                                  
                                @else
                                    <div class="alert alert-warning mb-0 py-2">
                                        No payment records found
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments Table Section -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Payment History</h5>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table  mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="fw-semibold">Date</th>
                                <th class="fw-semibold">Receipt</th>
                                <th class="fw-semibold">Site</th>
                                <th class="fw-semibold">Site Owner</th>
                                <th class="fw-semibold text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr class="align-middle">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ $payment->created_at->format('d M Y') }}</span>
                                            <small class="text-muted">{{ $payment->created_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($payment->screenshot)
                                            <a href="{{ asset('storage/' . $payment->screenshot) }}" 
                                               data-fancybox="gallery" 
                                               class="receipt-thumbnail"
                                               data-caption="Payment on {{ $payment->created_at->format('d M Y') }} - {{ Number::currency($payment->amount, 'INR') }}">
                                                <div class="position-relative" style="width: 60px; height: 60px;">
                                                    <img src="{{ asset('storage/' . $payment->screenshot) }}" 
                                                         alt="Payment receipt" 
                                                         class="img-thumbnail h-100 w-100 object-fit-cover">
                                                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-10">
                                                        <i class="fas fa-search-plus text-white"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        @else
                                            <span class="text-danger">No receipt</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                           
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ ucwords($payment->site->site_name ?? 'N/A') }}</h6>
                                                <small class="text-muted">{{ $payment->site->location ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ ucwords($payment->site->site_owner_name ?? 'N/A') }}
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success">{{ Number::currency($payment->amount, 'INR') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No payments recorded yet</h5>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($payments->hasPages())
                    <div class="card-footer bg-white border-top-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} entries
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    {{ $payments->links() }}
                                </ul>
                            </nav>
                        </div>
                    </div>
                @endif
            </div>
        </div>

 

    <!-- Scripts -->
    <script>
        // Initialize Fancybox
        Fancybox.bind("[data-fancybox]", {
            // Your custom options
        });

        // Tooltip initialization
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</x-app-layout>