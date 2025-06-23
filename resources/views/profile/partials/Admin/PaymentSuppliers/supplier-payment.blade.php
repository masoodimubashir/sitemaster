<x-app-layout>
    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    @if ($user === 'admin')
        <x-breadcrumb :names="['Dashboard', 'View ' . $supplier->name, 'View ' . $supplier->name . ' Payments']" 
                     :urls="['admin/dashboard', 'admin/suppliers/' . $supplier->id, 'admin/supplier/payments/' . $supplier->id]" />
    @else
        <x-breadcrumb :names="['supplier', $supplier->name, 'View ' . $supplier->name . ' Payments']" 
                     :urls="['user/dashboard', 'user/suppliers/' . $supplier->id, 'user/supplier/payments/' . $supplier->id]" />
    @endif

    <div class="row g-4 mb-4">
        <!-- Supplier Info Cards -->
        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-opacity-10">
                            <i class="fas fa-user text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Supplier</h6>
                            <h5 class="mb-0">{{ ucwords($supplier->name) }}</h5>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="bg-opacity-10">
                            <i class="fa-solid fa-phone text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Contact</h6>
                            <h5 class="mb-0">
                                <a href="tel:{{ $supplier->contact_no }}" class="text-decoration-none">
                                    {{ $supplier->contact_no }}
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-opacity-10">
                            <i class="fas fa-map-marker-alt text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Location</h6>
                            <h5 class="mb-0">{{ ucwords($supplier->address) }}</h5>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="bg-opacity-10">
                            <i class="fas fa-money-bill text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Payments</h6>
                            <h5 class="mb-0">{{ Number::currency($payments->sum('amount'), 'INR') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="fw-bold bg-info text-white">Date</th>
                                <th class="fw-bold bg-info text-white">Screenshot</th>
                                <th class="fw-bold bg-info text-white">Site</th>
                                <th class="fw-bold bg-info text-white">Site Owner</th>
                                <th class="fw-bold bg-info text-white">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($payments->count() > 0)
                                @foreach ($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('d-M-Y') }}</td>
                                        <td>
                                            <a href="{{ asset('storage/' . $payment->screenshot) }}" 
                                               data-fancybox="gallery"
                                               data-caption="Payment - {{ $payment->created_at->format('d M Y') }}">
                                                <img src="{{ asset('storage/' . $payment->screenshot) }}"
                                                    alt="Payment Receipt"
                                                    class="img-thumbnail"
                                                    style="width: 50px; height: 50px; object-fit: cover;">
                                            </a>
                                        </td>
                                        <td>{{ ucwords($payment->site->site_name ?? 'N/A') }}</td>
                                        <td>{{ ucwords($payment->site->site_owner_name ?? 'N/A') }}</td>
                                        <td class="fw-bold text-success">
                                            {{ Number::currency($payment->amount, 'INR') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-danger fw-bold py-4">
                                        No payment records found
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($payments->hasPages())
                    <div class="p-3 border-top">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                @if ($payments->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">&laquo;</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $payments->previousPageUrl() }}" rel="prev">&laquo;</a>
                                    </li>
                                @endif

                                @foreach ($payments->getUrlRange(1, $payments->lastPage()) as $page => $url)
                                    @if ($page == $payments->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                @if ($payments->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $payments->nextPageUrl() }}" rel="next">&raquo;</a>
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
</x-app-layout>